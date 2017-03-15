<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource stepCreateTestCase.php
 * @Author francisco.mancardi@gmail.com
 *
 * @internal revisions 
 */
 
// require_once 'util.php';
// require_once 'sample.inc.php';
$method='createTestCase';

$args=array();
$args["devKey"]='985978c915f50e47a4b1a54a943d1b76';

$args["testprojectid"] = $env->tlProjectID;
$args["testsuiteid"] = $env->tlSuiteID;
$args["testcasename"]='ZZ - TEST CASE NAME IS OK';
$args["summary"]='Test Case created via API';
$args["preconditions"]='Test Link API Up & Running';
$args["authorlogin"]='admin';
$args["checkduplicatedname"]=0;
$args["steps"][]=array('step_number' => 1, 'actions' => 'Start Server', 'expected_results' => 'green light');

$unitTestDescription = "";
echo $unitTestDescription;

$tlIdx++;
$client = new IXR_Client($server_url);
$client->debug = $tlDebug;
$ret = runTest($client,$method,$args,$tlIdx);