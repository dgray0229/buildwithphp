<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require 'vendor/autoload.php';

date_default_timezone_set('America/Los_angeles');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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

$app->get('/contact', function ($request, $response, $args) {
  return $this->view->render($response, 'contact.twig', [
    'name' => $args['name']
  ]);
})->setName('contact');

$app->post('/contact', function ($request, $response, $args) {

  // Form Variables
  $body = $this->request->getParsedBody();
  $name = $body['name'];
  $email = $body['email'];
  $message = $body['msg'];
  $uri = $request->getUri();
  $basePath = $body['index'];
  // Form variables
  $cleanName = filter_var($name, FILTER_SANATIZE_STRING);
  $cleanEmail = filter_var($email, FILTER_SANATIZE_EMAIL);
  $cleanMessage = filter_var($message, FILTER_SANATIZE_STRING);

  // Swiftmailer setup
  // Start a Session
  if (!session_id()) @session_start();

  // Instantiate the class
  $msg = new \Plasticbrain\FlashMessages\FlashMessages();

  if (empty($cleanName) || empty($cleanEmail) || empty($cleanMessage)) {
    //message the user that there was a problem
    // return $response->withHeader('Location', $uri);
    $msg->warning('Please make sure that you have all of the field values filled in correctly.', strval($uri));
    $msg->display();
  }

  // Create the Transport
  $transport = Swift_MailTransport::newInstance();

  // Create the Mailer using your created Transport
  $mailer = \Swift_Mailer::newInstance($transport);
  // Create a message
  $swiftMessage = Swift_Message::newInstance()
    ->setSubject('Thank you for your input')
    ->setFrom(array($cleanEmail => $cleanName))
    ->setTo(array('devin@devingray.me', 'devin.gray92@gmail.com' => 'Devin Gray'))
    ->setBody($cleanMessage);

  // Send the message
  $result = $mailer->send($swiftMessage);

  // Add messages
  // After the message sends
    if ($result > 0) {
      // send a message that says thank you
      // return $response->withHeader('Location', $basePath);
      $msg->success('Your message has been sent. Thank you!', strval($uri));
    } else {
      // send a message to the user that the message failed to send
      // log that there was an error
      $msg->warning('I\'m sorry. There was an issue sending your mail. Please try again.', strval($uri));
    }
    $msg->display();


}); // End of post submission

// Run app
$app->run();
