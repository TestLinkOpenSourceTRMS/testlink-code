<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource clientCloseBuild.php
 *
 * @Author: francisco.mancardi@gmail.com
 *
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();


$test_num=0;
$devKey = 'qaz';

// --------------------------------------------------------

$method='closeBuild';

$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=$devKey;
$additionalInfo='NO BUILD ID';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);

// --------------------------------------------------------

// --------------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=$devKey;
$args["buildid"]='DDD';
$additionalInfo='BUILD ID IS STRING';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);
// --------------------------------------------------------

// --------------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=$devKey;
$args["buildid"]=9999999999;
$additionalInfo='BUILD ID DOES NOT EXIST';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);
// --------------------------------------------------------

// --------------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=$devKey;
$args["buildid"]=9;
$additionalInfo='User HAS NO RIGHT on TEST PLAN';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);
// --------------------------------------------------------
