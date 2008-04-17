<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: reqSpecEdit.php,v $
 * @version $Revision: 1.18 $
 * @modified $Date: 2008/04/17 08:24:10 $ $Author: franciscom $
 *
 * @author Martin Havlat
 *
 * View existing and create a new req. specification.
 *
 * rev : 20071106 - franciscom - custom field management
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("req_tree_menu.php");
require_once('requirements.inc.php');
require_once('requirement_spec_mgr.class.php');
require_once("web_editor.php");
testlinkInitPage($db);


$sqlResult = null;
$action = null;
$main_descr = null;
$action_descr = null;
$cf_smarty = null;
$user_feedback = null;
$req_spec_mgr = new requirement_spec_mgr($db);
$smarty = new TLSmarty();

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initialize_gui($db);
$commandMgr=new reqSpecCommands($db);

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
		$req_spec = $req_spec_mgr->get_by_id($args->req_spec_id);
		$req_spec_mgr->delete($args->req_spec_id);
		logAuditEvent(TLS("audit_req_spec_deleted",$args->title),"DELETE",$args->req_spec_id,"req_specs");
		$template = 'show_message.tpl';
		$user_feedback = sprintf(lang_get('req_spec_deleted'),$req_spec['title']);
		$smarty->assign('title', lang_get('delete_req_spec'));
		$smarty->assign('item_type', lang_get('requirement_spec'));
		$smarty->assign('item_name', $req_spec['title']);
		$smarty->assign('user_feedback',$user_feedback );
		$smarty->assign('refresh_tree','yes');
		$smarty->assign('result','ok');
		break;

	case "reorder":
		$template = $templateCfg->template_dir .  'reqSpecReorder.tpl';
		$order_by = ' ORDER BY NH.node_order,REQ_SPEC.id ';
		$all_req_spec = $req_spec_mgr->get_all_in_testproject($args->tproject_id,$order_by);
		$smarty->assign('arrReqSpecs', $all_req_spec);
		break;

  	case "doReorder":
		$nodes_in_order = transform_nodes_order($args->nodes_order);
		// need to remove first element, is testproject
		array_shift($nodes_in_order);
		$req_spec_mgr->set_order($nodes_in_order);
		$template = $templateCfg->template_dir .  'project_req_spec_mgmt.tpl';
		$smarty->assign('refresh_tree', 'yes');
		break;
}


renderGui($smarty,$args,$gui,$op,$templateCfg);

/*
  function: init_args

  args:
  
  returns: 

*/
function init_args()
{
	$args = new stdClass();
	$_REQUEST = strings_stripSlashes($_REQUEST);

	$args->title = isset($_REQUEST['req_spec_title']) ? $_REQUEST['req_spec_title'] : null;
	$args->scope = isset($_REQUEST['scope']) ? $_REQUEST['scope'] : null;
	$args->countReq = isset($_REQUEST['countReq']) ? intval($_REQUEST['countReq']) : 0;
	$args->req_spec_id = isset($_REQUEST['req_spec_id']) ? intval($_REQUEST['req_spec_id']) : null;

	$args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction']:null;
	$args->nodes_order = isset($_REQUEST['nodes_order']) ? $_REQUEST['nodes_order'] : null;
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : "";
	$args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
	$args->basehref=$_SESSION['basehref'];
	
	

	return $args;
}

/*
  function: renderGui

  args :

  returns:

*/
function renderGui(&$smartyObj,&$argsObj,$guiObj,$opObj,$templateCfg)
{
    $actionOperation=array('create' => 'doCreate', 'edit' => 'doUpdate',
                           'doDelete' => '', 'doReorder' => '', 'reorder' => '',
                           'doCreate' => 'doCreate', 'doUpdate' => 'doUpdate');

    $owebEditor = web_editor('scope',$argsObj->basehref) ;
    $owebEditor->Value = $argsObj->scope;
	  $guiObj->scope=$owebEditor->CreateHTML();
      
    $renderType='none';
    
    switch($argsObj->doAction)
    {
        case "edit":
        case "create":
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

