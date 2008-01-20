<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: searchForm.php,v 1.14 2008/01/20 15:39:18 franciscom Exp $
 * Purpose:  This page presents the search results. 
 *
**/
require_once("../../config.inc.php");
require_once("../functions/keyword.class.php");
require_once("../functions/common.php");
testlinkInitPage($db);

$template_dir='testcases/';


$tproject_mgr = new testproject($db);
$args=init_args();

$enabled=1;
$no_show_filter=null;
$mainCaption=lang_get('testproject') . " " . $args->tprojectName;
$cf_map_for_tcases = $tproject_mgr->cfield_mgr->get_linked_cfields_at_design($args->tprojectID,$enabled,
	                                                                           $no_show_filter,'testcase');

$smarty = new TLSmarty();
$smarty->assign('mainCaption',$mainCaption);
$smarty->assign('keywords', $tproject_mgr->getKeywords($args->tprojectID));
$smarty->assign('design_cf', $cf_map_for_tcases);
$smarty->display($template_dir . 'tcSearchForm.tpl');
?>

<?php
function init_args()
{
    $args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tprojectName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 0;
    return $args;
}
?>
