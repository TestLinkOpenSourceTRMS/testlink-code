<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientCreateTestCase.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2009/01/06 18:42:32 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
 
 /** 
  * Need the IXR class for client
  */
define("THIRD_PARTY_CODE","/../../../../third_party");
require_once dirname(__FILE__) . THIRD_PARTY_CODE . '/xml-rpc/class-IXR.php';
require_once dirname(__FILE__) . THIRD_PARTY_CODE . '/dBug/dBug.php';

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
$dummy=explode('sample_clients',$target);
$server_url=$prefix . $dummy[0] . "xmlrpc.php";

// substitute your Dev Key Here
define("DEV_KEY", "1111");

$unitTestDescription="Test - createTestCase";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=11260;
$args["testsuiteid"]=11465;
$args["testcasename"]='Francisco';
$args["summary"]='Test Case created via API';
$args["steps"]="These are the steps";
$args["expectedresults"]="All OK";
$args["authorlogin"]='admin';
$args["authorlogin"]='admin';
$args["checkduplicatedname"]=1;
$args["keywordid"]='1,2,3';
$args["keywords"]='ALFA,BETA,ZETA';


$debug=true;
//$debug=false;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;

new dBug($args);
if(!$client->query('tl.createTestCase', $args))
{
		echo "something went wrong - " . $client->getErrorCode() . " - " . $client->getErrorMessage();			
		$response=null;
}
else
{
		$response=$client->getResponse();
}

echo "<br> Result was: ";
new dBug($response);
echo "<br>";

// ----------------------------------------------------------------------------------------------------
$unitTestDescription="Test - createTestCase";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=11260;
$args["testsuiteid"]=11465;
$args["testcasename"]='Francisco';
$args["summary"]='Test Case created via API';
$args["steps"]="These are the steps";
$args["expectedresults"]="All OK";
$args["authorlogin"]='admin';
$args["authorlogin"]='admin';
$args["checkduplicatedname"]=1;
$args["keywordid"]='1,2,3,4';


$debug=true;
//$debug=false;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;

new dBug($args);
if(!$client->query('tl.createTestCase', $args))
{
		echo "something went wrong - " . $client->getErrorCode() . " - " . $client->getErrorMessage();			
		$response=null;
}
else
{
		$response=$client->getResponse();
}

echo "<br> Result was: ";
new dBug($response);
echo "<br>";

?>