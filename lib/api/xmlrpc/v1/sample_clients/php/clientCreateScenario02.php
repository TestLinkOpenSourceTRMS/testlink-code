<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @Author		francisco.mancardi@gmail.com
 *
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$devKey = '985978c915f50e47a4b1a54a943d1b76';
$devKey = isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $devKey;

$prefix = 'BMX';

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;

$step = 0;

// ------------------------------------------------------------
// Clean Up Before START
$step++;
$args = array();
$args['devKey'] = $devKey;
$args['prefix'] = $prefix;
$method='deleteTestProject';
$answer = runTest($client,$method,$args,$step);

// Create Test Project
$step++;
$method='createTestProject';

$args = array();
$arg['prefix'] = $prefix;
$args["devKey"] = $devKey;
$args["testcaseprefix"] = $prefix;
$args["testprojectname"] = "API Methods Test Project {$args['testcaseprefix']}";
$args["public"] = 0;
$unitTestDescription = '';

$dummy = '';
$additionalInfo = $dummy;
$args["notes"] = "test project created using XML-RPC-API - <br> {$additionalInfo}";

echo $unitTestDescription . ' ' . $additionalInfo;
$answer = runTest($client,$method,$args,$step);

// Create Test Suite
$step++;
$method = 'createTestSuite';

$args = array();
$arg['prefix'] = $prefix;
$args["devKey"] = $devKey;
$args["testprojectid"] = $answer[0]['id'];
$args["testsuitename"] = 'TS API 2';
$args["details"] = 'This has been created by XMLRPC API Call';

$unitTestDescription = '';

$dummy = '';
$additionalInfo = $dummy;
echo $unitTestDescription . ' ' . $additionalInfo;
$answer = runTest($client,$method,$args,$step);

// Create Test Plan
$step++;
$method = 'createTestPlan';

$args = array();
$args['prefix'] = $prefix;
$args["devKey"] = $devKey;
$args["testplanname"] = 'TPLAN A';
$args["details"] = 'This has been created by XMLRPC API Call';

$dummy = '';
$additionalInfo = $dummy;
echo $unitTestDescription . ' ' . $additionalInfo;
$answer = runTest($client,$method,$args,$step);







