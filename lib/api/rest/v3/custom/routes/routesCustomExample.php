<?php
/**
 * @filesource  routesCustomExample.php
 *
 *
 */
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;


return function (App $app) {
  $app->get('/CustomExample/whoAmI',
            array($app->restApiCustomExample,'whoAmI'));

};