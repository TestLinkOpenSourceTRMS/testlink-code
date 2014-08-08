<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource  reqImport.php
 * @author Martin Havlat
 * 
 * Import ONLY requirements to a req specification. 
 * Supported: simple CSV, Doors CSV, XML, DocBook
 *
 * @internal revisions
 * since 1.9.12
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
require_once('xml.inc.php');
require_once('csv.inc.php');

testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$req_spec_mgr = new requirement_spec_mgr($db);
$req_mgr = new requirement_mgr($db);

$args = init_args();
$gui = initializeGui($db,$args,$_SESSION,$req_spec_mgr,$req_mgr);
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

   
$smarty = new TLSmarty;
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



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
  $context->user_id = $argsObj->user_id;
  $context->importType = $argsObj->importType;

  $opts = array();
  $opts['skipFrozenReq'] = ($argsObj->skip_frozen_req ? true : false);
  $opts['hitCriteria'] = $argsObj->hitCriteria;
  $opts['actionOnHit'] = $argsObj->actionOnHit;
    
  // manage file upload process
  $source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;
  if (($source != 'none') && ($source != '' ))
  { 
    if (move_uploaded_file($source, $fileName))
    {
      if( $argsObj->importType == 'XML' )
      {
        $retval->file_check['status_ok']=!(($xml=simplexml_load_file_wrapper($fileName)) === FALSE);
        if( !$retval->file_check['status_ok'] )
        {
          $retval->file_check['msg'] = lang_get('import_failed_xml_load_failed');
        }  
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
    if($argsObj->importType == 'XML')
    {
      // If there is no req_spec in XML, and req_spec_id 
      // from context is null, we must raise an error, to avoid ghots requirements in DB
      $isReqSpec = property_exists($xml,'req_spec');
      if(!$isReqSpec && $argsObj->req_spec_id <= 0) 
      {
        $retval->file_check = array('status_ok' => FALSE, 'msg' => lang_get('please_create_req_spec_first'));
      }
      else
      {  
        $retval->items = doReqImportFromXML($reqSpecMgr,$reqMgr,$xml,$context,$opts);
      }  
    }
    else
    {
      $dummy = doReqImportOther($reqMgr,$fileName,$context,$opts);
      $retval->items = $dummy['items'];
      $retval->userFeedback = $dummy['userFeedback'];
    }
    unlink($fileName);
    $retval->msg = lang_get('req_import_finished');
  }

  return $retval;    
}

/*
  function: 

  args :
  
  returns: 

*/
function init_args()
{
  $args = new stdClass();
  $request = strings_stripSlashes($_REQUEST);
   
   
  $key='actionOnHit';
  $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : 'update_last_version';

  $key='hitCriteria';
  $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : 'docid';
   
   
  $args->req_spec_id = isset($request['req_spec_id']) ? intval($request['req_spec_id']) : null;
  $args->importType = isset($request['importType']) ? $request['importType'] : null;
  $args->emptyScope = isset($request['noEmpty']) ? $request['noEmpty'] : null;
  $args->conflictSolution = isset($request['conflicts']) ? $request['conflicts'] : null;
  $args->bUpload = isset($request['uploadFile']) ? 1 : 0;
    
  // useRecursion: used when you want to work on test project or req. spec
  $args->useRecursion = isset($request['useRecursion']) ? 1 : 0;
  $args->skip_frozen_req = isset($request['skip_frozen_req']) ? 1 : 0;

    
  $args->doAction='askFileName';
  $action_keys = array('uploadFile','executeImport');
  foreach($action_keys as $action)
  {
    if( isset($request[$action]) )
    {
      $args->doAction=$action;
      break;            
    }    
  }
    
  $args->achecked_req = isset($request['achecked_req']) ? $request['achecked_req'] : null;
  $args->tproject_id = intval($_SESSION['testprojectID']);
  $args->tproject_name = $_SESSION['testprojectName'];
  $args->user_id = intval(isset($_SESSION['userID']) ? $_SESSION['userID'] : 0);
  $args->scope = isset($_REQUEST['scope']) ? $_REQUEST['scope'] : 'items';

  $args->refreshTree = isset($_SESSION['setting_refresh_tree_on_action']) ? 
                       $_SESSION['setting_refresh_tree_on_action'] : 0;

  return $args;
}





/**
 * initializeGui()
 * create object that will be used by Smarty template
 *
 */
function initializeGui(&$dbHandler,&$argsObj,$session,&$reqSpecMgr,&$reqMgr)
{
  $gui=new stdClass();
  $gui->file_check = array('status_ok' => 1, 'msg' => 'ok');
  $gui->items=null;
  $gui->try_upload = $argsObj->bUpload;
  $gui->importResult = null;
  $gui->refreshTree = false;

  $gui->doAction=$argsObj->doAction;
  $gui->scope = $argsObj->scope;
  $gui->req_spec = null;
  $gui->req_spec_id = $argsObj->req_spec_id;
  $gui->hitCriteria = $argsObj->hitCriteria;
  $gui->actionOnHit = $argsObj->actionOnHit;  
  
  switch($gui->scope)
  {
    case 'tree':
      $gui->main_descr = sprintf(lang_get('tproject_import_req_spec'),$argsObj->tproject_name);
      $gui->importTypes = $reqSpecMgr->get_import_file_types();
    break;
      
    case 'branch':
      $gui->req_spec = $reqSpecMgr->get_by_id($argsObj->req_spec_id);
      $gui->main_descr = sprintf(lang_get('reqspec_import_req_spec'),$gui->req_spec['title']);
      $gui->importTypes = $reqSpecMgr->get_import_file_types();
    break;
      
    case 'items':
      $gui->req_spec = $reqSpecMgr->get_by_id($argsObj->req_spec_id);
      $gui->main_descr = sprintf(lang_get('reqspec_import_requirements'),$gui->req_spec['title']);
      $gui->importTypes = $reqMgr->get_import_file_types();
    break;
  }

  
  $gui->importType = $argsObj->importType;
  $gui->fileName = TL_TEMP_PATH . "importReq-" . session_id() . ".tmp";
    
  $gui->importFileGui = new stdClass();
  $gui->importFileGui->importTypes = $gui->importTypes;
  $gui->importFileGui->importType = $argsObj->importType;
    
  $file_size_limit = config_get('import_file_max_size_bytes');
  $gui->importFileGui->maxFileSize=round(strval($file_size_limit)/1024);
  $gui->importFileGui->fileSizeLimitMsg=sprintf(lang_get('max_file_size_is'), $gui->importFileGui->maxFileSize  . ' KB ');
    

  $gui->importFileGui->skip_frozen_req_checked = $argsObj->skip_frozen_req ? ' checked="checked" ' : '';
    
    
  $gui->importFileGui->return_to_url=$session['basehref'];
  if( is_null($argsObj->req_spec_id) )
  {
    $gui->importFileGui->return_to_url .= "lib/project/project_req_spec_mgmt.php?id=$argsObj->tproject_id";
  }
  else
  {
    $gui->importFileGui->return_to_url .= "lib/requirements/reqSpecView.php?req_spec_id=$argsObj->req_spec_id";
  } 
    
  $gui->actionOptions=array('update_last_version' => lang_get('update_last_requirement_version'),
                            'create_new_version' => lang_get('create_new_requirement_version'));
  
  $gui->hitOptions=array('docid' => lang_get('same_docid'),'title' => lang_get('same_title'));

  $gui->duplicate_criteria_verbose = lang_get('duplicate_req_criteria');

  return $gui;    
}


/**
 * 
 *
 */
function checkRights(&$db,&$user)
{
  return ($user->hasRight($db,'mgt_view_req') && $user->hasRight($db,'mgt_modify_req'));
}


/**
 * 
 *
 */
function doReqImportFromXML(&$reqSpecMgr,&$reqMgr,&$simpleXMLObj,$importContext,$importOptions)
{
  $items = array();
  $isReqSpec = property_exists($simpleXMLObj,'req_spec');
  if($isReqSpec)
  {
    foreach($simpleXMLObj->req_spec as $xkm)
    {
      $dummy = $reqSpecMgr->createFromXML($xkm,$importContext->tproject_id,$importContext->req_spec_id,
                        $importContext->user_id,null,$importOptions);
      $items = array_merge($items,$dummy);
    }
  }   
  else
  {
    $loop2do = count($simpleXMLObj->requirement);
    for($kdx=0; $kdx < $loop2do; $kdx++)
    {   
      $dummy = $reqMgr->createFromXML($simpleXMLObj->requirement[$kdx],$importContext->tproject_id,
                                        $importContext->req_spec_id,$importContext->user_id,null,$importOptions);
      $items = array_merge($items,$dummy);
    }
  }
    return $items;
}


/**
 * 
 *
 */
function doReqImportOther(&$reqMgr,$fileName,$importContext,$importOptions)
{
  $impSet = loadImportedReq($fileName, $importContext->importType);
  $items = array();
  
  if( !is_null($impSet) )
  { 
    $reqSet = $impSet['info'];
    if( ($loop2do=count($reqSet)) )
    {
      for($kdx=0; $kdx < $loop2do; $kdx++)
      {   
        $dummy = $reqMgr->createFromMap($reqSet[$kdx],$importContext->tproject_id,
                                        $importContext->req_spec_id,
                                        $importContext->user_id,null,$importOptions);
        $items = array_merge($items,$dummy);
      }
    }
  }
  return array('items' => $items, 'userFeedback' => $impSet['userFeedback']);
}