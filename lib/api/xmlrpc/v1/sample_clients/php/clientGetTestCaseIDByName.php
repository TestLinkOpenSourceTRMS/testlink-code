<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetTestCaseIDByName.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2010/08/31 20:06:31 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getTestCaseIDByName';
$test_num=1;
$unitTestDescription="Test {$test_num} - {$method}";
$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testcasename"]='KOMODO';

$additionalInfo='- Test Case Name is longer that allowed';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args);
die();
// ---------------------------------------------------------------------------------

$test_num=2;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testcaseexternalid"]='API-2';
$args["version"]=1;
$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args);
// ---------------------------------------------------------------------------------

$test_num=2;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testcaseid"]='1667';
$args["version"]=1;
$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args);
// ---------------------------------------------------------------------------------