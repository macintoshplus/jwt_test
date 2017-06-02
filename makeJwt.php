<?php

require __DIR__.'/vendor/autoload.php';

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\key;

$key = new Key('file://'.__DIR__.'/privkey.pem', 'toto');
$pubKey = new Key('file://'.__DIR__.'/public.pem');

$signer = new Sha256();

$token = (new Builder())->setIssuer('http://example.com') // Configures the issuer (iss claim)
                        ->setAudience('http://example.org') // Configures the audience (aud claim)
                        ->setId('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
                        ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
                        ->setNotBefore(time() + 60) // Configures the time that the token can be used (nbf claim)
                        ->setExpiration(time() + 3600) // Configures the expiration time of the token (nbf claim)
                        ->set('uid', 1) // Configures a new claim, called "uid"
                        ->sign($signer, $key)
                        ->getToken(); // Retrieves the generated token

file_put_contents(__DIR__.'/token_'.uniqid(), (string) $token);


$token->getHeaders(); // Retrieves the token headers
$token->getClaims(); // Retrieves the token claims

echo $token->getHeader('jti'), "\n"; // will print "4f1g23a12aa"
echo $token->getClaim('iss'), "\n"; // will print "http://example.com"
echo $token->getClaim('uid'), "\n"; // will print "1"
echo $token, "\n"; // The string representation of the object is a JWT string (pretty easy, right?)

var_dump($token->verify($signer, $pubKey));
echo "\n";
