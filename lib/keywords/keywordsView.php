<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsView.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2006/01/04 09:43:56 $ by $Author: franciscom $
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

$_REQUEST = strings_stripSlashes($_REQUEST);
$keywordID = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
$bDeleteKey = isset($_REQUEST['deleteKey']) ? 1 : 0;
$keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : null;
$bNewKey = isset($_REQUEST['newKey']) ? 1 : 0;
$bEditKey = isset($_REQUEST['editKey']) ? 1 : 0;
$notes = isset($_REQUEST['notes']) ? $_REQUEST['notes'] : null;


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

// 20060103 - fm
$my_kw_array = selectKeywords($db,$prodID);
$num_kw = count($my_kw_array);
for($idx=0; $idx <= $num_kw; $idx++)
{
  $my_kw_array[$idx]['notes'] = nl2br(htmlspecialchars($my_kw_array[$idx]['notes']));
}

$smarty = new TLSmarty();
$smarty->assign('action',$action);
$smarty->assign('sqlResult',$sqlResult);
$smarty->assign('rightsKey',$bModifyKeywordRight);
$smarty->assign('arrKeywords', $my_kw_array);
$smarty->assign('name',$keyword);
$smarty->assign('keyword',$keyword);
$smarty->assign('notes',$notes);
$smarty->assign('keywordID',$keywordID);
$smarty->display('keywordsView.tpl');
?>