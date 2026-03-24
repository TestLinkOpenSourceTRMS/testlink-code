<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;


return function (App $app) {

  // @20201124 - I do not understand this
  // $app->get('/',World::class . ':hello');

  // using array(), was the way in Slim3 and 
  // still seems valid
  $app->get('/whoAmI',array($app->restApi,'whoAmI'));

  $app->get('/testprojects',
            array($app->restApi,'testprojects'));
  $app->get('/testprojects/{id}',
            array($app->restApi,'testprojects'));
  
  $app->get('/testprojects/{id}/testcases',
            array($app->restApi,'getProjectTestCases'));
  $app->get('/testprojects/{mixedID}/testplans', 
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

  // Vue SPA 용 확장 라우트
  $app->get('/testprojects/{id}/testsuites',
            array($app->restApi,'getProjectTestSuites'));

  $app->get('/testprojects/{id}/dashboard',
            array($app->restApi,'getProjectDashboard'));

  $app->get('/testprojects/{id}/milestones',
            array($app->restApi,'getProjectMilestones'));

  $app->post('/testprojects/{id}/milestones',
             array($app->restApi,'createMilestone'));

  $app->get('/testsuites/{id}/testcases',
            array($app->restApi,'getSuiteTestCases'));

  $app->get('/testcases/{id}',
            array($app->restApi,'getTestCaseDetail'));

  $app->get('/testplans/{id}/executions',
            array($app->restApi,'getPlanExecutions'));

  $app->get('/testplans/{id}/progress',
            array($app->restApi,'getPlanProgress'));

  $app->get('/milestones/{id}',
            array($app->restApi,'getMilestone'));

  $app->put('/milestones/{id}',
            array($app->restApi,'updateMilestone'));

  $app->delete('/milestones/{id}',
               array($app->restApi,'deleteMilestone'));

};
