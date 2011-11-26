<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource clientCreateTestCase.php
 * @author: francisco.mancardi@gmail.com
 *
 * @internal revisions
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$client = new IXR_Client($server_url);

// Get user input
$run = isset($_REQUEST['run']) ? $_REQUEST['run'] : 'all';


$tcCounter = 1;
$dummy = explode(',',$run);
$tc2run = array_flip($dummy);

// ..................................................................................
//if( isset($tc2run['all'] )
//{
//	$tc2
//}

//$suffix = $run == 'all' ? 
/*
tc_a($client,$tcCounter);
tc_b($client,$tcCounter);
tc_c($client,$tcCounter);
tc_d($client,$tcCounter);
tc_e($client,$tcCounter);
*/
tc_sushi_rise($client,$tcCounter);
// ..................................................................................


// ..................................................................................
function tc_b(&$client,&$tcCounter)
{
	global $server_url;
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
	$client->debug=$debug;
	runTest($client,$method,$args);
}
// ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------
function tc_c(&$client,&$tcCounter)
{
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
	$client->debug=$debug;
	runTest($client,$method,$args);
}
// ----------------------------------------------------------------------------------------------------
function tc_d(&$client,&$tcCounter)
{
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
$client->debug=$debug;
runTest($client,$method,$args);
}

// ----------------------------------------------------------------------------------------------------
function tc_e(&$client,&$tcCounter)
{
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
$client->debug=$debug;
runTest($client,$method,$args);
}
// ----------------------------------------------------------------------------------------------------


// ----------------------------------------------------------------------------------------------------
function tc_a(&$client,&$tcCounter)
{
	$method='createTestCase';
	$unitTestDescription="Test #{$tcCounter}- {$method} - Test Case IS NOT STRING";
	$tcCounter++;
	
	$args=array();
	$args["devKey"]=DEV_KEY;
	$args["testprojectid"]=378;
	$args["testsuiteid"]=379;
	$args["testcasename"]=1000;
	$args["summary"]='Test Case created via API';
	$args["authorlogin"]='admin';
	
	
	$debug=true;
	echo $unitTestDescription;
	$client->debug=$debug;
	runTest($client,$method,$args);
}
// ----------------------------------------------------------------------------------------------------


function tc_sushi(&$client,&$tcCounter)
{
	$method='createTestCase';
	$unitTestDescription="Test #{$tcCounter}- {$method} - Test Case OK - Sushi Rice recipe";
	$tcCounter++;
	
	$args=array();
	$args["devKey"]=DEV_KEY;
	$args["testprojectid"]=378;
	$args["testsuiteid"]=379;
	$args["testcasename"]='Sushi Rice recipe';
	$args["summary"] = "When making authentic sushi, it is important to first create authentic sushi rice " .
	                   " -- it is, after all, the base flavor and texture of the rolls you create. "  .
	                   " While it may seem difficult to make this recipe for the first time, " .
	                   " it is well worth perfecting. " .
	                   " It adds sweet, tangy flavor to sushi rolls, and the sticky texture is necessary " .
	                   " to hold the rolls together. Use this rice whenever cooked sushi rice is called for.";	

	$args["prerequisites"] = "3 cups uncooked sushi or sticky rice<br/>" .
							 "3 cups water<br/>" .
							 "1/2 cup rice vinegar<br/>" . 
							 "1/2cup sugar<br/>" . "1 teaspoon salt";	

	$args["authorlogin"]='admin';
	
	
	$debug=true;
	echo $unitTestDescription;
	$client->debug=$debug;
	runTest($client,$method,$args);
}
// ----------------------------------------------------------------------------------------------------
?>