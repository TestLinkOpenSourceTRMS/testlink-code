<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Factory\AppFactory;

require_once('../../../../config.inc.php');
require 'autoload.php';
require 'RestApi.class.php';

//
$app = AppFactory::create();
$basePath = "/lib/api/rest/v3";
$app->setBasePath($basePath);
$app->restApi = new RestApi();

// Register routes
$routes = require './core/routes.php';
$routes($app);

// Middleware
// $app->add(RestApi::class . ':authenticate');
$app->add(array($app->restApi,'authenticate'));

// https://stackoverflow.com/questions/37255635/
// php-slim-framework-v3-set-
//     global-content-type-for-responses/37255946
$app->add(array($app->restApi,'setContentTypeJSON'));

$app->run();