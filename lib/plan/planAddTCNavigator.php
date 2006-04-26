<?php
/** 
*	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version $Id: planAddTCNavigator.php,v 1.12 2006/04/26 07:07:55 franciscom Exp $
*	@author Martin Havlat
* 
* 	Navigator for feature: add Test Cases to a Test Case Suite in Test Plan. 
*	It builds the javascript tree that allow the user select a required part 
*	Test specification. Keywords should be used for filter.
* 
* 20051126 - scs - changed passing keyword to keyword id
*/
require('../../config.inc.php');

require_once("common.php");
require_once("../keywords/keywords.inc.php");
require_once("treeMenu.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage($db);

//setting up the top table with the date and build selection
$key = null;

// 20050905 - fm
$tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

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
//$linked_versions=$tplan_mgr->get_linked_tcversions($tplan_id);	
//$treeString = generateTestSpecTree($db,$tproject_id, $tproject_name, 
//                                   $workPath, 1, $args,$linked_versions);
                                   
$treeString = generateTestSpecTree($db,$tproject_id, $tproject_name, 
                                   $workPath, 1, $args);
                                   
$tree = invokeMenu($treeString);
$smarty = new TLSmarty;
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('tree', $tree);
$smarty->assign('arrKeys', selectKeywords($db,$tproject_id,$key));
$smarty->assign('menuUrl', $workPath);
$smarty->assign('args', $args);
$smarty->display('planAddTCNavigator.tpl');
?>