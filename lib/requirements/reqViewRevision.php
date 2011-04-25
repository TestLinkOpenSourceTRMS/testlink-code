<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource	reqViewRevision.php
 * @author 		francisco.mancardi@gmail.com
 * @package 	TestLink
 * @copyright 	2008-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 *	@internal revisions
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('attachments.inc.php');
require_once('requirements.inc.php');
require_once('users.inc.php');
testlinkInitPage($db,!TL_UPDATE_ENVIRONMENT,false,"checkRights");

$templateCfg = templateConfiguration();

$args = init_args($db);
$gui = initialize_gui($db,$args);
$smarty = new TLSmarty();

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . 'reqViewRevisionRO.tpl');

/**
 *
 */
function init_args(&$dbHandler)
{
	$iParams = array("item_id" => array(tlInputParameter::INT_N),
			         "tproject_id" => array(tlInputParameter::INT_N),
			         "showReqSpecTitle" => array(tlInputParameter::INT_N));	
		
	$args = new stdClass();
	R_PARAMS($iParams,$args);
	
	$args->tproject_name = '';
    if($args->tproject_id > 0)
    {
    	$treeMgr = new tree($dbHandler);
    	$dummy = $treeMgr->get_node_hierarchy_info($args->tproject_id);
    	$args->tproject_name = $dummy['name'];
    }

    $user = $_SESSION['currentUser'];
	$args->userID = $user->dbID;
	
    return $args;
}

/**
 * 
 *
 */
function initialize_gui(&$dbHandler,$argsObj)
{
    $tproject_mgr = new testproject($dbHandler);
    $req_mgr = new requirement_mgr($dbHandler);
    $commandMgr = new reqCommands($dbHandler);

    $gui = $commandMgr->initGuiBean();
    $gui->req_cfg = config_get('req_cfg');
    $gui->tproject_name = $argsObj->tproject_name;

    $gui->grants = new stdClass();
    $gui->grants->req_mgmt = has_rights($dbHandler,"mgt_modify_req");
    
    $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($argsObj->tproject_id);
    $gui->glueChar = config_get('testcase_cfg')->glue_character;
    $gui->pieceSep = config_get('gui_title_separator_1');
    
    $gui->item_id = $argsObj->item_id;
    
    // identify item is version or revision ?
    $node_type_id = $req_mgr->tree_mgr->get_available_node_types();
    $node_id_type = array_flip($node_type_id);
    $item = $req_mgr->tree_mgr->get_node_hierarchy_info($gui->item_id);
    
    $info = null;
    switch ($node_id_type[$item['node_type_id']])
    {
    	case 'requirement_version':
			$info = $req_mgr->get_version($gui->item_id);
			$info['getFromVersion'] = $gui->item_id;
    	break;
    	
    	case 'requirement_revision':
			$info = $req_mgr->get_revision($gui->item_id);
			$info['getFromRevision'] = $gui->item_id;
    	break;
    }
    
    $gui->item = $info;
	$gui->cfields = $req_mgr->html_table_of_custom_field_values(null,$gui->item_id,$argsObj->tproject_id);
    $gui->show_title = false;
    $gui->main_descr = lang_get('req') . $gui->pieceSep .  $gui->item['title'];
    
    $gui->showReqSpecTitle = $argsObj->showReqSpecTitle;
    if($gui->showReqSpecTitle)
    {
        $gui->parent_descr = lang_get('req_spec_short') . $gui->pieceSep . $gui->item['req_spec_title'];
    }
    
    $gui->reqStatus = init_labels($gui->req_cfg->status_labels);
    $gui->reqTypeDomain = init_labels($gui->req_cfg->type_labels);
    return $gui;
}


/**
 * 
 *
 */
function checkRights(&$dbHandler,&$user)
{
	return $user->hasRight($dbHandler,'mgt_view_req');
}
?>