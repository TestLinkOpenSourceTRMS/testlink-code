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
 
 /** 
  * Need the IXR class for client
  */
define("THIRD_PARTY_CODE","/../../../../third_party");

require_once dirname(__FILE__) . THIRD_PARTY_CODE . '/xml-rpc/class-IXR.php';
require_once dirname(__FILE__) . THIRD_PARTY_CODE . '/dBug/dBug.php';

// substitute your server URL Here
define("SERVER_URL", "http://localhost/w3/tl/tl18/head_20080303/lib/api/xmlrpc.php");

// substitute your Dev Key Here
define("DEV_KEY", "f2a979d533cdd9761434bba60a88e4d8");

$tcaseStatusCode['passed']='p';
$tcaseStatusCode['blocked']='b';
$tcaseStatusCode['failed']='f';

// Substitute for tcid and tpid that apply to your project
$unitTestDescription="Test - Call with valid parameters: testPlanID,testCaseID,buildID";
$testPlanID=95;
$testCaseID=83;
$testCaseExternalID=null;
$buildID=5;
$exec_notes="Call using all INTERNAL ID's ({$testCaseID}) - status= {$tcaseStatusCode['blocked']}";
//$exec_notes=null;

//$debug=true;
$debug=false;
echo $unitTestDescription;
$response = reportResult($testCaseID,$testCaseExternalID,$testPlanID,
                         $buildID,null,$tcaseStatusCode['blocked'],$exec_notes,$debug);

echo "<br> Result was: ";
// Typically you'd want to validate the result here and probably do something more useful with it
// print_r($response);
new dBug($response);
echo "<br>";


// Now do a wrong build call
$unitTestDescription="Test - Call with at least one NON EXISTENT parameters: testPlanID,testCaseID,buildID";
$testPlanID=95;
$testCaseID=86;
$testCaseExternalID=null;
$buildID=50;
$exec_notes="";

//$debug=true;
$debug=false;
echo $unitTestDescription;
$response = reportResult($testCaseID,$testCaseExternalID,$testPlanID,
                         $buildID,null,$tcaseStatusCode['passed'],$exec_notes,$debug);

echo "<br> Result was: ";
new dBug($response);
echo "<br>";

// ----------------------------------------------------------------------------------------
// Now do a build name call
$unitTestDescription="Test - Call with valid parameters: testPlanID,testCaseID,buildName";
$testPlanID=95;
$testCaseID=83;
$testCaseExternalID='';
$buildName="Spock";
$exec_notes="Call using all Build by name ({$testCaseID})";

//$debug=true;
$debug=false;
echo $unitTestDescription;
$response = reportResult($testCaseID,$testCaseExternalID,$testPlanID,null,
                         $buildName,$tcaseStatusCode['blocked'],$exec_notes,$debug);

echo "<br> Result was: ";
new dBug($response);
echo "<br>";
// ----------------------------------------------------------------------------------------


// Now do a build name call
$unitTestDescription="Test - Call with valid parameters: testPlanID,testCaseExternalID,buildName";
$testPlanID=95;
$testCaseID=null;
$testCaseExternalID='ESP-3';
$buildName="Spock";
// $exec_notes="Call using Test Case External ID and Build by Name";
$exec_notes=null;

//$debug=true;
$debug=false;
echo $unitTestDescription;
$response = reportResult($testCaseID,$testCaseExternalID,$testPlanID,null,
                         $buildName,$tcaseStatusCode['failed'],$exec_notes,$debug);

echo "<br> Result was: ";
new dBug($response);
echo "<br>";



/*
  function: 

  args:
  
  returns: 

*/
function reportResult($tcaseid=null, $tcaseexternalid=null,
                      $tplanid, $buildid=null, $buildname=null, $status,$notes=null,$debug=false)
{

	$client = new IXR_Client(SERVER_URL);
 
  $client->debug=$debug;
  
	$data = array();
	$data["devKey"] = constant("DEV_KEY");
	$data["testplanid"] = $tplanid;

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