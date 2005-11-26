<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: keywordsAssign.php,v $
 *
 * @version $Revision: 1.7 $
 * @modified $Date: 2005/11/26 13:27:25 $
 *
 * Purpose:  Assign keywords to set of testcases in tree structure
 *
 * @author Francisco Mancardi - 20051011 - refactoring $_REQUEST
 * @author Andreas Morsing - cosmetic code changes
 * 20050907 - scs - moved POST to the top, refactoring
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("keywords.inc.php");
require_once("../testcases/archive.inc.php");
testlinkInitPage();

$_REQUEST = strings_stripSlashes($_REQUEST);
$id = isset($_REQUEST['data']) ? intval($_REQUEST['data']) : null;
$keyword = isset($_REQUEST['keywords']) ? strings_stripSlashes($_REQUEST['keywords']) : null;
$edit = isset($_REQUEST['edit']) ? strings_stripSlashes($_REQUEST['edit']) : null;
$bAssignComponent = isset($_REQUEST['assigncomponent']) ? 1 : 0;
$bAssignCategory = isset($_REQUEST['assigncategory']) ? 1 : 0;
$bAssignTestCase = isset($_REQUEST['assigntestcase']) ? 1 : 0;
$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;

$keysOfProduct = selectKeywords($prodID);
$smarty = new TLSmarty();
$smarty->assign('data', $id);
$title = null;
$level = null;
if ($edit == 'product')
{
	redirect($_SESSION['basehref'] . $g_rpath['help'] . '/keywordsAssign.html');
	exit();
}
else if ($edit == 'component')
{
	if($bAssignComponent) 
	{
		$result = updateComponentKeywords($id,$keyword);
		$smarty->assign('sqlResult', $result);
	}
	$componentData = getComponent($id);
	$title = $componentData['name'];
	$level = 'component';
}
else if ($edit == 'category')
{
	if($bAssignCategory) 
	{
		$result = updateCategoryKeywords($id,$keyword);
		$smarty->assign('sqlResult', $result);
	}
	$categoryData = getCategory($id);
	$title = $categoryData['name'];
	$level = 'category';
}
else if($edit == 'testcase')
{
	if($bAssignTestCase) 
	{
		$result = updateTCKeywords($id,$keyword);
		$smarty->assign('sqlResult', $result);
	}
	$DO_NOT_CONVERT=false;
	$tcData = getTestcase($id,$DO_NOT_CONVERT);
	$tcKeywords = null;
	if ($tcData['keywords'])
	{
		$tcKeywords = explode(",",$tcData['keywords']);  
	}
  
	//find actual keywords
	for($i = 0;$i < count($keysOfProduct);$i++)
	{
		$productKeyword = $keysOfProduct[$i]['keyword'];
		$sel = 'no';
		if ($tcKeywords && in_array($productKeyword,$tcKeywords))
		{
			$sel  = 'yes';
		}	
		$keysOfProduct[$i]['selected'] = $sel;	
	}

	$title = $tcData['title'];
	$level = 'testcase';
	$smarty->assign('tcKeys', $tcData['keywords']);
}
else
{
	tlog("keywordsAssigns> Missing GET/POST arguments.");
	exit();
}

$smarty->assign('level', $level);
$smarty->assign('title',$title);
$smarty->assign('arrKeys', $keysOfProduct);
$smarty->display('keywordsAssign.tpl');
?>