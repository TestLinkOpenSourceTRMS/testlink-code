<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsView.php,v $
 *
 * @version $Revision: 1.15 $
 * @modified $Date: 2007/02/23 23:26:23 $ by $Author: schlundus $
 *
 * allows users to manage keywords. 
 *
 * 20061007 - franciscom - export logic moved here
 *
 *
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("../functions/csv.inc.php");
require_once("../functions/xml.inc.php");
require_once("keywords.inc.php");
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
$sqlResult = null;
$action = null;

//show the details of the keyword to edit
if ($keywordID && !$bEditKey && !$bDeleteKey)
{
	$info = $tproject->getKeywords($testproject_id,$keywordID);
	if ($info)
	{
		$keyword = $info[0]['keyword'];
		$notes = $info[0]['notes'];
	}
}

if ($bModifyKeywordRight)
{
	//insert or update a keyword
	if ($bEditKey || $bNewKey)
	{
		$sqlResult = checkKeywordName($keyword);
		if (is_null($sqlResult))
		{
			if ($bNewKey)
				$sqlResult = $tproject->addKeyword($testproject_id,$keyword,$notes);
			else
			{
				$check = $tproject->updateKeyword($testproject_id,$keywordID,$keyword,$notes);
				if ($check['status_ok'])
					$sqlResult = 'ok';
				else
		   			$sqlResult = lang_get('kw_update_fails') . ': ' . $check['msg'];
			}
		}
		//reset info, after successful updating	
		$action = $bEditKey ? "updated" : "do_add";
	}
	//delete the keyword
	if ($bDeleteKey)
	{
		$sqlResult = 'ok';
		if (!deleteKeyword($db,$keywordID))
			$sqlResult = lang_get('kw_delete_fails'). ' : ' . $db->error_msg();
			
		$action = 'deleted';	
	}
	if ($action && $sqlResult == 'ok')
		$notes = $keyword = $keywordID = null;
}

if($do_export)
{
	$tproject = new testproject($db);
	$keywords = $tproject->getKeywords($testproject_id);
	$pfn = null;

	switch(strtoupper($exportType))
	{
		case 'CSV':
 		  $pfn = "exportKeywordDataToCSV";
		  $fileName = 'keywords.csv';
			break;
			
		case 'XML':
			$pfn = "exportKeywordDataToXML";
			$fileName = 'keywords.xml';
			break;
	}
	if ($pfn)
	{
		$content = $pfn($keywords);
		downloadContentsToFile($content,$fileName);

		// why this exit() ?
		// If we don't use it, we will find in the exported file
		// the contents of the smarty template.
		exit();
	}
}

$keywords = $tproject->getKeywords($testproject_id);

$smarty = new TLSmarty();
$smarty->assign('action',$action);
$smarty->assign('sqlResult',$sqlResult);
$smarty->assign('rightsKey',$bModifyKeywordRight);
$smarty->assign('arrKeywords', $keywords);
$smarty->assign('name',$keyword);
$smarty->assign('keyword',$keyword);
$smarty->assign('notes',$notes);
$smarty->assign('keywordID',$keywordID);
$smarty->assign('exportTypes',$g_keywordExportTypes);

$smarty->display('keywordsView.tpl');
?>
