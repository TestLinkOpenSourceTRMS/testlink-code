<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientCreateTestCase.php,v $
 *
 * @version $Revision: 1.8 $
 * @modified $Date: 2010/08/31 19:59:48 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();



$tcaseQty = 100;
$tcCounter = 1;
$method='createTestCase';


$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=90288;
$args["testsuiteid"]=90296;
$args["preconditions"]='Test Link API Up & Running';
$args["steps"][]=array('step_number' => 1, 'actions' => 'Start Server', 'expected_results' => 'green light');
$args["authorlogin"]='admin';

$client = new IXR_Client($server_url);
$client->debug=true;

for($idx=1 ; $idx <= $tcaseQty; $idx++)
{
	$args["testcasename"] = "Sample TEST #{$idx}";
	$args["summary"]=$args["testcasename"] . 'created via XML-RPC API';
	runTest($client,$method,$args);
}
echo 'Test Cases Created';
?>