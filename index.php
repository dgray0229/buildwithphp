<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require "vendor/autoload.php";

date_default_timezone_set("America/Los_angeles");

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Configure App Settings

$configuration = [
    "settings" => [
        "displayErrorDetails" => true,
    ],
];

$c = new \Slim\Container($configuration);

// Create App
$app = new \Slim\App($c);

// Start your session
session_start();

// Get container
$container = $app->getContainer();

// Register component on container
$container["view"] = function ($c) {
    $view = new \Slim\Views\Twig("templates", [
        "cache" => false
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace("index.php", "", $c["request"]->getUri()->getBasePath()), "/");
    $view->addExtension(new Slim\Views\TwigExtension($c["router"], $basePath));

    return $view;
};
// Register Provider
$container["flash"] = function ($c) {
  return new \Slim\Flash\Messages();
};

// Add middleware
$app->add(function ($request, $response, $next) {
    $this->view->offsetSet('flash', $this->flash);
    return $next($request, $response);
});

// Render Twig template in route
$app->get("/", function ($request, $response, $args) {
  return $this->view->render($response, "about.twig", [
    "name" => $args["name"]
  ]);
})->setName("about");

$app->get("/contact", function ($request, $response, $args) {
  return $this->view->render($response, "contact.twig", [
    "name" => $args["name"]
  ]);
})->setName("contact");

$app->post("/contact", function ($request, $response, $args) {

  // Form Variables
  $body = $this->request->getParsedBody();
  $name = $body["name"];
  $email = $body["email"];
  $message = $body["msg"];
  $uri = $request->getUri();
  $basePath = $body["index"];

  // Swiftmailer setup

  // Instantiate the class
  if (empty($cleanName) || empty($cleanEmail) || empty($cleanMessage)) {
    return $this->response->withStatus(302)->withHeader("Location", "/contact");
  } else {
    $cleanName = filter_var($name, FILTER_SANITIZE_STRING);
    $cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
    $cleanMessage = filter_var($message, FILTER_SANITIZE_STRING);
  } // End if empty else !empty statement

  // Create the Transport
  $transport = Swift_MailTransport::newInstance();

  // Create the Mailer using your created Transport
  $mailer = \Swift_Mailer::newInstance($transport);
  // Create a message
  $swiftMessage = Swift_Message::newInstance()
    ->setSubject("Thank you for your input")
    ->setFrom(array($cleanEmail => $cleanName))
    ->setTo(array("#", "devin.gray92@gmail.com" => "Devin Gray"))
    ->setBody($cleanMessage);

  // Send the message
  $result = $mailer->send($swiftMessage);

  // After the message sends
  if($result > 0) {
    // Say thank you
    $this->flash->addMessage("success", "Your message has been sent. Thank you!");
    return $this->response->withStatus(200)->withHeader("Location", "/contact");
  } else {
    // Send message to user that message failed to send
    // Lod that there was an error_log
    $this->flash->addMessage("fail", "I'm sorry. There was an issue sending your mail. Please try again.");
    return $this->response->withStatus(301)->withHeader("Location", "/contact");
  }
  $app->get('/contact', function ($request, $response, $args) {
      // Get flash messages from previous request
      $messages = $this->flash->getMessages();
      print_r($messages);
  });
}); // End of post submission

// Run app
$app->run();
