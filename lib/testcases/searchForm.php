<?
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: searchForm.php,v 1.2 2005/08/16 18:00:59 franciscom Exp $ */
// Purpose:  This page is the left frame of the search pages. It builds the
//	    form for adding criteria for search.
////////////////////////////////////////////////////////////////////////////////
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("../keywords/keywords.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$smarty = new TLSmarty;
$smarty->assign('arrKeys', selectKeywords());
$smarty->display('tcSearchForm.tpl');
?>

