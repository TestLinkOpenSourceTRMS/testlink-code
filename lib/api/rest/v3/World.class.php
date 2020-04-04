<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class World
{
  
  /**
   */
  public function __construct() {
  }

  /**
   *
   */
  function hello(Request $request, Response $response, $args) 
  {
    $msg = "Hello world! Using: " . __METHOD__ . "()";
    $response->getBody()->write($msg);
    return $response;
  }


} // class end
