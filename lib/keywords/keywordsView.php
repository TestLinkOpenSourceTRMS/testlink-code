<?
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: keywordsView.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:55 $
 *
 * @author Martin Havlat
 * 
 * Purpose:  This page this allows users to view keywords. 
 *
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("keywords.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$smarty = new TLSmarty();
$smarty->assign('rightsKey', has_rights("mgt_modify_key"));
$smarty->assign('arrKeywords', selectKeywords());
$smarty->display('keywordsView.tpl');
?>

