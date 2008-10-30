<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: rolesEdit.php,v $
 *
 * @version $Revision: 1.23 $
 * @modified $Date: 2008/10/30 20:00:37 $ by $Author: franciscom $
 *
 * rev: 20081030 - franciscom - added system_mgmt member on getRightsCfg()
 *      20080827 - franciscom - BUGID 1692
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once("web_editor.php");
$editorCfg=getWebEditorCfg('role');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db);
init_global_rights_maps();

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initialize_gui($editorCfg['type']);
$op = initialize_op();

$owebeditor = web_editor('notes',$_SESSION['basehref'],$editorCfg) ;
$owebeditor->Value = null;
$canManage=has_rights($db,"role_management") ? true : false;


switch($args->doAction)
{
    case 'create':
		break;

	  case 'edit':
		    $op->role = tlRole::getByID($db,$args->roleid);
		break;

	  case 'doCreate':
	  	  if($canManage)
	  	  {
	  	  	$op = doCreate($db,$args);
	  	  	$templateCfg->template=$op->template;
        }
	  break;

	  case 'doUpdate':
	  	  if($canManage)
	  	  {
	  	  	$op = doUpdate($db,$args);
	  	  	$templateCfg->template=$op->template;
        }
	  break;
}

$gui=complete_gui($db,$gui,$args,$op->role,$owebeditor);
$gui->userFeedback=$op->userFeedback;

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->assign('highlight',$gui->highlight);

renderGui($smarty,$args,$templateCfg);

/*
  function: init_args

  args:

  returns:

*/
function init_args()
{
  $args = new stdClass();
	$_REQUEST = strings_stripSlashes($_REQUEST);

	$key2loop = array('doAction' => null,'rolename' => null , 'roleid' => 0, 'notes' => '', 'grant' => null);
	foreach($key2loop as $key => $value)
	{
		$args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
	}
	return $args;
}

/*
  function: doCreate

  args:

  returns:

*/
function doCreate(&$dbHandler,$argsObj)
{
  $op=doOperation($dbHandler,$argsObj,'doCreate');
	return $op;
}

/*
  function: doUpdate

  args:

  returns:

*/
function doUpdate(&$dbHandler,$argsObj)
{
  $op=doOperation($dbHandler,$argsObj,'doUpdate');
	return $op;
}


/*
  function: doOperation

  args:

  returns:

*/
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

  switch($operation)
  {
      case 'doCreate':
          $auditCfg['msg']="audit_role_created";
          $auditCfg['activity']="CREATE";
      break;

      case 'doUpdate':
          $auditCfg['msg']="audit_role_saved";
          $auditCfg['activity']="SAVE";
      break;

  }

	$result = $op->role->writeToDB($dbHandler);
	if ($result >= tl::OK)
	{
		  logAuditEvent(TLS($auditCfg['msg'],$argsObj->rolename),$auditCfg['activity'],$op->role->dbID,"roles");
		  $op->template = null;
  }
  else
  {
      $op->userFeedback = getRoleErrorMessage($result);
  }

  return $op;
}




/*
  function: renderGui

  args :

  returns:

*/
function renderGui(&$smartyObj,&$argsObj,$templateCfg)
{
    $doRender=false;
    switch($argsObj->doAction)
    {
        case "edit":
        case "create":
        $doRender=true;
    		$tpl = $templateCfg->default_template;
    		break;

	      case "doCreate":
        case "doUpdate":
        if( !is_null($templateCfg->template) )
        {
            $doRender=true;
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
    $cfg=new stdClass();
    $cfg->tplan_mgmt=config_get('rights_tp');
    $cfg->tcase_mgmt=config_get('rights_mgttc');
    $cfg->kword_mgmt=config_get('rights_kw');
    $cfg->tproject_mgmt=config_get('rights_product');
    $cfg->user_mgmt=config_get('rights_users');
    $cfg->req_mgmt=config_get('rights_req');
    $cfg->cfield_mgmt=config_get('rights_cf');
    $cfg->system_mgmt=config_get('rights_system');
    return $cfg;
}


/*
  function: initialize_gui

  args : -

  returns:

*/
function initialize_gui($editorType)
{
    $gui=new stdClass();
    $gui->checkboxStatus = null;
    $gui->userFeedback = null;
    $gui->affectedUsers = null;
    $gui->highlight = initialize_tabsmenu();
    $gui->editorType=$editorType;

    return $gui;
}

/*
  function: initialize_op

  args : -

  returns:

*/
function initialize_op()
{
    $op=new stdClass();
    $op->role=new tlRole();
    $op->userFeedback='';
    return $op;
}

/*
  function: complete_gui

  args :

  returns:

*/
function complete_gui(&$dbHandler,&$guiObj,&$argsObj,&$roleObj,&$webEditorObj)
{
    $actionCfg['operation']=array('create' => 'doCreate', 'edit' => 'doUpdate',
                                  'doCreate' => 'doCreate', 'doUpdate' => 'doUpdate');

    $actionCfg['highlight']=array('create' => 'create_role', 'edit' => 'edit_role',
                                  'doCreate' => 'create_role', 'doUpdate' => 'edit_role');


    $guiObj->highlight->$actionCfg['highlight'][$argsObj->doAction]=1;
    $guiObj->operation = $actionCfg['operation'][$argsObj->doAction];
    $guiObj->role=$roleObj;
    $guiObj->grants=getGrantsForUserMgmt($dbHandler,$_SESSION['currentUser']);
    $guiObj->rightsCfg=getRightsCfg();
	  $guiObj->mgt_view_events = $_SESSION['currentUser']->hasRight($db,"mgt_view_events");

    // Create status for all checkboxes and set to unchecked
    foreach( $guiObj->rightsCfg as $grantDetails )
    {
        foreach( $grantDetails as $grantCode => $grantDescription )
        {
            $guiObj->checkboxStatus[$grantCode] ="";
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

    $guiObj->notes=$webEditorObj->CreateHTML();
    return $guiObj;
}
?>