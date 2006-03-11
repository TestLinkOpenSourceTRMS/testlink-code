<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsView.php,v $
 *
 * @version $Revision: 1.12 $
 * @modified $Date: 2006/03/11 23:09:28 $ by $Author: schlundus $
 *
 * Purpose:  This page this allows users to view keywords. 
 *
 * 20050907 - scs - cosmetic changes
 * 20051216 - scs - put all keyword management into this script
 * 20060104 - fm  - using nl2br() for the notes
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("keywords.inc.php");
testlinkInitPage($db);

$_REQUEST = strings_stripSlashes($_REQUEST);
$keywordID = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
$bDeleteKey = isset($_REQUEST['deleteKey']) ? 1 : 0;
$keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : null;
$bNewKey = isset($_REQUEST['newKey']) ? 1 : 0;
$bEditKey = isset($_REQUEST['editKey']) ? 1 : 0;
$notes = isset($_REQUEST['notes']) ? $_REQUEST['notes'] : null;

$testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$bModifyKeywordRight = has_rights($db,"mgt_modify_key");

$tproject = new testproject($db);
$sqlResult = null;
$action = null;
//show the details of the keyword to edit
if ($keywordID && !$bEditKey && !$bDeleteKey)
{
	$info = getKeyword($db,$keywordID);
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
		$action = $bEditKey ? "updated" : "add";
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
$smarty->display('keywordsView.tpl');
?>