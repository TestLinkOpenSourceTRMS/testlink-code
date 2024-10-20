<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
//show_api_db_sample_msg();

// -------------------------------------------------------------------------------------
$method=lcfirst(str_replace('client','',basename(__FILE__,".php")));
$unitTestDescription="Test - {$method}";

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;

$debug=true;
echo $unitTestDescription . "<br />";
$client = new IXR_Client($server_url);
$client->debug=$debug;

$args["testplanid"]="21";
$args["testcaseid"]="4";
runTest($client,$method,$args,1);

echo "Test adding a filter on a the build 2<br \>";
$args["buildid"]="2";
runTest($client,$method,$args,2);

echo "Test adding a filter on the build 1<br \>";
$args["buildid"]="1";
runTest($client,$method,$args,3);

echo "Test adding a filter on a platform<br \>";
$args["platformid"]="1";
runTest($client,$method,$args,4);

echo "Test adding a filter on an execution ID<br \>";
$args["executionid"]=1;
runTest($client,$method,$args,5);

echo "Test adding a filter on another execution ID<br \>";
$args["executionid"]=3;
runTest($client,$method,$args,6);
