<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientCreateTestCase.php
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal revisions 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$tcCounter = 0;
// --------------------------------------------------------------------------------------------
$tcCounter++;
$method='createTestCaseSteps';
$unitTestDescription = "Test #{$tcCounter}- {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testcaseexternalid"]='MKO-1';
$args["version"]=1;
$args["action"] = 'push'; // 'update', 'push','create'
$args["steps"][]=array('step_number' => 12, 'actions' => 'SKIP !!!!Start Server Ubuntu 11.04', 
					   'expected_results' => 'green light' . ' ' . $args["action"]);

// $args["steps"][]=array('step_number' => 12, 'actions' => 'SKIP !!!! Start Server Fedora 15', 
//					   'expected_results' => 'green lantern' . ' ' . $args["action"]);
//$args["authorlogin"]='admin';


$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$tcCounter);
die();
// ----------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------
$tcCounter++;
$method='createTestCaseSteps';
$unitTestDescription = "Test #{$tcCounter}- {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testcaseexternalid"]='MKO-1';
$args["steps"][]=array('step_number' => 1, 'actions' => 'Start Server', 'expected_results' => 'green light');
$args["authorlogin"]='admin';


$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$tcCounter);
// ----------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------
$tcCounter++;
$method='createTestCaseSteps';
$unitTestDescription = "Test #{$tcCounter}- {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testcaseexternalid"]='MKO-1';
$args["version"]=100;
$args["steps"][]=array('step_number' => 1, 'actions' => 'Start Server VERSION DOES NOT EXIST', 
					   'expected_results' => 'green light');
$args["authorlogin"]='admin';


$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$tcCounter);
// ----------------------------------------------------------------------------------------------------

?>