<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Scope: Import keywords page
 *
 * Filename $RCSfile: keywordsImport.php,v $
 * @version $Revision: 1.11 $
 * @modified $Date: 2010/02/14 17:33:58 $ by $Author: franciscom $
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('csv.inc.php');
require_once('xml.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$args = init_args();

$dest = TL_TEMP_PATH . session_id()."-importkeywords.".$args->importType;

$msg = getFileUploadErrorMessage($args->fInfo);
if(!$msg && $args->UploadFile)
{
	if(($args->source != 'none') && ($args->source != ''))
	{ 
		if (move_uploaded_file($args->source, $dest))
		{
			$pfn = null;
			switch($args->importType)
			{
				case 'iSerializationToCSV':
					$pfn = "importKeywordsFromCSV";
					break;
				case 'iSerializationToXML':
					$pfn = "importKeywordsFromXMLFile";
					break;
			}
			if($pfn)
			{
				$tproject = new testproject($db);
				$result = $tproject->$pfn($args->testproject_id,$dest);
				if ($result != tl::OK)
					$msg = lang_get('wrong_keywords_file'); 
				else
				{
					header("Location: keywordsView.php");
					exit();		
				}
			}
			@unlink($dest);
		}
	} 
	else
		$msg = lang_get('please_choose_keywords_file');
}

$tlKeyword = new tlKeyword();
$importTypes = $tlKeyword->getSupportedSerializationInterfaces();
$formatStrings = $tlKeyword->getSupportedSerializationFormatDescriptions();
			
			
$file_size_limit = config_get('import_file_max_size_bytes');
			
$smarty = new TLSmarty();
$smarty->assign('import_type_selected',$args->importType);
$smarty->assign('msg',$msg);  
$smarty->assign('keywordFormatStrings',$formatStrings);
$smarty->assign('importTypes',$importTypes);
$smarty->assign('tproject_name', $args->testproject_name);
$smarty->assign('tproject_id', $args->testproject_id);
$smarty->assign('fileSizeLimitMsg',sprintf(lang_get('max_file_size_is'), $file_size_limit/1024 . ' KB '));
$smarty->assign('importLimit',$file_size_limit);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * @return object returns the arguments for the page
 */
function init_args()
{
	$iParams = array(
			"UploadFile" => array(tlInputParameter::STRING_N,0,1),
			"importType" => array(tlInputParameter::STRING_N,0,100),
		);
	$args = new stdClass();
		
	P_PARAMS($iParams,$args);

	$args->UploadFile = ($args->UploadFile != "") ? 1 : 0; 
	
	$args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->testproject_name = $_SESSION['testprojectName'];

	$args->fInfo = isset($_FILES['uploadedFile']) ? $_FILES['uploadedFile'] : null;
	$args->source = isset($args->fInfo['tmp_name']) ? $args->fInfo['tmp_name'] : null;

	return $args;
}

/**
 * @param $db resource the database connection handle
 * @param $user the current active user
 * 
 * @return boolean returns true if the page can be accessed
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_modify_key') && $user->hasRight($db,'mgt_modify_key');
}
?>