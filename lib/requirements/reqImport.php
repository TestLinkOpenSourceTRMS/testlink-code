<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqImport.php,v $
 * @version $Revision: 1.5 $
 * @modified $Date: 2008/05/05 09:11:43 $ by $Author: franciscom $
 * @author Martin Havlat
 * 
 * Import requirements to a specification. 
 * Supported: simple CSV, Doors CSV, XML
 * 
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
require_once('requirement_spec_mgr.class.php');

testlinkInitPage($db);

$templateCfg = templateConfiguration();
$args = init_args();
$fileName = TL_TEMP_PATH . "importReq-" . session_id() . ".tmp";

$importResult = null;
$arrImport = null;
$file_check = array('status_ok' => 1, 'msg' => 'ok');

if ($args->bUpload)
{
	$source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;
	$arrImport = array();

	if (($source != 'none') && ($source != '' ))
	{ 
		// 20070904 - franciscom - this check is a failure :(
		$file_check['status_ok']=1;
		if($file_check['status_ok'])
		{
			if (move_uploaded_file($source, $fileName))
			{
			   $file_check = check_syntax($fileName,$args->importType);
			   if($file_check['status_ok'])
			   {
					 $arrImport = doImport($db,$args->user_id,$args->req_spec_id,$fileName,
											           $args->importType,$args->emptyScope,$args->conflictSolution,false);
			   }
			}
		}
	}
	else
		$file_check=array('status_ok' => 0, 'msg' => lang_get('please_choose_req_file'));
}
else if ($args->bExecuteImport)
{
	$arrImport = doImport($db,$args->user_id,$args->req_spec_id,$fileName,$args->importType,
	                      $args->emptyScope,$args->conflictSolution,true);
	unlink($fileName);
	$importResult = lang_get('req_import_finished');
}

$req_spec_mgr = new requirement_spec_mgr($db);
$req_spec = $req_spec_mgr->get_by_id($args->req_spec_id);
$import_types = $req_spec_mgr->get_import_file_types();

$smarty = new TLSmarty;
$smarty->assign('try_upload',$args->bUpload);
$smarty->assign('importType', $args->importType);
$smarty->assign('req_spec_id', $args->req_spec_id);

$smarty->assign('file_check',$file_check);  
$smarty->assign('reqFormatStrings',$g_reqFormatStrings);
$smarty->assign('importTypes',$import_types);
$smarty->assign('reqSpec', $req_spec);
$smarty->assign('arrImport', $arrImport);
$smarty->assign('importResult', $importResult);
$smarty->assign('uploadedFile', $fileName);
$smarty->assign('importLimit', TL_IMPORT_LIMIT);
$smarty->assign('importLimitKB', round(strval(TL_IMPORT_LIMIT) / 1024));
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);




/*
  function: 

  args :
  
  returns: 

*/
function init_args()
{
    $args = new stdClass();
    $request=strings_stripSlashes($_REQUEST);
      
    $args->req_spec_id = isset($request['req_spec_id']) ? $request['req_spec_id'] : null;
    $args->importType = isset($request['importType']) ? $request['importType'] : null;
    $args->emptyScope = isset($request['noEmpty']) ? $request['noEmpty'] : null;
    $args->conflictSolution = isset($request['conflicts']) ? $request['conflicts'] : null;
    $args->bUpload = isset($request['UploadFile']) ? 1 : 0;
    $args->bExecuteImport = isset($request['executeImport']);
    
    $args->tproject_id = $_SESSION['testprojectID'];
    $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
    
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
?>