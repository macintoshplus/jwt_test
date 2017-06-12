<?php

require __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\key;
use Lcobucci\JWT\Builder;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

$key = new Key('file://'.__DIR__.'/../privkey.pem', 'toto');

$signer = new Sha256();

$token = (new Builder())->setIssuer('localhost1') // Configures the issuer (iss claim)
                        ->setAudience('http://127.0.0.1:8000') // Configures the audience (aud claim)
                        ->setId('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
                        ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
                        ->setNotBefore(time()) // Configures the time that the token can be used (nbf claim)
                        ->setExpiration(time() + 10) // Configures the expiration time of the token (nbf claim)
                        ->set('uid', 1) // Configures a new claim, called "uid"
                        ->sign($signer, $key)
                        ->getToken(); // Retrieves the generated token

$client = new \GuzzleHttp\Client(['http_errors'=>false]);

$response = $client->get('http://127.0.0.1:8000/api/list', ['headers'=>['Accept'=>'application/json', 'Authorization'=>sprintf('Bearer %s', (string) $token)]]);
if ($response->getStatusCode() == 200) {
    echo "OK : ", $response->getBody(),"\n";
} else {
    echo "KO : ", $response->getStatusCode(), " : ",$response->getBody(),"\n";
}

$name = 'test '.uniqid();
$token = (new Builder())->setIssuer('localhost') // Configures the issuer (iss claim)
                        ->setAudience('http://127.0.0.1:8000') // Configures the audience (aud claim)
                        ->setId('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
                        ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
                        ->setNotBefore(time()) // Configures the time that the token can be used (nbf claim)
                        ->setExpiration(time() + 10) // Configures the expiration time of the token (nbf claim)
                        ->set('name', $name) // Configures a new claim, called "uid"
                        ->sign($signer, $key)
                        ->getToken(); // Retrieves the generated token

$response = $client->get('http://127.0.0.1:8000/api/add/'.$name, ['headers'=>['Accept'=>'application/json', 'Authorization'=>sprintf('Bearer %s', (string) $token)]]);
if ($response->getStatusCode() == 200) {
    echo "OK : ", $response->getBody(),"\n";
} else {
    echo "KO : ", $response->getStatusCode(), " : ",$response->getBody(),"\n";
}


$token = (new Builder())->setIssuer('localhost') // Configures the issuer (iss claim)
                        ->setAudience('http://127.0.0.1:8000') // Configures the audience (aud claim)
                        ->setId('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
                        ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
                        ->setNotBefore(time()) // Configures the time that the token can be used (nbf claim)
                        ->setExpiration(time() + 10) // Configures the expiration time of the token (nbf claim)
                        ->set('uid', 1) // Configures a new claim, called "uid"
                        ->sign($signer, $key)
                        ->getToken(); // Retrieves the generated token

$response = $client->get('http://127.0.0.1:8000/api/list', ['headers'=>['Accept'=>'application/json', 'Authorization'=>sprintf('Bearer %s', (string) $token)]]);
if ($response->getStatusCode() == 200) {
    echo "OK : ", $response->getBody(),"\n";
}
