<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: tcEdit.php,v $
 *
 * @version $Revision: 1.18 $
 * @modified $Date: 2006/01/09 08:41:09 $  by $Author: franciscom $
 * This page manages all the editing of test cases.
 *
 * @author Martin Havlat
 *
 * 20050827 - fm - BUGID 0000086
 * 20050827 - fm - fckeditor
 * 20050821 - fm - added missing control in tc title len interface - reduce global coupling
 * 20050810 - fm - refactoring, deprecated $_SESSION['product'] removed
 * 20051015 - scs - moved some POST params to the top
 * 20060106 - scs - refactoring, fixed bug 9
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require('archive.inc.php');
require('../keywords/keywords.inc.php');
require_once("../../third_party/fckeditor/fckeditor.php");
testlinkInitPage($db);

// set variables
// --------------------------------------------------------------------
// create  fckedit objects
$a_ofck = array('summary','steps','exresult');
$oFCK = array();
foreach ($a_ofck as $key)
{
	$oFCK[$key] = new fckeditor($key) ;
	$of = &$oFCK[$key];
	$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
	$of->ToolbarSet=$g_fckeditor_toolbar;;
}
// --------------------------------------------------------------------
$productID = $_SESSION['productID'];
$show_newTC_form = 0;
$smarty = new TLSmarty;
$smarty->assign('path_htmlarea', $_SESSION['basehref'] . 'third_party/htmlarea/');

$tc = isset($_REQUEST['editTC']) ? $_REQUEST['editTC'] : null;
$categoryID = isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 0;
$testcaseID = isset($_GET['testcaseID']) ? intval($_GET['testcaseID']) : 0;
$title 		= isset($_POST['title']) ? strings_stripSlashes($_POST['title']) : null;
$summary 	= isset($_POST['summary']) ? strings_stripSlashes($_POST['summary']) : null;
$steps 		= isset($_POST['steps']) ? strings_stripSlashes($_POST['steps']) : null;
$outcome 	= isset($_POST['exresult']) ? strings_stripSlashes($_POST['exresult']) : null;
$catID = isset($_POST['moveCopy']) ? intval($_POST['moveCopy']) : 0;
$oldCat = isset($_POST['oldCat']) ? intval($_POST['oldCat']) : 0;
$bAddTC = isset($_POST['addTC']) ? 1 : 0;
$bUpdateTC = isset($_POST['updateTC']) ? 1 : 0;
$bNewTC = isset($_POST['newTC']) ? 1 : 0;
$bDeleteTC = isset($_POST['deleteTC']) ? 1 : 0;
$version = isset($_POST['version']) ? intval($_POST['version']) : 0; 
$bSure = (isset($_GET['sure']) && $_GET['sure'] == 'yes');
$bMoveTC = isset($_POST['moveTC']) ? 1 : 0;
$bUpdateTCMove = isset($_POST['updateTCmove']) ? 1 : 0;
$bUpdateTCCopy = isset($_POST['updateTCcopy']) ? 1 : 0;
$user = $_SESSION['user'];

$updatedKeywords = null;
if (isset($_POST['keywords']))
	$updatedKeywords = strings_stripSlashes(implode(",",$_POST['keywords']).",");

