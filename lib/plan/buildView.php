<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: buildView.php,v $
 *
 * @version $Revision: 1.14 $
 * @modified $Date: 2009/06/10 19:36:00 $ $Author: franciscom $
 *
 * rev:
 *      20090509 - franciscom - minor refactoring      
 *       
 *
*/
require('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$tplan_mgr = new testplan($db);
$build_mgr = new build_mgr($db);

$gui = new StdClass();
$gui->tplan_id = isset($_SESSION['testplanID']) ? $_SESSION['testplanID'] : 0;
$gui->tplan_name = $_SESSION['testplanName'];
$gui->buildSet = $tplan_mgr->get_builds($gui->tplan_id);
$gui->user_feedback = null;

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_create_build');
}
?>
