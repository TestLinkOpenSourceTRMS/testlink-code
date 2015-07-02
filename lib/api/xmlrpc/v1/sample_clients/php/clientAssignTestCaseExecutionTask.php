<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientAssignTestCaseExecutionTask.php
 * @Author: francisco.mancardi@gmail.com
 *
 *  
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method="assignTestCaseExecutionTask";
$utc = 0;
$devKey = isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : 'admin';

// ---------------------------------------------------------------------------------------
$utc++;
$unitTestDescription="Test #{$utc} - {$method} - All OK";

$args=array();
$args["devKey"] = $devKey;
$args["testplanid"] = 2808;
$args["testcaseexternalid"] = 'DSM-1';
// $args["platformname"] = 'Apache Derby';
// $args["platformname"] = 'Informix';
$args["buildname"] = '1.0';
$args["user"] = 'David.Gilmour';
//$args["user"] = 'Nick.Mason';


/*
$args=array();
$args["devKey"] = $devKey;
$args["testplanid"] = 278;
$args["testcaseexternalid"] = 'APX-1';
$args["platformname"] = 'Informix';
$args["buildname"] = '2.0';
$args["user"] = 'giskard';
*/

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;

echo $unitTestDescription;
$answer = runTest($client,$method,$args);
new dBug($answer);
die();

// ---------------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------------
$utc++;
$unitTestDescription="Test #{$utc} - {$method} - All OK";

$args=array();
$args["devKey"] = $devKey;
$args["testplanid"] = 278;
$args["testcaseexternalid"] = 'APX-1';
$args["platformname"] = 'Informix';
$args["buildname"] = '2.0';
$args["user"] = 'giskard';



$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;

echo $unitTestDescription;
$answer = runTest($client,$method,$args);

// ---------------------------------------------------------------------------------------

$utc++;
$unitTestDescription="Test #{$utc} - {$method} - Missing argument - Test Plan ID";

$args=array();
$args["devKey"] = $devKey;
// $args["testplanid"] = 9;
$args["testcaseexternalid"] = 'GK-1';
$args["platformname"] = 'P2';


$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;

echo $unitTestDescription;
$answer = runTest($client,$method,$args);
// ---------------------------------------------------------------------------------------

$utc++;
$unitTestDescription="Test #{$utc} - {$method} - Missing argument - Test Case ";

$args=array();
$args["devKey"] = $devKey;
$args["testplanid"] = 9;
// $args["testcaseexternalid"] = 'GK-1';
$args["platformname"] = 'P2';


$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;

echo $unitTestDescription;
$answer = runTest($client,$method,$args);
// ---------------------------------------------------------------------------------------

$utc++;
$unitTestDescription="Test #{$utc} - {$method} - Missing argument - Build Name ";

$args=array();
$args["devKey"] = $devKey;

$args["testplanid"] = 9;
$args["testcaseexternalid"] = 'GK-1';
// $args["buildname"] = '1.0';
$args["platformname"] = 'P2';


$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;

echo $unitTestDescription;
$answer = runTest($client,$method,$args);
// ---------------------------------------------------------------------------------------

$utc++;
$unitTestDescription="Test #{$utc} - {$method} - Wrong argument - Test Plan ID ";

$args=array();
$args["devKey"] = $devKey;

$args["testplanid"] = 900000;
$args["testcaseexternalid"] = 'GK-1';
$args["buildname"] = 'WRONG - 1.0';
$args["platformname"] = 'P2';


$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;

echo $unitTestDescription;
$answer = runTest($client,$method,$args);
// ---------------------------------------------------------------------------------------

$utc++;
$unitTestDescription="Test #{$utc} - {$method} - Wrong argument - Test Case External ID ";

$args=array();
$args["devKey"] = $devKey;

$args["testplanid"] = 9;
$args["testcaseexternalid"] = 'GK-WRONG-1';
$args["buildname"] = 'WRONG - 1.0';
$args["platformname"] = 'P2';


$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;

echo $unitTestDescription;
$answer = runTest($client,$method,$args);
// ---------------------------------------------------------------------------------------

$utc++;
$unitTestDescription="Test #{$utc} - {$method} - Wrong argument - Build Name ";

$args=array();
$args["devKey"] = $devKey;

$args["testplanid"] = 9;
$args["testcaseexternalid"] = 'GK-1';
$args["buildname"] = 'WRONG - 1.0';
$args["platformname"] = 'P2';


$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;

echo $unitTestDescription;
$answer = runTest($client,$method,$args);
// ---------------------------------------------------------------------------------------
