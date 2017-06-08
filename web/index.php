<?php

require __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


$app = new Application();
$app->register(new \Silex\Provider\MonologServiceProvider(), ['monolog.logfile' => __DIR__.'/../logs/server.log']);
$app->register(new \Silex\Provider\SecurityServiceProvider(), ['security.firewalls' => [
        'api'=> [
            'pattern' => '/api/',
            'users' => function () use ($app) {
                return $app['userProvider'];
            },
            'guard' => array(
                'authenticators' => array(
                    'app.jwtoken_authenticator'
                ),
            ),
            'stateless'=>true,
            'anonymous' => false,
        ]
    ]]);
$app['clientKeyList'] = [
    'localhost' => ['pem'=>'file://'.__DIR__.'/../public.pem', 'roles'=>['ROLE_LIST', 'ROLE_ADD']],
];
$app['userProvider'] = (function ($app) {
    return new \JWT\Server\UserProvider($app['clientKeyList']);
});

$app['app.jwtoken_authenticator'] = function ($app) {
    return new \JWT\Security\JWTokenAuthenticator($app['logger']);
};

$app['route_class'] = 'JWT\MyRoute';


$liste = [['name'=> 'element 1'], ['name'=> 'element 2'], ['name'=> 'element 3']];
if (file_exists(__DIR__.'/../datas.json')) {
    $liste = json_decode(file_get_contents(__DIR__.'/../datas.json'), true);
}

$app->get('/api/list', function (Request $request) use (&$liste) {
    return new JsonResponse($liste);
})->secure('ROLE_LIST');

$app->get('/api/add/{name}', function (Request $request, $name) use (&$liste) {
    if ($request->attributes->get('jwt')->getClaim('name') != $name) {
        throw new BadRequestHttpException("Invalid Name", 1);
    }
    $data = ['name' => $name];
    $liste[] = $data;
    return new JsonResponse($data);
})->secure('ROLE_ADD');


$app->run();
file_put_contents(__DIR__.'/../datas.json', json_encode($liste));
