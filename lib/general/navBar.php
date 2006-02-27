<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: navBar.php,v $
 *
 * @version $Revision: 1.17 $
 * @modified $Date: 2006/02/27 08:05:58 $
 *
 * This file manages the navigation bar. 
 *
 * 20050813 - fm - added Product Filter con TestPlan 
 * 20060205 - JBA - Remember last product (BTS 221); added by MHT
 * 20060224 - franciscom - changes in session testproject instead of product
 * 20060226 - franciscom - gestione logo
**/
require('../../config.inc.php');
require_once("common.php");
require_once("plan.core.inc.php");
testlinkInitPage($db,true);

$arr_tprojects = getAccessibleProducts($db);
$curr_tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : null;
if ($curr_tproject_id)
	getAccessibleTestPlans($db,$curr_tproject_id,1,isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : null);
	
$roles = getAllRoles($db);
$testprojectRole = null;
if ($curr_tproject_id && isset($_SESSION['testprojectRoles'][$curr_tproject_id]))
	$testprojectRole = '['.$roles[$_SESSION['testprojectRoles'][$curr_tproject_id]['role_id']].']';
$roleName = $roles[$_SESSION['roleId']];

// 20050810 - fm - interface changes
$countPlans = getNumberOfAccessibleTestPlans($db,$curr_tproject_id, $_SESSION['filter_tp_by_product'],null);
$smarty = new TLSmarty();

// 20050813 - fm
// only when the user has changed the product using the combo
// the _GET has this key.
// Use this clue to launch a refresh of other frames present on the screen
// using the onload HTML body attribute
$updateMainPage = 0;
if (isset($_GET['testproject']))
{
	$updateMainPage = 1;
	// set product ID for the next session
	setcookie('lastProductForUser'. $_SESSION['userID'], $_GET['testproject'], TL_COOKIE_KEEPTIME, '/');
}


// 20060226 - franciscom
$logo_img='';
if (defined('LOGO_NAVBAR') )
{
  $logo_img=LOGO_NAVBAR;
}
$smarty->assign('logo', $logo_img);

$smarty->assign('view_tc_rights',has_rights($db,"mgt_view_tc"));
$smarty->assign('user', $_SESSION['user'] . ' [' . $roleName . ']');
$smarty->assign('testprojectRole',$testprojectRole);
$smarty->assign('rightViewSpec', has_rights($db,"mgt_view_tc"));
$smarty->assign('rightExecute', has_rights($db,"testplan_execute"));
$smarty->assign('rightMetrics', has_rights($db,"testplan_metrics"));
$smarty->assign('rightUserAdmin', has_rights($db,"mgt_users"));
$smarty->assign('countPlans', $countPlans);
$smarty->assign('arrayProducts', $arr_tprojects);
$smarty->assign('currentProduct', $curr_tproject_id);
// 20050816 - scs - added $updateMainPage, if set to 1, the mainpage should be reloaded
$smarty->assign('updateMainPage', $updateMainPage); 
$smarty->display('navBar.tpl');
?>