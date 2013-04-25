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

$tcCounter = 1;
$method='createTestCase';
$unitTestDescription = "Test #{$tcCounter}- {$method} - With NAME exceeding limit";
$tcCounter++;

$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=280;
$args["testsuiteid"]=297;
$args["testcasename"]=
'TEST CASE NAME IS LONGER THAT ALLOWED SIZE - 100 CHARACTERS - The quick brown fox jumps over the X % lazydog (bye bye dog)';
$args["summary"]='Test Case created via API';
$args["preconditions"]='Test Link API Up & Running';
$args["steps"][]=array('step_number' => 1, 'actions' => 'Start Server', 'expected_results' => 'green light');
$args["authorlogin"]='admin';
$args["checkduplicatedname"]=1;


$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);


// ----------------------------------------------------------------------------------------------------
$method='createTestCase';
$unitTestDescription="Test #{$tcCounter}- {$method}";
$tcCounter++;

$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=620;
$args["testsuiteid"]=621;
$args["testcasename"]='Network Interface Card (NIC) driver update';
$args["summary"]='Test Case created via API';
$args["authorlogin"]='admin';
$args["checkduplicatedname"]=1;
$args["keywordid"]='1,2,3,4';


$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);

// ----------------------------------------------------------------------------------------------------
$method='createTestCase';
$unitTestDescription="Test #{$tcCounter}- {$method}";
$tcCounter++;

$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=620;
$args["testsuiteid"]=621;
$args["testcasename"]='Volume Manager Increase size';
$args["summary"]='Test Case created via API - Volume Manager Increase size';
$args["steps"][]=array('step_number' => 1, 'actions' => 'Start Server', 'expected_results' => 'green light');
$args["steps"][]=array('step_number' => 2, 'actions' => 'Connect to Server', 'expected_results' => 'beep twice');
$args["authorlogin"]='admin';
$args["checkduplicatedname"]=1;

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);

// ----------------------------------------------------------------------------------------------------
$method='createTestCase';
$unitTestDescription="Test #{$tcCounter}- {$method}";
$tcCounter++;

$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=620;
$args["testsuiteid"]=621;
$args["testcasename"]='Volume Manager Increase size';
$args["summary"]='Want to test Action On Duplicate with value create_new_version FOR Volume Manager Increase size';
$args["authorlogin"]='admin';
$args["checkduplicatedname"]=1;
$args["actiononduplicatedname"]="create_new_version";

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);
// ----------------------------------------------------------------------------------------------------
?>