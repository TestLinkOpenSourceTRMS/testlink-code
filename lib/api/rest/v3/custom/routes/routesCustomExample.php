<?php
/**
 * @filesource  routesCustomExample.php
 *
 *
 */
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;


return static function (App $app): void {
  $app->get('/CustomExample/whoAmI',
            array($app->restApiCustomExample,'whoAmI'));

};
