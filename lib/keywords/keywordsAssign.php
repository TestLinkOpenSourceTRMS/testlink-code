<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: keywordsAssign.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:55 $
 *
 * Purpose:  Assign keywords to set of testcases in tree structure
 *
 * @author Andreas Morsing - cosmetic code changes
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("keywords.inc.php");
require_once("../testcases/archive.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$id = isset($_GET['data']) ? intval($_GET['data']) : null;
$keysOfProduct = selectKeywords();
$keyword = isset($_POST['keywords']) ? strings_stripSlashes($_POST['keywords']) : null;
$edit = isset($_GET['edit']) ? strings_stripSlashes($_GET['edit']) : null;

$smarty = new TLSmarty();
$smarty->assign('data', $id);

//If the user has chosen to edit a product then show this code. 
if ($edit == 'product')
{
	redirect($_SESSION['basehref'] . $g_rpath['help'] . '/keywordsAssign.html');
	exit();
} //If the user has chosen to edit a component then show this code
else if ($edit == 'component')
{
	// execute update
	if(isset($_POST['assigncomponent'])) 
	{
		$result = updateComponentKeywords($id,$keyword);
		$smarty->assign('sqlResult', $result);
	}

	$componentData = getComponent($id);
	$smarty->assign('title', $componentData[1]);
	$smarty->assign('level', 'component');
}//If the user has chosen to edit a category then show this code
else if ($edit == 'category')
{
	// execute update
	if(isset($_POST['assigncategory'])) 
	{
		$result = updateCategoryKeywords($id,$keyword);
		$smarty->assign('sqlResult', $result);
	}

	$categoryData = getCategory($id);
	$smarty->assign('title', $categoryData[1]);
	$smarty->assign('level', 'category');
} //If the user has chosen to edit a testcase then show this code
else if($edit == 'testcase')
{
	// execute update
	if(isset($_POST['assigntestcase'])) 
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

	$smarty->assign('title', $tcData[1]);
	$smarty->assign('tcKeys', $tcData[6]);
	$smarty->assign('level', 'testcase');
}
else
{
	tlog("keywordsAssigns> Missing GET/POST arguments.");
	exit();
}

$smarty->assign('arrKeys', $keysOfProduct);
$smarty->display('keywordsAssign.tpl');
?>