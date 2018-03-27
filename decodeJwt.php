<?php

require __DIR__.'/vendor/autoload.php';

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Keychain; // just to make our life simpler
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Parser;

$keychain = new Keychain();

$signer = new Sha256();


$token = (new Parser())->parse(file_get_contents(__DIR__.'/token_593075af6c8c7')); // Parses from a string
$token->getHeaders(); // Retrieves the token header
$token->getClaims(); // Retrieves the token claims

echo $token->getHeader('jti'), "\n"; // will print "4f1g23a12aa"
echo $token->getClaim('iss'), "\n"; // will print "http://example.com"
echo $token->getClaim('uid'), "\n"; // will print "1"
echo $token, "\n"; // The string representation of the object is a JWT string (pretty easy, right?)

echo "alog : ".$token->getHeader('alg')." => ". $signer->getAlgorithmId()."\n";

var_dump($token->verify($signer, new Key('file://'.__DIR__.'/public.pem')));
echo "\n";
