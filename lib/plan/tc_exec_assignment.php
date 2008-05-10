<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @version $Id: tc_exec_assignment.php,v 1.24 2008/05/10 17:59:15 franciscom Exp $ 
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

$tree_mgr = new tree($db); 
$tplan_mgr = new testplan($db); 
$tcase_mgr = new testcase($db); 
$assignment_mgr = new assignment_mgr($db); 

$templateCfg = templateConfiguration();

$args = init_args();
$gui=initializeGui($db,$args,$tplan_mgr,$tcase_mgr);

$keywordsFilter=null;
if( is_array($args->keyword_id) )
{
    $keywordsFilter=new stdClass();
    $keywordsFilter->items = $args->keyword_id;
    $keywordsFilter->type = $gui->keywordsFilterType->selected;
}

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

$map_node_tccount = get_testplan_nodes_testcount($db,$args->tproject_id, $args->tproject_name,
                                                 $args->tplan_id,$gui->testPlanName,$keywordsFilter);

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
		
		// 20080510 - franciscom
		$my_out = gen_spec_view($db,'testplan',$args->tplan_id,$tsuite_data['id'],$tsuite_data['name'],
				    		            $linked_items,$map_node_tccount,
							              $keywordsFilter->items,FILTER_BY_TC_OFF,WRITE_BUTTON_ONLY_IF_LINKED);
							           
		// index 0 contains data for the parent test suite of this test case, 
		// other elements are not needed.
		$out=array();
		$out['spec_view'][0]=$my_out['spec_view'][0];
		$out['num_tc']=1;
		break;
		
	case 'testsuite':
    $out=processTestSuite($db,$args,$map_node_tccount,$keywordsFilter,$tplan_mgr,$tcase_mgr);
		break;

	default:
		show_instructions('tc_exec_assignment');
		break;
}

$gui->items=$out['spec_view'];
$gui->has_tc=$out['num_tc'] > 0 ? 1:0;

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


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
	  	              'version_id' => 0, 'has_prev_assignment' => null,
	  	              'tester_for_tcid' => null, 'feature_id' => null, 'id' => 0, 'assigned_to' => 0);
	  foreach($key2loop as $key => $value)
	  {
	  	$args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
	  }
    
    // Can be a list (string with , (comma) has item separator), that will be trasformed in an array.
    $keywordSet = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : null;
    $args->keyword_id = is_null($keywordSet) ? 0 : explode(',',$keywordSet); 
    $args->keywordsFilterType=isset($_REQUEST['keywordsFilterType']) ? $_REQUEST['keywordsFilterType'] : 'OR';
    
	  return $args;
}

/*
  function: initializeGui

  args :
  
  returns: 

*/
function initializeGui(&$dbHandler,$argsObj,&$tplanMgr,&$tcaseMgr)
{
    $tcase_cfg = config_get('testcase_cfg');
    $gui = new stdClass();
    
    $gui->testCasePrefix = $tcaseMgr->tproject_mgr->getTestCasePrefix($argsObj->tproject_id);
    $gui->testCasePrefix .= $tcase_cfg->glue_character;

    $gui->keywordsFilterType=$argsObj->keywordsFilterType;

    $tplan_info = $tplanMgr->get_by_id($argsObj->tplan_id);
    $gui->testPlanName = $tplan_info['name'];
    $gui->main_descr=lang_get('title_tc_exec_assignment') . $gui->testPlanName;
    
    
    $gui->users = getUsersForHtmlOptions($dbHandler);
    $gui->testers = getTestersForHtmlOptions($dbHandler,$argsObj->tplan_id,$argsObj->tproject_id);
    return $gui;
}


/*
  function: processTestSuite 

  args :
  
  returns: 

*/
function processTestSuite(&$dbHandler,&$argsObj,$map_node_tccount,
                          $keywordsFilter,&$tplanMgr,&$tcaseMgr)
{
    $out=keywordFilteredSpecView($dbHandler,$argsObj,$map_node_tccount,
                                 $keywordsFilter,$tplanMgr,$tcaseMgr);
    return $out;
}
?>