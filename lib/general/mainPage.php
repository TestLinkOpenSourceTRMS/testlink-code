<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: mainPage.php,v $
 *
 * @version $Revision: 1.10 $ $Author: franciscom $
 * @modified $Date: 2006/01/09 08:13:45 $
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
 *
 * @author Francisco Mancardi - 20050928 - 
 * changes to filter test plan by product
 *
 * @author Francisco Mancardi - 20050928 - 
 * adding new User Interface feature: filter test plan by product
 * 
 * 20051112 - scs - removed undefined index notices
 * 20050103 - scs - ADOdb changes
 * 20060106 - scs - changes because new product functionality
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('plan.core.inc.php');
require_once('configCheck.php');
require_once('users.inc.php');

testlinkInitPage($db,TRUE);
$smarty = new TLSmarty;

// ----------------------------------------------------------------------
/** redirect admin to create product if not found */
if ($_SESSION['role'] == 'admin' && !isset($_SESSION['productID']))
{ 
	redirect($_SESSION['basehref'] . 'lib/admin/adminProductEdit.php');
}
// ----------------------------------------------------------------------

// ----- Product Section ----------------------------------  
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
$smarty->assign('opt_requirements', isset($_SESSION['productOptReqs']) ? $_SESSION['productOptReqs'] : null); 

// view and modify Keywords 
$smarty->assign('view_keys_rights', has_rights($db,"mgt_view_key"));
$smarty->assign('modify_keys_rights', has_rights($db,"mgt_modify_key"));

// User has Product rights
$smarty->assign('modify_product_rights', has_rights($db,"mgt_modify_product"));


// ----- Test Statistics Section --------------------------
// only print the metrics table if it is enabled
$smarty->assign('metricsEnabled', MAIN_PAGE_METRICS_ENABLED);
if(MAIN_PAGE_METRICS_ENABLED == "TRUE")
{
	require_once('myTPInfo.php');
    $smarty->assign('myTPdata', printMyTPData());
}

// 20050928 - fm
$filter_tp_by_product = 1;
if( isset($_REQUEST['filter_tp_by_product']) )
{
  $filter_tp_by_product = 1;
}
else if ( isset($_REQUEST['filter_tp_by_product_hidden']) )
{
  $filter_tp_by_product = 0;
} 
else
{
	if ( isset($_SESSION['filter_tp_by_product']) )
  {
    $filter_tp_by_product = $_SESSION['filter_tp_by_product'];
  }
}
$_SESSION['filter_tp_by_product'] = $filter_tp_by_product;
$smarty->assign('filter_tp_by_product',$filter_tp_by_product);


// ----- Test Plan Section ----------------------------------  
// get Test Plans available for the user 
// 20050928 - fm - Interface changes
// 20050810 - fm - Interface changes
// 20050809 - fm - get only test plan for the selected product
$arrPlans = getTestPlans($db,isset($_SESSION['productID']) ? $_SESSION['productID'] : 0,
						$_SESSION['userID'],$filter_tp_by_product);

//20050826 - scs - added displaying of security notes
$securityNotes = getSecurityNotes($db);

$smarty->assign('securityNotes',$securityNotes);
$smarty->assign('arrPlans', $arrPlans);
$smarty->assign('countPlans', count($arrPlans));

//can the user test
$smarty->assign('tp_execute', has_rights($db,"tp_execute"));

//can the user create build
$smarty->assign('tp_create_build', has_rights($db,"tp_create_build"));

//can the user view metrics
$smarty->assign('tp_metrics', has_rights($db,"tp_metrics"));

//can the user manage Test Plan
$smarty->assign('tp_planning', has_rights($db,"tp_planning"));
$smarty->assign('launcher','lib/general/frmWorkArea.php');

// 20051002 - fm
global $g_ui_show_check_filter_tp_by_product;
$smarty->assign('show_filter_tp_by_product',
                $g_ui_show_check_filter_tp_by_product);


$smarty->display('mainPage.tpl');
?>