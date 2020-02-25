<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientSetTestCaseTestSuite.php
 *
 * @Author: francisco.mancardi@gmail.com
 *
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$tcaseQty = 100;
$tcCounter = 0;
$method='setTestCaseTestSuite';


// Update Only Summary
$args=array();
$args["devKey"]='rey-momo';
$args["testcaseexternalid"]='PQQ-1';
$args["testsuiteid"]=3;

$client = new IXR_Client($server_url);
$client->debug=true;

$tcCounter++;
runTest($client,$method,$args,$tcCounter);
die();