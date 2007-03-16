<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Scope: Import keywords page
 *
 * Filename $RCSfile: keywordsimport.php,v $
 * @version $Revision: 1.11 $
 * @modified $Date: 2007/03/16 20:09:48 $ by $Author: schlundus $
 *
 * Revisions:
 * 20070210 - franciscom - added checks: user has choosen a file
 *                                       the file format seems ok
 *
 * 20070102 - MHT - Fixed typo error, updated header
 * 
 */
require('../../config.inc.php');
require_once('common.php');
require_once('import.inc.php');
require_once('csv.inc.php');
require_once('xml.inc.php');
testlinkInitPage($db);

$source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;
$bUpload = isset($_REQUEST['UploadFile']) ? 1 : 0;

$importType = isset($_POST['importType']) ? $_POST['importType'] : null;
$location = isset($_POST['location']) ? strings_stripSlashes($_POST['location']) : null; 

$testproject_id = $_SESSION['testprojectID'];
$tproject_name = $_SESSION['testprojectName'];
$dest = TL_TEMP_PATH . session_id()."-importkeywords.".$importType;

$file_check = array('status_ok' => 1, 'msg' => 'ok');

// check the uploaded file
if( $bUpload )
{
	if (($source != 'none') && ($source != ''))
	{ 
		$file_check = check_valid_ftype($_FILES['uploadedFile'],$importType);
		if($file_check['status_ok'])
		{
			// store the file
			if (move_uploaded_file($source, $dest))
			{
				switch($importType)
				{
					case 'CSV':
						$pfn = "importKeywordDataFromCSV";
						break;
					case 'XML':
						$pcheck_fn  = "check_xml_keywords";
						$pfn = "importKeywordDataFromXML";
						break;
				}
				// optional "light" format check 
				if ($pcheck_fn)
				{
					$file_check = $pcheck_fn($dest);
				}
				if($file_check['status_ok'] && $pfn)
				{
					$keywordData = $pfn($dest);
					$tproject = new testproject($db);
					$sqlResult = $tproject->addKeywords($testproject_id,$keywordData);
					header("Location: keywordsView.php");
					exit();		
				}
			} // move_uploaded_file
		} // file_check
  } 
  else
  {
		$file_check = array('status_ok' => 0, 'msg' => lang_get('please_choose_keywords_file'));
  }	
} // $bUpload

			
$smarty = new TLSmarty();

$smarty->assign('import_type_selected',$importType);
$smarty->assign('file_check',$file_check);  
$smarty->assign('keywordFormatStrings',$g_keywordFormatStrings);
$smarty->assign('importTypes',$g_keywordImportTypes);
$smarty->assign('tproject_name', $tproject_name);
$smarty->assign('tproject_id', $testproject_id);
$smarty->assign('importLimitKB',TL_IMPORT_LIMIT / 1024);
$smarty->display('keywordsimport.tpl');

function check_valid_ftype($upload_info,$import_type)
{
	$ret = array();
	$ret['status_ok'] = 0;
	$ret['msg'] = lang_get('file_is_not_ok_for_import_type');
	
	$mime_import_types = null;      
	$mime_import_types['text/plain'] = array('CSV' => 'csv');
	$mime_import_types['application/octet-stream'] = array('CSV' => 'csv');
	$mime_import_types['text/xml']= array('XML' => 'xml');

	$uploadType = $upload_info['type']; 
	$ext = isset($mime_import_types[$uploadType][$import_type]) ? $mime_import_types[$uploadType][$import_type] : null;
	
	if(!is_null($ext))
	{
		$path_parts = pathinfo($upload_info['name']);
		if(strtolower($path_parts['extension']) == $ext)
		{
			$ret['status_ok'] = 1;
			$ret['msg'] = 'ok';
		}
	}
	return $ret;
}
/*
  function: 

           Check if at least the file start seems OK

*/
function check_xml_keywords($fileName)
{
	$file_check = array('status_ok' => 0, 'msg' => 'dom_ko');    		  

	$dom = domxml_open_file($fileName);
	if ($dom)
	{
		$file_check = array('status_ok' => 1, 'msg' => 'ok');    		  
		$root = $dom->document_element();
		if($root->tagname != 'keywords')
		{
			$file_check = array('status_ok' => 0, 'msg' => lang_get('wrong_xml_keywords_file'));
		}
	}
	return $file_check;
}
?>
	
