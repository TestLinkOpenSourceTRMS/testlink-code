<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource $RCSfile: planUrgency.php,v $
 * @version $Revision: 1.9 $
 * @modified $Date: 2008/10/16 18:50:53 $ by $Author: schlundus $
 * 
 * @copyright Copyright (c) 2008, TestLink community
 * @author Martin Havlat
 * 
 * Define urgency of a Test Suite. 
 * It requires "prioritization" feature enabled.
 *
 * --------------------------------------------------------------------------------------
 * Revision: 20080925 - franciscom - BUGID 1746
 *           20080721 - franciscom
 *           code refactored to follow last development standard.
 * ------------------------------------------------------------------------------------ */
 
require('../../config.inc.php');
require_once('common.php');
require_once('priority.class.php');
testlinkInitPage($db);
$args = init_args();

if($args->show_help)
{
    show_instructions('test_urgency');
    exit();  
}
$templateCfg = templateConfiguration();
$tplan_mgr = new testPlanUrgency($db);
$gui = new stdClass();

$node_info = $tplan_mgr->tree_manager->get_node_hierachy_info($args->node_id);
$gui->urgencyCfg = config_get('urgency');
$gui->node_name = $node_info['name'];
$gui->user_feedback = null;
$gui->node_id = $args->node_id;
$gui->tplan_id = $args->tplan_id;
$gui->tplan_name = $args->tplan_name;


if($args->urgency != OFF)
{
	$gui->user_feedback['type'] = $tplan_mgr->setSuiteUrgency($args->tplan_id, $args->node_id, $args->urgency);
	
	if ($gui->user_feedback['type'] == OK)
		$feedback = lang_get("feedback_urgency_ok");
	else
		$feedback = lang_get("feedback_urgency_fail");
		
	$gui->user_feedback['message'] = $feedback;
}

// get the current urgency for child test cases
$gui->listTestCases = $tplan_mgr->getSuiteUrgency($args->tplan_id, $args->node_id);

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
    
    $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];
    $args->tplan_name = $_SESSION['testPlanName'];
    $args->node_type = isset($_REQUEST['level']) ? $_REQUEST['level'] : OFF;
    $args->node_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ERROR;

    if (isset($_REQUEST['high_urgency']))
    	$args->urgency = HIGH;
    elseif (isset($_REQUEST['medium_urgency']))
    	$args->urgency = MEDIUM;
    elseif (isset($_REQUEST['low_urgency']))
    	$args->urgency = LOW;
    else
    	$args->urgency = OFF;
    	
    return $args;
}
?>