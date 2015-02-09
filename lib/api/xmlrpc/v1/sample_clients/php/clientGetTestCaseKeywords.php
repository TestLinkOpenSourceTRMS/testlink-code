<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename clientGetTestCaseKeywords.php
 *
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

// -------------------------------------------------------------------------------------
$method='getTestCaseKeywords';
$unitTestDescription="Test - {$method} - using testcaseid";

$args=array();
$args["devKey"]='admin';
$args["testcaseid"]=41;

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);


// ---
$unitTestDescription="Test - {$method} - using testcaseexternalid";

$args=array();
$args["devKey"]='admin';
$args["testcaseexternalid"]="XL5-1";

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);

// -------------------------------------------------------------------------------------
$method='getTestCaseKeywords';
$unitTestDescription="Test - {$method} - using array of testcaseid";

$args=array();
$args["devKey"]='admin';
$args["testcaseid"]=array(41);

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);

// ---
$unitTestDescription="Test - {$method} - using array of testcaseexternalid";

$args=array();
$args["devKey"]='admin';
$args["testcaseexternalid"] = array("XL5-1","XL5-2","XL5-3");

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);


