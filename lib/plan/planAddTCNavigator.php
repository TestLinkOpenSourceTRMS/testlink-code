<?php
/** 
*	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version $Id: planAddTCNavigator.php,v 1.13 2006/05/03 08:30:07 franciscom Exp $
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
require_once("../../lib/functions/lang_api.php");
require_once(dirname(__FILE__) . "/../functions/testproject.class.php");

testlinkInitPage($db);

$tproject_mgr=New testproject($db);

// 20050905 - fm
$tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

$keyword_id = 0;
$keywords_map = $tproject_mgr->get_keywords_map($tproject_id, " order by keyword "); 

if( !is_null($keywords_map) )
{
  $keywords_map = array( 0 => '') + $keywords_map;
}

if(isset($_POST['filter']))
{
	$keyword_id = isset($_POST['keyword_id']) ? $_POST['keyword_id'] : 0;
}


// generate tree 
$workPath = 'lib/plan/planAddTC.php';
$args = '&keyword_id=' . $keyword_id;

$hide_testcase_items=0;             
$tc_action_disabled=0;          

// 20060501 - franciscom - interface changes
$treeString = generateTestSpecTree($db,$tproject_id, $tproject_name, 
                                   $workPath, $hide_testcase_items, $tc_action_disabled,
                                   $args, $keyword_id);

                                   
$tree = invokeMenu($treeString);
$smarty = new TLSmarty;

$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('tree', $tree);
$smarty->assign('keywords_map', $keywords_map);
$smarty->assign('keyword_id', $keyword_id);

$smarty->assign('menuUrl', $workPath);
$smarty->assign('args', $args);
$smarty->display('planAddTCNavigator.tpl');
?>