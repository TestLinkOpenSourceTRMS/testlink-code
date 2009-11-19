<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource $RCSfile: reqEdit.php,v $
 * @version $Revision: 1.33 $
 * @modified $Date: 2009/11/19 17:52:13 $ by $Author: franciscom $
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
require_once("configCheck.php");
require_once("web_editor.php");

$editorCfg = getWebEditorCfg('requirement');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initialize_gui($db,$args);
$commandMgr = new reqCommands($db);

$pFn = $args->doAction;
$op = null;
if(method_exists($commandMgr,$pFn))
	$op = $commandMgr->$pFn($args,$_REQUEST);
renderGui($args,$gui,$op,$templateCfg,$editorCfg);


function init_args()
{
	$iParams = array(
			"requirement_id" => array(tlInputParameter::INT_N),
			"req_spec_id" => array(tlInputParameter::INT_N),
			"reqDocId" => array(tlInputParameter::STRING_N,0,32),
			"req_title" => array(tlInputParameter::STRING_N,0,100),
			"scope" => array(tlInputParameter::STRING_N),
			"reqStatus" => array(tlInputParameter::STRING_N,0,1),
			"countReq" => array(tlInputParameter::INT_N),
			"doAction" => array(tlInputParameter::STRING_N,0,100),
			"req_id_cbox" => array(tlInputParameter::ARRAY_INT),
	);	
		
	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);

	$args->req_id = $args->requirement_id;
	$args->title = $args->req_title;
	$args->arrReqIds = $args->req_id_cbox;


	$args->basehref = $_SESSION['basehref'];
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : "";
	$args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;

	return $args;
}

function renderGui(&$argsObj,$guiObj,$opObj,$templateCfg,$editorCfg)
{
    $smartyObj = new TLSmarty();
    $actionOperation = array('create' => 'doCreate', 'edit' => 'doUpdate',
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
            
            $tplDir = (!isset($opObj->template_dir)  || is_null($opObj->template_dir)) ? $templateCfg->template_dir : $opObj->template_dir;
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

function initialize_gui(&$dbHandler,&$argsObj)
{
    $req_spec_mgr = new requirement_spec_mgr($dbHandler);
    $gui = new stdClass();
    
  	$gui->req_spec_id = $argsObj->req_spec_id;
	if ($argsObj->req_spec_id)
	{
		$gui->requirements_count = $req_spec_mgr->get_requirements_count($gui->req_spec_id);
		$gui->req_spec = $req_spec_mgr->get_by_id($gui->req_spec_id);
	}
    $gui->user_feedback = null;
    $gui->main_descr = lang_get('req_spec_short');
    if (isset($gui->req_spec))
     	$gui->main_descr .= config_get('gui_title_separator_1') . $gui->req_spec['title'];
    $gui->action_descr = null;

    $gui->grants = new stdClass();
    $gui->grants->req_mgmt = has_rights($dbHandler,"mgt_modify_req");
	$gui->grants->mgt_view_events = has_rights($dbHandler,"mgt_view_events");
	
	return $gui;
}


function checkRights(&$db,&$user)
{
	return ($user->hasRight($db,'mgt_view_req') && $user->hasRight($db,'mgt_modify_req'));
}
?>