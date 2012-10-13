<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	rolesEdit.php
 * @internal revisions 
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once("web_editor.php");

$editorCfg = getWebEditorCfg('role');
require_once(require_web_editor($editorCfg['type']));
testlinkInitPage($db);
$templateCfg = templateConfiguration();
$args = init_args();
checkRights($db,$_SESSION['currentUser'],$args);

$gui = initialize_gui($args,$editorCfg['type']);
$op = initialize_op();

$owebeditor = web_editor('notes',$args->basehref,$editorCfg) ;
$owebeditor->Value = getItemTemplateContents('role_template', $owebeditor->InstanceName, null);
$canManage = $_SESSION['currentUser']->hasRight($db,"role_management") ? true : false;

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

renderGui($args,$gui,$templateCfg);



function init_args()
{
	$iParams = array("rolename" => array("POST",tlInputParameter::STRING_N,0,100),
					         "roleid" => array("REQUEST",tlInputParameter::INT_N),
		 			         "doAction" => array("REQUEST",tlInputParameter::STRING_N,0,100),
					         "notes" => array("POST",tlInputParameter::STRING_N),
					         "grant" => array("POST",tlInputParameter::ARRAY_STRING_N),
					         "tproject_id" => array("REQUEST",tlInputParameter::INT_N));

	$args = new stdClass();
	I_PARAMS($iParams,$args);
	
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
    $op->userFeedback = tlRole::getRoleErrorMessage($result);
	}

	return $op;
}


function renderGui(&$argsObj,&$guiObj,$templateCfg)
{
	$smarty = new TLSmarty();
	$smarty->assign('gui',$guiObj);

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
 	  			header("Location: rolesView.php?tproject_id={$guiObj->tproject_id}");
	  			exit();
        }
    break;
	}

  if($doRender)
  {
		$smarty->display($templateCfg->template_dir . $tpl);
	}
}


function initialize_gui(&$argsObj,$editorType)
{
    $gui = new stdClass();
    $gui->checkboxStatus = null;
    $gui->userFeedback = null;
    $gui->affectedUsers = null;
    $gui->highlight = initialize_tabsmenu();
    $gui->editorType = $editorType;
    $gui->tproject_id = $argsObj->tproject_id;

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
    $guiObj->rightsCfg = tlRight::getRightsCfg();
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

/**
 * 
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	// For this feature check must be done on Global Rights => those that belong to
	// role assigned to user when user was created (Global/Default Role)
	// => enviroment is ignored.
	// To instruct method to ignore enviromente, we need to set enviroment but with INEXISTENT ID 
	// (best option is negative value)
	$env['tproject_id'] = -1;
	$env['tplan_id'] = -1;
	checkSecurityClearance($db,$userObj,$env,array('role_management'),'and');
}
?>