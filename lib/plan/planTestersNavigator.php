<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: planTestersNavigator.php,v 1.3 2005/11/21 07:02:25 franciscom Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* 
* This page lists users and plan for assignment. 
* 
*/
require_once('../../config.inc.php');
require_once('common.php');
require_once('users.inc.php');
require_once('plan.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

// 20051120 - fm
// The current selected Product
$prod->id   = $_SESSION['productID'];
$prod->name = $_SESSION['productName'];


$type = isset($_GET['type']) ? $_GET['type'] : 'users';

$arrData = null;
if ($type == 'plans')
{
	$title = lang_get('nav_test_plan');
	$selected = 'selected="selected"';
	
	// $arrData = getAllActiveTestPlans();
	// 20051120 - fm - filter by product
	$arrData = getAllActiveTestPlans($prod->id,FILTER_BY_PRODUCT);
	
	
}
else
{
	$title = lang_get('nav_users');
	$selected = '';
	getAllUsers($arrData);
}

$smarty = new TLSmarty;
$smarty->assign('title', $title);
$smarty->assign('type', $type);
$smarty->assign('selected', $selected);
$smarty->assign('arrData', $arrData);
$smarty->display('planTestersNavigator.tpl');
?>
