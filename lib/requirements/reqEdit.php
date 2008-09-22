<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource $RCSfile: reqEdit.php,v $
 * @version $Revision: 1.22 $
 * @modified $Date: 2008/09/22 19:14:09 $ by $Author: schlundus $
 * @author Martin Havlat
 *
 * Screen to view existing requirements within a req. specification.
 *
 * rev: 20080827 - franciscom - BUGID 1692
 *      20080411 - franciscom - BUGID 1476
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
require_once("configCheck.php");
require_once("web_editor.php");
$editorCfg = getWebEditorCfg('requirement');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db);

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initialize_gui($db);
$commandMgr = new reqCommands($db);

switch($args->doAction)
{
	case "create":
	  $op=$commandMgr->create($args);
		break;

	case "edit":
	 	$op = $commandMgr->edit($args);
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

	case "createTestCases":
		$op=$commandMgr->createTestCases($args);
		break;
	
	case "doCreateTestCases":
		$op=$commandMgr->doCreateTestCases($args);
		break;
} // switch

renderGui($args,$gui,$op,$templateCfg,$editorCfg);


/*
  function: 

  args :
  
  returns: 

*/
function init_args()
{
	$_REQUEST = strings_stripSlashes($_REQUEST);
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
function renderGui(&$argsObj,$guiObj,$opObj,$templateCfg,$editorCfg)
{
    $smartyObj = new TLSmarty();
    $actionOperation=array('create' => 'doCreate', 'edit' => 'doUpdate',
                           'doDelete' => '', 'doReorder' => '', 'reorder' => '',
                           'createTestCases' => 'doCreateTestCases',
                           'doCreateTestCases' => 'doCreateTestCases',
                           'doCreate' => 'doCreate', 'doUpdate' => 'doUpdate');

    $owebEditor = web_editor('scope',$argsObj->basehref,$editorCfg) ;
    $owebEditor->Value = $argsObj->scope;
	$guiObj->scope = $owebEditor->CreateHTML();
    $guiObj->editorType = $editorCfg['type'];
      
    $renderType = 'none';
    switch($argsObj->doAction)
    {
        case "edit":
        case "create":
        case "reorder":
        case "doDelete":
        case "doReorder":
        case "createTestCases":
        case "doCreateTestCases":
		case "doCreate":
      	case "doUpdate":
            $renderType = 'template';
            $key2loop = get_object_vars($opObj);
            foreach($key2loop as $key => $value)
            {
                $guiObj->$key = $value;
            }
            $guiObj->operation = $actionOperation[$argsObj->doAction];
            
            $tplDir = is_null($opObj->template_dir) ? $templateCfg->template_dir : $opObj->template_dir;
            $tpl = is_null($opObj->template) ? $templateCfg->default_template : $opObj->template;
            
            $pos = strpos($tpl, '.php');
           	if($pos === false)
                $tpl = $tplDir . $tpl;      
            else
                $renderType = 'redirect';  

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
    $gui = new stdClass();
    $gui->user_feedback = null;
    $gui->main_descr = null;
    $gui->action_descr = null;

    $gui->grants = new stdClass();
    $gui->grants->req_mgmt = has_rights($dbHandler,"mgt_modify_req");
	$gui->grants->mgt_view_events = has_rights($dbHandler,"mgt_view_events");
	
    return $gui;
}
?>