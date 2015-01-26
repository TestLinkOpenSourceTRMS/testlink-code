<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource clientAddTestCaseKeywords.php
 *
 * @Author: francisco.mancardi@gmail.com
 *
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method = lcfirst(str_replace('client','',basename(__FILE__,".php")));
$devKey = 'admin';
$unitTestDescription="Test - {$method}";

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $devKey;
$args["testcaseexternalid"]='MAB-1';
$args["keywords"] = array('NATAZ'); 



$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
$answer = runTest($client,$method,$args);