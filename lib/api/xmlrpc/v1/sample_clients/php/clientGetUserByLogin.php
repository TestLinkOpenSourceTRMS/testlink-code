<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientGetUserByLogin.php
 * @since 1.9.8
 *
 * @Author: francisco.mancardi@gmail.com
 *
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getUserByLogin';
$unitTestDescription="Test - {$method}";

$args=array();
$args["devKey"]='21232f297a57a5a743894a0e4a801fc3';
$args["user"]='qaz';

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);