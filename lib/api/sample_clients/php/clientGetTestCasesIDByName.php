<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetTestCasesIDByName.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2009/11/28 15:45:48 $ by $Author: franciscom $
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
$args["devKey"]=DEV_KEY;
$args["testcasename"]='100% moisture conditions';

$additionalInfo='';
$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args);
// ---------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testcasename"]='Full speed unload';
$args["testcasepathname"]='ZATHURA::Holodeck::Apollo 10 Simulation::Unload::Full speed unload';

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
runTest($client,$method,$args);
// ---------------------------------------------------------------------------------

?>