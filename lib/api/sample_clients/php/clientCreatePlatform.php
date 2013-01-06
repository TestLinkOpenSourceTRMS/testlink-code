<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  clientCreatePlatform.php
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal revisions 
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method = 'createPlatform';
$unitTestDescription="Test - {$method}";
$idx=1;

$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectname"]='API TEST';
$args["platformname"] = "Nian";
$args["notes"] = "Blue Notte XX";

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$idx);
$idx++;
?>