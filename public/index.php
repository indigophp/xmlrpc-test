<?php

require __DIR__.'/../vendor/autoload.php';

use fXmlRpc\Client;
use fXmlRpc\Transport\Guzzle4Bridge;
use GuzzleHttp\Client as GuzzleClient;


$app = new Proton\Application;

$app['xmlrpc'] = function() {
    return new Client(null, new Guzzle4Bridge(new GuzzleClient));
};

$app['Controller'] = function() use ($app) {
    return new Controller($app->getContainer());
};

$app->get('/', 'Controller::index');
$app->get('/listMethods', 'Controller::listMethods');
$app->get('/getMethodInfo/{method}', 'Controller::getMethodInfo');
$app->get('/call/{method}', 'Controller::call');

$app->run();
