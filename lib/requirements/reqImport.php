<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqImport.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/11/19 21:02:56 $ by $Author: franciscom $
 * @author Martin Havlat
 * 
 * Import requirements to a specification. 
 * Supported: simple CSV, Doors CSV, XML
 * 
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

$idSRS = isset($_REQUEST['idSRS']) ? strings_stripSlashes($_REQUEST['idSRS']) : null;
$importType = isset($_REQUEST['importType']) ? strings_stripSlashes($_REQUEST['importType']) : null;
$emptyScope = isset($_REQUEST['noEmpty']) ? strings_stripSlashes($_REQUEST['noEmpty']) : null;
$conflictSolution = isset($_REQUEST['conflicts']) ? strings_stripSlashes($_REQUEST['conflicts']) : null;
$bUpload = isset($_REQUEST['UploadFile']) ? 1 : 0;
$bExecuteImport = isset($_POST['executeImport']);

$tprojectID = $_SESSION['testprojectID'];
$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;

$fileName = TL_TEMP_PATH . "importReq-".session_id().".csv";

$tproject = new testproject($db);
$importResult = null;
$arrImport = null;
$file_check=array('status_ok' => 1, 'msg' => 'ok');

if ($bUpload)
{
	$source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;
	$arrImport = array();

	if (($source != 'none') && ($source != '' ))
	{ 
	  // 20070904 - franciscom - this check is a failure :(
	  // $file_check=check_valid_ftype($_FILES['uploadedFile'],$importType);
    $file_check['status_ok']=1;
    if( $file_check['status_ok'] )
    {
  		if (move_uploaded_file($source, $fileName))
  		{
  		   $file_check = check_syntax($fileName,$importType);
  		   if($file_check['status_ok'])
  		   {
  			     $arrImport = doImport($db,$userID,$idSRS,$fileName,
			                             $importType,$emptyScope,$conflictSolution,false);
			   }
			}
		}
	}
  else
  {
    $file_check=array('status_ok' => 0, 'msg' => lang_get('please_choose_req_file'));
  }	
}
else if ($bExecuteImport)
{
	$arrImport = doImport($db,$userID,$idSRS,$fileName,$importType,$emptyScope,$conflictSolution,true);
	$importResult = lang_get('req_import_finished');
}

$arrSpec = $tproject->getReqSpec($tprojectID,$idSRS);

$req_spec_mgr = new requirement_spec_mgr($db);
$import_types=$req_spec_mgr->get_import_file_types();

$smarty = new TLSmarty;



$smarty->assign('file_check',$file_check);  
$smarty->assign('try_upload',$bUpload);
$smarty->assign('reqFormatStrings',$g_reqFormatStrings);
$smarty->assign('importTypes',$import_types);
$smarty->assign('reqSpec', $arrSpec[0]);
$smarty->assign('arrImport', $arrImport);
$smarty->assign('importResult', $importResult);
$smarty->assign('importType', $importType);
$smarty->assign('uploadedFile', $fileName);
$smarty->assign('importLimit', TL_IMPORT_LIMIT);
$smarty->assign('importLimitKB', round(strval(TL_IMPORT_LIMIT) / 1024));
$smarty->display('reqImport.tpl');

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