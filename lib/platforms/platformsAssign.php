<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: platformsAssign.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2009/08/19 06:59:02 $ $Author: franciscom $
 *
 * Purpose:  Assign keywords to set of testcases in tree structure
 *
 *
 **/
require_once("../../config.inc.php");
require_once("common.php");
require_once("opt_transfer.php");
// require_once("platform.class.php");
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

$gui = new stdClass();
$gui->platform_assignment_subtitle = null;
$gui->tplan_id = $args->tplan_id;
$gui->can_do = isset($args->tplan_id);
$gui->mainTitle = lang_get('add_remove_platforms');

if (isset($args->tplan_id))
{
    $opt_cfg->global_lbl = '';
    $opt_cfg->additional_global_lbl = null;
    $opt_cfg->from->lbl = lang_get('available_platforms');
    $opt_cfg->to->lbl = lang_get('assigned_platforms');
    $opt_cfg->from->map = $platform_mgr->getAllAsMap();
    $opt_cfg->to->map = $platform_mgr->getLinkedToTestplanAsMap($args->tplan_id);

    $tplanData = $tplan_mgr->get_by_id($args->tplan_id);
    if (isset($tplanData))
    {
        $gui->mainTitle = sprintf($gui->mainTitle,$tplanData['name']);
    }

    if($args->doAssignPlatforms)
    {
    	$platform_mgr->linkToTestplan($args->platformsToAdd,$args->tplan_id);
    	$platform_mgr->unlinkFromTestplan($args->platformsToRemove,$args->tplan_id);

        // Update option panes with newly updated config
        $opt_cfg->from->map = $platform_mgr->getAllAsMap();
        $opt_cfg->to->map = $platform_mgr->getLinkedToTestplanAsMap($args->tplan_id);
    }
}


$opt_cfg->from->desc_field = 'platform';
$opt_cfg->to->desc_field = 'platform';
item_opt_transf_cfg($opt_cfg, null);

$smarty->assign('gui', $gui);
$smarty->assign('opt_cfg', $opt_cfg);

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
