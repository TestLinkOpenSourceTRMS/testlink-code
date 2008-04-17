<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource $RCSfile: reqEdit.php,v $
 * @version $Revision: 1.16 $
 * @modified $Date: 2008/04/17 08:24:10 $ by $Author: franciscom $
 * @author Martin Havlat
 *
 * Screen to view existing requirements within a req. specification.
 *
 * rev: 20080411 - franciscom - BUGID 1476
 *      20070415 - franciscom - custom field manager
 *      20070415 - franciscom - added reorder feature
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once('requirements.inc.php');
require_once('attachments.inc.php');
require_once("csv.inc.php");
require_once("xml.inc.php");
require_once('requirement_spec_mgr.class.php');
require_once('requirement_mgr.class.php');
require_once("web_editor.php");
require_once("configCheck.php");
testlinkInitPage($db);

$req_spec_mgr = new requirement_spec_mgr($db);
$req_mgr = new requirement_mgr($db);

$get_cfield_values = array();
$get_cfield_values['req_spec'] = 0;
$get_cfield_values['req'] = 0;

$user_feedback = '';
$sqlResult = null;
$action = null;
$sqlItem = 'SRS';
$arrReq = array();
$template_dir = "requirements/";
$template = 'reqSpecView.tpl';

$templateCfg = templateConfiguration();

$args = init_args();
$gui = initialize_gui();

$tproject = new testproject($db);
$smarty = new TLSmarty();

$commandMgr=new reqCommands($db);

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

	case "create_tcases":
	case "doCreate_tcases":
		$template = $template_dir .  'reqCreateTestCases.tpl';
		$req_spec=$req_spec_mgr->get_by_id($args->req_spec_id);
		$main_descr=lang_get('req_spec') . TITLE_SEP . $req_spec['title'];
		$action_descr=lang_get('create_testcase_from_req');

		$all_reqs=$req_spec_mgr->get_requirements($args->req_spec_id);
		$smarty->assign('req_spec_id', $args->req_spec_id);
		$smarty->assign('req_spec_name', $req_spec['title']);
		$smarty->assign('arrReqs', $all_reqs);

		if( $args->doAction=='doCreate_tcases')
		{
			$feedback=$req_mgr->create_tc_from_requirement($args->arrReqIds,$args->req_spec_id,$args->user_id);
			$smarty->assign('array_of_msg', $feedback);
		}
		break;
} // switch

renderGui($smarty,$args,$gui,$op,$templateCfg);


/*
  function: 

  args :
  
  returns: 

*/
function init_args()
{
	$args = new stdClass();
	$args->req_id = isset($_REQUEST['requirement_id']) ? $_REQUEST['requirement_id'] : null;
	$args->req_spec_id = isset($_REQUEST['req_spec_id']) ? $_REQUEST['req_spec_id'] : null;
	$args->reqDocId = isset($_REQUEST['reqDocId']) ? trim($_REQUEST['reqDocId']) : null;
	$args->title = isset($_REQUEST['req_title']) ? trim($_REQUEST['req_title']) : null;
	$args->scope = isset($_REQUEST['scope']) ? $_REQUEST['scope'] : null;
	$args->reqStatus = isset($_REQUEST['reqStatus']) ? $_REQUEST['reqStatus'] : TL_REQ_STATUS_VALID;
	$args->reqType = isset($_REQUEST['reqType']) ? $_REQUEST['reqType'] : TL_REQ_TYPE_1;
	$args->countReq = isset($_REQUEST['countReq']) ? intval($_REQUEST['countReq']) : 0;

	$args->arrReqIds = isset($_POST['req_id_cbox']) ? $_POST['req_id_cbox'] : null;

	$args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction']:null;
	$args->do_export = isset($_REQUEST['exportAll']) ? 1 : 0;
	$args->exportType = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : null;
	$args->do_create_tc_from_req = isset($_REQUEST['create_tc_from_req']) ? 1 : 0;
	$args->do_delete_req = isset($_REQUEST['req_select_delete']) ? 1 : 0;

  $args->basehref=$_SESSION['basehref'];
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : "";
	$args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
	$args->nodes_order = isset($_REQUEST['nodes_order']) ? $_REQUEST['nodes_order'] : null;

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
function initialize_gui()
{
    $gui=new stdClass();
    $gui->user_feedback = null;
    $gui->main_descr = null;
    $gui->action_descr = null;
    return $gui;
}

?>
