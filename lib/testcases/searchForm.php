<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: searchForm.php,v 1.8 2006/02/04 20:13:15 schlundus Exp $ */
// Purpose:  This page is the left frame of the search pages. It builds the
//	    form for adding criteria for search.
////////////////////////////////////////////////////////////////////////////////
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("../keywords/keywords.inc.php");
testlinkInitPage($db);

$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;

$smarty = new TLSmarty();
$smarty->assign('arrKeys', selectKeywords($db,$prodID));
$smarty->display('tcSearchForm.tpl');
?>

