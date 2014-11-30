<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientGetTestCasesForTestPlan.php
 * @Author		francisco.mancardi@gmail.com
 *
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getTestCasesForTestPlan';
$test_num=1;
$unitTestDescription="Test {$test_num} - {$method}";

$tplan_id = intval(isset($_REQUEST['id']) ? $_REQUEST['id'] : 92) ;
$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : 'admin';
$args["testplanid"]=$tplan_id;
$args["platformid"]=3;

$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

$answer = runTest($client,$method,$args,$test_num);
// ---------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------
$test_num++;

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testplanid"]=$tplan_id;
$args["getstepsinfo"]=false;
$args["details"]='simple';
$additionalInfo = '$args["details"]: ->'  . $args["details"] . '<br>';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

$answer = runTest($client,$method,$args,$test_num);
// ---------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------
$test_num++;

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testplanid"]=$tplan_id;
$args["getstepsinfo"]=false;
$args["details"]='full';
$additionalInfo = '$args["details"]: ->'  . $args["details"] . '<br>';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

$answer = runTest($client,$method,$args,$test_num);
// ---------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------
$test_num++;

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testplanid"]=$tplan_id;
$args["getstepsinfo"]=false;
$args["details"]='summary';
$additionalInfo = '$args["details"]: ->'  . $args["details"] . '<br>';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

$answer = runTest($client,$method,$args,$test_num);
// ---------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------
$test_num++;

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testplanid"]=$tplan_id;
$args["keywords"]='Key Feature';
$additionalInfo='Filter by Keyword name - JUST ONE KEYWORD';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

$answer = runTest($client,$method,$args,$test_num);
// ---------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------
$test_num++;

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testplanid"]=$tplan_id;
$args["keywords"]='Key Feature,Must have,Obsolete,Performance,System wide,Usability';
$additionalInfo='Filter by Keyword name - Multiple Keywords - ONLY OR Search';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

$answer = runTest($client,$method,$args,$test_num);
// ---------------------------------------------------------------------------------


// ---------------------------------------------------------------------------------
$test_num++;

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testplanid"]=$tplan_id;
$args["getstepsinfo"]=false;

$additionalInfo='get steps info: -> false';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

$answer = runTest($client,$method,$args,$test_num);
// ---------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------
$test_num++;

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testplanid"]=$tplan_id;
$args["getstepsinfo"]=true;

$additionalInfo='get steps info: -> true';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

$answer = runTest($client,$method,$args,$test_num);
// ---------------------------------------------------------------------------------