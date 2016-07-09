<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientDeleteTestCase.php
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal revisions 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$tcCounter = 0;
// --------------------------------------------------------------------------------------------
$tcCounter++;
$method='deleteTestCaseSteps';
$unitTestDescription = "Test #{$tcCounter}- {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testcaseexternalid"]='MKO-1';
$args["version"]=1;
$args["steps"] = array(12,1);

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$tcCounter);
// ----------------------------------------------------------------------------------------------------
?>