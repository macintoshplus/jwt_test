#!/usr/bin/env php
<?php
/**
 * Test the JWT Token
 * @copyright 2017-2018 Jean-Baptiste Nahan
 * @license MIT
 */

require __DIR__.'/vendor/autoload.php';

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key;

if (php_sapi_name() !== 'cli') {
    echo "This file can be used only in cli sapi.";
    exit(255);
}

function displayHelp()
{
    global $argv;
    echo "Usage : ".$argv[0].' <priv_key_file> <public_key_file>'."\n";
}


if (count($argv) < 2) {
    displayHelp();
    exit(1);
}

$privkeyFile = $argv[1];

$publicKeyFile = $argv[2];

if (!file_exists($privkeyFile)) {
    echo "Private key file is not found : ".$privkeyFile."\n\n";
    displayHelp();
    exit(1);
}


if (!file_exists($publicKeyFile)) {
    echo "Public key file is not found : ".$publicKeyFile."\n\n";
    displayHelp();
    exit(1);
}

$qh = new \Symfony\Component\Console\Helper\QuestionHelper();
$q = new \Symfony\Component\Console\Question\Question("Type the pass phrase for the private key \"".$privkeyFile."\":\n");
$q->setHidden(true);
$q->setMaxAttempts(3);
$output = new \Symfony\Component\Console\Output\ConsoleOutput();

$input = new \Symfony\Component\Console\Input\ArgvInput();

$secret = $qh->ask($input, $output, $q);

$q = new \Symfony\Component\Console\Question\Question("Type the Issuer : \n");
$issuer = $qh->ask($input, $output, $q);

$q = new \Symfony\Component\Console\Question\Question("Type the Audience : \n");
$audience = $qh->ask($input, $output, $q);

$q = new \Symfony\Component\Console\Question\Question("Type the Id : \n", '4f1g23a12aa');
$jwt_id = $qh->ask($input, $output, $q);

$q = new \Symfony\Component\Console\Question\Question("Type the Uid : \n", '1');
$jwt_uid = $qh->ask($input, $output, $q);


$q = new \Symfony\Component\Console\Question\Question("Time to live (in second) : \n", 3600);
$ttl = intval($qh->ask($input, $output, $q));


$key = new Key('file://'.$privkeyFile, $secret);
$pubKey = new Key('file://'.$publicKeyFile);

$signer = new Sha256();

$token = (new Builder())->setIssuer($issuer) // Configures the issuer (iss claim)
                        ->setAudience($audience) // Configures the audience (aud claim)
                        ->setId($jwt_id, true) // Configures the id (jti claim), replicating as a header item
                        ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
                        ->setNotBefore(time() + 60) // Configures the time that the token can be used (nbf claim)
                        ->setExpiration(time() + $ttl) // Configures the expiration time of the token (nbf claim)
                        ->set('uid', $jwt_uid) // Configures a new claim, called "uid"
                        ->sign($signer, $key)
                        ->getToken(); // Retrieves the generated token
$file = __DIR__.'/token_'.uniqid();
file_put_contents($file, (string) $token);
echo "Token written in '".$file."'\n";

$token->getHeaders(); // Retrieves the token headers
$token->getClaims(); // Retrieves the token claims

echo "jti: ".$token->getHeader('jti'), "\n"; // will print "4f1g23a12aa"
echo "iss: ".$token->getClaim('iss'), "\n"; // will print "http://example.com"
echo "uid: ".$token->getClaim('uid'), "\n"; // will print "1"
echo "Token: ".$token, "\n"; // The string representation of the object is a JWT string (pretty easy, right?)

echo "Result of verify: ";
var_dump($token->verify($signer, $pubKey));
echo "\n";
