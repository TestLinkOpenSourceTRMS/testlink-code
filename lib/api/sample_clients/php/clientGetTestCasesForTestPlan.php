<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetTestCasesForTestPlan.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2010/07/15 16:27:25 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 *		20100715 - franciscom - new argument getstepinfo
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getTestCasesForTestPlan';
$test_num=1;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testplanid"]=227;
$args["executiontype"]=2;
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
$args["devKey"]=DEV_KEY;
$args["testplanid"]=227;
$args["keywords"]='KU,UOL';
$additionalInfo='Filter by Keyword name';

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
$args["devKey"]=DEV_KEY;
$args["testplanid"]=227;
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
$args["devKey"]=DEV_KEY;
$args["testplanid"]=227;
$args["getstepsinfo"]=true;

$additionalInfo='get steps info: -> true';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

$answer = runTest($client,$method,$args,$test_num);
// ---------------------------------------------------------------------------------




?>