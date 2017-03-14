<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientCreateTestProjectWithITS.php
 *
 * @version 
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal revisions
 * 
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='createTestProject';
$test_num = 0;

// ------------------------------------------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}() ::: ";
$prefix = uniqid();
$devKey = '985978c915f50e47a4b1a54a943d1b76';
$devKey = isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $devKey;

$args=array();
$args["devKey"] = $devKey;
$args["testcaseprefix"] = $prefix . $test_num;
$args["testprojectname"] = "API Methods Test Project {$args['testcaseprefix']}";
$args["itsname"] = "JIRA NON";

$dummy = '';
$additionalInfo = $dummy;
$args["notes"]="test project created using XML-RPC-API - <br> {$additionalInfo}";

echo $unitTestDescription . ' ' . $additionalInfo;

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
//runTest($client,$method,$args);
// ------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}() ::: ";
$prefix = uniqid();
$devKey = '985978c915f50e47a4b1a54a943d1b76';
$devKey = isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $devKey;

$args=array();
$args["devKey"] = $devKey;
$args["testcaseprefix"] = $prefix . $test_num;
$args["testprojectname"] = "API Methods Test Project {$args['testcaseprefix']}";
$args["itsname"] = "jira-testlink.jira";
$args["itsenabled"] = 1;

$dummy = '';
$additionalInfo = $dummy;
$args["notes"]="test project created using XML-RPC-API - <br> {$additionalInfo}";

echo $unitTestDescription . ' ' . $additionalInfo;

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);

