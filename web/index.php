<?php

require __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\key;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;

$app = new Application();
$app->register(new \Silex\Provider\MonologServiceProvider(), ['monolog.logfile' => __DIR__.'/../logs/server.log']);

$clientKeyList = [
    'localhost' => ['pem'=>'file://'.__DIR__.'/../public.pem', 'scope'=>['list', 'add']],
];

$liste = [['name'=> 'element 1'], ['name'=> 'element 2'], ['name'=> 'element 3']];
if (file_exists(__DIR__.'/../datas.json')) {
    $liste = json_decode(file_get_contents(__DIR__.'/../datas.json'), true);
}

$app->get('/api/list', function (Request $request) use (&$liste) {
    return new JsonResponse($liste);
});

$app->get('/api/add/{name}', function (Request $request, $name) use (&$liste) {
    if ($request->attributes->get('jwt')->getClaim('name') != $name) {
        throw new BadRequestHttpException("Invalid Name", 1);
    }
    $data = ['name' => $name];
    $liste[] = $data;
    return new JsonResponse($data);
});

$app->before(function (Request $request, Application $app) use ($clientKeyList) {
    if (! $authorization = $request->headers->get('Authorization')) {
        $app['logger']->info('Token invalid header authorization');
        throw new UnauthorizedHttpException("Bad token", 1);
    }
    $element = explode(' ', $authorization);
    $signer = new Sha256();
    $token = (new Parser())->parse($element[1]);

    if (!$token->verify($signer, new Key($clientKeyList[$token->getClaim('iss')]['pem']))) {
        $app['logger']->info('Token invalid signature', ['iss'=>$token->getClaim('iss')]);
        throw new UnauthorizedHttpException("Bad token", 1);
    }
    $data = new ValidationData();
    $data->setAudience('http://127.0.0.1:8000');
    
    if (!$token->validate($data)) {
        $app['logger']->info('Token invalid datas', ['iss'=>$token->getClaim('iss')]);
        throw new UnauthorizedHttpException("Bad token", 1);
    }
    $app['logger']->info('Token valid', ['iss'=>$token->getClaim('iss')]);
    $request->attributes->set('jwt', $token);
}, Application::EARLY_EVENT);

$app->run();
file_put_contents(__DIR__.'/../datas.json', json_encode($liste));
