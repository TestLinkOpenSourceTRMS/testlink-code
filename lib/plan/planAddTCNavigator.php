<?
/** 
*	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version $Id: planAddTCNavigator.php,v 1.3 2005/09/06 06:45:23 franciscom Exp $
*	@author Martin Havlat
* 
* 	Navigator for feature: add Test Cases to a Test Case Suite in Test Plan. 
*	It builds the javascript tree that allow the user select a required part 
*	Test specification. Keywords should be used for filter.
*/
require('../../config.inc.php');
require("common.php");
require_once("../keywords/keywords.inc.php");
require_once("treeMenu.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

//setting up the top table with the date and build selection
$key = null;

// 20050905 - fm
$prodID   = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;
$prodName = isset($_SESSION['productName']) ? $_SESSION['productName'] : '';

if(isset($_POST['filter']))
{
	$key = isset($_POST['keyword']) ? strings_stripSlashes($_POST['keyword']) : 'NONE';
}

// generate tree 
$workPath = 'lib/plan/planAddTC.php';
$args = null;
if (strlen($key))
{
	$args = '&key=' . $key;
}

// 20050905 - fm	
$treeString = generateTestSpecTree($prodID, $prodName, $workPath, 1, $args);
$tree = invokeMenu($treeString);

$smarty = new TLSmarty;
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('tree', $tree);
$smarty->assign('arrKeys', selectKeywords($prodID,$key));
$smarty->assign('menuUrl', $workPath);
$smarty->assign('args', $args);
$smarty->display('planAddTCNavigator.tpl');
?>