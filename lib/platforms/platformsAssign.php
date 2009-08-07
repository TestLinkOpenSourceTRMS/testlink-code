<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: platformsAssign.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2009/08/07 06:48:12 $ $Author: franciscom $
 *
 * Purpose:  Assign keywords to set of testcases in tree structure
 *
 *
 **/
require_once("../../config.inc.php");
require_once("common.php");
require_once("opt_transfer.php");
require_once("platform.class.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$opt_cfg = opt_transf_empty_cfg();
$opt_cfg->js_ot_name = 'ot';
$args = init_args($opt_cfg);

if ($args->edit == 'testproject')
{
	show_instructions('platformAssign');
	exit();
}

$smarty = new TLSmarty();
$tplan_mgr = new testplan($db);
$platform_mgr = new tlPlatform($db, $args->testproject_id);

$result = null;
$platform_assignment_subtitle = null;
$can_do = false;
if (isset($args->tplan_id))
{
    $opt_cfg->global_lbl = '';
    $opt_cfg->additional_global_lbl = null;
    $opt_cfg->from->lbl = lang_get('available_platforms');
    $opt_cfg->to->lbl = lang_get('assigned_platforms');
    $opt_cfg->from->map = $platform_mgr->getAllAsMap();
    $opt_cfg->to->map = $platform_mgr->getLinkedToTestplanAsMap($args->tplan_id);

    $can_do = true;
    $tplanData = $tplan_mgr->get_by_id($args->tplan_id);
    if (isset($tplanData))
    {
        $platform_assignment_subtitle = lang_get('test_plan') . TITLE_SEP . $tplanData['name'];
    }
    if($args->doAssignPlatforms)
    {
        $result = 'ok';
        $tplan_mgr->add_platforms($args->tplan_id, (array)$args->platformsToAdd);
        $tplan_mgr->remove_platforms($args->tplan_id, (array)$args->platformsToRemove);
        // Update option panes with newly updated config
        $opt_cfg->from->map = $platform_mgr->getAllAsMap();
        $opt_cfg->to->map = $platform_mgr->getLinkedToTestplanAsMap($args->tplan_id);
    }
}

$opt_cfg->from->desc_field = 'platform';
$opt_cfg->to->desc_field = 'platform';
item_opt_transf_cfg($opt_cfg, null);

$smarty->assign('can_do', $can_do);
$smarty->assign('sqlResult', $result);
$smarty->assign('tplan_id', $args->tplan_id);
$smarty->assign('level', $args->edit);
$smarty->assign('opt_cfg', $opt_cfg);
$smarty->assign('platform_assignment_subtitle',$platform_assignment_subtitle);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * 
 *
 */
function init_args(&$opt_cfg)
{
    $added = $opt_cfg->js_ot_name . "_addedRight";
    $removed = $opt_cfg->js_ot_name . "_removedRight";

	$iParams = array( "tplan_id" => array(tlInputParameter::INT_N),
		              "edit" => array(tlInputParameter::STRING_N,0,100),
		              "assignPlatforms" => array(tlInputParameter::STRING_N,0,1),
		              $added => array(tlInputParameter::STRING_N),
		              $removed => array(tlInputParameter::STRING_N));

	$pParams = R_PARAMS($iParams);

	$args = new stdClass();
	$args->tplan_id = $pParams["tplan_id"];
    $args->platformsToAdd = null;
    $args->platformsToRemove = null;
    if ($pParams[$added] != "") {
        $args->platformsToAdd = explode(",", $pParams[$added]);
    }
    if ($pParams[$removed] != "") {
        $args->platformsToRemove = explode(",", $pParams[$removed]);
    }

	$args->edit = $pParams["edit"];
	$args->doAssignPlatforms = ($pParams["assignPlatforms"] != "") ? 1 : 0;
	$args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

	return $args;
}

function checkRights(&$db,&$user)
{
	return ($user->hasRight($db,'platform_management') && 
	        $user->hasRight($db,'platform_view'));
}
?>
