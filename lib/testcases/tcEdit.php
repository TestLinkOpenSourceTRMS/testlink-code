<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: tcEdit.php,v $
 *
 * @version $Revision: 1.9 $
 * @modified $Date: 2005/09/08 12:25:26 $  by $Author: franciscom $
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
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require('archive.inc.php');
require('../keywords/keywords.inc.php');
require_once("../../lib/functions/lang_api.php");

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

$tc = null;
$keySize = null;

if (isset($_REQUEST['editTC']))
{
	$tc = $_REQUEST['editTC'];
}	

$product = $_SESSION['productID'];
//$data = isset($_GET['data']) ? intval($_GET['data']) : 0;

// 20050827
$categoryID = isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 0;
$testcaseID = isset($_GET['testcaseID']) ? intval($_GET['testcaseID']) : 0;
$show_newTC_form = 0;


$smarty = new TLSmarty;

// 20050810 - fm
// from 3 to only 1 assignment
$smarty->assign('path_htmlarea', $_SESSION['basehref'] . 'third_party/htmlarea/');


// -------------------------------------------------------------------------------------------
// 20050908 - fm
$name_ok = 1;
if( isset($_POST['addTC']) || isset($_POST['updateTC']) )
{
	$title 		= isset($_POST['title']) ? strings_stripSlashes($_POST['title']) : null;
	$summary 	= isset($_POST['summary']) ? strings_stripSlashes($_POST['summary']) : null;
	$steps 		= isset($_POST['steps']) ? strings_stripSlashes($_POST['steps']) : null;
	$outcome 	= isset($_POST['exresult']) ? strings_stripSlashes($_POST['exresult']) : null;

  // BUGID 0000086
  $result = lang_get('warning_empty_tc_title');	
	if( $name_ok && !check_string($title,$g_ereg_forbidden) )
	{
		$msg = lang_get('string_contains_bad_chars');
		$name_ok = 0;
	}
	
  if( $name_ok && strlen($title) == 0)
  {
    $msg = lang_get('warning_empty_tc_title');
    $name_ok = 0;
  }
}
// -------------------------------------------------------------------------------------------





	
//If the user has chosen to edit a testcase then show this code
if($tc)
{
	$setOfKeys = array();
	
	// get TC data
	$myrowTC = getTestcase($testcaseID,false);

	$tcKeywords = null;
	getTCKeywords($testcaseID,$tcKeywords);
	$prodKeywords = null;
	getProductKeywords($_SESSION['productID'],$prodKeywords);
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

   global $g_tpl;
	$smarty->display($g_tpl['tcEdit']);
	
	
	//saving a test case but not archiving it
} 
else if(isset($_POST['updateTC']))
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
	
	tLog($_POST['version']);
	
	//everytime a test case is saved I update its version
	$version = isset($_POST['version']) ? intval($_POST['version']) : 0; 
	$version++;

  $sqlResult = $msg;
  if( $name_ok)
  {
   $sqlResult = 'ok';
   if (!updateTestcase($testcaseID,$title,$summary,$steps,$outcome,$_SESSION['user'],$updatedKeywords,$version))
   {
		$sqlResult =  mysql_error();
   }
    
  }	

	// show testcase
	// 20050820 - fm
	$allow_edit=1;
	showTestcase($testcaseID, $allow_edit);
}
else if(isset($_POST['newTC']))
{
	$show_newTC_form = 1;
}
else if(isset($_POST['addTC']))
{
	$show_newTC_form=1;
	 
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
else if(isset($_POST['deleteTC']))
{
	//check to see if the user said he was sure he wanted to delete
	if(isset($_GET['sure']) && $_GET['sure'] == 'yes') 
	{
		if (deleteTestcase($testcaseID))
			$smarty->assign('sqlResult', 'ok');
	   	else
			$smarty->assign('sqlResult', mysql_error());
	}
	$smarty->assign('testcaseID', $testcaseID);
	$smarty->display('tcDelete.tpl');
}
else if(isset($_POST['moveTC']))
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
else if(isset($_POST['updateTCmove']))
{
	$catID = isset($_POST['moveCopy']) ? intval($_POST['moveCopy']) : 0;
	$oldCat = isset($_POST['oldCat']) ? intval($_POST['oldCat']) : 0;
	
	$result = moveTc($catID, $testcaseID);
	showCategory($oldCat, $result);
}
else if(isset($_POST['updateTCcopy']))
{
	$catID = isset($_POST['moveCopy']) ? intval($_POST['moveCopy']) : 0;
	$oldCat = isset($_POST['oldCat']) ? intval($_POST['oldCat']) : 0;

  // 20050821 - fm - interface - reduce global coupling
	$result = copyTc($catID, $testcaseID, $_SESSION['user']);
	
	showCategory($oldCat, $result,'update',$catID);
}
else
{
	// ERROR
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