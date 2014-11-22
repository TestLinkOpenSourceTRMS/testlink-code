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
$args["testcaseid"]=2610;

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);

// ---
$unitTestDescription="Test - {$method} - using testcaseexternalid";

$args=array();
$args["devKey"]='admin';
$args["testcaseexternalid"]="AF-1";

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);

