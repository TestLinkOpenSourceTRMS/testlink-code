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

  // SPA (ui/) endpoints
  $app->post('/auth/login', array($app->restApi,'authLogin'));
  $app->get('/testprojects/{id}/suites',
            array($app->restApi,'getProjectSuites'));
  $app->get('/testsuites/{id}/testcases',
            array($app->restApi,'getSuiteTestCases'));
  $app->get('/testcases/{id}/detail',
            array($app->restApi,'getTestCaseDetail'));
  $app->get('/testplans/{id}/summary',
            array($app->restApi,'getPlanSummary'));
  $app->get('/testplans/{id}/trend',
            array($app->restApi,'getPlanTrend'));
  $app->get('/testplans/{id}/flaky',
            array($app->restApi,'getPlanFlaky'));
  $app->get('/testplans/{id}/queue',
            array($app->restApi,'getPlanQueue'));
  $app->get('/testplans/{id}/buildsById',
            array($app->restApi,'getPlanBuildsById'));
  $app->get('/testplans/{id}/matrix',
            array($app->restApi,'getPlanMatrix'));
  $app->get('/testplans/{id}/matrixBySuite',
            array($app->restApi,'getPlanMatrixBySuite'));
  $app->put('/testcases/{id}/update',
            array($app->restApi,'updateTestCase'));
  $app->post('/testplans/{id}/link',
             array($app->restApi,'linkPlanCases'));
  $app->get('/users',
            array($app->restApi,'getUsers'));
  $app->post('/users',
             array($app->restApi,'createUser'));
  $app->put('/users/{id}',
            array($app->restApi,'updateUser'));
  $app->get('/testprojects/{id}/keywords',
            array($app->restApi,'getProjectKeywords'));
  $app->delete('/keywords/{id}',
               array($app->restApi,'deleteKeyword'));
  $app->get('/testprojects/{id}/platforms',
            array($app->restApi,'getProjectPlatforms'));
  $app->post('/platforms',
             array($app->restApi,'createPlatform'));
  $app->get('/testprojects/{id}/customfields',
            array($app->restApi,'getProjectCustomFields'));
  $app->get('/testprojects/{id}/search',
            array($app->restApi,'searchTestCases'));
  $app->post('/executions/{id}/attachments',
             array($app->restApi,'uploadExecutionAttachment'));
  $app->get('/executions/{id}/attachments',
            array($app->restApi,'getExecutionAttachments'));
  $app->get('/attachments/{id}',
            array($app->restApi,'downloadAttachment'));
  $app->post('/testplans/{id}/assign',
             array($app->restApi,'assignPlanCases'));
  $app->get('/testplans/{id}/byTester',
            array($app->restApi,'getPlanByTester'));
  $app->get('/testplans/{id}/byBuild',
            array($app->restApi,'getPlanByBuild'));
  $app->get('/testprojects/{id}/reqspecs',
            array($app->restApi,'getProjectReqSpecs'));
  $app->post('/reqspecs',
             array($app->restApi,'createReqSpec'));
  $app->get('/reqspecs/{id}/requirements',
            array($app->restApi,'getReqSpecRequirements'));
  $app->post('/requirements',
             array($app->restApi,'createRequirement'));
  $app->get('/requirements/{id}/detail',
            array($app->restApi,'getRequirementDetail'));
  $app->post('/requirements/{id}/coverage',
             array($app->restApi,'addRequirementCoverage'));
  $app->delete('/requirements/{id}/coverage/{tcaseID}',
               array($app->restApi,'deleteRequirementCoverage'));
  $app->get('/testplans/{id}/reqCoverage',
            array($app->restApi,'getPlanReqCoverage'));

  $app->get('/testprojects',
            array($app->restApi,'testprojects'));
  $app->get('/testprojects/{id}',
            array($app->restApi,'testprojects'));
  
  $app->get('/testprojects/{mixedID}/testcases',
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

};
