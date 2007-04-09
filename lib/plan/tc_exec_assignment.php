<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @version $Id: tc_exec_assignment.php,v 1.7 2007/04/09 08:02:02 franciscom Exp $ 
 * 
 * 20070124 - franciscom
 * use show_help.php to apply css configuration to help pages
 */         
require_once(dirname(__FILE__)."/../../config.inc.php");
require_once(dirname(__FILE__)."/../functions/common.php");
require_once(dirname(__FILE__)."/../functions/assignment_mgr.class.php");
require_once(dirname(__FILE__)."/../functions/treeMenu.inc.php");
require_once("plan.inc.php");

testlinkInitPage($db);

$tree_mgr = new tree($db); 
$tsuite_mgr = new testsuite($db); 
$tplan_mgr = new testplan($db); 
$tcase_mgr = new testcase($db); 
$assignment_mgr = new assignment_mgr($db); 

$tproject_id = $_SESSION['testprojectID'];
$tproject_name = $_SESSION['testprojectName'];

$tplan_id = $_SESSION['testPlanId'];
$tplan_name = $_SESSION['testPlanName'];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
$version_id = isset($_REQUEST['version_id']) ? $_REQUEST['version_id'] : 0;
$level = isset($_REQUEST['level']) ? $_REQUEST['level'] : null;
$keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;
$do_action = isset($_POST['assign_tc']) ? 1 : 0;

$resultString = null;
$arrData = array();

// ---------------------------------------------------------------------------------------
if($do_action)
{
  $a_tc = isset($_POST['achecked_tc']) ? $_POST['achecked_tc'] : null;
  if(!is_null($a_tc))
  {
      $types_map = $assignment_mgr->get_available_types();
      $status_map = $assignment_mgr->get_available_status();
      
      $task_test_execution = $types_map['testcase_execution']['id'];
      $open = $status_map['open']['id'];
      $db_now = $db->db_now();
      
      $features2upd = array();
      $features2ins = array();
      $features2del = array();
      
      foreach($a_tc as $key_tc => $value_tcversion)
      {
        $feature_id = $_POST['feature_id'][$key_tc];
        
        if($_POST['has_prev_assignment'][$key_tc] > 0)
        {
           if( $_POST['tester_for_tcid'][$key_tc] > 0 )
           {
              $features2upd[$feature_id]['user_id'] = $_POST['tester_for_tcid'][$key_tc];
              $features2upd[$feature_id]['assigner_id'] = $_SESSION['userID'];
              $features2upd[$feature_id]['type'] = $task_test_execution;
              $features2upd[$feature_id]['status'] = $open;
           } 
           else
           {
              $features2del[$feature_id] = $feature_id;
           }
        }
        else if($_POST['tester_for_tcid'][$key_tc] > 0)
        {
           $features2ins[$feature_id]['user_id'] = $_POST['tester_for_tcid'][$key_tc];
           $features2ins[$feature_id]['type'] = $task_test_execution;
           $features2ins[$feature_id]['status'] = $open;
           $features2ins[$feature_id]['creation_ts'] = $db_now;
           $features2ins[$feature_id]['assigner_id'] = $_SESSION['userID'];
        }
      }
      
      if( count($features2upd) > 0 )
      {
         $assignment_mgr->update($features2upd);      
      }
      if( count($features2del) > 0 )
      {
         $assignment_mgr->delete_by_feature_id($features2del);      
      }
      if( count($features2ins) > 0 )
      {
         $assignment_mgr->assign($features2ins);      
      }
  }  
}

$users = get_users_for_html_options($db,ALL_USERS_FILTER,ADD_BLANK_OPTION);

$map_node_tccount = get_testplan_nodes_testcount($db,$tproject_id, $tproject_name,
                                                    $tplan_id,$tplan_name,$keyword_id);


switch($level)
{
	case 'testcase':
		// build the data need to call gen_spec_view
		$my_path = $tree_mgr->get_path($id);
		$idx_ts = count($my_path) - 1;
		$tsuite_data= $my_path[$idx_ts - 1];
		
		
		$pp = $tcase_mgr->get_versions_status_quo($id, $version_id);
		$linked_items[$id] = $pp[$version_id];
		$linked_items[$id]['testsuite_id'] = $tsuite_data['id'];
		$linked_items[$id]['tc_id'] = $id;
		
		$p3 = $tcase_mgr->get_version_exec_assignment($version_id,$tplan_id);
		$linked_items[$id]['user_id'] = $p3[$version_id]['user_id'];
		$linked_items[$id]['feature_id'] = $p3[$version_id]['feature_id'];
		
		$my_out = gen_spec_view($db,'testplan',$tplan_id,$tsuite_data['id'],$tsuite_data['name'],
				    		            $linked_items,$map_node_tccount,
							              $keyword_id,FILTER_BY_TC_OFF,WRITE_BUTTON_ONLY_IF_LINKED);
							           
    // index 0 conatins data for the parent test suite of this test case, 
    // other elements are not needed.
		$out=array();
		$out['spec_view'][0]=$my_out['spec_view'][0];
		// $out['spec_view'][0]['next_level']=0;
		$out['num_tc']=1;
		break;


	case 'testsuite':
		$tsuite_data = $tsuite_mgr->get_by_id($id);
		$out = gen_spec_view($db,'testplan',$tplan_id,$id,$tsuite_data['name'],
                         $tplan_mgr->get_linked_tcversions($tplan_id,FILTER_BY_TC_OFF,$keyword_id),
                         $map_node_tccount,
                         $keyword_id,FILTER_BY_TC_OFF,WRITE_BUTTON_ONLY_IF_LINKED);
                         
    
    // 20070408 - for new gui
    // $spec_view=$out['spec_view'];
    // $qta_loops=count($spec_view)-1;
    // $out['spec_view'][$qta_loops]['next_level']=0;  
    // for($idx=0; $idx < $qta_loops; $idx++)
    // {
    //   $out['spec_view'][$idx]['next_level']=$out['spec_view'][$idx+1]['level'];  
    // }
 break;
		
		
	default:
		// show instructions
		//redirect( $_SESSION['basehref'] . $g_rpath['instructions'].'/tc_exec_assignment.html');
  	redirect($_SESSION['basehref'] . "/lib/general/show_help.php?help=tc_exec_assignment&locale={$_SESSION['locale']}");
	break;
}

$smarty = new TLSmarty();
$smarty->assign('users', $users);
$smarty->assign('has_tc', ($out['num_tc'] > 0 ? 1:0));
$smarty->assign('arrData', $out['spec_view']);
$smarty->assign('testPlanName', $tplan_name);
$smarty->display('tc_exec_assignment.tpl');
?>