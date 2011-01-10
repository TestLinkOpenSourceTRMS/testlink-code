<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: rolesEdit.php,v $
 *
 * @version $Revision: 1.34.6.2 $
 * @modified $Date: 2011/01/10 15:38:59 $ by $Author: asimon83 $
 *
 * @internal revision 
 *	20091124 - franciscom - added contribution item template
 *	20081030 - franciscom - added system_mgmt member on getRightsCfg()
 *	20080827 - franciscom - BUGID 1692
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once("web_editor.php");

$editorCfg = getWebEditorCfg('role');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db,false,false,"checkRights");
init_global_rights_maps();
$templateCfg = templateConfiguration();
$args = init_args();
$gui = initialize_gui($editorCfg['type']);
$op = initialize_op();

$owebeditor = web_editor('notes',$args->basehref,$editorCfg) ;
$owebeditor->Value = getItemTemplateContents('role_template', $owebeditor->InstanceName, null);
$canManage = has_rights($db,"role_management") ? true : false;

switch($args->doAction)
{
	case 'create':
		break;

	case 'edit':
	    $op->role = tlRole::getByID($db,$args->roleid);
		break;

	case 'doCreate':
	case 'doUpdate':
		if($canManage)
	  	{
	  	  	$op = doOperation($db,$args,$args->doAction);
	  	  	$templateCfg->template = $op->template;
        }
		break;
	default:
		break;
}

$gui = complete_gui($db,$gui,$args,$op->role,$owebeditor);

$gui->userFeedback = $op->userFeedback;

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->assign('highlight',$gui->highlight);
renderGui($smarty,$args,$templateCfg);

function init_args()
{
	$iParams = array(
			"rolename" => array("POST",tlInputParameter::STRING_N,0,100),
			"roleid" => array("REQUEST",tlInputParameter::INT_N),
			"doAction" => array("REQUEST",tlInputParameter::STRING_N,0,100),
			"notes" => array("POST",tlInputParameter::STRING_N),
			"grant" => array("POST",tlInputParameter::ARRAY_STRING_N),
		);

	$args = new stdClass();
	$pParams = I_PARAMS($iParams,$args);
	
	// BUGID 4066 - take care of proper escaping when magic_quotes_gpc is enabled
	$_REQUEST=strings_stripSlashes($_REQUEST);

	$args->basehref = $_SESSION['basehref'];
	
	return $args;
}


function doOperation(&$dbHandler,$argsObj,$operation)
{
	$rights = implode("','",array_keys($argsObj->grant));

	$op = new stdClass();
 	$op->role = new tlRole();
	$op->role->rights = tlRight::getAll($dbHandler,"WHERE description IN ('{$rights}')");
	$op->role->name = $argsObj->rolename;
	$op->role->description = $argsObj->notes;
	$op->role->dbID = $argsObj->roleid;
	$op->userFeedback = null;
	$op->template = 'rolesEdit.tpl';

	$result = $op->role->writeToDB($dbHandler);
	if ($result >= tl::OK)
	{
		$auditCfg = null;
		switch($operation)
		{
	    	case 'doCreate':
				$auditCfg['msg'] = "audit_role_created";
				$auditCfg['activity'] = "CREATE";
				break;
	
			case 'doUpdate':
	      		$auditCfg['msg'] = "audit_role_saved";
				$auditCfg['activity'] = "SAVE";
				break;
		}
		
		logAuditEvent(TLS($auditCfg['msg'],$argsObj->rolename),$auditCfg['activity'],$op->role->dbID,"roles");
		$op->template = null;
	}
	else
	{
    	$op->userFeedback = getRoleErrorMessage($result);
	}

	return $op;
}


function renderGui(&$smartyObj,&$argsObj,$templateCfg)
{
    $doRender = false;
    switch($argsObj->doAction)
    {
		case "edit":
        case "create":
        	$doRender = true;
    		$tpl = $templateCfg->default_template;
    		break;

		case "doCreate":
        case "doUpdate":
        	if(!is_null($templateCfg->template))
        	{
            	$doRender = true;
            	$tpl = $templateCfg->template;
        	}
        	else
        	{
 	  			header("Location: rolesView.php");
	  			exit();
        	}
    		break;
	}

    if($doRender)
    {
		$smartyObj->display($templateCfg->template_dir . $tpl);
	}
}


/*
  function: getRightsCfg

  args : -

  returns: object
  
  rev: 20081030 - franciscom - added system_mgmt member

*/
function getRightsCfg()
{
    $cfg = new stdClass();
    $cfg->tplan_mgmt = config_get('rights_tp');
    $cfg->tcase_mgmt = config_get('rights_mgttc');
    $cfg->kword_mgmt = config_get('rights_kw');
    $cfg->tproject_mgmt = config_get('rights_product');
    $cfg->user_mgmt = config_get('rights_users');
    $cfg->req_mgmt = config_get('rights_req');
    $cfg->cfield_mgmt = config_get('rights_cf');
    $cfg->system_mgmt = config_get('rights_system');
    $cfg->platform_mgmt = config_get('rights_platforms');
    return $cfg;
}


function initialize_gui($editorType)
{
    $gui = new stdClass();
    $gui->checkboxStatus = null;
    $gui->userFeedback = null;
    $gui->affectedUsers = null;
    $gui->highlight = initialize_tabsmenu();
    $gui->editorType = $editorType;

    return $gui;
}


function initialize_op()
{
    $op = new stdClass();
    $op->role = new tlRole();
    $op->userFeedback = '';
    
    return $op;
}

function complete_gui(&$dbHandler,&$guiObj,&$argsObj,&$roleObj,&$webEditorObj)
{
    $actionCfg['operation'] = array('create' => 'doCreate', 'edit' => 'doUpdate',
                                  'doCreate' => 'doCreate', 'doUpdate' => 'doUpdate');

    $actionCfg['highlight'] = array('create' => 'create_role', 'edit' => 'edit_role',
                                  'doCreate' => 'create_role', 'doUpdate' => 'edit_role');


    $guiObj->highlight->$actionCfg['highlight'][$argsObj->doAction] = 1;
    $guiObj->operation = $actionCfg['operation'][$argsObj->doAction];
    $guiObj->role = $roleObj;
    $guiObj->grants = getGrantsForUserMgmt($dbHandler,$_SESSION['currentUser']);
    $guiObj->rightsCfg = getRightsCfg();
	$guiObj->mgt_view_events = $_SESSION['currentUser']->hasRight($db,"mgt_view_events");

    // Create status for all checkboxes and set to unchecked
    foreach($guiObj->rightsCfg as $grantDetails)
    {
        foreach($grantDetails as $grantCode => $grantDescription)
        {
			$guiObj->checkboxStatus[$grantCode] = "";
        }
    }

    if($roleObj->dbID)
    {
    	$webEditorObj->Value = $roleObj->description;

    	// build checked attribute for checkboxes
    	if(sizeof($roleObj->rights))
    	{
    	    foreach($roleObj->rights as $key => $right)
    	    {
    	    	$guiObj->checkboxStatus[$right->name] = "checked=\"checked\"";
    	    }
    	}
    	//get all users which are affected by changing the role definition
		$guiObj->affectedUsers = $roleObj->getAllUsersWithRole($dbHandler);
    }

    $guiObj->notes = $webEditorObj->CreateHTML();
    return $guiObj;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,"role_management");
}
?>