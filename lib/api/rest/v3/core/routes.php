<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;


return function (App $app) {

  // using array(), was the way in Slim3 and 
  // still seems valid
  $app->get('/whoAmI',array($app->restApi,'whoAmI'));

  $app->get('/testprojects',
            array($app->restApi,'testprojects'));
  $app->get('/testprojects/{id}',
            array($app->restApi,'testprojects'));
  
  $app->get('/testprojects/{id}/testcases',
            array($app->restApi,'getProjectTestCases'));
  $app->get('/testprojects/{id}/testplans', 
            array($app->restApi,'getProjectTestPlans'));

  $app->get('/testplans/{tplanApiKey}/builds', 
            array($app->restApi,'getPlanBuilds'));

  /*
  $app->get('/builds/{id}', 
            array($app->restApi,'getBuild'));
  */

  $app->post('/executions', 
             array($app->restApi,'createTestCaseExecution'));

  $app->post('/builds',
             array($app->restApi,'createBuild'));

  $app->post('/keywords', 
             array($app->restApi,'createKeyword'));

  $app->post('/testcases', 
             array($app->restApi,'createTestCase'));

  $app->post('/testplans', 
             array($app->restApi,'createTestPlan'));

  $app->post('/testprojects',
            array($app->restApi,'createTestProject'));
  
  $app->post('/testsuites', 
             array($app->restApi,'createTestSuite'));


  // Update Routes
  // Following advice from
  // https://restfulapi.net/rest-put-vs-post/
  //
  $app->put('/builds/{id}', 
             array($app->restApi,'updateBuild'));

  $app->put('/testplans/{id}', 
             array($app->restApi,'updateTestPlan'));

  $app->put('/testplans/{tplan_id}/platforms',
             array($app->restApi,'addPlatformsToTestPlan'));

};
