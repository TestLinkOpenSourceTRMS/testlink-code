<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: buildView.php,v $
 *
 * @version $Revision: 1.8 $
 * @modified $Date: 2008/02/17 16:35:30 $ $Author: franciscom $
 *
 * rev :
 *       20070122 - franciscom - use build_mgr methods
 *       20070121 - franciscom - active and open management
 *
*/
require('../../config.inc.php');
require_once("common.php");
require_once("builds.inc.php");
testlinkInitPage($db);

$template_dir = 'plan/';
$tplan_mgr = new testplan($db);
$build_mgr = new build_mgr($db);

$tplan_id = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$tplan_name = $_SESSION['testPlanName'];

$the_builds = $tplan_mgr->get_builds($tplan_id);

$smarty = new TLSmarty();
$smarty->assign('api_ui_show', $g_api_ui_show);
$smarty->assign('user_feedback',null); // disable notice
$smarty->assign('tplan_name', $tplan_name);
$smarty->assign('tplan_id', $tplan_id);
$smarty->assign('the_builds', $the_builds);
$smarty->display($template_dir . 'buildView.tpl');
?>
