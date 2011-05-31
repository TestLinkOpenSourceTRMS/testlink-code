<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	reqSpecView.php
 * @author 		Martin Havlat
 *
 * Screen to view existing requirements within a req. specification.
 *
 * @internal revisions
 * 20100810 - asimon - BUGID 3317: disabled total count of requirements by default
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once('requirements.inc.php');
require_once('attachments.inc.php');
require_once("configCheck.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$args = init_args($db);
checkRights($db,$_SESSION['currentUser'],$args);

$gui = initialize_gui($db,$args);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 *
 */
function init_args(&$dbHandler)
{
	$iParams = array("req_spec_id" => array(tlInputParameter::INT_N),
					 "tproject_id" => array(tlInputParameter::INT_N));

    $args = new stdClass();
    R_PARAMS($iParams,$args);

	$args->tproject_name = '';
	if($args->tproject_id > 0)
	{
		$treeMgr = new tree($dbHandler);
		$dummy = $treeMgr->get_node_hierarchy_info($args->tproject_id);
		$args->tproject_name = $dummy['name'];    
	}
    
    return $args;
}

/**
 * 
 *
 */
function initialize_gui(&$dbHandler,&$argsObj)
{
	$req_spec_mgr = new requirement_spec_mgr($dbHandler);
	$tproject_mgr = new testproject($dbHandler);
	$commandMgr = new reqSpecCommands($dbHandler);
	
    $gui = $commandMgr->initGuiBean();
	$gui->req_spec_cfg = config_get('req_spec_cfg');
	$gui->req_cfg = config_get('req_cfg');
	
	// 20100810 - asimon - BUGID 3317: disabled total count of requirements by default
	$gui->external_req_management = ($gui->req_cfg->external_req_management == ENABLED) ? 1 : 0;
	
	$gui->grants = new stdClass();
	$gui->grants->req_mgmt = has_rights($db,"mgt_modify_req");
	$gui->req_spec = $req_spec_mgr->get_by_id($argsObj->req_spec_id);
	
	$gui->req_spec_id = $argsObj->req_spec_id;
	$gui->tproject_id = $argsObj->tproject_id;
	$gui->tproject_name = $argsObj->tproject_name;
	$gui->name = $gui->req_spec['title'];
	
	$gui->main_descr = lang_get('req_spec_short') . config_get('gui_title_separator_1') . 
	                   "[{$gui->req_spec['doc_id']}] :: " .$gui->req_spec['title'];

	$gui->refresh_tree = 'no';
	$gui->cfields = $req_spec_mgr->html_table_of_custom_field_values($argsObj->req_spec_id,$argsObj->tproject_id);
	$gui->attachments = getAttachmentInfosFrom($req_spec_mgr,$argsObj->req_spec_id);
	$gui->requirements_count = $req_spec_mgr->get_requirements_count($argsObj->req_spec_id);
	
	$gui->reqSpecTypeDomain = init_labels($gui->req_spec_cfg->type_labels);

	/* contribution BUGID 2999, show direct link */
	$prefix = $tproject_mgr->getTestCasePrefix($argsObj->tproject_id);
	$gui->direct_link = $_SESSION['basehref'] . 'linkto.php?tprojectPrefix=' . urlencode($prefix) . 
	                    '&item=reqspec&id=' . urlencode($gui->req_spec['doc_id']);



	$module = $_SESSION['basehref'] . "lib/requirements/";
	$context = "tproject_id=$gui->tproject_id&req_spec_id=$gui->req_spec_id";
	$gui->actions = new stdClass();
	$gui->actions->req_import = $module . "reqImport.php?doAction=import&$context";
	$gui->actions->req_export = $module . "reqExport.php?doAction=export&$context";
	$gui->actions->req_edit = $module . "reqEdit.php?doAction=create&$context";
	$gui->actions->req_reorder = $module . "reqEdit.php?doAction=reorder&$context";
	$gui->actions->req_create_tc = $module . "reqEdit.php?doAction=createTestCases&$context";

	$gui->actions->req_spec_new = $module . "reqSpecEdit.php?doAction=createChild" .
								  "&tproject_id=$gui->tproject_id&reqParentID=$gui->req_spec_id";

	$gui->actions->req_spec_copy = $module . "reqSpecEdit.php?doAction=copy&$context";
	
	$gui->actions->req_spec_copy_req = $module . "reqSpecEdit.php?doAction=copyRequirements&$context";
								  
	$gui->actions->req_spec_import = $gui->actions->req_import . "&scope=branch";
	$gui->actions->req_spec_export = $gui->actions->req_export . "&scope=branch";


    return $gui;
}


/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('mgt_view_req'),'and');
}
?>