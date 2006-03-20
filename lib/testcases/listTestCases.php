<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: listTestCases.php,v 1.12 2006/03/20 18:02:37 franciscom Exp $
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

$tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'xxx';

$title = lang_get('title_navigator'). ' - ' . lang_get('title_test_spec');
if(strlen($feature))
{
	if ($feature == 'edit_tc')
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

$treeString = generateTestSpecTree($db,$tproject_id, $tproject_name,$workPath, 0);
$tree = null;
if (strlen($treeString))
{
	$tree = invokeMenu($treeString);
}
	
$smarty = new TLSmarty();
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('tree', $tree);
$smarty->assign('treeHeader', $title);
$smarty->assign('menuUrl',$workPath);
$smarty->display('tcTree.tpl');
?>
