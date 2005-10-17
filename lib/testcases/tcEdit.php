<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: tcEdit.php,v $
 *
 * @version $Revision: 1.13 $
 * @modified $Date: 2005/10/17 20:11:27 $  by $Author: schlundus $
 * This page manages all the editing of test cases.
 *
 * @author Martin Havlat
 *
 * @todo deactive users instead of delete
 * 
 * @author Francisco Mancardi - 20050827
 * BUGID 0000086
 *
 * @author Francisco Mancardi - 20050827
 * fckeditor
 *
 * @author Francisco Mancardi - 20050821
 * added missing control in tc title len
 * interface - reduce global coupling
 *
 * @author Francisco Mancardi - 20050810
 * refactoring, deprecated $_SESSION['product'] removed
 * 
 * 20051015 - am - moved some POST params to the top
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require('archive.inc.php');
require('../keywords/keywords.inc.php');
require_once("../../third_party/fckeditor/fckeditor.php");
testlinkInitPage();

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
$keySize = null;
$product = $_SESSION['productID'];
$show_newTC_form = 0;
$smarty = new TLSmarty;
// 20050810 - fm - from 3 to only 1 assignment
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
	
	$myrowTC = getTestcase($testcaseID,false);

	// 20051004 - fm - refactoring
	$tcKeywords = getTCKeywords($testcaseID);
	$prodKeywords = getProductKeywords($_SESSION['productID']);
	
	if (sizeof($prodKeywords))
	{
		$result = array_intersect($tcKeywords,$prodKeywords);
		for($i = 0;$i < sizeof($prodKeywords);$i++)
		{
			$selected = 'no';
			$keyword = $prodKeywords[$i];
			if (in_array($keyword,$result))
				$selected = 'yes';
			$setOfKeys[] = array( 'key' => $keyword, 'selected' => $selected);
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
	$smarty->assign('keysize', $keySize);

	$smarty->display($g_tpl['tcEdit']);
	//saving a test case but not archiving it
} 
else if($bUpdateTC)
{
	$updatedKeywords = null;

	// Since the keywords are being passed in as an array I need to seperate them 
	// into a comma separated string
	if(isset($_POST['keywords']) && count($_POST['keywords']) > 0)
	{ 	
		//if there actually are values passed in
		foreach($_POST['keywords'] as $bob)
			$updatedKeywords .= strings_stripSlashes($bob) . ","; //Build this string
	}
	
	
	//everytime a test case is saved I update its version
	tLog($_POST['version']);
	$version++;

	//20051008 - am - added message
	$sqlResult = lang_get('string_contains_bad_chars');
	if( $name_ok)
	{
		$sqlResult = 'ok';
		if (!updateTestcase($testcaseID,$title,$summary,$steps,$outcome,$_SESSION['user'],$updatedKeywords,$version))
		{
			$sqlResult =  mysql_error();
		}
	}	

	// 20050820 - fm - show testcase
	$allow_edit=1;
	showTestcase($testcaseID, $allow_edit);
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
		if (insertTestcase($categoryID,$title,$summary,$steps,$outcome,$_SESSION['user']))
		{
			$msg = 'ok';
		}
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
		if (deleteTestcase($testcaseID))
			$smarty->assign('sqlResult', 'ok');
	   	else
			$smarty->assign('sqlResult', mysql_error());
	}
	$smarty->assign('testcaseID', $testcaseID);
	$smarty->display('tcDelete.tpl');
}
else if($bMoveTC)
{
	$catID = 0;
	$compID = 0;
	$arrOptCategories = null;
	
	getTestCaseCategoryAndComponent($testcaseID,$catID,$compID);
	getOptionCategoriesOfComponent($compID, $arrOptCategories);
	$arrOptCategories[$catID] .= ' (' . lang_get('current') . ')'; 

	$tcTitle = getTestcaseTitle($testcaseID);

	$smarty->assign('oldCat', $catID); // original Category
	$smarty->assign('arrayCat', $arrOptCategories);
	$smarty->assign('testcaseID', $testcaseID);
	$smarty->assign('title', $tcTitle);
	$smarty->display('tcMove.tpl');

// move test case to another category
}
else if($bUpdateTCMove)
{
	$result = moveTc($catID, $testcaseID);
	showCategory($oldCat, $result);
}
else if($bUpdateTCCopy)
{
	// 20050821 - fm - interface - reduce global coupling
	$result = copyTc($catID, $testcaseID, $_SESSION['user']);
	showCategory($oldCat, $result,'update',$catID);
}
else
{
	tlog("A correct POST argument is not found.");
}
// --------------------------------------------------------------------------
if ($show_newTC_form)
{
	//Creating a new test case
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
	$smarty->display($g_tpl['tcNew']);
}
?>