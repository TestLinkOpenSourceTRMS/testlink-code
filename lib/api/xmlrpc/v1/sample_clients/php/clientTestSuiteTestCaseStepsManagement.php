<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientDeleteTestCase.php
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal revisions 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

// GLOBAL + Config
$cfg = new stdClass();
$cfg->tcasePrefix = 'MKO';
$cfg->tcaseVersionNumber = 1;
$client = new IXR_Client($server_url);
$client->debug=$debug;

$tcCounter = 0;

$commonArgs = array();
$commonArgs["devKey"]=DEV_KEY;
$commonArgs["testcaseexternalid"]=$cfg->tcasePrefix . '-1';

// Sample data
$qtySteps = 6;
$fakeSteps = null;
for($idx=1; $idx < $qtySteps; $idx++)
{
	$action = 'FULL STOP NOW!!!! - intensity='; 
	$expected_results = '%s Red Lights ON';
	if( $idx & 1 )
	{
		$action = 'Start Server with power='; 
		$expected_results = 'GREEN Lantern %s ON';
	}
	$expected_results = sprintf($expected_results,$idx);
	$fakeSteps[] = array('step_number' => $idx, 'actions' => $action . $idx, 
					     'expected_results' => $expected_results);
}
// ---------------------------------------------------------------------------------


// ---------------------------------------------------------------------------------
// Get existent Test Case
$additionalInfo='';
$tcCounter++;
$args = $commonArgs;
$rr = runTest($client,'getTestCase',$args,$tcCounter);
$ret = $rr[0];
if(isset($ret['code']))
{
	new dBug($ret);
	exit();
}

// Build data to Delete ALL steps
$originalSteps = null;
if( !is_null($ret) && isset($ret['steps']) && !is_null($ret['steps']) && $ret['steps'] != '')
{
	$originalSteps = (array)$ret['steps'];
}

if( ($loop2do = count($originalSteps)) > 0 )
{
	$runDelete = true;
	$allSteps = null;
	for($idx=0; $idx < $loop2do; $idx++)
	{
		$allSteps[] = $originalSteps[$idx]['step_number'];
	}

	// Now request delete
	$args=$commonArgs;
	$args["version"]=$cfg->tcaseVersionNumber;
	$args["steps"] = $allSteps;
	$rr = runTest($client,'deleteTestCaseSteps',$args,$tcCounter);
	$ret = isset($rr[0]) ? $rr[0] : $rr;
	if(isset($ret['code']))
	{
		new dBug($ret);
		exit();
	}
}

// Now reinsert original content if any
$steps2insert = !is_null($originalSteps) && count((array)$originalSteps >0) ? $originalSteps : $fakeSteps;
$args=$commonArgs;
$args["version"]=$cfg->tcaseVersionNumber;
$args["action"] = 'create';
$args['steps'] = $steps2insert;

// new dBug($steps2insert);
$rr = runTest($client,'createTestCaseSteps',$args,$tcCounter);
$ret = isset($rr[0]) ? $rr[0] : $rr;
if(isset($ret['code']))
{
	new dBug($ret);
	exit();
}
// ----------------------------------------------------------------------------------------------------

// Now Create a Fake Step to PUSH
$alienStartPos = intval($qtySteps/3);
$aliens[] = array('step_number' => $alienStartPos, 'actions' => 'ALIEN ' . $action, 
			      'expected_results' => 'Ripley Will BE INFECTED');

$args=$commonArgs;
$args["version"]=$cfg->tcaseVersionNumber;
$args["action"] = 'push';
$args['steps'] = $aliens;
$rr = runTest($client,'createTestCaseSteps',$args,$tcCounter);
$ret = isset($rr[0]) ? $rr[0] : $rr;
if(isset($ret['code']))
{
	new dBug($ret);
	exit();
}
// ----------------------------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------------------------
// Now TRY TO Create EXISTENT STEP
$alienStartPos = intval($qtySteps/3);
$aliens[] = array('step_number' => $alienStartPos, 
				  'actions' => 'If you see this content => Houston we have a problem' . $action, 
			      'expected_results' => 'Ripley Will BE INFECTED');

$args=$commonArgs;
$args["version"]=$cfg->tcaseVersionNumber;
$args["action"] = 'create';
$args['steps'] = $aliens;
$rr = runTest($client,'createTestCaseSteps',$args,$tcCounter);
$ret = isset($rr[0]) ? $rr[0] : $rr;
if(isset($ret['code']))
{
	new dBug($ret);
	exit();
}
// ----------------------------------------------------------------------------------------------------


// ----------------------------------------------------------------------------------------------------
// Now TRY TO UPDATE a NON EXISTENT STEP
$hint = 'You have requested UPDATE of NON EXISTENT Step => we will CREATE it';
$alienStartPos = 1000;
$aliens[] = array('step_number' => $alienStartPos, 
				  'actions' => $hint . $action, 
			      'expected_results' => 'Ripley Will BE INFECTED');

$args=$commonArgs;
$args["version"]=$cfg->tcaseVersionNumber;
$args["action"] = 'update';
$args['steps'] = $aliens;
$rr = runTest($client,'createTestCaseSteps',$args,$tcCounter);
$ret = isset($rr[0]) ? $rr[0] : $rr;
if(isset($ret['code']))
{
	new dBug($ret);
	exit();
}
// ----------------------------------------------------------------------------------------------------



?>