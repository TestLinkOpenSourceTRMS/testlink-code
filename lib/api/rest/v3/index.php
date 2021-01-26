<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Factory\AppFactory;

require_once('../../../../config.inc.php');
require 'autoload.php';
require 'RestApi.class.php';

$app = AppFactory::create();
$basePath = "/lib/api/rest/v3";
$app->setBasePath($basePath);
$app->restApi = new RestApi();


/**
 * Load Custom API
 *
 */
clearstatcache();
$APIExtensions = [];
$where = './custom/api/';
if (is_dir($where)) {
  $itera = new DirectoryIterator($where);
  foreach ($itera as $fileinfo) {
    if ($fileinfo->isFile()) {
      $who = $fileinfo->getFilename();
      require ($where . $who);

      // generate class name
      $className = str_replace('.class.php', '', $who);
      $APIExtensions[$className] = $className;
    }
  }
  
  // Register Custom API
  foreach ($APIExtensions as $class) {
    $instance = lcfirst($class);
    $app->$instance = new $class();
  }
}
// -----------------------------------------------------------


// Register Standard routes
$routes = require './core/routes.php';
$routes($app);

// Register CUSTOM routes
clearstatcache();
$where = './custom/routes/';
if (is_dir($where)) {
  $itera = new DirectoryIterator($where);
  foreach ($itera as $fileinfo) {
    if ($fileinfo->isFile()) {
      $who = $fileinfo->getFilename();
      $routes = require ($where . $who);
      $routes($app);
    }
  }
}

// Middleware
$app->add(array($app->restApi,'authenticate'));

// https://stackoverflow.com/questions/37255635/
// php-slim-framework-v3-set-
//     global-content-type-for-responses/37255946
$app->add(array($app->restApi,'setContentTypeJSON'));

$app->run();
