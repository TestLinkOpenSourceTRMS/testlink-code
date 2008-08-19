<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientSample2.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2008/08/19 13:18:07 $ by $Author: franciscom $
 *
 *
 * A sample client implementation in php
 * 
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net>
 * @package 	TestlinkAPI
 * @link      http://testlink.org/api/
 *
 *
 *
 * rev: 20080818 - franciscom - start work on custom field tests
 *      20080306 - franciscom - added dBug to improve diagnostic info.
 *      20080305 - franciscom - refactored
 */
 
 /** 
  * Need the IXR class for client
  */
define("THIRD_PARTY_CODE","/../../../../third_party");

// require_once dirname(__FILE__) . '/../../../functions/lang_api.php';
require_once dirname(__FILE__) . THIRD_PARTY_CODE . '/xml-rpc/class-IXR.php';
require_once dirname(__FILE__) . THIRD_PARTY_CODE . '/dBug/dBug.php';

// substitute your server URL Here
define("SERVER_URL", "http://localhost/w3/tl/tl18/head_20080804/lib/api/xmlrpc.php");

// substitute your Dev Key Here
define("DEV_KEY", "1111");

// 
$unitTestDescription="Test - getTestCasesForTestPlan";

/**
* getTestCasesForTestPlan
* List test cases linked to a test plan
* 
* @param struct $args
* @param string $args["devKey"]
* @param int $args["testplanid"]
* @param int $args["testcaseid"] - optional
* @param int $args["buildid"] - optional
* @param int $args["keywordid"] - optional
* @param boolean $args["executed"] - optional
* @param int $args["$assignedto"] - optional
* @param string $args["executestatus"] - optional
* @return mixed $resultInfo
*/
$args=array();
$args["devKey"]=DEV_KEY;
$args["testplanid"]=95;

// optional
// $args["testcaseid"] - optional
// $args["buildid"] - optional
// $args["keywordid"] - optional
// $args["executed"] - optional
// $args["$assignedto"] - optional
// $args["executestatus"] - optional

//$debug=true;
$debug=false;
echo $unitTestDescription;


$client = new IXR_Client(SERVER_URL);
$client->debug=$debug;



new dBug($args);
if(!$client->query('tl.getTestCasesForTestPlan', $args))
{
		echo "something went wrong - " . $client->getErrorCode() . " - " . $client->getErrorMessage();			
		$response=null;
}
else
{
		$response=$client->getResponse();
}

echo "<br> Result was: ";
// Typically you'd want to validate the result here and probably do something more useful with it
// print_r($response);
new dBug($response);
echo "<br>";


// 20080518 - franciscom
$unitTestDescription="Test - createTestProject";
$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectname"]='API TestProject';

$debug=true;
//$debug=false;
echo $unitTestDescription;

$client = new IXR_Client(SERVER_URL);
$client->debug=$debug;

new dBug($args);
if(!$client->query('tl.createTestProject', $args))
{
		echo "something went wrong - " . $client->getErrorCode() . " - " . $client->getErrorMessage();			
		$response=null;
}
else
{
		$response=$client->getResponse();
}

echo "<br> Result was: ";
new dBug($response);
echo "<br>";

// ----------------------------------------------------------------------
$method='getTestCaseCustomFieldDesignValue';
$client_query='tl.' . $method;
$unitTestDescription="Test - $method";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testcaseexternalid"]='ESP-1';
// $args["customfieldname"]='TESTER_EXPERIENCE';
$args["customfieldname"]='SSCRIPT_CF1';

$debug=true;

echo $unitTestDescription;

$client = new IXR_Client(SERVER_URL);
$client->debug=$debug;

new dBug($args);
if(!$client->query($client_query, $args))
{
		echo "something went wrong - " . $client->getErrorCode() . " - " . $client->getErrorMessage();			
		$response=null;
}
else
{
		$response=$client->getResponse();
}

echo "<br> Result was: ";
new dBug($response);
echo "<br>";
?>