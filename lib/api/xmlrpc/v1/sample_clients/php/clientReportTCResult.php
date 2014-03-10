<?php
 /**
 * A sample client implementation in php
 * 
 * @author    Asiel Brumfield <asielb@users.sourceforge.net>
 * @package   TestlinkAPI
 * @link      http://testlink.org/api/
 *
 * rev: 20080306 - franciscom - added dBug to improve diagnostic info.
 *      20080305 - franciscom - refactored
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
$unitTestDescription="Test - Call with valid parameters: testPlanID,testCaseID,buildID";
// $testPlanID=189;
$testPlanID=190;
// $testCaseExternalID='API-71';  // 'API-69'
// $testCaseExternalID='AF-66';
$testCaseExternalID='AF-1';

$testCaseID=null;
// $buildID=21;
$buildID=4;

$status=$tcaseStatusCode['blocked'];
//$status=$tcaseStatusCode['failed'];
//$status=$tcaseStatusCode['passed'];

$exec_notes="Call using all EXTERNAL ID ({$testCaseExternalID}) - status={$status}";
//$platformName='NO PLATFORM LINKED';
$platformName='Solaris 9';
$overwrite=true;

$debug=false;
echo $unitTestDescription;
$devKey = isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$response = reportResult($devKey,$server_url,$testCaseID,$testCaseExternalID,$testPlanID,
                         $buildID,null,$status,$exec_notes,$bug_id,$customfields,
                         $platformName,$overwrite,$debug);

echo "<br> Result was: ";
// Typically you'd want to validate the result here and probably do something more useful with it
// print_r($response);
new dBug($response);
echo "<br>";


// Substitute for tcid and tpid that apply to your project
// $unitTestDescription="Test - Call with valid parameters: testPlanID,testCaseID,buildID";
// $testPlanID=446;
// $testCaseExternalID='AA-1';
// $testCaseID=null;
// $buildID=2;
// // $status=$tcaseStatusCode['departed'];
// $status=$tcaseStatusCode['blocked'];
// // $status=$tcaseStatusCode['wrong'];
// // $exec_notes="Call using all INTERNAL ID's ({$testCaseID}) - status={$status}";
// $exec_notes="Call using all EXTERNAL ID ({$testCaseExternalID}) - status={$status}";
// $bug_id='999FF';
// $customfields=array('CF_EXE1' => 'COMODORE64','CF_DT' => mktime(10,10,0,7,29,2009));
// 
// $debug=false;
// echo $unitTestDescription;
// $response = reportResult($server_url,$testCaseID,$testCaseExternalID,$testPlanID,
//                          $buildID,null,$status,$exec_notes,$bug_id,$customfields,$debug);
// 
// echo "<br> Result was: ";
// // Typically you'd want to validate the result here and probably do something more useful with it
// // print_r($response);
// new dBug($response);
// echo "<br>";



/*
  function: 

  args:
  
  returns: 

*/
function reportResult($devKey,$server_url,$tcaseid=null, $tcaseexternalid=null,$tplanid, $buildid=null, 
                      $buildname=null, $status,$notes=null,$bugid=null,$customfields=null,
                      $platformname=null,$overwrite=false,$debug=false)
{

  $client = new IXR_Client($server_url);
 
  $client->debug=$debug;
  
  $data = array();
  $data["devKey"] = $devKey;
  $data["testplanid"] = $tplanid;

  if( !is_null($bugid) )
  {
      $data["bugid"] = $bugid;  
  }

  if( !is_null($tcaseid) )
  {
      $data["testcaseid"] = $tcaseid;
  }
  else if( !is_null($tcaseexternalid) )
  {
      $data["testcaseexternalid"] = $tcaseexternalid;
  }
  
  if( !is_null($buildid) )
  {
      $data["buildid"] = $buildid;
  }
  else if ( !is_null($buildname) )
  {
        $data["buildname"] = $buildname;
  }
  
  if( !is_null($notes) )
  {
     $data["notes"] = $notes;  
  }
  $data["status"] = $status;

    if( !is_null($customfields) )
    {
       $data["customfields"]=$customfields;
    }
    
    if( !is_null($platformname) )
    {
       $data["platformname"]=$platformname;
    }
    
    if( !is_null($overwrite) )
    {
       $data["overwrite"]=$overwrite;
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