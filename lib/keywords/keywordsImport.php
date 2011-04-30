<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Scope: Import keywords page
 *
 * @filesource	keywordsImport.php
 * @package 	TestLink
 * @copyright 	2005,2011 TestLink community 
 * @link 		http://www.teamst.org/index.php 
 *
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('csv.inc.php');
require_once('xml.inc.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$args = init_args($db);
checkRights($db,$_SESSION['currentUser'],$args);

$dest = TL_TEMP_PATH . session_id()."-importkeywords.".$args->importType;

$gui = new stdClass();
$gui->tproject_id = $args->tproject_id;
$gui->tproject_name = $args->tproject_name;

$gui->msg = getFileUploadErrorMessage($args->fInfo);

if(!$gui->msg && $args->UploadFile)
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
				$result = $tproject->$pfn($args->tproject_id,$dest);
				if ($result != tl::OK)
				{
					$gui->msg = lang_get('wrong_keywords_file'); 
				}
				else
				{
					header("Location: keywordsView.php?tproject_id=$gui->tproject_id");
					exit();		
				}
			}
			@unlink($dest);
		}
	} 
	else
	{
		$gui->msg = lang_get('please_choose_keywords_file');
	}	
}

$tlKeyword = new tlKeyword();

$gui->import_type_selected = $args->importType;
$gui->importTypes = $tlKeyword->getSupportedSerializationInterfaces();
$gui->keywordFormatStrings = $tlKeyword->getSupportedSerializationFormatDescriptions();			


$gui->importLimit = config_get('import_file_max_size_bytes');
$gui->fileSizeLimitMsg = sprintf(lang_get('max_file_size_is'), $gui->importLimit/1024 . ' KB ');

			
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * @return object returns the arguments for the page
 */
function init_args(&$dbHandler)
{
	$_REQUEST=strings_stripSlashes($_REQUEST);
	$args = new stdClass();

	$iParams = array("UploadFile" => array(tlInputParameter::STRING_N,0,1),
					 "importType" => array(tlInputParameter::STRING_N,0,100),
					 "tproject_id" => array(tlInputParameter::INT_N));
					 
		
	R_PARAMS($iParams,$args);

	$args->UploadFile = ($args->UploadFile != "") ? 1 : 0; 
	$args->fInfo = isset($_FILES['uploadedFile']) ? $_FILES['uploadedFile'] : null;
	$args->source = isset($args->fInfo['tmp_name']) ? $args->fInfo['tmp_name'] : null;

	if( $args->tproject_id > 0 )
	{
		$treeMgr = new tree($dbHandler);
		$dummy = $treeMgr->get_node_hierarchy_info($args->tproject_id);
		$args->tproject_name = $dummy['name'];
	}

	return $args;
}

/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('mgt_modify_key','mgt_view_key'),'and');
}
?>