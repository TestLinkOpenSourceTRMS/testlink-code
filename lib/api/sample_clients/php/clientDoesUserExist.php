<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientDoesUserExist.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2010/06/24 17:25:53 $ by $Author: asimon83 $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='doesUserExist';
$test_num=1;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["user"]='pippo';

$additionalInfo='<br>User does not exist<br>';
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
$args["user"]='admin';

$additionalInfo='<br>User exists<br>';
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

