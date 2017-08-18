<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  clientCreateUser.php
 * @Author: glegall@wyplay.com
 *
 * @internal revisions 
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method = 'addUser';
$unitTestDescription="Test - {$method}";
$idx=1;

$args=array();
$args["devKey"]=DEV_KEY;
$args["auth_method"] = "NULL";

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$idx);
$idx++;
?>
