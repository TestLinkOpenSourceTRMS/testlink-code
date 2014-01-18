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

$method='getTestCasesForTestPlan';
$test_num=1;
$unitTestDescription="Test {$test_num} - {$method}";

$tplan_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 713;
$args=array();
$args["devKey"]='21232f297a57a5a743894a0e4a801fc3';
$args["testplanid"]=$tplan_id;
$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

$answer = runTest($client,$method,$args,$test_num);