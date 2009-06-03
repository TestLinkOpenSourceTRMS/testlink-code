<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: planView.php,v $
 *
 * @version $Revision: 1.11 $
 * @modified $Date: 2009/06/03 19:51:45 $ $Author: schlundus $
 *
*/
require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$args=init_args();

$gui = new stdClass();
$gui->tproject_id = $args->tproject_id;
$gui->tplans = null;
$gui->user_feedback = '';
$gui->grants = new stdClass();
$gui->grants->testplan_create=has_rights($db,"mgt_testplan_create");
$gui->main_descr = lang_get('testplan_title_tp_management'). " - " . 
                   lang_get('testproject') . ' ' . $args->tproject_name;


if($args->tproject_id)
{
	$tproject_mgr = new testproject($db);
	$gui->tplans = $tproject_mgr->get_all_testplans($args->tproject_id);
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * init_args
 *
 */
function init_args()
{
    $args = new stdClass();
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0 ;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? trim($_SESSION['testprojectName']) : '' ;

    return $args;
}

/**
 * checkRights
 *
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_testplan_create');
}
?>