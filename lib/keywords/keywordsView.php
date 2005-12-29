<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsView.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2005/12/29 20:59:00 $ by $Author: schlundus $
 *
 * Purpose:  This page this allows users to view keywords. 
 *
 * 20050907 - scs - cosmetic changes
 * 20051216 - scs - put all keyword management into this script
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("keywords.inc.php");
testlinkInitPage();

$_POST = strings_stripSlashes($_POST);
$keywordID = isset($_GET['id']) ? $_GET['id'] : null;
$bDeleteKey = isset($_GET['deleteKey']) ? 1 : 0;
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
$bNewKey = isset($_POST['newKey']) ? 1 : 0;
$bEditKey = isset($_POST['editKey']) ? 1 : 0;
$notes = isset($_POST['notes']) ? $_POST['notes'] : null;
//when editing a keyword, the keywordID is sent via POST!
if (is_null($keywordID) && $bEditKey)
	$keywordID = isset($_POST['keywordID']) ? $_POST['keywordID'] : null;

$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;
$bModifyKeywordRight = has_rights("mgt_modify_key");

$sqlResult = null;
$action = null;
//show the details of the keyword to edit
if ($keywordID && !$bEditKey && !$bDeleteKey)
{
	$info = selectKeywords($db,$prodID,null,$keywordID);
	if (sizeof($info))
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
		$sqlResult = checkKeyword($keyword);
		if (is_null($sqlResult))
		{
			if ($bNewKey)
				$sqlResult = addNewKeyword($db,$prodID,$keyword,$notes);
			else
			{
				$check = updateKeyword($db,$prodID,$keywordID,$keyword,$notes);
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

$smarty = new TLSmarty();
$smarty->assign('action',$action);
$smarty->assign('sqlResult',$sqlResult);
$smarty->assign('rightsKey',$bModifyKeywordRight);
$smarty->assign('arrKeywords', selectKeywords($db,$prodID));
$smarty->assign('name',$keyword);
$smarty->assign('keyword',$keyword);
$smarty->assign('notes',$notes);
$smarty->assign('keywordID',$keywordID);
$smarty->display('keywordsView.tpl');
?>