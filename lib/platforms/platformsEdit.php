<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: platformsEdit.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2009/08/08 14:11:50 $ by $Author: franciscom $
 *
 * allows users to manage platforms. 
 *
 * This is a fully commented model of How I think we need to develop new
 * pages of this kind, and how we need to refactor old pages.
 *
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("csv.inc.php");
require_once("xml.inc.php");
require_once("platform.class.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$smarty = new TLSmarty();


$op = new stdClass();
$op->status = 0;

$args = init_args();
$gui = init_gui($db,$args,$_SESSION['currentUser']);
$platform_mgr= new tlPlatform($db, $args->testproject_id);

$action = $args->doAction;
switch ($action)
{
	case "do_create":
	case "do_update":
	case "do_delete":
		if (!$gui->canManage)
		{
			break;
		}
			
	case "edit":
	case "create":
		$op = $action($args,$gui,$platform_mgr);
	break;
}

if($op->status == 1)
{
	$default_template = $op->template;
}
else
{
	$gui->user_feedback = getErrorMessage($op->status);
}

$gui->platforms = $platform_mgr->getAll();

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $default_template);


/*
  function: init_args

  args:
  
  returns: 

*/
function init_args()
{
	$args = new stdClass();
	$source = sizeof($_POST) ? "POST" : "GET";
	$iParams = array("doAction" => array($source,tlInputParameter::STRING_N,0,50),
			         "id" => array($source, tlInputParameter::INT_N),
			         "name" => array($source, tlInputParameter::STRING_N,0,100),
			         "notes" => array($source, tlInputParameter::STRING_N));
		
	$pParams = I_PARAMS($iParams);

	$args->doAction = $pParams["doAction"];
	$args->platform_id = $pParams["id"];
	$args->name = $pParams["name"];
	$args->notes = $pParams["notes"];

	// why we need this logic ????
	if ($args->doAction == "edit")
	{
		$_SESSION['platform_id'] = $args->platform_id;
	}
	else if($args->doAction == "do_update")
	{
		$args->platform_id = $_SESSION['platform_id'];
	}
	
	$args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->testproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 0;

	return $args;
}

/*
  function: create
            initialize variables to launch user interface (smarty template)
            to get information to accomplish create task.

  args:
  
  returns: - 

*/
function create(&$args,&$guiObj)
{
	$ret = new stdClass();
	$ret->template = 'platformsEdit.tpl';
	$ret->status = 1;
	$guiObj->submit_button_label = lang_get('btn_save');
	$guiObj->submit_button_action = 'do_create';
    $guiObj->main_descr = lang_get('platform_management');
	$guiObj->action_descr = lang_get('create_platform');
	
	return $ret;
}


/*
  function: edit
            initialize variables to launch user interface (smarty template)
            to get information to accomplish edit task.

  args:
  
  returns: - 

*/
function edit(&$args,&$guiObj,&$platform_mgr)
{
	$ret = new stdClass();
	$ret->template = 'platformsEdit.tpl';
	$ret->status = 1;

	$guiObj->action_descr = lang_get('edit_platform');
	$platform = $platform_mgr->getPlatform($args->platform_id);
	
	if ($platform)
	{
		$args->name = $platform['name'];
		$args->notes = $platform['notes'];
		$guiObj->name = $args->name;
		$guiObj->notes = $args->notes;
		$guiObj->action_descr .= TITLE_SEP . $platform['name'];
	}
	
	$guiObj->submit_button_label = lang_get('btn_save');
	$guiObj->submit_button_action = 'do_update';
	$guiObj->main_descr = lang_get('platform_management');
	return $ret;
}

/*
  function: do_create 
            do operations on db

  args :
  
  returns: 

*/
function do_create(&$args,&$guiObj,&$platform_mgr)
{
	$guiObj->main_descr = lang_get('platform_management');
	$guiObj->action_descr = lang_get('create_platform');
	$guiObj->submit_button_label = lang_get('btn_save');
	$guiObj->submit_button_action = 'do_create';

	$ret = new stdClass();
	$ret->template = 'platformsView.tpl';
	$ret->status = $platform_mgr->create($args->name,$args->notes);
	return $ret;
}

/*
  function: do_update
            do operations on db

  args :
  
  returns: 

*/
function do_update(&$args,&$guiObj,&$platform_mgr)
{
	$action_descr = lang_get('edit_platform');
	$platform = $platform_mgr->getPlatform($args->platform_id);
	if ($platform)
	{
		$action_descr .= TITLE_SEP . $platform['name'];
    }
	$guiObj->submit_button_label = lang_get('btn_save');
	$guiObj->submit_button_action = 'do_update';
	$guiObj->main_descr = lang_get('platform_management');
	$guiObj->action_descr = $action_descr;

	$ret = new stdClass();
	$ret->template = 'platformsView.tpl';
	$ret->status = $platform_mgr->update($args->platform_id,
						 		         $args->name,$args->notes);

	return $ret;
}

/*
  function: do_delete
            do operations on db

  args :
  
  returns: 

*/
function do_delete(&$args,&$guiObj,&$platform_mgr)
{
	$guiObj->main_descr = lang_get('testproject') . TITLE_SEP . 
	                      $args->testproject_name;

	$guiObj->submit_button_label = lang_get('btn_save');
	$guiObj->submit_button_action = 'do_update';
	$guiObj->action_descr = lang_get('edit_platform');

	$ret = new stdClass();
	$ret->template = 'platformsView.tpl';
	$ret->status = $platform_mgr->delete($args->platform_id);

	return $ret;
}

/*
  function: getErrorMessage

  args:
  
  returns: 

*/
function getErrorMessage($code)
{
	switch($code)
	{
		case tlPlatform::E_NAMENOTALLOWED:
			$msg = lang_get('platforms_char_not_allowed'); 
			break;

		case tlPlatform::E_NAMELENGTH:
			$msg = lang_get('empty_platform_no');
			break;

		case tlPlatform::E_DBERROR:
		case ERROR: 
			$msg = lang_get('kw_update_fails');
			break;

		case tlPlatform::E_NAMEALREADYEXISTS:
			$msg = lang_get('platform_already_exists');
			break;

		default:
			$msg = 'ok';
  }
  return $msg;
}


/**
 * 
 *
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'platform_management');
}


/**
 * 
 *
 */
function init_gui(&$dbHandler,&$argsObj,&$currentUser)
{
	$guiObj = new stdClass();
	$guiObj->canManage = $currentUser->hasRight($dbHandler,"platform_management");
    $guiObj->mgt_view_events = $currentUser->hasRight($dbHandler,"mgt_view_events");
	$guiObj->user_feedback = '';
    $guiObj->name = $argsObj->name;
    $guiObj->notes = $argsObj->notes;
    $guiObj->platformID = $argsObj->platform_id;
    return $guiObj;
}
?>
