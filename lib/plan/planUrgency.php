<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource $RCSfile: planUrgency.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2008/07/18 14:26:23 $ by $Author: havlat $
 * 
 * @copyright Copyright (c) 2008, TestLink community
 * @author Martin Havlat
 * 
 * The page allows to define urgency of a Test Suite. 
 * It requires "prioritization" feature enabled.
 *
 * --------------------------------------------------------------------------------------
 * Revision:
 *  None.
 *
 * ------------------------------------------------------------------------------------ */
 
require('../../config.inc.php');
require_once('common.php');
require_once('priority.inc.php');

testlinkInitPage($db);

$_REQUEST = strings_stripSlashes($_REQUEST);

$args = new stdClass();
$args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];
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

tLog('planUrgency.php> Arguments: Node='.$args->node_id.' Urgency='.$args->urgency);

$tplan_mgr = new testPlanUrgency($db);

// get node name
$node_name = $tplan_mgr->get_node_name($args->node_id);

// set urgency
if($args->urgency != OFF)
{
	$user_feedback['type'] = $tplan_mgr->setSuiteUrgency($args->tplan_id, $args->node_id, $args->urgency);
	
	if ($user_feedback['type'] == OK)
		$user_feedback['message'] = lang_get("feedback_urgency_ok");
	else
		$user_feedback['message'] = lang_get("feedback_urgency_fail");;
}
else
{
	$user_feedback = null;
}


// get the current urgency for child test cases
$listTestCases = $tplan_mgr->getSuiteUrgency($args->tplan_id, $args->node_id);


$smarty = new TLSmarty();
$smarty->assign('user_feedback', $user_feedback);
$smarty->assign('listTestCases', $listTestCases);
$smarty->assign('node_name', $node_name);
$smarty->assign('node_id', $args->node_id);
$smarty->assign('tplan_id', $args->tplan_id);
$smarty->assign('tplan_name', $_SESSION['testPlanName']);
$smarty->display('plan/planUrgency.tpl');


?>