<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetTestCase.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2009/05/21 20:28:39 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$devKey = md5('admin');
$method='getTestCase';
$test_num=0;

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;

$args["testcaseexternalid"]='AF-2';
$additionalInfo='';

$debug=true;
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args,$test_num);
// ---------------------------------------------------------------------------------


$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testcaseexternalid"]='API-2';
$args["version"]=1;
$additionalInfo='';

$debug=true;
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args);
// ---------------------------------------------------------------------------------

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testcaseid"]='1667';
$args["version"]=1;
$additionalInfo='';

$debug=true;
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args);
// ---------------------------------------------------------------------------------

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testcaseexternalid"]='API-2';
$args["version"]=3;
$additionalInfo='';

$debug=true;
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args);
// ---------------------------------------------------------------------------------
