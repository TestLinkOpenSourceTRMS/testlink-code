<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  reqCreateFromIssueFromMantisXML.php
 * @package     TestLink
 * @copyright   2007-2013, TestLink community 
 * @link        http://www.teamst.org/index.php
 * 
 *
 *
 * <?xml version="1.0" encoding="UTF-8"?>
 *  <mantis version="1.2.14" urlbase="http://localhost/development/closet/mantisbt-1.2.14/" issuelink="#" notelink="~" format="1">
 *   <issue>
 *       <id>21</id>
 *       <project id="1">testlink-test</project>
 *       <reporter id="1">administrator</reporter>
 *       <priority id="30">normal</priority>
 *       <severity id="50">minor</severity>
 *       <reproducibility id="70">have not tried</reproducibility>
 *       <status id="10">new</status>
 *       <resolution id="10">open</resolution>
 *       <projection id="10">none</projection>
 *       <category id="2">FromTestLink</category>
 *       <date_submitted>1365184242</date_submitted>
 *       <last_updated>1365184242</last_updated>
 *       <eta id="10">none</eta>
 *       <target_version>V1</target_version>
 *       <view_state id="10">public</view_state>
 *       <summary>ISSUE-V1</summary>
 *       <due_date>1</due_date>
 *       <description>ISSUE-V1</description>
 *   </issue>
 *   <issue>
 *       <id>20</id>
 *       <project id="1">testlink-test</project>
 *
 * @internal revisions
 * @since 1.9.10
 *
 */
require('../../config.inc.php');
require_once('common.php');
require_once('xml.inc.php');

testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();
$req_spec_mgr = new requirement_spec_mgr($db);
$req_mgr = new requirement_mgr($db);
$args = init_args();
$gui = initializeGui($db,$args);

new dBug($args);
new dBug($gui);

switch($args->doAction)
{
  case 'uploadFile':
    $dummy = doExecuteImport($gui->fileName,$args,$req_spec_mgr,$req_mgr);
    $gui->items = $dummy->items;        
    $gui->file_check = $dummy->file_check;
    $gui->userFeedback = (array)$dummy->userFeedback;
    if(array_key_exists("syntaxError", $gui->userFeedback) && count($gui->userFeedback['syntaxError']) > 0) 
    {
      $gui->importResult = lang_get('import_syntax_error');
    } 
    else 
    {
      $gui->importResult = lang_get('import_done');
    }
    $gui->refreshTree = $args->refreshTree && $gui->file_check['status_ok'];  
  break;
}


// new dBug($gui);   
$smarty = new TLSmarty;
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/**
 *
 */
function init_args()
{
  $argsObj = new stdClass();
  $_REQUEST = strings_stripSlashes($_REQUEST);

  $iParams = array("importType" => array(tlInputParameter::STRING_N,0,5),
                   "req_spec_id" => array(tlInputParameter::INT_N),
                   "doAction" => array(tlInputParameter::STRING_N,0,20));
    
  R_PARAMS($iParams,$argsObj);

  $argsObj->doAction = ($argsObj->doAction == '') ? 'askFileName' : $argsObj->doAction;
  $argsObj->userID = intval($_SESSION['userID']);
  $argsObj->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  $argsObj->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
  return $argsObj;
}




/**
 * 
 *
 * 
 **/
function initializeGui(&$dbHandler,&$argsObj)
{
  $guiObj = new stdClass();
  $guiObj->importLimitBytes = config_get('import_file_max_size_bytes');
  $guiObj->importLimitKB = ($guiObj->importLimitBytes / 1024);
  $guiObj->importTypes = array('XML' => 'Mantis XML');


  $guiObj->req_spec_id = $argsObj->req_spec_id;
  $guiObj->refreshTree = $guiObj->doImport = tlStringLen($argsObj->importType);
  $guiObj->resultMap = null;
  $guiObj->req_spec_name = '';
  $guiObj->file_check = array('status_ok' => 1, 'msg' => 'ok');
  $guiObj->import_title = lang_get('title_req_import');

  $guiObj->fileName = TL_TEMP_PATH . session_id(). "-import_req_from_issue";

  if($argsObj->req_spec_id)
  {
    $tree_mgr = new tree($dbHandler);
    $node_info = $tree_mgr->get_node_hierarchy_info($argsObj->req_spec_id);
    unset($tree_mgr);    
    $guiObj->req_spec_name = $node_info['name'];
  }


  return $guiObj;
}


