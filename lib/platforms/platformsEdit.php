<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: platformsEdit.php,v $
 *
 * @version $Revision: 1.10 $
 * @modified $Date: 2009/11/30 21:52:19 $ by $Author: erikeloff $
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
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$smarty = new TLSmarty();
$default_template = $templateCfg->default_template;

$op = new stdClass();
$op->status = 0;

$args = init_args();
$gui = init_gui($db,$args);
$platform_mgr = new tlPlatform($db, $args->testproject_id);

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
	$gui->user_feedback = getErrorMessage($op->status, $args->name);
}
$gui->platforms = $platform_mgr->getAll(array('include_linked_count' => true));

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $default_template);


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
	$args->currentUser = $_SESSION['currentUser'];
	
	
	return $args;
}

/*
  function: create
            initialize variables to launch user interface (smarty template)
            to get information to accomplish create task.

  args:
  
  returns: - 

*/
function create(&$args,&$gui)
{
	$ret = new stdClass();
	$ret->template = 'platformsEdit.tpl';
	$ret->status = 1;
	$gui->submit_button_label = lang_get('btn_save');
	$gui->submit_button_action = 'do_create';
    $gui->main_descr = lang_get('platform_management');
	$gui->action_descr = lang_get('create_platform');
	
	return $ret;
}


/*
  function: edit
            initialize variables to launch user interface (smarty template)
            to get information to accomplish edit task.

  args:
  
  returns: - 

*/
function edit(&$args,&$gui,&$platform_mgr)
{
	$ret = new stdClass();
	$ret->template = 'platformsEdit.tpl';
	$ret->status = 1;

	$gui->action_descr = lang_get('edit_platform');
	$platform = $platform_mgr->getById($args->platform_id);
	
	if ($platform)
	{
		$args->name = $platform['name'];
		$args->notes = $platform['notes'];
		$gui->name = $args->name;
		$gui->notes = $args->notes;
		$gui->action_descr .= TITLE_SEP . $platform['name'];
	}
	
	$gui->submit_button_label = lang_get('btn_save');
	$gui->submit_button_action = 'do_update';
	$gui->main_descr = lang_get('platform_management');
	
	return $ret;
}

/*
  function: do_create 
            do operations on db

  args :
  
  returns: 

*/
function do_create(&$args,&$gui,&$platform_mgr)
{
	$gui->main_descr = lang_get('platform_management');
	$gui->action_descr = lang_get('create_platform');
	$gui->submit_button_label = lang_get('btn_save');
	$gui->submit_button_action = 'do_create';

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
function do_update(&$args,&$gui,&$platform_mgr)
{
	$action_descr = lang_get('edit_platform');
	$platform = $platform_mgr->getPlatform($args->platform_id);
	if ($platform)
		$action_descr .= TITLE_SEP . $platform['name'];
    
	$gui->submit_button_label = lang_get('btn_save');
	$gui->submit_button_action = 'do_update';
	$gui->main_descr = lang_get('platform_management');
	$gui->action_descr = $action_descr;

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
function do_delete(&$args,&$gui,&$platform_mgr)
{
	$gui->main_descr = lang_get('testproject') . TITLE_SEP . 
	                      $args->testproject_name;

	$gui->submit_button_label = lang_get('btn_save');
	$gui->submit_button_action = 'do_update';
	$gui->action_descr = lang_get('edit_platform');

	$ret = new stdClass();
	$ret->template = 'platformsView.tpl';
	// This also removes all exec data on this platform
	$ret->status = $platform_mgr->delete($args->platform_id,true);

	return $ret;
}


function getErrorMessage($code,$platform_name)
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
			$msg = lang_get('platform_update_failed');
			break;

		case tlPlatform::E_NAMEALREADYEXISTS:
			$msg = sprintf(lang_get('platform_name_already_exists'),$platform_name);
			break;

		default:
			$msg = 'ok';
  }
  return $msg;
}

function init_gui(&$db,&$args)
{
	$gui = new stdClass();
	$gui->canManage = $args->currentUser->hasRight($db,"platform_management");
    $gui->mgt_view_events = $args->currentUser->hasRight($db,"mgt_view_events");
	$gui->user_feedback = '';
    $gui->name = $args->name;
    $gui->notes = $args->notes;
    $gui->platformID = $args->platform_id;
    
    return $gui;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'platform_management');
}
?>
