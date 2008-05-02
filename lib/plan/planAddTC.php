<?php
////////////////////////////////////////////////////////////////////////////////
// @version $Id: planAddTC.php,v 1.51 2008/05/02 07:09:36 franciscom Exp $
// File:     planAddTC.php
// Purpose:  link/unlink test cases to a test plan
//
//
// rev :
//      20080404 - franciscom - reorder logic
//      20080114 - franciscom - added testCasePrefix management
//      20070930 - franciscom - BUGID
//      20070912 - franciscom - BUGID 905
//      20070124 - franciscom
//      use show_help.php to apply css configuration to help pages
//
////////////////////////////////////////////////////////////////////////////////
require_once('../../config.inc.php');
require_once("common.php");
require("specview.php");

testlinkInitPage($db);

$tree_mgr = new tree($db);
$tsuite_mgr = new testsuite($db);
$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$template_dir = 'plan/';

$args = init_args();
$tcase_cfg = config_get('testcase_cfg');
$do_display = 0;

$testCasePrefix = $tproject_mgr->getTestCasePrefix($args->tproject_id);
$testCasePrefix .= $tcase_cfg->glue_character;


$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tplan_name = $tplan_info['name'];


$smarty = new TLSmarty();
$smarty->assign('testPlanName', $tplan_name);

define('DONT_FILTER_BY_TCASE_ID',null);
define('ANY_EXEC_STATUS',null);

switch($args->item_level)
{
    case 'testsuite':
		$map_node_tccount = get_testproject_nodes_testcount($db,$args->tproject_id, $args->tproject_name,
		                                                   $args->keyword_id);
		
		$tsuite_data = $tsuite_mgr->get_by_id($args->object_id);
		
		$tplan_linked_tcversions = $tplan_mgr->get_linked_tcversions($args->tplan_id,DONT_FILTER_BY_TCASE_ID,
		                                                            $args->keyword_id,ANY_EXEC_STATUS,ANY_OWNER);
		
		$out = gen_spec_view($db,'testproject',$args->tproject_id,$args->object_id,$tsuite_data['name'],
		                    $tplan_linked_tcversions,$map_node_tccount,$args->keyword_id,DONT_FILTER_BY_TCASE_ID);
		
		$do_display = 1;
		break;

    case 'testproject':
	    show_instructions('planAddTC');
	    exit();
	    break;
}


switch($args->doAction)
{
	
	case 'doAddRemove':
	// Remember:  checkboxes exist only if are checked
	if(!is_null($args->testcases2add))
	{
		$atc = $args->testcases2add;
		$atcversion = $args->tcversion_for_tcid;
		$items_to_link = my_array_intersect_keys($atc,$atcversion);
		$tplan_mgr->link_tcversions($args->tplan_id,$items_to_link);
	
	}

	if(!is_null($args->testcases2remove))
	{
		// remove without warning
		$rtc = $args->testcases2remove;
		$tplan_mgr->unlink_tcversions($args->tplan_id,$rtc);
	}

  doReorder($args,$tplan_mgr);
	$do_display = 1;
	break;
	
	case 'doReorder':
	doReorder($args,$tplan_mgr);
	$do_display = 1;
	break;
	
  default:
	break;
}


if($do_display)
{
	  $map_node_tccount = get_testproject_nodes_testcount($db,$args->tproject_id, $args->tproject_name,
	                                                      $args->keyword_id);
	  $tsuite_data = $tsuite_mgr->get_by_id($args->object_id);
	  
	  // BUGID 905
	  $tplan_linked_tcversions = $tplan_mgr->get_linked_tcversions($args->tplan_id,DONT_FILTER_BY_TCASE_ID,
	                                                               $args->keyword_id);
    
	  $out = gen_spec_view($db,'testproject',$args->tproject_id,$args->object_id,$tsuite_data['name'],
	  	                   $tplan_linked_tcversions, $map_node_tccount,$args->keyword_id,DONT_FILTER_BY_TCASE_ID);
    
    
	  // full_control, controls the operations planAddTC_m1.tpl will allow
	  // 1 => add/remove
	  // 0 => just remove
	  $smarty->assign('full_control', 1);
	  $smarty->assign('testCasePrefix', $testCasePrefix);
	  $smarty->assign('has_tc', ($out['num_tc'] > 0 ? 1 : 0));
	  $smarty->assign('arrData', $out['spec_view']);
	  $smarty->assign('has_linked_items',$out['has_linked_items']);
	  $smarty->assign('key', '');
	  $smarty->display($template_dir .  'planAddTC_m1.tpl');
}

/*
  function: init_args
            creates a sort of namespace

  args:

  returns: object with some REQUEST and SESSION values as members

*/
function init_args()
{
	$_REQUEST = strings_stripSlashes($_REQUEST);

	$args = new stdClass();
	$args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];
	$args->keyword_id = isset($_REQUEST['keyword_id']) ? intval($_REQUEST['keyword_id']) : 0;
	$args->object_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$args->item_level = isset($_REQUEST['edit']) ? trim($_REQUEST['edit']) : null;
	$args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : "default";
	$args->tproject_id = $_SESSION['testprojectID'];
	$args->tproject_name = $_SESSION['testprojectName'];
	$args->testcases2add = isset($_REQUEST['achecked_tc']) ? $_REQUEST['achecked_tc'] : null;
	$args->tcversion_for_tcid = isset($_REQUEST['tcversion_for_tcid']) ? $_REQUEST['tcversion_for_tcid'] : null;
	$args->testcases2remove = isset($_REQUEST['remove_checked_tc']) ? $_REQUEST['remove_checked_tc'] : null;

  // 20080331 -franciscom
	$args->testcases2order = isset($_REQUEST['exec_order']) ? $_REQUEST['exec_order'] : null;
	$args->linkedOrder = isset($_REQUEST['linked_exec_order']) ? $_REQUEST['linked_exec_order'] : null;
	$args->linkedVersion = isset($_REQUEST['linked_version']) ? $_REQUEST['linked_version'] : null;
	return $args;
}



/*
  function: doSaveOrder
            

  args:

  returns: 

*/
function doReorder(&$argsObj,&$tplanMgr)
{
    $mapo=null;
    if(!is_null($argsObj->linkedVersion))
    {
        // Using memory of linked test case, try to get order
        foreach($argsObj->linkedVersion as $tcid => $tcversion_id)
        {
            if($argsObj->linkedOrder[$tcid] != $argsObj->testcases2order[$tcid] )
            { 
                $mapo[$tcversion_id]=$argsObj->testcases2order[$tcid];
            }    
        }
    }
    
    // Now add info for new liked test cases if any
    if(!is_null($argsObj->testcases2add))
    {
        foreach($argsObj->testcases2add as $tcid)
        {
            $tcversion_id=$argsObj->tcversion_for_tcid[$tcid];
            $mapo[$tcversion_id]=$argsObj->testcases2order[$tcid];
        }
    }  
    
    if( !is_null($mapo) )
    {
        $tplanMgr->setExecutionOrder($argsObj->tplan_id,$mapo);  
    }
    
}

?>