$name_ok = 1;
if($bAddTC || $bUpdateTC)
{
	// BUGID 0000086
	$result = lang_get('warning_empty_tc_title');	
	if($name_ok && !check_string($title,$g_ereg_forbidden) )
	{
		$msg = lang_get('string_contains_bad_chars');
		$name_ok = 0;
	}
	if($name_ok && strlen($title) == 0)
	{
		$msg = lang_get('warning_empty_tc_title');
		$name_ok = 0;
	}
}
//If the user has chosen to edit a testcase then show this code
if($tc)
{
	$setOfKeys = array();
	
	$myrowTC = getTestcase($db,$testcaseID,false);
	$tcKeywords = getTCKeywords($db,$testcaseID);
	$prodKeywords = getProductKeywords($db,$productID);
	if (sizeof($prodKeywords))
	{
		if (sizeof($tcKeywords))
			$result = array_intersect($tcKeywords,$prodKeywords);
		else
			$result = array();
			
		for($i = 0;$i < sizeof($prodKeywords);$i++)
		{
			$selected = 'no';
			$keyword = $prodKeywords[$i];
			if (in_array($keyword,$result))
				$selected = 'yes';
			$setOfKeys[] = array( 'key' => $keyword, 
								  'selected' => $selected);
		}
	}

	foreach ($a_ofck as $key)
  	{
	  	// Warning:
	  	// the data assignment will work while the keys in $the_data are identical
	  	// to the keys used on $oFCK.
	  	$of = &$oFCK[$key];
	  	$of->Value = $myrowTC[$key];
	  	$smarty->assign($key, $of->CreateHTML());
	}

	$smarty->assign('tc', $myrowTC);
	$smarty->assign('testcaseID', $testcaseID);
	$smarty->assign('keys', $setOfKeys);

	$smarty->display($g_tpl['tcEdit']);
} 
else if($bUpdateTC)
{
	//everytime a test case is saved I update its version
	$version++;
	//20051008 - scs - added message
	$sqlResult = lang_get('string_contains_bad_chars');
	if( $name_ok)
	{
		$sqlResult = 'ok';
		// 20060108 - fm - user->user id 
		if (!updateTestcase($db,$testcaseID,$title,$summary,$steps,
		                    $outcome,$_SESSION['userID'],$updatedKeywords,$version))
		{
			$sqlResult =  $db->error_msg();
		}
	}	
	// 20050820 - fm - show testcase
	$allow_edit = 1;
	showTestcase($db,$testcaseID, $allow_edit);
}
else if($bNewTC)
{
	$show_newTC_form = 1;
}
else if($bAddTC)
{
	$show_newTC_form = 1;
	
	if ($name_ok)
	{
		$msg = lang_get('error_tc_add');
		if (insertTestcase($db,$categoryID,$title,$summary,$steps,$outcome,$_SESSION['userID'],null,$updatedKeywords))
			$msg = 'ok';
	}
  
	$smarty->assign('sqlResult', $msg);
	$smarty->assign('name', $title);
	$smarty->assign('item', 'Test case');
}
else if($bDeleteTC)
{
	//check to see if the user said he was sure he wanted to delete
	if($bSure) 
	{
		if (deleteTestcase($db,$testcaseID))
			$smarty->assign('sqlResult', 'ok');
	   	else
			$smarty->assign('sqlResult', $db->error_msg());
	}
	$smarty->assign('testcaseID', $testcaseID);
	$smarty->display('tcDelete.tpl');
}
else if($bMoveTC)
{
	$catID = 0;
	$compID = 0;
	$arrOptCategories = null;
	
	getTestCaseCategoryAndComponent($db,$testcaseID,$catID,$compID);
	getOptionCategoriesOfComponent($db,$compID, $arrOptCategories);
	$arrOptCategories[$catID] .= ' (' . lang_get('current') . ')'; 

	$tcTitle = getTestcaseTitle($db,$testcaseID);

	$smarty->assign('oldCat', $catID); // original Category
	$smarty->assign('arrayCat', $arrOptCategories);
	$smarty->assign('testcaseID', $testcaseID);
	$smarty->assign('title', $tcTitle);
	$smarty->display('tcMove.tpl');

// move test case to another category
}
else if($bUpdateTCMove)
{
	$result = moveTc($db,$catID, $testcaseID);
	showCategory($db,$oldCat, $result);
}
else if($bUpdateTCCopy)
{
	$result = copyTc($db,$catID, $testcaseID, $_SESSION['userID']);
	showCategory($db,$oldCat, $result,'update',$catID);
}
else
{
	tlog("A correct POST argument is not found.");
}
// --------------------------------------------------------------------------
if ($show_newTC_form)
{
	$smarty->assign('categoryID', $categoryID);
	
	foreach ($a_ofck as $key)
	{
		// Warning:
		// the data assignment will work while the keys in $the_data are identical
		// to the keys used on $oFCK.
		$of = &$oFCK[$key];
		$of->Value = "";
		$smarty->assign($key, $of->CreateHTML());
	}

	$prodKeywords = getProductKeywords($db,$productID);
	$smarty->assign('keys',$prodKeywords);
	$smarty->display($g_tpl['tcNew']);
}
?>