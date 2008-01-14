<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: planView.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2008/01/14 18:51:52 $ $Author: asielb $
 *
 * Purpose:  Add new or edit existing Test Plan 
 *
*/
require('../../config.inc.php');
require_once("../functions/common.php");
testlinkInitPage($db);

$template_dir='plan/';

$tproject_mgr = New testproject($db);
$tplans=null;
$tproject_id=isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0 ;
if($tproject_id)
{ 
  $tplans = $tproject_mgr->get_all_testplans($tproject_id,FILTER_BY_PRODUCT,TP_ALL_STATUS);
}

$smarty = new TLSmarty();
$smarty->assign('api_ui_show',$g_api_ui_show);
$smarty->assign('tplans',$tplans);
$smarty->assign('testplan_create', has_rights($db,"mgt_testplan_create"));
$smarty->display($template_dir . 'planView.tpl');
?>