<?php
 /**
 * A sample client implementation in php
 * 
 * @author    Francisco Mancardi
 * @package   TestlinkAPI
 * @link      http://testlink.org/api/
 *
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$tcaseStatusCode['passed']='p';
$tcaseStatusCode['blocked']='b';
$tcaseStatusCode['failed']='f';
$tcaseStatusCode['wrong']='w';
$tcaseStatusCode['departed']='d';

// Substitute for tcid and tpid that apply to your project
$unitTestDescription="Test - Call with valid parameters: testPlanID,testCaseID,buildID,userID";

$context = new stdClass();
$context->testplanid=279348;
// $context->buildid=1;
$context->buildname='2.0';
$context->platformname='PLAT01';
$context->testcaseexternalid='ZQ-3';
// $context->testcaseid=null;

$exec = new stdClass();
$exec->status = $tcaseStatusCode['blocked'];
$exec->notes="Call using all EXTERNAL ID ({$context->testcaseexternalid}) - status={$exec->status}";
$exec->customfields = null;
$exec->bugid = null;
$exec->timestamp = '2015-09-07 12:00:00';
$exec->overwrite=false;

$debug=false;
echo $unitTestDescription;
$response = executeTestCase($server_url,$context,$exec,$debug);

echo "<br> Result was: ";
new dBug($response);
echo "<br>";



/*
  function: 

  args:
  
  returns: 

*/
function executeTestCase($server_url,$context,$exec,$debug=false)
{

  new dBug($context);
  new dBug($exec);
  
  $client = new IXR_Client($server_url);
  $client->debug=$debug;
  
  $data = array();
  $data["devKey"] = 'admin';
  $data["status"] = $exec->status;

  if( property_exists($exec, 'user') &&  !is_null($exec->user) )
  {
    $data["user"]=$exec->user;
  }

  if( property_exists($exec, 'notes') && !is_null($exec->notes) )
  {
     $data["notes"] = $exec->notes;  
  }

  if( property_exists($exec, 'bugid') && !is_null($exec->bugid) ) 
  {
    $data["bugid"] = $exec->bugid;  
  }

  if( property_exists($exec, 'overwrite') && !is_null($exec->overwrite) ) 
  {
    $data["overwrite"]=$exec->overwrite;
  }

  if( property_exists($exec, 'customfields') && !is_null($exec->customfields) ) 
  {
    $data["customfields"]=$customfields;
  }

  if( property_exists($exec, 'timestamp') &&  !is_null($exec->timestamp) )
  {
    $data["timestamp"]=$exec->timestamp;
  }

  $data["testplanid"] = $context->testplanid;
  if( property_exists($context, 'testcaseid') && !is_null($context->testcaseid) ) 
  {
    $data["testcaseid"] = $context->testcaseid;
  }
  else if( property_exists($context, 'testcaseexternalid') && !is_null($context->testcaseexternalid) )
  {
    $data["testcaseexternalid"] = $context->testcaseexternalid;
  }
  
  if( property_exists($context, 'buildid') &&  !is_null($context->buildid) )
  {
    $data["buildid"] = $context->buildid;
  }
  else if ( property_exists($context, 'buildname') && !is_null($context->buildname) )
  {
    $data["buildname"] = $context->buildname;
  }
  
  if( property_exists($context, 'platformname') &&  !is_null($context->platformname) )
  {
    $data["platformname"]=$context->platformname;
  }
    
  
  new dBug($data);

  if(!$client->query('tl.reportTCResult', $data))
  {
    echo "something went wrong - " . $client->getErrorCode() . " - " . $client->getErrorMessage();      
  }
  else
  {
    return $client->getResponse();
  }
}