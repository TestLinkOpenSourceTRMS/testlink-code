<?
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: listTestCases.php,v 1.2 2005/08/16 18:00:59 franciscom Exp $
* 	@author 	Martin Havlat
* 
* 	This page generates tree menu with test specification. It builds the
*	javascript tree that allows the user to choose required container
*	or test case.
*
*////////////////////////////////////////////////////////////////////////////////
require('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
testlinkInitPage();

// set using data
$title = lang_get('title_navigator'). ' - ' . lang_get('title_test_spec'); //'Navigator - Test Specification';
$feature = isset($_GET['feature']) ? $_GET['feature'] : null;
if(strlen($feature))
{
	if ($feature == 'tcEdit')
	{
		$workPath = "lib/testcases/archiveData.php";
	} 
	else if ($feature == 'keywordsAssign') 
	{
		$workPath = "lib/keywords/keywordsAssign.php";
	}
	else if ($feature == 'assignReqs') 
	{
		$workPath = "lib/req/reqTcAssign.php";
	}
	else
	{
		tLog("Wrong get argument 'feature'.", 'ERROR');
		exit();
	}
}
else
{
	tLog("Missing argument 'feature'.", 'ERROR');
	exit();
}
// generate tree 
$treeString = generateTestSpecTree($workPath, 0);
$tree = null;
if (strlen($treeString))
	$tree = invokeMenu($treeString);
	
$smarty = new TLSmarty;
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('tree', $tree);
$smarty->assign('treeHeader', $title);
$smarty->assign('menuUrl',$workPath);
$smarty->display('tcTree.tpl');
?>
