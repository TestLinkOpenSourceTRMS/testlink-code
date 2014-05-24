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

$unitTestDescription="Test - {$method} - Test Plan WITHOUT Platforms";

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
// $args["testcaseexternalid"]='GK-13';
$args["testplanid"] = 9;
$args["testcaseexternalid"] = 'GK-1';
$args["platformname"] = 'P2';


$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
$answer = runTest($client,$method,$args);
// ---------------------------------------------------------------------------------------
