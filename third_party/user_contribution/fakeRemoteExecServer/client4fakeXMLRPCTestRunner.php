<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: client4fakeXMLRPCTestRunner.php,v $
 *
 * @version $Revision: 1.1.2.1 $
 * @modified $Date: 2011/01/25 21:46:41 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
require_once dirname(__FILE__) . '../../../xml-rpc/class-IXR.php';

echo 'Sample Client to test remote execution<br>';
if( isset($_SERVER['HTTP_REFERER']) )
{
    $target = $_SERVER['HTTP_REFERER'];
    $prefix = '';
}
else
{
    $target = $_SERVER['REQUEST_URI'];
    $prefix = "http://" . $_SERVER['HTTP_HOST'] . ":" . $_SERVER['SERVER_PORT'];
} 
$serverURL = $prefix . $target . "fakeXMLRPCTestRunner.php";
$client = new IXR_Client($serverURL);

// -------------------------------------------------------------------
$args=array();
$args["testCaseName"]='My TEST';
$args["testCaseID"]=1;
$args["testCaseVersionID"]=2;
$args["testProjectID"]=11;
$args["testPlanID"]=22;
$args["platformID"]=33;
$args["buildID"]=44;
$args["executionMode"]='now';

$client->query('executeTestCase',$args);
echo '<pre>';
var_dump($client->getResponse());
echo '</pre>';

// -----------------------------------------------
$args=array();
$args["testCaseName"]='sayPassed';
$args["testCaseID"]=1;
$args["testCaseVersionID"]=2;
$args["testProjectID"]=11;
$args["testPlanID"]=22;
$args["platformID"]=33;
$args["buildID"]=44;
$args["executionMode"]='now';

$client->query('executeTestCase',$args);
echo '<pre>';
var_dump($client->getResponse());
echo '</pre>';

// -----------------------------------------------
$args=array();
$args["testCaseName"]='sayBlocked';
$args["testCaseID"]=1;
$args["testCaseVersionID"]=2;
$args["testProjectID"]=11;
$args["testPlanID"]=22;
$args["platformID"]=33;
$args["buildID"]=44;
$args["executionMode"]='now';

$client->query('executeTestCase',$args);
echo '<pre>';
var_dump($client->getResponse());
echo '</pre>';

// -----------------------------------------------
$args=array();
$args["testCaseName"]='sayFailed';
$args["testCaseID"]=1;
$args["testCaseVersionID"]=2;
$args["testProjectID"]=11;
$args["testPlanID"]=22;
$args["platformID"]=33;
$args["buildID"]=44;
$args["executionMode"]='now';

$client->query('executeTestCase',$args);
echo '<pre>';
var_dump($client->getResponse());
echo '</pre>';

// -----------------------------------------------
$args=array();
$args["testCaseName"]='sayScheduled';
$args["testCaseID"]=1;
$args["testCaseVersionID"]=2;
$args["testProjectID"]=11;
$args["testPlanID"]=22;
$args["platformID"]=33;
$args["buildID"]=44;
$args["executionMode"]='now';

$client->query('executeTestCase',$args);
echo '<pre>';
var_dump($client->getResponse());
echo '</pre>';

?>

