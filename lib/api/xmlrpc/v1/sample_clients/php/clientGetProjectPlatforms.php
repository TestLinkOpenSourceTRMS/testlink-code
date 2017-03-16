<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource clientGetProjectPlatforms
 * @Author: francisco.mancardi@gmail.com
 *
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();


$method='getProjectPlatforms';
$test_num=0;
$tlDevKey = '985978c915f50e47a4b1a54a943d1b76';
$tlDevKey = isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $tlDevKey;


// ------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=$tlDevKey;
$args["prefix"]='ZTZ';
$additionalInfo='Access By Test Project PREFIX';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args,$test_num);


// ------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=$tlDevKey;
$args["testprojectid"]=1046;
$additionalInfo='Access By Test Project ID';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args,$test_num);