<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: searchForm.php,v 1.11 2007/09/12 06:24:53 franciscom Exp $
 * Purpose:  This page presents the search results. 
 *
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
testlinkInitPage($db);

$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_mgr = new testproject($db);

$enabled=1;
$no_show_filter=null;

$cf_map_for_tcases = $tproject_mgr->cfield_mgr->get_linked_cfields_at_design($tproject_id,$enabled,
	                                                                           $no_show_filter,'testcase');

$smarty = new TLSmarty();
$smarty->assign('arrKeys', $tproject_mgr->getKeywords($tproject_id));
$smarty->assign('design_cf', $cf_map_for_tcases);
$smarty->display('tcSearchForm.tpl');
?>

