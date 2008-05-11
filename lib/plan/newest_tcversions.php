<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @version $Id: newest_tcversions.php,v 1.8 2008/05/11 22:13:22 schlundus Exp $ 
 * 
 *
 * rev :
 *      20070930 - franciscom - added tplan combo box
 *
 */         
require('../../config.inc.php');
require_once("common.php");

testlinkInitPage($db);

$template_dir = 'plan/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$tree_mgr = new tree($db); 
$tsuite_mgr = new testsuite($db); 
$tplan_mgr = new testplan($db); 
$tcase_mgr = new testcase($db); 


$args = init_args();
$user_feedback = '';

$tcasePrefix = $tcase_mgr->tproject_mgr->getTestCasePrefix($args->tproject_id);
$tplan_info = $tcase_mgr->get_by_id($args->tplan_id);
$tplan_name = $tplan_info['name'];

$linked_tcases = $tplan_mgr->get_linked_tcversions($args->tplan_id);
$tcases = $tplan_mgr->get_linked_and_newest_tcversions($args->tplan_id);

$qty_linked = count($linked_tcases);
$qty_newest = count($tcases);

$show_details = 0;
if($qty_linked)
{
	if($qty_newest)
		$show_details = 1;
  	else
    	$user_feedback = lang_get('no_newest_version_of_linked_tcversions');  
} 
else
	$user_feedback = lang_get('no_linked_tcversions');  

$tplans = getAccessibleTestPlans($db,$args->tproject_id,$args->user_id,1);
$map_tplans = array();
foreach($tplans as $key => $value)
{
	$map_tplans[$value['id']] = $value['name'];
}

$testcase_cfg = config_get('testcase_cfg');

$smarty = new TLSmarty();
$smarty->assign('tcasePrefix',$tcasePrefix . $testcase_cfg->glue_character);
$smarty->assign('tplans', $map_tplans);
$smarty->assign('tplan_id', $args->tplan_id);
$smarty->assign('can_manage_testplans', has_rights($db,"mgt_testplan_create"));
$smarty->assign('show_details', $show_details );
$smarty->assign('user_feedback', $user_feedback);
$smarty->assign('testPlanName', $tplan_name);
$smarty->assign('tproject_name', $args->tproject_name);
$smarty->assign('testcases', $tcases);
$smarty->display($template_dir . $default_template);

function init_args()
{
	$_REQUEST = strings_stripSlashes($_REQUEST);
    
    $args = new stdClass();
    $args->user_id = $_SESSION['userID'];
    $args->tproject_id = $_SESSION['testprojectID'];
    $args->tproject_name = $_SESSION['testprojectName'];
    
    $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];
    
    $args->id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
    $args->version_id = isset($_REQUEST['version_id']) ? $_REQUEST['version_id'] : 0;
    $args->level = isset($_REQUEST['level']) ? $_REQUEST['level'] : null;
    
    // Can be a list (string with , (comma) has item separator), 
    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;

    return $args;  
}
?>