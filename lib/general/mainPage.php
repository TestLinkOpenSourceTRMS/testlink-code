<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: mainPage.php,v $
 *
 * @version $Revision: 1.33 $ $Author: schlundus $
 * @modified $Date: 2007/12/17 21:31:45 $
 *
 * @author Martin Havlat
 * 
 * Page has two functions: navigation and select Test Plan
 *
 * This file is the first page that the user sees when they log in.
 * Most of the code in it is html but there is some logic that displays
 * based upon the login. 
 * There is also some javascript that handles the form information.
 *
 * rev :
 *       20070725 - franciscom - refactoring of rights checking 
 *       20070509 - franciscom - improving test plan availabilty checking
 *       20070829 - jbarchibald - fix bug 1000 - Testplan role assignments
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('configCheck.php');
testlinkInitPage($db,TRUE);

$smarty = new TLSmarty();
$tproject_mgr = new testproject($db);

$testprojectID = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;

// ----------------------------------------------------------------------
/** redirect admin to create product if not found */
$can_manage_tprojects=has_rights($db,'mgt_modify_product');
if ($can_manage_tprojects && !isset($_SESSION['testprojectID']))
{ 
	redirect($_SESSION['basehref'] . 'lib/project/projectedit.php?show_create_screen');
}
// ----------------------------------------------------------------------

// ----- Test Project Section ----------------------------------  
if(has_rights($db,"mgt_view_tc"))
{ 
  	//user can view tcs 
    $smarty->assign('view_tc_rights', 'yes');
    
    //users can modify tcs
    $smarty->assign('modify_tc_rights', has_rights($db,"mgt_modify_tc")); 
}

// REQS
$smarty->assign('view_req_rights', has_rights($db,"mgt_view_req")); 
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 
$smarty->assign('opt_requirements', isset($_SESSION['testprojectOptReqs']) ? $_SESSION['testprojectOptReqs'] : null); 

// view and modify Keywords 
$smarty->assign('view_keys_rights', has_rights($db,"mgt_view_key"));
$smarty->assign('modify_keys_rights', has_rights($db,"mgt_modify_key"));

// User has test project rights
$smarty->assign('modify_product_rights', $can_manage_tprojects);


// ----- Test Statistics Section --------------------------
// only print the metrics table if it is enabled
// $smarty->assign('metricsEnabled', MAIN_PAGE_METRICS_ENABLED);
// if(MAIN_PAGE_METRICS_ENABLED == "TRUE")
// {
// 	require_once('myTPInfo.php');
//   $smarty->assign('myTPdata', printMyTPData($db));
// }

$filter_tp_by_product = 1;
if(isset($_REQUEST['filter_tp_by_product']))
	$filter_tp_by_product = 1;
else if(isset($_REQUEST['filter_tp_by_product_hidden']))
	$filter_tp_by_product = 0;
else
{
	if (isset($_SESSION['filter_tp_by_product']))
		$filter_tp_by_product = $_SESSION['filter_tp_by_product'];
}
$_SESSION['filter_tp_by_product'] = $filter_tp_by_product;
$smarty->assign('filter_tp_by_product',$filter_tp_by_product);

// ----- Test Plan Section ----------------------------------  
$num_active_tplans =0;
$active_tplans = $tproject_mgr->get_all_testplans($testprojectID,0,ACTIVE);

if( !is_null($active_tplans) )
{
  $num_active_tplans = count($active_tplans);
}


// get Test Plans available for the user 
// 20070906 - interface changes
$arrPlans = getAccessibleTestPlans($db,$testprojectID,$_SESSION['userID'],$filter_tp_by_product);

$testPlanID = isset($_SESSION['testPlanId']) ? intval($_SESSION['testPlanId']) : 0;

$roles = getAllRoles($db);
$testPlanRole = null;
$role_separator=config_get('role_separator');

if ($testPlanID && isset($_SESSION['testPlanRoles'][$testPlanID]))
{
	$idx = $_SESSION['testPlanRoles'][$testPlanID]['role_id'];
	$testPlanRole = $role_separator->open . $roles[$idx] . $role_separator->close;
}
$securityNotes = getSecurityNotes($db);


$rights2check=array('testplan_execute','testplan_create_build',
                    'testplan_metrics','testplan_planning',
                    'cfield_view', 'cfield_management');
                        
foreach($rights2check as $key => $the_right)
{
  $smarty->assign($the_right, has_rights($db,$the_right));
}                         

$smarty->assign('metrics_dashboard_url','lib/results/metrics_dashboard.php');

$smarty->assign('testplan_creating', has_rights($db,"mgt_testplan_create"));
$smarty->assign('tp_user_role_assignment', has_rights($db,"testplan_user_role_assignment"));
$smarty->assign('tproject_user_role_assignment', has_rights($db,"user_role_assignment",null,-1));
$smarty->assign('usermanagement_rights',has_rights($db,"mgt_users"));

$smarty->assign('securityNotes',$securityNotes);
$smarty->assign('arrPlans', $arrPlans);
$smarty->assign('countPlans', count($arrPlans));
$smarty->assign('num_active_tplans', $num_active_tplans);
$smarty->assign('launcher','lib/general/frmWorkArea.php');
$smarty->assign('show_filter_tp_by_product',$g_ui_show_check_filter_tp_by_testproject);
$smarty->assign('sessionProductID',$testprojectID);	
$smarty->assign('sessionTestPlanID',$testPlanID);
$smarty->assign('testPlanRole',$testPlanRole);
$smarty->display('mainPage.tpl');
?>
