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
        // Read part from header
        $element = explode(' ', $authorization);
        // Parse the token
        $token = (new Parser())->parse($element[1]);
        // Set the token into the request for use into the controller
        $request->attributes->set('jwt', $token);

        // Return the credential
        return [
            'username' => $token->getClaim('iss'),
            'token' => $token,
        ];
    }

    /**
     * Return the user from credential read into getCredentials
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials['username']);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $signer = new Sha256();
        $token = $credentials['token'];
        // Check if the token signature is valid.
        if (!$token->verify($signer, new Key($user->getPassword()))) {
            $this->logger->info('Token invalid signature', ['iss'=>$token->getClaim('iss')]);
            throw new AuthenticationException("Bad token", 403);
        }
        // Valid the token data.
        $data = new ValidationData();
        // Set the audience : URL of API service
        $data->setAudience('http://127.0.0.1:8000');
        // Check if datas is valid. Check date "not before", "not after", "created at"
        if (!$token->validate($data)) {
            $this->logger->info('Token invalid datas', ['iss'=>$token->getClaim('iss')]);
            throw new AuthenticationException("Bad token", 403);
        }
        // If all check is OK.
        $this->logger->info('Allowed access by JWT token', ['iss'=>$token->getClaim('iss')]);
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, 403);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'message' => 'Authentication Required',
        ];

        return new JsonResponse($data, 401);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
