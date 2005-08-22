<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: tcEdit.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2005/08/22 07:35:30 $  by $Author: franciscom $
 * This page manages all the editing of test cases.
 *
 * @author Martin Havlat
 *
 * @todo deactive users instead of delete
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
testlinkInitPage();

// set variables
$tc = null;
$keySize = null;
if (isset($_POST['editTC']))
	$tc = $_POST['editTC'];
else if (isset($_GET['editTC']))
	$tc = $_GET['editTC'];

$product = $_SESSION['productID'];
$data = isset($_GET['data']) ? intval($_GET['data']) : 0;



$smarty = new TLSmarty;

// 20050810 - fm
// from 3 to only 1 assignment
$smarty->assign('path_htmlarea', $_SESSION['basehref'] . 'third_party/htmlarea/');
	
//If the user has chosen to edit a testcase then show this code
if($tc)
{
	$setOfKeys = array();
	
	// get TC data
	$myrowTC = getTestcase($data,false);

	$tcKeywords = null;
	getTCKeywords($data,$tcKeywords);
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


	$smarty->assign('tc', $myrowTC);
	$smarty->assign('data', $data);
	$smarty->assign('keys', $setOfKeys);
	$smarty->assign('keysize', $keySize);
	$smarty->display('tcEdit.tpl');
	//saving a test case but not archiving it
} 
else if(isset($_POST['updateTC']))
{
	$title 		= isset($_POST['title']) ? strings_stripSlashes($_POST['title']) : null;
	$summary 	= isset($_POST['summary']) ? strings_stripSlashes($_POST['summary']) : null;
	$steps 		= isset($_POST['steps']) ? strings_stripSlashes($_POST['steps']) : null;
	$outcome 	= isset($_POST['exresult']) ? strings_stripSlashes($_POST['exresult']) : null;
	
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
	
	if (updateTestcase($data,$title,$summary,$steps,$outcome,$_SESSION['user'],$updatedKeywords,$version))
		$sqlResult = 'ok';
   	else
		$sqlResult =  mysql_error();

	// show testcase
	// 20050820 - fm
	$allow_edit=1;
	// wrong call
	// showTestcase($data, $sqlResult);
	showTestcase($data, $allow_edit);
}
else if(isset($_POST['newTC']))
{
	//Creating a new test case
	$smarty->assign('data', $data);
	$smarty->display('tcNew.tpl');
}
else if(isset($_POST['addTC']))
{
	$title 		= isset($_POST['title']) ? strings_stripSlashes($_POST['title']) : null;
	$summary 	= isset($_POST['summary']) ? strings_stripSlashes($_POST['summary']) : null;
	$steps 		= isset($_POST['steps']) ? strings_stripSlashes($_POST['steps']) : null;
	$outcome 	= isset($_POST['exresult']) ? strings_stripSlashes($_POST['exresult']) : null;


  // 20050820 - fm
  if (strlen($title))
	{
		$result = lang_get('error_tc_add');
	  if (insertTestcase($data,$title,$summary,$steps,$outcome,$_SESSION['user']))
	  {
			$result = 'ok';
		}
  }
  else
  {
  	$result = lang_get('warning_empty_tc_title');	
  }
  	
  $smarty->assign('sqlResult', $result);
	
	$smarty->assign('name', $title);
	$smarty->assign('item', 'Test case');
	$smarty->assign('data', $data);
	$smarty->display('tcNew.tpl');
}
else if(isset($_POST['deleteTC']))
{
	if(isset($_GET['sure']) && $_GET['sure'] == 'yes') //check to see if the user said he was sure he wanted to delete
	{
		if (deleteTestcase($data))
			$smarty->assign('sqlResult', 'ok');
	   	else
			$smarty->assign('sqlResult', mysql_error());
	}
	$smarty->assign('data', $data);
	$smarty->display('tcDelete.tpl');
}
else if(isset($_POST['moveTC']))
{
	$catID = 0;
	$compID = 0;
	$arrOptCategories = null;
	
	getTestCaseCategoryAndComponent($data,$catID,$compID);
	getOptionCategoriesOfComponent($compID, $arrOptCategories);
	$arrOptCategories[$catID] .= ' (' . lang_get('current') . ')'; 

	$tcTitle = getTestcaseTitle($data);

	$smarty->assign('oldCat', $catID); // original Category
	$smarty->assign('arrayCat', $arrOptCategories);
	$smarty->assign('data', $data);
	$smarty->assign('title', $tcTitle);
	$smarty->display('tcMove.tpl');

// move test case to another category
}
else if(isset($_POST['updateTCmove']))
{
	$catID = isset($_POST['moveCopy']) ? intval($_POST['moveCopy']) : 0;
	$oldCat = isset($_POST['oldCat']) ? intval($_POST['oldCat']) : 0;
	
	$result = moveTc($catID, $data);
	showCategory($oldCat, $result);
}
else if(isset($_POST['updateTCcopy']))
{
	$catID = isset($_POST['moveCopy']) ? intval($_POST['moveCopy']) : 0;
	$oldCat = isset($_POST['oldCat']) ? intval($_POST['oldCat']) : 0;

  // 20050821 - fm - interface - reduce global coupling
	$result = copyTc($catID, $data, $_SESSION['user']);
	
	showCategory($oldCat, $result,'update',$catID);
}
else
{
	// ERROR
	tlog("A correct POST argument is not found.");
}
?>