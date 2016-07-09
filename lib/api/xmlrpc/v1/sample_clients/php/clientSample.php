<?php
 /**
 * A sample client implementation in php
 * 
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net>
 * @package 	TestlinkAPI
 * @link      http://testlink.org/api/
 *
 * rev: 20080306 - franciscom - added dBug to improve diagnostic info.
 *      20080305 - franciscom - refactored
 */
 
require_once 'util.php';
require_once 'sample.inc.php';

$tcaseStatusCode['passed']='p';
$tcaseStatusCode['blocked']='b';
$tcaseStatusCode['failed']='f';
$tcaseStatusCode['wrong']='w';
$tcaseStatusCode['departed']='d';



// Substitute for tcid and tpid that apply to your project
$unitTestDescription="Test - Call with valid parameters: testPlanID,testCaseID,buildID";
$testPlanID=222;
// $testCaseID=185;
$testCaseID=58;
$testCaseExternalID=null;
$buildID=15;
// $status=$tcaseStatusCode['departed'];
$status=$tcaseStatusCode['blocked'];
// $status=$tcaseStatusCode['wrong'];
$exec_notes="Call using all INTERNAL ID's ({$testCaseID}) - status={$status}";
$bug_id='999FF';

$debug=false;
echo $unitTestDescription;
$response = reportResult($server_url,$testCaseID,$testCaseExternalID,$testPlanID,
                         $buildID,null,$status,$exec_notes,$bug_id,$debug);

echo "<br> Result was: ";
// Typically you'd want to validate the result here and probably do something more useful with it
// print_r($response);
new dBug($response);
echo "<br>";
// 
// 
// // Now do a wrong build call
// $unitTestDescription="Test - Call with at least one NON EXISTENT parameters: testPlanID,testCaseID,buildID";
// $testPlanID=95;
// $testCaseID=86;
// $testCaseExternalID=null;
// $buildID=50;
// $exec_notes="";
// 
// //$debug=true;
// $debug=false;
// echo $unitTestDescription;
// $response = reportResult($server_url,$testCaseID,$testCaseExternalID,$testPlanID,
//                          $buildID,null,$tcaseStatusCode['passed'],$exec_notes,$bug_id,$debug);
// 
// echo "<br> Result was: ";
// new dBug($response);
// echo "<br>";
// 
// // ----------------------------------------------------------------------------------------
// // Now do a build name call
// $unitTestDescription="Test - Call with valid parameters: testPlanID,testCaseID,buildName";
// $testPlanID=95;
// $testCaseID=83;
// $testCaseExternalID='';
// $buildName="Spock";
// $exec_notes="Call using all Build by name ({$testCaseID})";
// 
// //$debug=true;
// $debug=false;
// echo $unitTestDescription;
// $response = reportResult($server_url,$testCaseID,$testCaseExternalID,$testPlanID,null,
//                          $buildName,$tcaseStatusCode['blocked'],$exec_notes,$bug_id,$debug);
// 
// echo "<br> Result was: ";
// new dBug($response);
// echo "<br>";
// // ----------------------------------------------------------------------------------------
// 
// 
// // Now do a build name call
// $unitTestDescription="Test - Call with valid parameters: testPlanID,testCaseExternalID,buildName";
// $testPlanID=95;
// $testCaseID=null;
// $testCaseExternalID='ESP-3';
// $buildName="Spock";
// // $exec_notes="Call using Test Case External ID and Build by Name";
// $exec_notes=null;
// 
// //$debug=true;
// $debug=false;
// echo $unitTestDescription;
// $response = reportResult($server_url,$testCaseID,$testCaseExternalID,$testPlanID,null,
//                          $buildName,$tcaseStatusCode['failed'],$exec_notes,$bug_id,$debug);
// 
// echo "<br> Result was: ";
// new dBug($response);
// echo "<br>";



/*
  function: 

  args:
  
  returns: 

*/
function reportResult($server_url,$tcaseid=null, $tcaseexternalid=null,$tplanid, $buildid=null, 
                      $buildname=null, $status,$notes=null,$bugid=null,$debug=false)
{

	$client = new IXR_Client($server_url);
 
  $client->debug=$debug;
  
	$data = array();
	$data["devKey"] = constant("DEV_KEY");
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


?>