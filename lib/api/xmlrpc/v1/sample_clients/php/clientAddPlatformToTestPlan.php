<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource clientAddPlatformToTestPlan.php,v $
 *
 * @Author: francisco.mancardi@gmail.com
 *
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method="addPlatformToTestPlan";

$unitTestDescription="Test - {$method} - ";

$args=array();
$args["devKey"]='d74058494841b830b6fb0f03f8b24d67';
$args["testplanid"]=1205;
$args["platformname"]='MANZA';

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
$answer = runTest($client,$method,$args);
// ---------------------------------------------------------------------------------------
?>