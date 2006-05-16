<?php
/** 
*	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version $Id: planAddTCNavigator.php,v 1.14 2006/05/16 19:35:40 schlundus Exp $
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
require_once("treeMenu.inc.php");
testlinkInitPage($db);

$tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
$keyword_id = 0;

$tproject_mgr = new testproject($db);
$keywords_map = $tproject_mgr->get_keywords_map($tproject_id, " order by keyword "); 

if(!is_null($keywords_map))
  $keywords_map = array( 0 => '') + $keywords_map;

if(isset($_POST['filter']))
{
	$keyword_id = isset($_POST['keyword_id']) ? $_POST['keyword_id'] : 0;
}


// generate tree 
$workPath = 'lib/plan/planAddTC.php';
$args = null;
if ($keyword_id)
	$args = '&keyword_id=' . $keyword_id;

$treeString = generateTestSpecTree($db,$tproject_id, $tproject_name, 
                                   $workPath,0,1,
                                   $args, $keyword_id);
                                   
$tree = invokeMenu($treeString);
$smarty = new TLSmarty();

$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('tree', $tree);
$smarty->assign('keywords_map', $keywords_map);
$smarty->assign('keyword_id', $keyword_id);
$smarty->assign('menuUrl', $workPath);
$smarty->assign('args', $args);
$smarty->display('planAddTCNavigator.tpl');
?>