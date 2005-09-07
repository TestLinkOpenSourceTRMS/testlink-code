<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: keywordsAssign.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2005/09/07 20:19:25 $
 *
 * Purpose:  Assign keywords to set of testcases in tree structure
 *
 * @author Andreas Morsing - cosmetic code changes
 * 20050907 - scs - moved POST to the top, refactoring
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("keywords.inc.php");
require_once("../testcases/archive.inc.php");
testlinkInitPage();

$_POST = strings_stripSlashes($_POST);
$_GET = strings_stripSlashes($_GET);
$id = isset($_GET['data']) ? intval($_GET['data']) : null;
$keyword = isset($_POST['keywords']) ? strings_stripSlashes($_POST['keywords']) : null;
$edit = isset($_GET['edit']) ? strings_stripSlashes($_GET['edit']) : null;
$bAssignComponent = isset($_POST['assigncomponent']) ? 1 : 0;
$bAssignCategory = isset($_POST['assigncategory']) ? 1 : 0;
$bAssignTestCase = isset($_POST['assigntestcase']) ? 1 : 0;

// 20050905 - fm
$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;
$keysOfProduct = selectKeywords($prodID);

$smarty = new TLSmarty();
$smarty->assign('data', $id);
$title = null;
$level = null;
//If the user has chosen to edit a product then show this code. 
if ($edit == 'product')
{
	redirect($_SESSION['basehref'] . $g_rpath['help'] . '/keywordsAssign.html');
	exit();
} //If the user has chosen to edit a component then show this code
else if ($edit == 'component')
{
	// execute update
	if($bAssignComponent) 
	{
		$result = updateComponentKeywords($id,$keyword);
		$smarty->assign('sqlResult', $result);
	}

	$componentData = getComponent($id);
	$title = $componentData[1];
	$level = 'component';
}//If the user has chosen to edit a category then show this code
else if ($edit == 'category')
{
	// execute update
	if($bAssignCategory) 
	{
		$result = updateCategoryKeywords($id,$keyword);
		$smarty->assign('sqlResult', $result);
	}

	$categoryData = getCategory($id);
	$title = $categoryData[1];
	$level = 'category';
} //If the user has chosen to edit a testcase then show this code
else if($edit == 'testcase')
{
	if($bAssignTestCase) 
	{
		$result = updateTCKeywords($id,$keyword);
		$smarty->assign('sqlResult', $result);
	}

	// collect data
	$tcData = getTestcase($id,false);
	$tcKeywords = null;
	if ($tcData[6])
		$tcKeywords = explode(",",$tcData[6]);  

	//find actual keywords
	for($i = 0;$i < count($keysOfProduct);$i++)
	{
		$productKeyword = $keysOfProduct[$i]['keyword'];
		$sel = 'no';
		if ($tcKeywords && in_array($productKeyword,$tcKeywords))
			$sel  = 'yes';
		$keysOfProduct[$i]['selected'] = $sel;	
	}

	$title = $tcData[1];
	$level = 'testcase';
	$smarty->assign('tcKeys', $tcData[6]);
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