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
$tcCounter = 1;

// Create Test Project
$other = array();
$status_ok = true;
if( $status_ok )
{
	$r = taskCreateTestProject($client,$tcCounter);
	$status_ok = !isset($r[0]['code']);
}

if( $status_ok )
{
	$r = $r[0];
	$other['testprojectid'] = $r['id'];
	$r = taskCreateTestSuite($client,$tcCounter,$other);
	$status_ok = !isset($r[0]['code']);
}

if( $status_ok )
{
	$r = $r[0];
	$other['testsuiteid'] = $r['id'];
	$r = tc_sushi_rise($client,$tcCounter,$other);
	$status_ok = !isset($r[0]['code']);
}


// -------------------------------------------------------------------------------
function tc_sushi_rise(&$client,&$tcCounter,$other)
{
	$method='createTestCase';
	$unitTestDescription="Test #{$tcCounter}- {$method} - Test Case OK - Sushi Rice recipe";
	$tcCounter++;
	
	$args=array();
	$args["devKey"]=DEV_KEY;
	$args["testprojectid"] = $other["testprojectid"];
	$args["testsuiteid"] = $other["testsuiteid"];
	
	$args["testcasename"]='Sushi Rice recipe';
	$args["summary"] = "When making authentic sushi, it is important to first create authentic sushi rice " .
	                   " -- it is, after all, the base flavor and texture of the rolls you create. "  .
	                   " While it may seem difficult to make this recipe for the first time, " .
	                   " it is well worth perfecting. " .
	                   " It adds sweet, tangy flavor to sushi rolls, and the sticky texture is necessary " .
	                   " to hold the rolls together. Use this rice whenever cooked sushi rice is called for.";	

	$args["preconditions"] = "3 cups uncooked sushi or sticky rice<br/>" .
							 "3 cups water<br/>" .
							 "1/2 cup rice vinegar<br/>" . 
							 "1/2cup sugar<br/>" . "1 teaspoon salt";	
							 
	$args["steps"] = array();
	$args["steps"][] = array('step_number' => 0, 'actions' => 'Wash the rice and rinse thoroughly.', 
							 'expected_results' => '');	
	$args["steps"][] = array('step_number' => 0,
							 'actions' => 'Place rice and water in a rice cooker and set until cooked. ' .
										  '<br>Alternately, place rice and water in a medium saucepan. ' .
										  '<br>Bring to a boil over high heat, reduce to a simmer, ' .
										  'and cook covered until done, 35 to 45 minutes.',
							 'expected_results' => '');			  

	foreach($args["steps"] as $edx => &$elem)
	{
		$elem['step_number'] = $edx+1;
	}
										  
/*
    Meanwhile, mix together the rice vinegar, sugar and salt in a small saucepan; cook over medium heat until sugar has dissolved. Allow to cool.

    Put the cooked rice into a large mixing bowl; pour the vinegar sauce over the hot rice and mix. Allow to cool slightly before using in sushi recipes.
*/

	$args["authorlogin"]='admin';
	
	
	$debug=true;
	echo $unitTestDescription;
	$client->debug=$debug;
	runTest($client,$method,$args);
}
// ----------------------------------------------------------------------------------------------------


function taskCreateTestProject(&$client,&$test_num)
{
	$method='createTestProject';
	$test_num++;
	$unitTestDescription="Test {$test_num} - {$method}() ::: ";
	
	
	$args=array();
	// requirementsEnabled,testPriorityEnabled,automationEnabled,inventoryEnabled
	$args["options"] = array('requirementsEnabled' => 0);
	$dummy = 'Options[';
	foreach($args["options"] as $key => $value)
	{
		$dummy .= $key . ' -> ' . $value . ' ';
	}
	$dummy .= "] ";
	
	$args["devKey"]=DEV_KEY;
	$args["testprojectname"] = "Test Project Asiatic Food Recipes";
	$dummy = explode(' ', $args["testprojectname"]);
	$args["testcaseprefix"] = '';
	foreach($dummy as $p) 
	{
		
		$args["testcaseprefix"] .= $p{0}; //substr($p, 0, 1);
	}
	
	$args["active"] = 1;
	$args["public"] = 1;
	
	$additionalInfo = $dummy . " active -> {$args['active']}, public -> {$args['public']}";
	$args["notes"]="test project created using XML-RPC-API - <br> {$additionalInfo}";
	
	
	echo $unitTestDescription . ' ' . $additionalInfo;
	
	$debug = true;
	$client->debug=$debug;
	return runTest($client,$method,$args,$test_num);
}	



function taskCreateTestSuite(&$client,&$test_num,$other)
{
	$method='createTestSuite';
	$test_num++;
	$unitTestDescription="Test {$test_num} - {$method}() ::: ";
	$additionalInfo = '';
	
	$args=array();
	$args["devKey"]=DEV_KEY;
	$args["testprojectid"]=$other["testprojectid"];
	$args["testsuitename"]='Test Suite Sushi Recipes';
	$args["details"]='Created by XMLRPC API Call';
	$args["checkduplicatedname"]=1;
	$args["actiononduplicatedname"]='generate_new';
	$args["order"]=1;

	echo $unitTestDescription . ' ' . $additionalInfo;
	
	$debug = true;
	$client->debug=$debug;
	return runTest($client,$method,$args,$test_num);
}	


?>
