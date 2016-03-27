<?php

require 'vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


date_default_timezone_set('America/Los_angeles');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// $log = new Logger('name');
// $log->pushHandler(new Monolog\Handler\StreamHandler('app.log', Logger::WARNING));
// $log->addWarning('Zoinks');

// Create and configure Slim app
$configuration = [
  'settings' => [
      'displayErrorDetails' => true,
  ],
];
// Get container
$container = $app->getContainer();

// Register componet on container
$container['view'] = function ($container) {
  $view = new \Slim\Views\Twig('templates', [
    'cache' => false
  ]);
  $view->addExtension(new \Slim\Views\TwigExtension(
    $container['router'],
    $container['request']->getUri()
  ));

  return $view;
};

// Creat App
$app = new \Slim\App($container);

// Render Twig template in route
$app->get('/', function ($request, $response, $args) {
  return $this->view->render($response, 'index.html', [
    'name' => $args['name']
  ]);
})->setName('index');

$app->get('/contact', function ($request, $response, $args) {
  return $this->view->render($response, 'contact.html', [
    'name' => $args['name']
  ]);
})->setName('contact');

// Run app
$app->run();
