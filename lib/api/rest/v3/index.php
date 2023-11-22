<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Factory\AppFactory;

require_once('../../../../config.inc.php');
require 'autoload.php';
require 'RestApi.class.php';

$app = AppFactory::create();

// CRITIC 
// This will work if your url to test link 
// is something like
//
// https://testlink.antartic.org/
//
// If your URL is like this
//   https://myserver.ibiza.org/testlink/
// You need to use:
//   $basePath = "/testlink/lib/api/rest/v3";
//
// The standard .htaccess provided with testlink, 
// that is similar to the .htaccess provided by MantisBT
// it's ok!!!
// No need to proceed as detailed in this documentation
// - https://www.slimframework.com/docs/v4/start/web-servers.html 
//   Section: Running in a sub-directory
//
// - https://akrabat.com/running-slim-4-in-a-subdirectory/
//   BUT this is a good example to understand how to configure 
//
$basePath = config_get('restAPI')->basePath;
$app->setBasePath($basePath);

$app->restApi = new RestApi();


/**
 * Load Custom API  - Begin
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

// Register CUSTOM routes
clearstatcache();
$where = './custom/routes/';
if (is_dir($where)) {
  $itera = new DirectoryIterator($where);
  foreach ($itera as $fileinfo) {
    if ($fileinfo->isFile()) {
      $who = $fileinfo->getFilename();
      $customRoutes = require ($where . $who);
      $customRoutes($app);
    }
  }
}
// * Load Custom API  - END

// Register Standard routes
$routes = require './core/routes.php';
$routes($app);

// Middleware
$app->add(array($app->restApi,'authenticate'));

// https://stackoverflow.com/questions/37255635/
// php-slim-framework-v3-set-
//     global-content-type-for-responses/37255946
$app->add(array($app->restApi,'setContentTypeJSON'));

$app->run();
