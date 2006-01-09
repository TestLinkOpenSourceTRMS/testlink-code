<?
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: listTestCases.php,v 1.7 2006/01/09 08:32:36 franciscom Exp $
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
testlinkInitPage($db);

$feature = isset($_GET['feature']) ? $_GET['feature'] : null;

$prodID   = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;
$prodName = isset($_SESSION['productName']) ? $_SESSION['productName'] : 'xxx';

$title = lang_get('title_navigator'). ' - ' . lang_get('title_test_spec');
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

$treeString = generateTestSpecTree($db,$prodID, $prodName,$workPath, 0);
$tree = null;
if (strlen($treeString))
	$tree = invokeMenu($treeString);
	
$smarty = new TLSmarty();
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('tree', $tree);
$smarty->assign('treeHeader', $title);
$smarty->assign('menuUrl',$workPath);
$smarty->display('tcTree.tpl');
?>
