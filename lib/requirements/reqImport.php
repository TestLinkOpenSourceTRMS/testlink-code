<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqImport.php,v $
 * @version $Revision: 1.22 $
 * @modified $Date: 2010/05/10 06:07:50 $ by $Author: franciscom $
 * @author Martin Havlat
 * 
 * Import ONLY requirements to a req specification. 
 * Supported: simple CSV, Doors CSV, XML, DocBook
 * 
 * 20100321 - franciscom - work on import child requirements XML format - not finished
 * 20081103 - sisajr - DocBook XML extension
 * 20080504 - franciscom - removed tmp file after import
 * 20061014 - franciscom - added check on file mime type
 *                         using check_valid_ftype()
 *
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

$args = init_args();
$gui = initializeGui($db,$args,$_SESSION,$req_spec_mgr);
$gui->importResult = null;
$gui->items = null;

switch($args->doAction)
{
    case 'uploadFile':
        $dummy = doUploadFile($db,$gui->fileName,$args,$req_spec_mgr);

        $gui->items = $dummy->items;
        $gui->items_qty = is_null($gui->items) ? 0 : count($gui->items);
        $gui->has_items = $gui->items_qty > 0;
        $gui->support_array = $gui->has_items ? array_keys($gui->items): array();  // do know is if really needed
        $gui->file_check = $dummy->file_check;
        if($gui->file_check['status_ok'] == 0)
        {
            $gui->doAction = 'askFileName';
        }
    break;
    
    case 'executeImport':
        $dummy = doExecuteImport($db,$gui->fileName,$args,$req_spec_mgr);
		$gui->items = $dummy->items;        
        $gui->importResult = lang_get('import_done');
    break;
}

// need to understand if has any sense
switch($args->scope)
{
	case 'tree':
		$req_spec = '';
	break;

	case 'branch':
	default:
		$req_spec = $req_spec_mgr->get_by_id($args->req_spec_id);
	break;
}

new dBug($gui->items);
$smarty = new TLSmarty;

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/**
 * doExecuteImport
 *
 */
function doExecuteImport(&$dbHandler,$fileName,&$argsObj,&$reqSpecMgr)
{
    $retval = new stdClass();
    $retval->items = null;
    $retval->msg = '';
    
	if($argsObj->importType == 'XML')
	{
		$xml = simplexml_load_file($fileName);
	    
	    // if achecked_req is null => user has not selected any requirement, anyway we are going to create reqspec tree
	    $filter['requirements'] = $argsObj->achecked_req;
	    $retval->items = array();
        foreach($xml->req_spec as $xkm)
    	{
    		$dummy = $reqSpecMgr->createFromXML($xkm,$argsObj->tproject_id,$argsObj->req_spec_id,$argsObj->user_id);
    		$retval->items = array_merge($retval->items,$dummy);
    	}
	}
	else
	{
	    $retval->items = doReqImport($dbHandler,$argsObj->tproject_id,$argsObj->user_id,$argsObj->req_spec_id,$fileName,
 	    				             $argsObj->importType,$argsObj->emptyScope,$argsObj->conflictSolution,true);
	}
	unlink($fileName);
	$retval->msg = lang_get('req_import_finished');
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
    $args->req_spec_id = isset($request['req_spec_id']) ? $request['req_spec_id'] : null;
    $args->importType = isset($request['importType']) ? $request['importType'] : null;
    $args->emptyScope = isset($request['noEmpty']) ? $request['noEmpty'] : null;
    $args->conflictSolution = isset($request['conflicts']) ? $request['conflicts'] : null;
    $args->bUpload = isset($request['uploadFile']) ? 1 : 0;
    
    // useRecursion: used when you want to work on test project or req. spec
    $args->useRecursion = isset($request['useRecursion']) ? 1 : 0;
    
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
    
    $args->achecked_req=isset($request['achecked_req']) ? $request['achecked_req'] : null;
    $args->tproject_id = $_SESSION['testprojectID'];
    $args->tproject_name = $_SESSION['testprojectName'];
    $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
   	$args->scope = isset($_REQUEST['scope']) ? $_REQUEST['scope'] : 'items';
    
    return $args;
}




