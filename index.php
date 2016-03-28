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

// Configure App Settings

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$c = new \Slim\Container($configuration);

// Create App
$app = new \Slim\App($c);

// Get container
$container = $app->getContainer();

// Register component on container
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig('templates', [
        'cache' => false
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));

    return $view;
};

// Render Twig template in route
$app->get('/', function ($request, $response, $args) {
  return $this->view->render($response, 'about.twig', [
    'name' => $args['name']
  ]);
})->setName('about');

$app->get('/{name}', function ($request, $response, $args) {
  return $this->view->render($response, 'contact.twig', [
    'name' => $args['name']
  ]);
})->setName('contact');

// Run app
$app->run();
