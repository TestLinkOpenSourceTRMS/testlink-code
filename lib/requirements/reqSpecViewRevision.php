<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource	reqSpecViewRevision.php
 * @author francisco.mancardi@gmail.com
 * 
 *
 * @internal revisions
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('requirements.inc.php');
require_once('users.inc.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();

$args = init_args($db);
$gui = initialize_gui($db,$args);
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 *
 */
function init_args(&$dbHandler)
{
	$iParams = array("item_id" => array(tlInputParameter::INT_N),
			         "showContextInfo" => array(tlInputParameter::INT_N),
			         "tproject_id" => array(tlInputParameter::INT_N) );	
		
	$args = new stdClass();
	R_PARAMS($iParams,$args);
	
	$args->tproject_name = null;
	if( $args->tproject_id > 0 )
	{
		$mgr = new tree($dbHandler);
		$dummy = $mgr->get_node_hierarchy_info($args->tproject_id);
   	$args->tproject_name = $dummy['name'];		
	}
  $args->user = $_SESSION['currentUser'];
	$args->userID = $args->user->dbID;
	
  return $args;
}

/**
 * 
 *
 */
function initialize_gui(&$dbHandler,$argsObj)
{
    $tproject_mgr = new testproject($dbHandler);
    $itemMgr = new requirement_spec_mgr($dbHandler);
    $commandMgr = new reqSpecCommands($dbHandler);

    $gui = $commandMgr->initGuiBean();
    $gui->itemCfg = config_get('req_spec_cfg');
    $gui->tproject_name = $argsObj->tproject_name;

    $gui->grants = new stdClass();
    $gui->grants->req_mgmt = $argsObj->hasRight($dbHandler,"mgt_modify_req",$argsObj->tproject_id);
    
    $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($argsObj->tproject_id);
    $gui->glueChar = config_get('testcase_cfg')->glue_character;
    $gui->pieceSep = config_get('gui_title_separator_1');
    
    $gui->item_id = $argsObj->item_id;
	  $info = $itemMgr->getRevisionByID($gui->item_id,array('decode_user' => true));
    $gui->item = $info;
	
	  $gui->cfields = $itemMgr->html_table_of_custom_field_values(null,$gui->item_id,$argsObj->tproject_id);
    $gui->show_title = false;
    $gui->main_descr = lang_get('req_spec') . $gui->pieceSep .  $gui->item['name'];
    
    $gui->showContextInfo = $argsObj->showContextInfo;
    if($gui->showContextInfo)
    {
        $gui->parent_descr = lang_get('req_spec_short') . $gui->pieceSep . $gui->item['name'];
    }
    
    $gui->itemSpecStatus = null;
    $gui->itemTypeDomain = init_labels($gui->itemCfg->type_labels);

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