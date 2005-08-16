<?
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: navBar.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:55 $
 *
 * @author Martin Havlat
 *
 * This file manages the navigation bar. 
 * @author Francisco Mancardi - 20050813 added Product Filter con TestPlan 
 *
**/
require('../../config.inc.php');
require_once("common.php");
require_once("plan.core.inc.php");


testlinkInitPage(true);

// Load data for combo box with all the available projects
$arrProducts = getOptionProducts();
$currentProduct = isset($_SESSION['productID']) ? $_SESSION['productID'] : null;

// $countPlans = getCountTestPlans4User();
// 20050810 - fm - interface changes
//$countPlans = getCountTestPlans4User($_SESSION['userID']);
// 20050813 - fm
$countPlans = getCountTestPlans4UserProd($_SESSION['userID'],$currentProduct);


$smarty = new TLSmarty;

// -----------------------------------------------------------------------------
// 20050813 - francisco.mancardi@gruppotesi.com
// only when the user has changed the product using the combo
// the _GET has this key.
// Use this clue to launch a refresh of other frames present on the screen
// using the onload HTML body attribute
//
// all this is needed to manage the Product Filter on testplans
//
$updateMainPage=0;
if (isset($_GET['product']))
{
	$updateMainPage=1;
}
// -----------------------------------------------------------------------------

$smarty->assign('user', $_SESSION['user'] . ' [' . $_SESSION['role'] . ']');
$smarty->assign('rightViewSpec', has_rights("mgt_view_tc"));
$smarty->assign('rightExecute', has_rights("tp_execute"));
$smarty->assign('rightMetrics', has_rights("tp_metrics"));
$smarty->assign('rightUserAdmin', has_rights("mgt_users"));
$smarty->assign('countPlans', $countPlans);
$smarty->assign('arrayProducts', $arrProducts);
$smarty->assign('currentProduct', $currentProduct);
// 20050816 - scs - added $updateMainPage, if set to 1, the mainpage should be reloaded
$smarty->assign('updateMainPage', $updateMainPage); 
$smarty->display('navBar.tpl');
?>