<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: reqSpecEdit.php,v $
 * @version $Revision: 1.24 $
 * @modified $Date: 2008/09/22 19:14:09 $ $Author: schlundus $
 *
 * @author Martin Havlat
 *
 * View existing and create a new req. specification.
 *
 * rev: 20080830 - franciscom - added code to manage unlimited depth tree
 *                              (will be not enabled yet)
 *
 *      20080827 - franciscom - BUGID 1692 
 *      20071106 - franciscom - custom field management
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("req_tree_menu.php");
require_once('requirements.inc.php');
require_once('requirement_spec_mgr.class.php');
require_once("web_editor.php");
$editorCfg=getWebEditorCfg('requirement_spec');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db);

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initialize_gui($db);
$commandMgr=new reqSpecCommands($db);

$auditContext=new stdClass();
$auditContext->tproject=$args->tproject_name;
$commandMgr->setAuditContext($auditContext);

switch($args->doAction)
{
	case "create":
 	  $op=$commandMgr->create($args);
		break;

	case "edit":
	  $op=$commandMgr->edit($args);
		break;

	case "doCreate":
	  $op=$commandMgr->doCreate($args,$_REQUEST);
		break;

	case "doUpdate":
	  $op=$commandMgr->doUpdate($args,$_REQUEST);
		break;

	case "doDelete":
	  $op=$commandMgr->doDelete($args);
		break;

	case "reorder":
	  $op=$commandMgr->reorder($args);
		break;

  case "doReorder":
		$op=$commandMgr->doReorder($args);
		break;

  case "createChild":
    // Will be use in future when we clarify how we will want to manage
    // requirements with unlimited depth
 	  $op=$commandMgr->createChild($args);
	  break;
}

renderGui($args,$gui,$op,$templateCfg,$editorCfg);

/*
  function: init_args

  args:
  
  returns: 
  
  rev: 20080830 - franciscom

*/
function init_args()
{
	$args = new stdClass();
	$_REQUEST = strings_stripSlashes($_REQUEST);
	
	
	$key2loop = array('scope','countReq','req_spec_id','doAction','reqParentID','nodes_order');
	$intkey2loop = array('countReq','req_spec_id','reqParentID');
	foreach($intkey2loop as $keyname)
	{
		$val = isset($_REQUEST[$keyname]) ? intval($_REQUEST[$keyname]) : null;
		if ($val < 0)
			$val = 0;
		$args->$keyname = $val;
	}
	
	$key2loop = array('scope','doAction','nodes_order');
	foreach($key2loop as $keyname)
	{
		$args->$keyname = isset($_REQUEST[$keyname]) ? $_REQUEST[$keyname] : null;
	}
	
	$args->title = isset($_REQUEST['req_spec_title']) ? $_REQUEST['req_spec_title'] : null;
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : "";
	$args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
	$args->basehref = $_SESSION['basehref'];
	
	$args->reqParentID = is_null($args->reqParentID) ? $args->tproject_id : $args->reqParentID;

	return $args;
}

/*
  function: renderGui

  args :

  returns:

*/
function renderGui(&$argsObj,$guiObj,$opObj,$templateCfg,$editorCfg)
{
    $smartyObj = new TLSmarty();
    $actionOperation=array('create' => 'doCreate', 'edit' => 'doUpdate',
                           'doDelete' => '', 'doReorder' => '', 'reorder' => '',
                           'doCreate' => 'doCreate', 'doUpdate' => 'doUpdate',
                           'createChild' => 'doCreate');

    $owebEditor = web_editor('scope',$argsObj->basehref,$editorCfg) ;
    $owebEditor->Value = $argsObj->scope;
	  $guiObj->scope=$owebEditor->CreateHTML();
    $guiObj->editorType=$editorCfg['type'];  
      
    $renderType='none';
    
    switch($argsObj->doAction)
    {
        case "edit":
        case "create":
        case "createChild":
        case "reorder":
        case "doDelete":
        case "doReorder":
            $renderType='template';
            $key2loop=get_object_vars($opObj);
            foreach($key2loop as $key => $value)
            {
                $guiObj->$key=$value;
            }
            $guiObj->operation = $actionOperation[$argsObj->doAction];
    		    $tpl = is_null($opObj->template) ? $templateCfg->default_template : $opObj->template;
            $tpd = isset($key2loop['template_dir']) ? $opObj->template_dir : $templateCfg->template_dir;
            $tpl = $tpd . $tpl;
    		break;

	      case "doCreate":
	      case "doUpdate":
            $renderType='template';
            $key2loop=get_object_vars($opObj);
            foreach($key2loop as $key => $value)
            {
                $guiObj->$key=$value;
            }
            $guiObj->operation = $actionOperation[$argsObj->doAction];
            $tpl = is_null($opObj->template) ? $templateCfg->default_template : $opObj->template;
            $pos = strpos($tpl, '.php');
            if( $pos === false )
            {
                $tpl = $templateCfg->template_dir . $tpl;      
            }
            else
            {
                $renderType='redirect';  
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

/*
  function: initialize_gui

  args : -

  returns:

*/
function initialize_gui(&$dbHandler)
{
    $gui=new stdClass();
    $gui->user_feedback = null;
    $gui->main_descr = null;
    $gui->action_descr = null;
    $gui->refresh_tree = 'no';

    $gui->grants=new stdClass();
    $gui->grants->req_mgmt = has_rights($dbHandler,"mgt_modify_req");

    return $gui;
}
?>