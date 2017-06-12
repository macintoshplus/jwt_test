<?php

require __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

//Instansite application object
$app = new Application();
$app->register(new \Silex\Provider\MonologServiceProvider(), ['monolog.logfile' => __DIR__.'/../logs/server.log']);
// Configure security
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
//List of allowed issiuer can use the API.
$app['clientKeyList'] = [
    'localhost' => ['pem'=>'file://'.__DIR__.'/../public.pem', 'roles'=>['ROLE_LIST', 'ROLE_ADD']],
];

// Setup custom user provider
$app['userProvider'] = (function ($app) {
    return new \JWT\Server\UserProvider($app['clientKeyList']);
});

// Setup custom Guard Authenticator
$app['app.jwtoken_authenticator'] = function ($app) {
    return new \JWT\Security\JWTokenAuthenticator($app['logger']);
};

// Custom Route class for add trai security
$app['route_class'] = 'JWT\MyRoute';

//Load datas liste
$liste = [['name'=> 'element 1'], ['name'=> 'element 2'], ['name'=> 'element 3']];
if (file_exists(__DIR__.'/../datas.json')) {
    $liste = json_decode(file_get_contents(__DIR__.'/../datas.json'), true);
}
//Configure route with security
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

// Add custom error handler for return response in json format if need.
$app->error(function (\Exception $e, Request $request, $code) {
    if ($request->headers->has('Accept') && false !== strpos($request->headers->get('Accept'), 'application/json')) {
        return new JsonResponse(['message'=>$e->getMessage()], $code);
    }
});
//Run the application
$app->run();
//Save the datas into file
file_put_contents(__DIR__.'/../datas.json', json_encode($liste));
