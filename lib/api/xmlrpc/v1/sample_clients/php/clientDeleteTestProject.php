<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @Author		francisco.mancardi@gmail.com
 *
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();


// Create Test Project

// Clean Up

$method='deleteTestProject';
$test_num=1;
$unitTestDescription="Test {$test_num} - {$method}";

$prefix = $_REQUEST['prefix'] ? $_REQUEST['prefix'] : 'VICTIM';

$args=array();
$args["devKey"] = '985978c915f50e47a4b1a54a943d1b76';
// $args["devKey"]='2d45078ccf15875cbf442d844b3b2f80';
$args["prefix"] = $prefix;
$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

$answer = runTest($client,$method,$args,$test_num);
