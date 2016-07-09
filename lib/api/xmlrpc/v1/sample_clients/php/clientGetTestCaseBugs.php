<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetTestCaseBugs.php,v $
 *
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

// -------------------------------------------------------------------------------------
$method='getTestCaseBugs';
$unitTestDescription="Test - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testplanid"]="177244";
$args["testcaseid"]="177317";

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);