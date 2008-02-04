<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: planView.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2008/02/04 19:41:35 $ $Author: schlundus $
 *
*/
require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);

$template_dir = 'plan/';

$tplans = null;
$tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0 ;
if($tproject_id)
{
	$tproject_mgr = new testproject($db);
	$tplans = $tproject_mgr->get_all_testplans($tproject_id,FILTER_BY_PRODUCT,TP_ALL_STATUS);
}

$smarty = new TLSmarty();
$smarty->assign('api_ui_show',$g_api_ui_show);
$smarty->assign('tplans',$tplans);
$smarty->assign('testplan_create', has_rights($db,"mgt_testplan_create"));
$smarty->display($template_dir . 'planView.tpl');
?>