<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetTestCasesForTestSuite.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2009/05/01 20:36:56 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getTestCasesForTestSuite';
$test_num=1;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : '21232f297a57a5a743894a0e4a801fc3';
$args["testprojectid"]=792;
$args["testsuiteid"]=793;  //801;
$args["deep"]=true;
$args["details"]='full';
$args["getkeywords"]=true;

$additionalInfo=' Parameter deep = ' . $args["deep"];

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args);

// ---------------------------------------------------------------------------------
$method='getTestCasesForTestSuite';
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testprojectid"]=12222;
$args["testsuiteid"]=186;
$args["deep"]=false;
$args["details"]='simple';

$additionalInfo=' Parameter deep = ' . $args["deep"];

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

runTest($client,$method,$args);