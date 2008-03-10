<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: planView.php,v $
 *
 * @version $Revision: 1.7 $
 * @modified $Date: 2008/03/10 21:52:00 $ $Author: schlundus $
 *
*/
require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);

$template_dir = 'plan/';

$tplans = null;
$tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0 ;
$tproject_name = isset($_SESSION['testprojectName']) ? trim($_SESSION['testprojectName']) : '' ;

$main_descr=lang_get('testplan_title_tp_management'). " - " . 
            lang_get('testproject') . ' ' . $tproject_name;

if($tproject_id)
{
	$tproject_mgr = new testproject($db);
	$tplans = $tproject_mgr->get_all_testplans($tproject_id,FILTER_BY_PRODUCT,TP_ALL_STATUS);
}

$smarty = new TLSmarty();
$smarty->assign('main_descr',$main_descr);
$smarty->assign('tplans',$tplans);
$smarty->assign('testplan_create', has_rights($db,"mgt_testplan_create"));
$smarty->display($template_dir . 'planView.tpl');
?>