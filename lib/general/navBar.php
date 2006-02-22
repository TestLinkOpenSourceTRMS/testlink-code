<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: navBar.php,v $
 *
 * @version $Revision: 1.13 $
 * @modified $Date: 2006/02/22 20:26:38 $
 *
 * This file manages the navigation bar. 
 * 20050813 - fm - added Product Filter con TestPlan 
 * 20060205 - JBA - Remember last product (BTS 221); added by MHT
 *
**/
require('../../config.inc.php');
require_once("common.php");
require_once("plan.core.inc.php");
testlinkInitPage($db,true);

$arrProducts = getOptionProducts($db);
$currentProduct = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : null;
$roles = getRoles($db);
$productRole = null;
if ($currentProduct && isset($_SESSION['productRoles'][$currentProduct]))
	$productRole = '['.$roles[$_SESSION['productRoles'][$currentProduct]['role_id']]['role'].']';
$roleName = $roles[$_SESSION['roleId']]['role'];

// 20050810 - fm - interface changes
$countPlans = getCountTestPlans4UserProd($db,$_SESSION['userID'],$currentProduct);

$smarty = new TLSmarty();
// 20050813 - fm
// only when the user has changed the product using the combo
// the _GET has this key.
// Use this clue to launch a refresh of other frames present on the screen
// using the onload HTML body attribute
$updateMainPage=0;
if (isset($_GET['product']))
{
	$updateMainPage=1;
	// set product ID for the next session
	setcookie('lastProductForUser'. $_SESSION['userID'], $_GET['product'], TL_COOKIE_KEEPTIME, '/');
}

$smarty->assign('view_tc_rights',has_rights($db,"mgt_view_tc"));
$smarty->assign('user', $_SESSION['user'] . ' [' . $roleName . ']');
$smarty->assign('productRole',$productRole);
$smarty->assign('testPlanRole',$testPlanRole);

$smarty->assign('rightViewSpec', has_rights($db,"mgt_view_tc"));
$smarty->assign('rightExecute', has_rights($db,"testplan_execute"));
$smarty->assign('rightMetrics', has_rights($db,"testplan_metrics"));
$smarty->assign('rightUserAdmin', has_rights($db,"mgt_users"));
$smarty->assign('countPlans', $countPlans);
$smarty->assign('arrayProducts', $arrProducts);
$smarty->assign('currentProduct', $currentProduct);
// 20050816 - scs - added $updateMainPage, if set to 1, the mainpage should be reloaded
$smarty->assign('updateMainPage', $updateMainPage); 
$smarty->display('navBar.tpl');
?>