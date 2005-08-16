<?
/** 
*	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version $Id: planAddTCNavigator.php,v 1.2 2005/08/16 18:00:57 franciscom Exp $
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
if(isset($_POST['filter']))
	$key = isset($_POST['keyword']) ? strings_stripSlashes($_POST['keyword']) : 'NONE';

// generate tree 
$workPath = 'lib/plan/planAddTC.php';
$args = null;
if (strlen($key))
	$args = '&key=' . $key;
$treeString = generateTestSpecTree($workPath, 1, $args);
$tree = invokeMenu($treeString);

$smarty = new TLSmarty;
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('tree', $tree);
$smarty->assign('arrKeys', selectKeywords($key));
$smarty->assign('menuUrl', $workPath);
$smarty->assign('args', $args);
$smarty->display('planAddTCNavigator.tpl');
?>