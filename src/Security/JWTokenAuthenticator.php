<?php

namespace JWT\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\key;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;

class JWTokenAuthenticator extends AbstractGuardAuthenticator
{
    private $logger;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getCredentials(Request $request)
    {
        // Checks if the credential header is provided
        if (!$authorization = $request->headers->get('Authorization')) {
            $this->logger->warning('No Authorization header');
            return;
        }
        $element = explode(' ', $authorization);
        $token = (new Parser())->parse($element[1]);
        $request->attributes->set('jwt', $token);

        return array(
            'username' => $token->getClaim('iss'),
            'token' => $token,
        );
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials['username']);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // return true to cause authentication success
        
        $signer = new Sha256();
        $token = $credentials['token'];

        if (!$token->verify($signer, new Key($user->getPassword()))) {
            $this->logger->info('Token invalid signature', ['iss'=>$token->getClaim('iss')]);
            throw new AuthenticationException("Bad token", 1);
        }
        $data = new ValidationData();
        $data->setAudience('http://127.0.0.1:8000');
        
        if (!$token->validate($data)) {
            $this->logger->info('Token invalid datas', ['iss'=>$token->getClaim('iss')]);
            throw new AuthenticationException("Bad token", 1);
        }

        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        );

        return new JsonResponse($data, 403);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            // you might translate this message
            'message' => 'Authentication Required',
        );

        return new JsonResponse($data, 401);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
