<?php
////////////////////////////////////////////////////////////////////////////////
// @version $Id: planAddTC.php,v 1.41 2008/02/04 19:41:35 schlundus Exp $
// File:     planAddTC.php
// Purpose:  link/unlink test cases to a test plan
//
//
// rev :
//      20080114 - franciscom - added testCasePrefix management
//      20070930 - franciscom - BUGID
//      20070912 - franciscom - BUGID 905
//      20070124 - franciscom
//      use show_help.php to apply css configuration to help pages
//
////////////////////////////////////////////////////////////////////////////////
require_once('../../config.inc.php');
require_once(dirname(__FILE__)."/../functions/common.php");
testlinkInitPage($db);

$tree_mgr = new tree($db); 
$tsuite_mgr = new testsuite($db); 
$tplan_mgr = new testplan($db); 
$tproject_mgr = new testproject($db); 

$template_dir = 'plan/';

$tcase_cfg = config_get('testcase_cfg');
$do_display = 0;
$tproject_id = $_SESSION['testprojectID'];
$tproject_name = $_SESSION['testprojectName'];

$testCasePrefix = $tproject_mgr->getTestCasePrefix($tproject_id);
$testCasePrefix .= $tcase_cfg->glue_character;

$tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];
$tplan_info = $tplan_mgr->get_by_id($tplan_id);
$tplan_name = $tplan_info['name'];

$keyword_id = isset($_REQUEST['keyword_id']) ? intval($_REQUEST['keyword_id']) : 0;
$object_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$smarty = new TLSmarty();
$smarty->assign('testPlanName', $tplan_name);

define('DONT_FILTER_BY_TCASE_ID',null);
define('ANY_EXEC_STATUS',null);

if($_GET['edit'] == 'testsuite')
{
    $map_node_tccount = get_testproject_nodes_testcount($db,$tproject_id, $tproject_name,
                                                           $keyword_id);

    $tsuite_data = $tsuite_mgr->get_by_id($object_id);

    $tplan_linked_tcversions = $tplan_mgr->get_linked_tcversions($tplan_id,DONT_FILTER_BY_TCASE_ID,
                                                               $keyword_id,ANY_EXEC_STATUS,ANY_OWNER);

    $out = gen_spec_view($db,'testproject',$tproject_id,$object_id,$tsuite_data['name'],
                         $tplan_linked_tcversions,$map_node_tccount,$keyword_id,DONT_FILTER_BY_TCASE_ID);
                       
    $do_display = 1;  
}
else if($_GET['edit'] == 'testproject')
{
	redirect($_SESSION['basehref'] . "/lib/general/staticPage.php?key=planAddTC");
	exit();
}

if(isset($_POST['do_action']))
{
	// Remember:  checkboxes exist only if are checked
	if(isset($_POST['achecked_tc']))
	{
		  $atc = $_POST['achecked_tc'];
		  $atcversion = $_POST['tcversion_for_tcid'];
		  $items_to_link = my_array_intersect_keys($atc,$atcversion);
		  $tplan_mgr->link_tcversions($tplan_id,$items_to_link);
	}
	
	if(isset($_POST['remove_checked_tc']))
	{
		// remove without warning
		$rtc = $_POST['remove_checked_tc'];
		$tplan_mgr->unlink_tcversions($tplan_id,$rtc);      
	}

	$map_node_tccount = get_testproject_nodes_testcount($db,$tproject_id, $tproject_name,
											   $keyword_id);
	$tsuite_data = $tsuite_mgr->get_by_id($object_id);
	// BUGID 905
	$tplan_linked_tcversions = $tplan_mgr->get_linked_tcversions($tplan_id,DONT_FILTER_BY_TCASE_ID,$keyword_id);

	$out = gen_spec_view($db,'testproject',$tproject_id,$object_id,$tsuite_data['name'],
		   $tplan_linked_tcversions,
		   $map_node_tccount,$keyword_id,DONT_FILTER_BY_TCASE_ID);
	$do_display = 1;
}

if($do_display)
{
	// full_control, controls the operations planAddTC_m1.tpl will allow
	// 1 => add/remove
	// 0 => just remove
	$smarty->assign('full_control', 1); 

	$smarty->assign('testCasePrefix', $testCasePrefix);
	$smarty->assign('has_tc', ($out['num_tc'] > 0 ? 1 : 0));
	$smarty->assign('arrData', $out['spec_view']);
	$smarty->assign('has_linked_items',$out['has_linked_items']);
	$smarty->display($template_dir .  'planAddTC_m1.tpl');
}
?>
