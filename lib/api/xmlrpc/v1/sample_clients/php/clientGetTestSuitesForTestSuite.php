<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetTestSuitesForTestSuite.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2010/07/19 10:13:56 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getTestSuitesForTestSuite';
$test_num=1;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testsuiteid"]=-123;

$additionalInfo='<br>Test suite ID is INEXISTENT<br>';
$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

$answer = runTest($client,$method,$args,$test_num);
// ---------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testsuiteid"]=689;

$additionalInfo='<br><br>';
$debug=true;
echo $unitTestDescription;
echo $additionalInfo;
echo 'arguments:<br>';
foreach($args as $key => $value)
{
	echo $key . '=' . $value . '<br>';
}

$client = new IXR_Client($server_url);
$client->debug=$debug;
$answer = runTest($client,$method,$args,$test_num);
// ---------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testsuiteid"]=193;

$additionalInfo='<br>Test Suite HAS NO CHILDREN<br>';
$debug=true;
echo $unitTestDescription;
echo $additionalInfo;
echo 'arguments:<br>';
foreach($args as $key => $value)
{
	echo $key . '=' . $value . '<br>';
}

$client = new IXR_Client($server_url);
$client->debug=$debug;
$answer = runTest($client,$method,$args,$test_num);
// ---------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testsuiteid"]=228;

$additionalInfo='';
$debug=true;
echo $unitTestDescription;
echo $additionalInfo;
echo 'arguments:<br>';
foreach($args as $key => $value)
{
	echo $key . '=' . $value . '<br>';
}

$client = new IXR_Client($server_url);
$client->debug=$debug;
$answer = runTest($client,$method,$args,$test_num);
// ---------------------------------------------------------------------------------

?>