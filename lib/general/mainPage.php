<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: mainPage.php,v $
 *
 * @version $Revision: 1.42 $ $Author: schlundus $
 * @modified $Date: 2008/01/31 22:15:48 $
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
 *       20080120 - franciscom - added logic to enable/disable test case search link
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
$currentUser = $_SESSION['currentUser'];
$userID = $currentUser->dbID;

// ----------------------------------------------------------------------
/** redirect admin to create testproject if not found */
$can_manage_tprojects = has_rights($db,'mgt_modify_product');
if ($can_manage_tprojects && !isset($_SESSION['testprojectID']))
{ 
	redirect($_SESSION['basehref'] . 'lib/project/projectEdit.php?doAction=create');
}
// ----------------------------------------------------------------------

// ----- Test Project Section ----------------------------------  
if(has_rights($db,"mgt_view_tc"))
{ 
  	//user can view tcs 
    $smarty->assign('view_tc_rights', 'yes');
    
    //users can modify tcs
    $smarty->assign('modify_tc_rights', has_rights($db,"mgt_modify_tc")); 
    
   	$hasTestCases = $tproject_mgr->count_testcases($testprojectID) > 0 ? 1 : 0;
   	$smarty->assign('hasTestCases',$hasTestCases);
}

// REQS
$smarty->assign('rights_reqs_view', has_rights($db,"mgt_view_req")); 
$smarty->assign('rights_reqs_edit', has_rights($db,"mgt_modify_req")); 
$smarty->assign('opt_requirements', isset($_SESSION['testprojectOptReqs']) ? $_SESSION['testprojectOptReqs'] : null); 

// view and modify Keywords 
$smarty->assign('rights_keywords_view', has_rights($db,"mgt_view_key"));
$smarty->assign('rights_keywords_edit', has_rights($db,"mgt_modify_key"));

// User has test project rights
$smarty->assign('rights_project_edit', $can_manage_tprojects);
$smarty->assign('rights_configuration', has_rights($db,"system_configuraton"));


// ----- Test Statistics Section --------------------------
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
$num_active_tplans = sizeof($tproject_mgr->get_all_testplans($testprojectID,0,ACTIVE));

// get Test Plans available for the user 
$arrPlans = getAccessibleTestPlans($db,$testprojectID,$userID,$filter_tp_by_product);

$testPlanRole = null;
$testPlanID = isset($_SESSION['testPlanId']) ? intval($_SESSION['testPlanId']) : 0;
if ($testPlanID && isset($currentUser->tplanRoles[$testPlanID]))
{
	$role_separator = config_get('role_separator');
	$role = $currentUser->tplanRoles[$testPlanID];
	$testPlanRole = $role_separator->open . $role->name . $role_separator->close;
}

$rights2check = array('testplan_execute','testplan_create_build',
                    'testplan_metrics','testplan_planning',
                    'cfield_view', 'cfield_management');
                        
foreach($rights2check as $key => $the_right)
{
	$smarty->assign($the_right, has_rights($db,$the_right));
}                         

$smarty->assign('metrics_dashboard_url','lib/results/metricsDashboard.php');
$smarty->assign('testplan_creating', has_rights($db,"mgt_testplan_create"));
$smarty->assign('tp_user_role_assignment', has_rights($db,"testplan_user_role_assignment"));
$smarty->assign('tproject_user_role_assignment', has_rights($db,"user_role_assignment",null,-1));
$smarty->assign('usermanagement_rights',has_rights($db,"mgt_users"));
$smarty->assign('securityNotes',getSecurityNotes($db));
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