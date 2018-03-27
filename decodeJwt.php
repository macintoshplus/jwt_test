#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Keychain; // just to make our life simpler
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Parser;

if (php_sapi_name() !== 'cli') {
    echo "This file can be used only in cli sapi.";
    exit(255);
}

function displayHelp()
{
    global $argv;
    echo "Usage : ".$argv[0].' <token_file> <public_key_file>'."\n";
}


if (count($argv) < 2) {
    displayHelp();
    exit(1);
}

$tokenFile = $argv[1];

$publicKeyFile = $argv[2];

if (!file_exists($tokenFile)) {
    echo "Token file is not found : ".$tokenFile."\n\n";
    displayHelp();
    exit(1);
}


if (!file_exists($publicKeyFile)) {
    echo "Public key file is not found : ".$publicKeyFile."\n\n";
    displayHelp();
    exit(1);
}


$keychain = new Keychain();

$signer = new Sha256();


$token = (new Parser())->parse(file_get_contents($tokenFile)); // Parses from a string
$token->getHeaders(); // Retrieves the token header
$token->getClaims(); // Retrieves the token claims

echo "jti: ".$token->getHeader('jti'), "\n"; // will print "4f1g23a12aa"
echo "iss: ".$token->getClaim('iss'), "\n"; // will print "http://example.com"
echo "aud: ".$token->getClaim('aud'), "\n";
echo "uid: ".$token->getClaim('uid'), "\n"; // will print "1"
echo "Token: ".$token, "\n"; // The string representation of the object is a JWT string (pretty easy, right?)

echo "algo: ".$token->getHeader('alg')." => ". $signer->getAlgorithmId()."\n";

echo "Result of verify: ";
var_dump($token->verify($signer, new Key('file://'.$publicKeyFile)));
echo "\n";