/**
 * doExecuteImport
 *
 */
function doExecuteImport($fileName,&$argsObj,&$reqSpecMgr,&$reqMgr)
{
  $retval = new stdClass();
  $retval->items = array();
  $retval->msg = '';
  $retval->file_check=array('status_ok' => 1, 'msg' => 'ok');
  $retval->userFeedback = null;

  $context = new stdClass();
  $context->tproject_id = $argsObj->tproject_id;
  $context->req_spec_id =  $argsObj->req_spec_id;
  $context->user_id = $argsObj->userID;
  $context->importType = $argsObj->importType;

  new dBug($context);
  // manage file upload process
  $source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;


  if (($source != 'none') && ($source != '' ))
  { 
    if (move_uploaded_file($source, $fileName))
    {
      if( strcasecmp($argsObj->importType,'XML') == 0 )
      {
        $retval->file_check['status_ok']=!(($xml=simplexml_load_file_wrapper($fileName)) === FALSE);
      }
    } 
  }
  else
  {
    $retval->file_check=array('status_ok' => 0, 'msg' => lang_get('please_choose_req_file'));
  } 
  // ----------------------------------------------------------------------------------------------
  
  if($retval->file_check['status_ok'])
  {
    //var_dump($xml);
    //die();

    $retval->items = doReqImportFromMantisXML($reqMgr,$xml,$context);
    $retval->msg = lang_get('req_import_finished');
    unlink($fileName);
  }
  return $retval;    
}


/**
 *
 */
function doReqImportFromMantisXML(&$reqMgr,&$simpleXMLObj,$importContext)
{

  $inputItems = getFromMantisIssueSimpleXMLObj($simpleXMLObj);
  $loop2do = count($inputItems);
  for($kdx=0; $kdx < $loop2do; $kdx++)
  {   
    $dummy = $reqMgr->createFromMap($inputItems[$kdx],$importContext->tproject_id,
                                    $importContext->req_spec_id,$importContext->user_id);
    $items = array_merge($items,$dummy);
  }
  return $items;
}

/**
 *
 */
function getFromMantisIssueSimpleXMLObj($xmlObj)
{
  $itemSet = null;
  if (!$xmlObj)
  {
    return $itemSet;
  }
    

  $l18n = init_labels( array('issue_issue' => null, 'issue_steps_to_reproduce' => null, 'issue_summary' => null,
                             'issue_target_version' => null,'issue_description' => null, 
                             'issue_additional_information' => null));

  $jdx = 0;
  $xmlIssue = $xmlObj->issue;
  $loops2do=sizeof($xmlIssue);
 
  $XMLDef['elements'] = array('string' => array('summary' => null,'description' => null,
                                                'additional_information' => null,
                                                'steps_to_reproduce' => null, 
                                                'target_version' => null, 'id' => null));
  $itemSet = array();
  $nl = "<p>";
  for($idx = 0; $idx < $loops2do; $idx++)
  {
    $dummy = getItemsFromSimpleXMLObj(array($xmlIssue[$idx]),$XMLDef);
    $dummy = $dummy[0];

    $isum = $l18n['issue_description'] . $nl . $dummy['description'];
    if(!is_null($dummy['steps_to_reproduce']))
    {
      $isum .= $nl . $l18n['issue_steps_to_reproduce'] . $nl . $dummy['steps_to_reproduce'];
    }  
    if(!is_null($dummy['additional_information']))
    {
      $isum .= $nl . $l18n['issue_additional_information'] . $nl . $dummy['additional_information'];
    }  

    $itemSet[$jdx++] = array('docid' => 'Mantis Task ID:' .$dummy['id'],
                             'title' => ($l18n['issue_issue'] . ':' . $dummy['id'] . ' - ' . $dummy['summary']), 
                             'description' => $isum,'node_order' => $idx,
                             'status' => '', 'type' => '', 'expected_coverage' => 1);    
  }
  return $itemSet;
}

/**
 * 
 *
 */
function checkRights(&$db,&$user)
{
  return ($user->hasRight($db,'mgt_view_req') && $user->hasRight($db,'mgt_modify_req'));
}
