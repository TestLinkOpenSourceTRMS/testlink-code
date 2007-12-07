<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsView.php,v $
 *
 * @version $Revision: 1.18 $
 * @modified $Date: 2007/12/07 07:05:37 $ by $Author: franciscom $
 *
 * allows users to manage keywords. 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("keyword.class.php");

$template_dir='keywords/';

testlinkInitPage($db);

$_REQUEST = strings_stripSlashes($_REQUEST);
$keywordID = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
$bDeleteKey = isset($_REQUEST['deleteKey']) ? 1 : 0;
$keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : null;
$bNewKey = isset($_REQUEST['newKey']) ? 1 : 0;
$bEditKey = isset($_REQUEST['editKey']) ? 1 : 0;
$notes = isset($_REQUEST['notes']) ? $_REQUEST['notes'] : null;
$do_export = isset($_REQUEST['exportAll']) ? 1 : 0;
$exportType = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : null;

$testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$bModifyKeywordRight = has_rights($db,"mgt_modify_key");

$tproject = new testproject($db);
$msg = null;
$action = null;

//show the details of the keyword to edit
if ($keywordID && !$bEditKey && !$bDeleteKey)
{
	$info = $tproject->getKeyword($keywordID);
	if ($info)
	{
		$keyword = $info['keyword'];
		$notes = $info['notes'];
	}
}

if ($bModifyKeywordRight)
{
	//insert or update a keyword
	if ($bEditKey || $bNewKey)
	{
		if ($bNewKey)
			$result = $tproject->addKeyword($testproject_id,$keyword,$notes);
		else
			$result = $tproject->updateKeyword($testproject_id,$keyword,$notes,$keywordID);

		switch($result)
		{
			case tlKeyword::KW_E_NOTALLOWED:
				$msg = lang_get('keywords_char_not_allowed'); 
				break;
			case tlKeyword::KW_E_EMPTY:
				$msg = lang_get('empty_keyword_no');
				break;
			case tlKeyword::KW_E_DBERROR:
			case ERROR: 
				$msg = lang_get('kw_update_fails');
				break;
			case tlKeyword::KW_E_DUPLICATE:
				$msg = lang_get('keyword_already_exists');
				break;
			default:
				$msg = 'ok';
		}
		//reset info, after successful updating	
		$action = $bEditKey ? "updated" : "do_add";
	}
	//delete the keyword
	if ($bDeleteKey)
	{
		$msg = 'ok';
		if (!$tproject->deleteKeyword($keywordID))
			$msg = lang_get('kw_delete_fails'). ' : ' . $db->error_msg();
			
		$action = 'deleted';	
	}
	if ($action && $msg == 'ok')
		$notes = $keyword = $keywordID = null;
}
if($do_export)
{
	$pfn = null;
	switch($exportType)
	{
		case 'iSerializationToCSV':
			$pfn = "exportKeywordDataToCSV";
			$fileName = 'keywords.csv';
			break;
		case 'iSerializationToXML':
			$pfn = "exportKeywordDataToXML";
			$fileName = 'keywords.xml';
			break;
	}
	if ($pfn)
	{
		$content = $tproject->$pfn($testproject_id);
		downloadContentsToFile($content,$fileName);

		// why this exit() ?
		// If we don't use it, we will find in the exported file
		// the contents of the smarty template.
		exit();
	}
}
$tlKeyword = new tlKeyword();
$exportTypes = $tlKeyword->getSupportedSerializationInterfaces();
$keywords = $tproject->getKeywords($testproject_id);

$smarty = new TLSmarty();
$smarty->assign('action',$action);
$smarty->assign('sqlResult',$msg);
$smarty->assign('rightsKey',$bModifyKeywordRight);
$smarty->assign('arrKeywords', $keywords);
$smarty->assign('name',$keyword);
$smarty->assign('keyword',$keyword);
$smarty->assign('notes',$notes);
$smarty->assign('keywordID',$keywordID);
$smarty->assign('exportTypes',$exportTypes);
$smarty->display($template_dir . 'keywordsView.tpl');
?>
