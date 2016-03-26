<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
date_default_timezone_set('America/Los_angeles');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


// $log = new Logger('name');
// $log->pushHandler(new Monolog\Handler\StreamHandler('app.log', Logger::WARNING));
// $log->addWarning('Zoinks');

$app = new \Slim\App(["settings" => $config]);
$app->get('/', function(){
  echo 'Hello, this is the home page.';
});

$app->get('/contact', function(){
  echo 'Feel free to contact us.';
});

$app->run();
