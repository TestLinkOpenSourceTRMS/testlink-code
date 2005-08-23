<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: mainPage.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2005/08/23 18:29:24 $
 *
 * @author Martin Havlat
 * 
 *	Page has two functions: navigation and select Test Plan
 *
 *  	This file is the first page that the user sees when they log in.
 *	Most of the code in it is html but there is some logic that displays
 *	based upon the login. There is also some javascript that handles the
 *	form information.
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('plan.core.inc.php');


// 20050811 - fm 
// it's realy ok ??? testlinkInitPage(TRUE);
testlinkInitPage(TRUE);
$smarty = new TLSmarty;

// ----------------------------------------------------------------------
/** redirect admin to create product if not found */
if ($_SESSION['role'] == 'admin' && !isset($_SESSION['productID']))
{ 
	redirect($_SESSION['basehref'] . 'lib/admin/adminProductNew.php');
}
// ----------------------------------------------------------------------

// ----- Product Section ----------------------------------  
if(has_rights("mgt_view_tc"))
{ 
  	//user can view tcs 
    $smarty->assign('view_tc_rights', 'yes');
    
    //users can modify tcs
    $smarty->assign('modify_tc_rights', has_rights("mgt_modify_tc")); 
}

// REQS
$smarty->assign('view_req_rights', has_rights("mgt_view_req")); 
$smarty->assign('modify_req_rights', has_rights("mgt_modify_req")); 
$smarty->assign('opt_requirements', $_SESSION['productOptReqs']); 

// view and modify Keywords 
$smarty->assign('view_keys_rights', has_rights("mgt_view_key"));
$smarty->assign('modify_keys_rights', has_rights("mgt_modify_key"));

// User has Product rights
$smarty->assign('modify_product_rights', has_rights("mgt_modify_product"));


// ----- Test Statistics Section --------------------------
// only print the metrics table if it is enabled
$smarty->assign('metricsEnabled', MAIN_PAGE_METRICS_ENABLED);
if(MAIN_PAGE_METRICS_ENABLED == "TRUE")
{
	require_once('myTPInfo.php');
    $smarty->assign('myTPdata', printMyTPData());
}

// ----- Test Plan Section ----------------------------------  
// get Test Plans available for the user 
// 20050810 - fm - Interface changes
// 20050809 - fm - get only test plan for the selected product
$arrPlans = getTestPlans($_SESSION['productID'], $_SESSION['userID']);


$smarty->assign('arrPlans', $arrPlans);
$smarty->assign('countPlans', count($arrPlans));

//can the user test
$smarty->assign('tp_execute', has_rights("tp_execute"));

//can the user create build
$smarty->assign('tp_create_build', has_rights("tp_create_build"));

//can the user view metrics
$smarty->assign('tp_metrics', has_rights("tp_metrics"));

//can the user manage Test Plan
$smarty->assign('tp_planning', has_rights("tp_planning"));
$smarty->assign('launcher','lib/general/frmWorkArea.php');

$smarty->display('mainPage.tpl');
?>