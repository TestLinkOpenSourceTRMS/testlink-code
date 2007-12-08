<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: searchForm.php,v 1.13 2007/12/08 19:10:19 schlundus Exp $
 * Purpose:  This page presents the search results. 
 *
**/
require_once("../../config.inc.php");
require_once("../functions/keyword.class.php");
require_once("../functions/common.php");
testlinkInitPage($db);

$template_dir='testcases/';

$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_mgr = new testproject($db);

$enabled=1;
$no_show_filter=null;

$cf_map_for_tcases = $tproject_mgr->cfield_mgr->get_linked_cfields_at_design($tproject_id,$enabled,
	                                                                           $no_show_filter,'testcase');

$smarty = new TLSmarty();
$smarty->assign('keywords', $tproject_mgr->getKeywords($tproject_id));
$smarty->assign('design_cf', $cf_map_for_tcases);
$smarty->display($template_dir . 'tcSearchForm.tpl');
?>

