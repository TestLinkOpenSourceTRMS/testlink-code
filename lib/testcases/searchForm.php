<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: searchForm.php,v 1.10 2006/06/30 18:41:25 schlundus Exp $
 * Purpose:  This page presents the search results. 
 *
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
testlinkInitPage($db);

$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_mgr = new testproject($db);

$smarty = new TLSmarty();
$smarty->assign('arrKeys', $tproject_mgr->getKeywords($tproject_id));
$smarty->display('tcSearchForm.tpl');
?>

