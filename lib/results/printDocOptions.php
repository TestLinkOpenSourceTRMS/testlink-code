<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @version 	$Id: printDocOptions.php,v 1.2 2007/12/10 22:59:45 havlat Exp $
* @author 	Martin Havlat
* 
* Navigator for print/export functionality. 
*	It builds the javascript tree that allow the user select a required part 
*	test specification.
*
* rev :
*      20070509 - franciscom - added contribution BUGID
*
*/
require('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage($db);

$tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : 'xxx';
$tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

$tplan_id   = isset($_GET['tplan_id']) ? $_GET['tplan_id'] : 0;
$format   = isset($_GET['format']) ? $_GET['format'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

// default vars
$arrFormat = array('html' => 'HTML', 'msword' => 'MS Word');

// Important Notice:
// If you made add/remove elements from this array, you must update
// $printingOptions in printData.php


$arrCheckboxes = array(
	array( 'value' => 'toc', 'description' => lang_get('opt_show_toc'), 'checked' => 'n'),
	array( 'value' => 'header', 'description' => lang_get('opt_show_doc_header'), 'checked' => 'n'),
	array( 'value' => 'summary', 'description' => lang_get('opt_show_tc_summary'), 'checked' => 'y'),
	array( 'value' => 'body', 'description' => lang_get('opt_show_tc_body'), 'checked' => 'n'),
  	array( 'value' => 'author',     'description' => lang_get('opt_show_tc_author'), 'checked' => 'n'),
	array( 'value' => 'requirement', 'description' => lang_get('opt_show_tc_reqs'), 'checked' => 'n'),
	array( 'value' => 'keyword', 'description' => lang_get('opt_show_tc_keys'), 'checked' => 'n')
);

if( $type == 'testplan')
{
  $arrCheckboxes[]=	array( 'value' => 'passfail', 'description' => lang_get('opt_show_passfail'), 'checked' => 'n');
}

//process setting for print
if(isset($_POST['setPrefs']))
{
  foreach($arrCheckboxes as $key => $elem)
  {
   $field_name=$elem['value'];
   if(isset($_POST[$field_name]) )
   {
    $arrCheckboxes[$key]['checked'] = 'y';   
   }  
  }
}


$selFormat = 'html';
if(isset($_POST['format']) && $_POST['format'] == 'msword') 
{
   	$selFormat = 'msword';
}

// generate tree for product test specification
$workPath = 'lib/results/printDocument.php';
$args = "&type=" . $type;
$smarty = new TLSmarty();

// generate tree 
if ($type == 'testspec')
{
  // 20071014 - franciscom 
	$treeString = generateTestSpecTree($db,$tproject_id, $tproject_name,$workPath,
	                                   FOR_PRINTING,HIDE_TESTCASES,ACTION_TESTCASE_DISABLE,$args);
	$smarty->assign('title', lang_get('title_tc_print_navigator'));
}	
else if ($type == 'testplan')
{
	$tp = new testplan($db);
	$latestBuild = $tp->get_max_build_id($tplan_id);
	$treeString = generateExecTree($db,$workPath,$tproject_id,$tproject_name,$tplan_id,$tplan_name,$latestBuild,$args,null,null,true);

	$smarty->assign('title', lang_get('title_tp_print_navigator'));
}	
else
{
	tLog("Argument GET['type'] has invalid value", 'ERROR');
	exit();
}

$tree = invokeMenu($treeString,null,null);
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('arrCheckboxes', $arrCheckboxes);
$smarty->assign('arrFormat', $arrFormat);
$smarty->assign('selFormat', $selFormat);
$smarty->assign('tree', $tree);
$smarty->assign('menuUrl', $workPath);
$smarty->assign('args', $args);
$smarty->assign('type', $type);
$smarty->assign('SP_html_help_file',TL_INSTRUCTIONS_RPATH . $_SESSION['locale'] . "/printTestSet.html");
$smarty->display('results/printDocOptions.tpl');
?>
