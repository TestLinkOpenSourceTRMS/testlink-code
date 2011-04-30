<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource	reqSpecEdit.php
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2005-2011, TestLink community
 * @link 		http://www.teamst.org/index.php
 *
 * View existing and create a new req. specification.
 *
 * @internal revisions
 *  20101028 - asimon - BUGID 3954: added contribution by Vincent to freeze all requirements
 *                                  inside a req spec (recursively)
 *  20100908 - asimon - BUGID 3755: tree not refreshed when copying requirements
 *  20100810 - asimon - BUGID 3317: disabled total count of requirements by default
 *  20100808 - aismon - added logic to refresh filtered tree on action
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
require_once("web_editor.php");
$editorCfg = getWebEditorCfg('requirement_spec');
require_once(require_web_editor($editorCfg['type']));
$req_cfg = config_get('req_cfg');

testlinkInitPage($db,!TL_UPDATE_ENVIRONMENT,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args($db);
$commandMgr = new reqSpecCommands($db);

$gui = initialize_gui($db,$commandMgr,$_SESSION['currentUser'],$args,$req_cfg);

$auditContext = new stdClass();
$auditContext->tproject = $args->tproject_name;
$commandMgr->setAuditContext($auditContext);

$pFn = $args->doAction;
$op = null;
if(method_exists($commandMgr,$pFn))
{
	$op = $commandMgr->$pFn($args,$_REQUEST);
}
renderGui($args,$gui,$op,$templateCfg,$editorCfg);


/**
 * 
 *
 */
function init_args(&$dbHandler)
{

	// BUGID 4066 - take care of proper escaping when magic_quotes_gpc is enabled
	$_REQUEST=strings_stripSlashes($_REQUEST);
	// new dBug($_REQUEST);

	$args = new stdClass();
	$iParams = array("countReq" => array(tlInputParameter::INT_N,99999),
			         "req_spec_id" => array(tlInputParameter::INT_N),
					 "reqParentID" => array(tlInputParameter::INT_N),
					 "doAction" => array(tlInputParameter::STRING_N,0,250),
					 "title" => array(tlInputParameter::STRING_N,0,100),
					 "scope" => array(tlInputParameter::STRING_N),
					 "doc_id" => array(tlInputParameter::STRING_N,1,32),
					 "nodes_order" => array(tlInputParameter::ARRAY_INT),
					 "containerID" => array(tlInputParameter::INT_N),
 			 		 "itemSet" => array(tlInputParameter::ARRAY_INT),
					 "reqSpecType" => array(tlInputParameter::STRING_N,0,1),
					 "copy_testcase_assignment" => array(tlInputParameter::CB_BOOL),
					 "tproject_id" => array(tlInputParameter::INT_N));	
		
	$args = new stdClass();
	R_PARAMS($iParams,$args);


	$args->tproject_name = '';
	if($args->tproject_id > 0 )
	{
		$treeMgr = new tree($dbHandler);
		$dummy = $treeMgr->get_node_hierarchy_info($args->tproject_id);
		$args->tproject_name = $dummy['name'];    
	}


	$args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
	$args->basehref = $_SESSION['basehref'];
	$args->reqParentID = is_null($args->reqParentID) ? $args->tproject_id : $args->reqParentID;

	// asimon - 20100808 - added logic to refresh filtered tree on action
	$args->refreshTree = isset($_SESSION['setting_refresh_tree_on_action']) ? $_SESSION['setting_refresh_tree_on_action'] : 0;
	
	$args->countReq = is_null($args->countReq) ? 0 : intval($args->countReq);

	return $args;
}


/**
 * renderGui
 *
 */
function renderGui(&$argsObj,$guiObj,$opObj,$templateCfg,$editorCfg)
{
    $smartyObj = new TLSmarty();
    $renderType = 'none';
    $tpl = $tpd = null;

    $actionOperation = array('create' => 'doCreate', 'edit' => 'doUpdate',
                             'doDelete' => '', 'doReorder' => '', 'reorder' => '',
                             'doCreate' => 'doCreate', 'doUpdate' => 'doUpdate',
                             'createChild' => 'doCreate', 'copy' => 'doCopy',
                             'doCopy' => 'doCopy',
	                         'doFreeze' => 'doFreeze',
                             'copyRequirements' => 'doCopyRequirements',
                             'doCopyRequirements' => 'doCopyRequirements');

    $owebEditor = web_editor('scope',$argsObj->basehref,$editorCfg) ;
	switch($argsObj->doAction)
    {
        case "edit":
        case "doCreate":
        $owebEditor->Value = $argsObj->scope;
        break;
        
        default:
        $owebEditor->Value = getItemTemplateContents('req_spec_template',$owebEditor->InstanceName, $argsObj->scope);
        break;
    }
	$guiObj->scope = $owebEditor->CreateHTML();
    $guiObj->editorType = $editorCfg['type'];  

    // 20100808 - aismon - added logic to refresh filtered tree on action
	switch($argsObj->doAction)
    {
        case "doCreate":
	    case "doUpdate": 
        case "doCopyRequirements":
        case "doCopy":
        case "doFreeze":
        case "doDelete":
    		$guiObj->refreshTree = $argsObj->refreshTree;
    	break;
    }
    
    switch($argsObj->doAction)
    {
        case "edit":
        case "create":
        case "createChild":
        case "reorder":
        case "doDelete":
        case "doReorder":
	    case "doCreate":
	    case "doUpdate":
        case "copyRequirements":
        case "doCopyRequirements":
        case "copy":
        case "doCopy":
        case "doFreeze":
        	$renderType = 'template';
            $key2loop = get_object_vars($opObj);
            foreach($key2loop as $key => $value)
            {
                $guiObj->$key = $value;
            }
            $guiObj->operation = $actionOperation[$argsObj->doAction];
            $tpl = is_null($opObj->template) ? $templateCfg->default_template : $opObj->template;
            $tpd = isset($key2loop['template_dir']) ? $opObj->template_dir : $templateCfg->template_dir;
    	break;
    }
    
	switch($argsObj->doAction)
    {
        case "edit":
        case "create":
        case "createChild":
        case "reorder":
        case "doDelete":
        case "doReorder":
        case "copyRequirements":
        case "copy":
        	$tpl = $tpd . $tpl;
            break;
    
        case "doCreate":
	    case "doUpdate": 
        case "doCopyRequirements":
        case "doCopy":
	    	$pos = strpos($tpl, '.php');
            if($pos === false)
            {
            	$tpl = $templateCfg->template_dir . $tpl;      
            }
            else
            {
                $renderType = 'redirect';  
			}
			break;  
    }

    switch($renderType)
    {
        case 'template':
			$smartyObj->assign('mgt_view_events',has_rights($db,"mgt_view_events"));
 		    $smartyObj->assign('gui',$guiObj);
		    $smartyObj->display($tpl);
        	break;  
 
        case 'redirect':
		    header("Location: {$tpl}");
	  		exit();
        	break;

        default:
        	break;
    }
}

/**
 * 
 *
 */
function initialize_gui(&$dbHandler, &$commandMgr, &$userObj,&$argsObj,&$req_cfg)
{
    $gui = $commandMgr->initGuiBean();

    $gui->tproject_id = $argsObj->tproject_id;
    $gui->user_feedback = null;
    $gui->main_descr = null;
    $gui->action_descr = null;
    $gui->refreshTree = 0;
	$gui->external_req_management = ($req_cfg->external_req_management == ENABLED) ? 1 : 0;
    
    $gui->grants = new stdClass();
    $gui->grants->req_mgmt = $userObj->hasRight($dbHandler,"mgt_modify_req",$gui->tproject_id);

    return $gui;
}

/**
 * 
 *
 */
function checkRights(&$db,&$user)
{
	return ($user->hasRight($db,'mgt_view_req') && $user->hasRight($db,'mgt_modify_req'));
}
?>