/*
  function: 

  args :
  
  returns: 

*/
function check_valid_ftype($upload_info,$import_type)
{
	$ret = array();
	$ret['status_ok'] = 0;
	$ret['msg'] = 'ok';
	
	$mime_types = array();
	$import_type = strtoupper($import_type);
	
	$mime_types['check_ext'] = array('application/octet-stream' => 'csv');                        
	
	$mime_import_types['text/plain'] = array('CSV' => 'CSV', 'CSV_DOORS' => 'CSV_DOORS');
	$mime_import_types['application/octet-stream'] = array('CSV' => 'CSV');
	$mime_import_types['text/xml'] = array('XML' => 'XML');
	$mime_import_types['text/xml'] = array('DocBook' => 'XML');
	
	if(isset($mime_import_types[$upload_info['type']])) 
	{
		if(isset($mime_import_types[$upload_info['type']][$import_type]))
		{
			$ret['status_ok'] = 1;
			if(isset($mime_types['check_ext'][$upload_info['type']]))
			{
				$path_parts = pathinfo($upload_info['name']);
				if($path_parts['extension'] != $mime_types['check_ext'][$upload_info['type']])
				{
					$status_ok = 0;    
					$ret['msg'] = lang_get('file_is_not_text');
				}
			}
		}
		else
		{
			$ret['msg'] = lang_get('file_is_not_ok_for_import_type');
		}
	}
	else
	{
		$ret['msg'] = lang_get('file_is_not_text');
	}
	
	return $ret;
}

/**
 * initializeGui()
 * create object that will be used by Smarty template
 *
 */
function initializeGui(&$dbHandler,&$argsObj,$session,&$reqSpecMgr)
{
    $gui=new stdClass();
    $gui->file_check = array('status_ok' => 1, 'msg' => 'ok');
    $gui->items=null;
	$gui->try_upload = $argsObj->bUpload;

    $gui->doAction=$argsObj->doAction;
	$gui->scope = $argsObj->scope;
	$gui->req_spec = null;
	$gui->req_spec_id = $argsObj->req_spec_id;

	
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
   		    $reqMgr = new requirement_mgr($dbHandler);
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
    
    $gui->importFileGui->return_to_url=$session['basehref'];
    if( is_null($argsObj->req_spec_id) )
    {
        $gui->importFileGui->return_to_url .= "lib/project/project_req_spec_mgmt.php?id=$argsObj->tproject_id";
    }
    else
    {
        $gui->importFileGui->return_to_url .= "lib/requirements/reqSpecView.php?req_spec_id=$argsObj->req_spec_id";
    } 
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
 * doUploadFile
 *
 */
function doUploadFile(&$dbHandler,$fileName,&$argsObj,&$reqSpecMgr)
{
    $retval=new stdClass();
    $retval->items=null;
    $retval->file_check=array('status_ok' => 1, 'msg' => 'ok');
    
    $source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;
	if (($source != 'none') && ($source != '' ))
	{ 
		if($retval->file_check['status_ok'])
		{
            if (move_uploaded_file($source, $fileName))
			{
			    // Must be recoded - $file_check = check_syntax($gui->fileName,$args->importType);
			    if($retval->file_check['status_ok'])
			    {
			        if( strcasecmp($argsObj->importType,'XML') == 0 )
			        {
    	                $retval->file_check['status_ok']=!(($xml=simplexml_load_file($fileName)) === FALSE);
    	                if($retval->file_check['status_ok'])
    	                { 
    	                	
    	                	$retval->items = array();
    	                	
    	                	// we can have two types of files:
    	                	// 1. req. specs + requirements
    	                	// 2. just requirements
    	                	if( property_exists($xml,'req_spec') )
    	                	{ 
    	                		foreach($xml->req_spec as $xkm)
    	                		{
    	                			$retval->items = array_merge($retval->items,$reqSpecMgr->xmlToMapReqSpec($xkm));
    	                		}
    	                	}
    	                	else
    	                	{
   	                		    $reqMgr = new requirement_mgr($dbHandler);
    	                		$loop2do=count($xml->requirement);
        						for($zdx=0; $zdx <= $loop2do; $zdx++)
        						{
        						    $xml_req=$reqMgr->xmlToMapRequirement($xml->requirement[$zdx]);
        						    if(!is_null($xml_req))
        						    { 
        						        $retval->items[]=$xml_req;
        						    }    
        						}    
    	                	}
    	                	new dBug($retval->items);
	                    }
	                    else
	                    {
	                        $retval->file_check['msg']=lang_get('xml_load_file_failed');
	                    }    
		    	    }
                    else
					{
					    $retval->items = doReqImport($dbHandler,$argsObj->tproject_id,$argsObj->user_id,$argsObj->req_spec_id,
					    							 $fileName,$argsObj->importType,$argsObj->emptyScope,
 					   				                 $argsObj->conflictSolution,false);
					}
			   }
			}
		}
	}
	else
	{
		$retval->file_check=array('status_ok' => 0, 'msg' => lang_get('please_choose_req_file'));
	}	
    return $retval;    
}
?>