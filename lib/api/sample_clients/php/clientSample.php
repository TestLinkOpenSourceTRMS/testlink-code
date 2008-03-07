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
$testPlanID=95;
$testCaseID=86;
$buildID=5;

$debug=true;
//$debug=false;
$response = reportResult($testCaseID,$testPlanID,$buildID,null,$tcaseStatusCode['passed'],$debug);

echo "result was: ";
// Typically you'd want to validate the result here and probably do something more useful with it
// print_r($response);
new dBug($response);


// Now do a wrong build call
$testPlanID=95;
$testCaseID=86;
$buildID=50;

$debug=true;
//$debug=false;
$response = reportResult($testCaseID,$testPlanID,$buildID,null,$tcaseStatusCode['passed'],$debug);

echo "result was: ";
// Typically you'd want to validate the result here and probably do something more useful with it
// print_r($response);
new dBug($response);

// Now do a build name call
$testPlanID=95;
$testCaseID=86;
$buildName="Spock";

$debug=true;
//$debug=false;
$response = reportResult($testCaseID,$testPlanID,null,$buildName,$tcaseStatusCode['passed'],$debug);

echo "result was: ";
// Typically you'd want to validate the result here and probably do something more useful with it
// print_r($response);
new dBug($response);


// Now do a build name call
$testPlanID=95;
$testCaseID=86;
$buildName="";

$debug=true;
//$debug=false;
$response = reportResult($testCaseID,$testPlanID,null,$buildName,$tcaseStatusCode['passed'],$debug);

echo "result was: ";
// Typically you'd want to validate the result here and probably do something more useful with it
// print_r($response);
new dBug($response);



/*
  function: 

  args:
  
  returns: 

*/
function reportResult($tcaseid, $tplanid, $buildid=null, $buildname=null, $status,$debug=false)
{

	$client = new IXR_Client(SERVER_URL);
 
  $client->debug=$debug;
  
	$data = array();
	$data["devKey"] = constant("DEV_KEY");
	$data["testcaseid"] = $tcaseid;
	$data["testplanid"] = $tplanid;
	
	if( !is_null($buildid) )
	{
	    $data["buildid"] = $buildid;
	}
	else if ( !is_null($buildname) )
	{
	      $data["buildname"] = $buildname;
	}
	
	$data["status"] = $status;

  echo "<pre>debug 20080306 - \ - " . __FUNCTION__ . " --- "; print_r($data); echo "</pre>";
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