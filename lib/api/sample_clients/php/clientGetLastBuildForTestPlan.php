<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientGetLastBuildForTestPlan.php
 * @Author: francisco.mancardi@gmail.com
 *
 * @since 2.0
 *
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getLastBuildForTestPlan';
$unitTestDescription="Test - {$method} - using BAD KEY on Calling parameters for for test plan";

$args = array();
$args["devKey"]=DEV_KEY;
$args["tplanid"]=1635;

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);
?>