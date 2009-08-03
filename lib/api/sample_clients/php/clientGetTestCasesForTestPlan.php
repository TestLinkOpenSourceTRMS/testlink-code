<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetTestCasesForTestPlan.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2009/08/03 08:15:43 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getTestCasesForTestPlan';
$test_num=1;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testplanid"]=181;
$args["executiontype"]=2;
$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args);
// ---------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------
$test_num=2;

$args=array();
$args["devKey"]=DEV_KEY;
$args["testplanid"]=446;
$args["keywords"]='KU,UOL';
$additionalInfo='Filter by Keyword name';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args);
// ---------------------------------------------------------------------------------

?>