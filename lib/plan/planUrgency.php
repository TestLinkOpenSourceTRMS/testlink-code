<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Define urgency of a Test Suite. 
 * It requires "prioritization" feature enabled.
 *
 * @filesource	planUrgency.php
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2003-2009, TestLink community 
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal revisions
 * @since 1.9.4
 *
 * @since 1.9.3
 * 20110415 - Julian - BUGID 4419: Add columns "Importance" and "Priority" to "Set urgent Tests"
 **/
 
require('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db,false,false,"checkRights");
$args = init_args();

if($args->show_help)
{
    show_instructions('test_urgency');
    exit();  
}
$templateCfg = templateConfiguration();
$tplan_mgr = new testPlanUrgency($db);
$gui = new stdClass();

// $filters = null;
// // $options = null;
// $options = array('details' => 'platform');
// $xx=$tplan_mgr->getPriority($args->tplan_id,$filters,$options);
// new dBug($xx);

// $options = null;
// $xx=$tplan_mgr->getPriority($args->tplan_id,$filters,$options);
// new dBug($xx);

$node_info = $tplan_mgr->tree_manager->get_node_hierarchy_info($args->node_id);
$gui->node_name = $node_info['name'];
$gui->user_feedback = null;
$gui->node_id = $args->node_id;
$gui->tplan_id = $args->tplan_id;
$gui->tplan_name = $args->tplan_name;


// Set urgency for test suite
if($args->urgency != OFF)
{
	$gui->user_feedback['type'] = $tplan_mgr->setSuiteUrgency($args->tplan_id, $args->node_id, $args->urgency);
	$msg_key = ($gui->user_feedback['type'] == OK) ? "feedback_urgency_ok" : "feedback_urgency_fail";
	$gui->user_feedback['message'] = lang_get($msg_key);
}

// Set urgency for individual testcases
if(isset($args->urgency_tc))
{
	foreach ($args->urgency_tc as $id => $urgency) {
		$tplan_mgr->setTestUrgency($args->tplan_id, $id, $urgency);
	}
}

// get the current urgency for child test cases
$gui->listTestCases = $tplan_mgr->getSuiteUrgency($args->tplan_id, $args->node_id,$args->tproject_id);

// get priority for each test case
foreach ((array)$gui->listTestCases as $id => $tcase) 
{
	$gui->listTestCases[$id]['priority'] = priority_to_level($tcase['priority']);
}

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: init_args()

  args: -
  
  returns: object with user input.

*/
function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);
    
    $args = new stdClass();
    $args->show_help = (isset($_REQUEST['level']) && $_REQUEST['level']=='testproject');
    
    $args->tproject_id = isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : $_SESSION['testprojectID'];
    $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID'];
    $args->tplan_name = $_SESSION['testplanName'];
    $args->node_type = isset($_REQUEST['level']) ? $_REQUEST['level'] : OFF;
    $args->node_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ERROR;

	// Sets urgency for suite
    if (isset($_REQUEST['high_urgency']))
    	$args->urgency = HIGH;
    elseif (isset($_REQUEST['medium_urgency']))
    	$args->urgency = MEDIUM;
    elseif (isset($_REQUEST['low_urgency']))
    	$args->urgency = LOW;
    else
    	$args->urgency = OFF;

	// Sets urgency for every single tc
	if (isset($_REQUEST['urgency'])) {
		$args->urgency_tc = $_REQUEST['urgency'];
	}
    	
    return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_planning');
}
?>