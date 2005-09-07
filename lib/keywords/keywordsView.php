<?
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: keywordsView.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2005/09/07 20:19:25 $
 *
 * @author Martin Havlat
 * 
 * Purpose:  This page this allows users to view keywords. 
 *
 * 20050907 - scs - cosmetic changes
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("keywords.inc.php");
testlinkInitPage();

// 20050905 - fm
$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;

$smarty = new TLSmarty();
$smarty->assign('rightsKey', has_rights("mgt_modify_key"));
$smarty->assign('arrKeywords', selectKeywords($prodID));
$smarty->display('keywordsView.tpl');
?>

