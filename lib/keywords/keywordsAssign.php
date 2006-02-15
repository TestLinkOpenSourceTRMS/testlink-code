<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsAssign.php,v $
 *
 * @version $Revision: 1.10 $
 * @modified $Date: 2006/02/15 08:49:20 $
 *
 * Purpose:  Assign keywords to set of testcases in tree structure
 *
 * 20051011 - fm - refactoring $_REQUEST
 * 20050907 - scs - moved POST to the top, refactoring
 * 20051217 - scs - cosmetic changes
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("../testcases/archive.inc.php");
require_once("keywords.inc.php");
testlinkInitPage($db);

$_REQUEST = strings_stripSlashes($_REQUEST);
$id = isset($_REQUEST['data']) ? intval($_REQUEST['data']) : null;
$keyword = isset($_REQUEST['keywords']) ? $_REQUEST['keywords'] : null;
$edit = isset($_REQUEST['edit']) ? $_REQUEST['edit'] : null;
$bAssignComponent = isset($_REQUEST['assigncomponent']) ? 1 : 0;
$bAssignCategory = isset($_REQUEST['assigncategory']) ? 1 : 0;
$bAssignTestCase = isset($_REQUEST['assigntestcase']) ? 1 : 0;

$prodID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

$smarty = new TLSmarty();
$title = null;
$result = null;
$keysOfProduct = selectKeywords($db,$prodID);

if ($edit == 'product')
{
	redirect($_SESSION['basehref'] . $g_rpath['help'] . '/keywordsAssign.html');
	exit();
}
else if ($edit == 'component')
{
	if($bAssignComponent) 
		$result = updateComponentKeywords($db,$id,$keyword);

	$componentData = getComponent($db,$id);
	$title = $componentData['name'];
}
else if ($edit == 'category')
{
	if($bAssignCategory) 
		$result = updateCategoryKeywords($db,$id,$keyword);

	$categoryData = getCategory($db,$id);
	$title = $categoryData['name'];
}
else if($edit == 'testcase')
{
	if($bAssignTestCase) 
		$result = updateTCKeywords($db,$id,$keyword);

	$tcKeywords = null;
	$tcData = getTestcase($db,$id,false);
	if ($tcData['keywords'])
		$tcKeywords = explode(",",$tcData['keywords']);  

	//find actual keywords by select those productKeywords which are set in the TC
	if ($tcKeywords)
	{
		for($i = 0;$i < count($keysOfProduct);$i++)
		{
			$productKeyword = $keysOfProduct[$i]['keyword'];
			$sel = 0;
			if (in_array($productKeyword,$tcKeywords))
				$sel  = 1;
	
			$keysOfProduct[$i]['selected'] = $sel;	
		}
	}

	$title = $tcData['title'];
	$smarty->assign('tcKeys', $tcData['keywords']);
}
else
{
	tlog("keywordsAssigns> Missing GET/POST arguments.");
	exit();
}

$smarty->assign('sqlResult', $result);
$smarty->assign('data', $id);
$smarty->assign('level', $edit);
$smarty->assign('title',$title);
$smarty->assign('arrKeys', $keysOfProduct);
$smarty->display('keywordsAssign.tpl');
?>