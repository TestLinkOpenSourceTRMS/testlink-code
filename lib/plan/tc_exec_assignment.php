<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @version $Id: tc_exec_assignment.php,v 1.22 2008/05/10 14:38:20 franciscom Exp $ 
 * 
 * rev :
 *       20080312 - franciscom - BUGID 1427
 *       20080114 - franciscom - added testcase external_id management
 *       20071228 - franciscom - BUG build combo of users using only users
 *                               that can execute test cases in testplan.
 * 
 *       20070912 - franciscom - BUGID 1041
 *       20070124 - franciscom
 *       use show_help.php to apply css configuration to help pages
 */         
require_once(dirname(__FILE__)."/../../config.inc.php");
require_once("common.php");
require_once("assignment_mgr.class.php");
require_once("treeMenu.inc.php");
require("specview.php");

testlinkInitPage($db);

$tcase_cfg = config_get('testcase_cfg');
$tree_mgr = new tree($db); 
$tsuite_mgr = new testsuite($db); 
$tplan_mgr = new testplan($db); 
$tcase_mgr = new testcase($db); 
$assignment_mgr = new assignment_mgr($db); 

$template_dir = 'plan/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$args = init_args();
$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tplan_name = $tplan_info['name'];

$testCasePrefix = $tcase_mgr->tproject_mgr->getTestCasePrefix($args->tproject_id);
$testCasePrefix .= $tcase_cfg->glue_character;

$arrData = array();

if(!is_null($args->doAction))
{
	if(!is_null($args->achecked_tc))
	{
		$types_map = $assignment_mgr->get_available_types();
		$status_map = $assignment_mgr->get_available_status();

		$task_test_execution = $types_map['testcase_execution']['id'];
		$open = $status_map['open']['id'];
		$db_now = $db->db_now();

		$features2upd = array();
		$features2ins = array();
		$features2del = array();

		foreach($args->achecked_tc as $key_tc => $value_tcversion)
		{
			$feature_id = $args->feature_id[$key_tc];

			if($args->has_prev_assignment[$key_tc] > 0)
			{
				if($args->tester_for_tcid[$key_tc] > 0)
				{
					$features2upd[$feature_id]['user_id'] = $args->tester_for_tcid[$key_tc];
					$features2upd[$feature_id]['type'] = $task_test_execution;
					$features2upd[$feature_id]['status'] = $open;
					$features2upd[$feature_id]['assigner_id'] = $args->user_id;
				} 
				else
					$features2del[$feature_id] = $feature_id;
			}
			else if($args->tester_for_tcid[$key_tc] > 0)
			{
				$features2ins[$feature_id]['user_id'] = $args->tester_for_tcid[$key_tc];
				$features2ins[$feature_id]['type'] = $task_test_execution;
				$features2ins[$feature_id]['status'] = $open;
				$features2ins[$feature_id]['creation_ts'] = $db_now;
				$features2ins[$feature_id]['assigner_id'] = $args->user_id;
			}
		}
		if(count($features2upd) > 0)
			$assignment_mgr->update($features2upd);
		if(count($features2del) > 0)
			$assignment_mgr->delete_by_feature_id($features2del);
		if(count($features2ins) > 0)
			$assignment_mgr->assign($features2ins);
	}  
}

$users = getUsersForHtmlOptions($db);
$testers = getTestersForHtmlOptions($db,$args->tplan_id,$args->tproject_id);
$map_node_tccount = get_testplan_nodes_testcount($db,$args->tproject_id, $args->tproject_name,
                                                    $args->tplan_id,$tplan_name,$args->keyword_id);

switch($args->level)
{
	case 'testcase':
		// build the data need to call gen_spec_view
		$my_path = $tree_mgr->get_path($args->id);
		$idx_ts = count($my_path) - 1;
		$tsuite_data= $my_path[$idx_ts - 1];
		
		
		$status_quo = $tcase_mgr->get_versions_status_quo($args->id, $args->version_id);
		$linked_items[$args->id] = $status_quo[$args->version_id];
		$linked_items[$args->id]['testsuite_id'] = $tsuite_data['id'];
		$linked_items[$args->id]['tc_id'] = $args->id;
		
		$exec_assignment = $tcase_mgr->get_version_exec_assignment($args->version_id,$args->tplan_id);
		$linked_items[$args->id]['user_id'] = $exec_assignment[$args->version_id]['user_id'];
		$linked_items[$args->id]['feature_id'] = $exec_assignment[$args->version_id]['feature_id'];
		
		$my_out = gen_spec_view($db,'testplan',$args->tplan_id,$tsuite_data['id'],$tsuite_data['name'],
				    		            $linked_items,$map_node_tccount,
							              $args->keyword_id,FILTER_BY_TC_OFF,WRITE_BUTTON_ONLY_IF_LINKED);
							           
		// index 0 contains data for the parent test suite of this test case, 
		// other elements are not needed.
		$out=array();
		$out['spec_view'][0]=$my_out['spec_view'][0];
		$out['num_tc']=1;
		break;
		
	case 'testsuite':
		$tsuite_data = $tsuite_mgr->get_by_id($args->id);
		
		// BUGID 1041
		$tplan_linked_tcversions=$tplan_mgr->get_linked_tcversions($args->tplan_id,FILTER_BY_TC_OFF,
		                                                           $args->keyword_id,FILTER_BY_EXECUTE_STATUS_OFF,
		                                                           $args->assigned_to);
		$out = gen_spec_view($db,'testplan',$args->tplan_id,$args->id,$tsuite_data['name'],
                         $tplan_linked_tcversions,
                         $map_node_tccount,
                         $args->keyword_id,FILTER_BY_TC_OFF,WRITE_BUTTON_ONLY_IF_LINKED);
		break;

	default:
	  // @ MUST BE REFACTORED
		// show instructions
		redirect($_SESSION['basehref'] . "/lib/general/show_help.php?help=tc_exec_assignment&locale={$_SESSION['locale']}");
		break;
}

$smarty = new TLSmarty();
$smarty->assign('testCasePrefix', $testCasePrefix);
$smarty->assign('users', $users);
$smarty->assign('testers', $testers);
$smarty->assign('has_tc', ($out['num_tc'] > 0 ? 1:0));
$smarty->assign('arrData', $out['spec_view']);
$smarty->assign('testPlanName', $tplan_name);
$smarty->display($template_dir . $default_template);


/*
  function: 

  args :
  
  returns: 

*/
function init_args()
{
	$_REQUEST = strings_stripSlashes($_REQUEST);
  $args = new stdClass();
	$args->user_id = $_SESSION['userID'];
	$args->tproject_id = $_SESSION['testprojectID'];
	$args->tproject_name = $_SESSION['testprojectName'];

	$args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];

	$key2loop=array('doAction' => null,'level' => null , 'achecked_tc' => null, 
		              'version_id' => 0, 'keyword_id' => 0, 'has_prev_assignment' => null,
		              'tester_for_tcid' => null, 'feature_id' => null, 'id' => 0, 'assigned_to' => 0);
	foreach($key2loop as $key => $value)
	{
		$args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
	}

	return $args;
}
?>