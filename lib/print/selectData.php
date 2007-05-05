<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @version 	$Id: selectData.php,v 1.15 2007/05/05 18:11:44 schlundus Exp $
* @author 	Martin Havlat
* 
* 	Navigator for print/export functionality. 
*	It builds the javascript tree that allow the user select a required part 
*	test specification.
*/
require('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage($db);

$tplan_id   = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : 'xxx';
$tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

// parse wrong type
$type = isset($_GET['type']) ? $_GET['type'] : '';
if ($type != 'testproject' && $type != 'testSet')
{
	tLog("Argument GET['type'] has invalid value", 'ERROR');
	exit();
}

// default vars
$arrFormat = array('html' => 'HTML', 'msword' => 'MS Word');
$arrCheckboxes = array(
	array( 'value' => 'header', 'description' => lang_get('opt_show_doc_header'), 'checked' => 'n'),
	array( 'value' => 'body', 'description' => lang_get('opt_show_tc_body'), 'checked' => 'n'),
	array( 'value' => 'summary', 'description' => lang_get('opt_show_tc_summary'), 'checked' => 'n'),
	array( 'value' => 'toc', 'description' => lang_get('opt_show_toc'), 'checked' => 'n'),
	array( 'value' => 'passfail', 'description' => lang_get('opt_show_passfail'), 'checked' => 'n'),
);

//process setting for print
if(isset($_POST['setPrefs']))
{
  	if(isset($_POST['header'])) $arrCheckboxes[0]['checked'] = 'y';
  	if(isset($_POST['body'])) $arrCheckboxes[1]['checked'] = 'y';
  	if(isset($_POST['summary'])) $arrCheckboxes[2]['checked'] = 'y';
  	if(isset($_POST['toc'])) $arrCheckboxes[3]['checked'] = 'y';
	if(isset($_POST['passfail'])) $arrCheckboxes[4]['checked'] = 'y';
}

if(isset($_POST['format']) && $_POST['format'] == 'msword') 
   	$selFormat = 'msword';
else
   	$selFormat = 'html';

// generate tree for product test specification
$workPath = 'lib/print/printData.php';
$args = "&type=" . $type;
$smarty = new TLSmarty();
// generate tree 
$HIDE_TCs = 1;
if ($type == 'testproject')
{
	$treeString = generateTestSpecTree($db,$tproject_id, $tproject_name,$workPath, 1, 0,$args);
	$smarty->assign('title', lang_get('title_tc_print_navigator'));
}	
else if ($type == 'testSet')
{
	$tp = new testplan($db);
	$latestBuild = $tp->get_max_build_id($tplan_id);
	$treeString = generateExecTree($db,$workPath,$tproject_id,$tproject_name,$tplan_id,$tplan_name,$latestBuild,$args,null,null,true);

	$smarty->assign('title', lang_get('title_tp_print_navigator'));
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
$smarty->display('tcPrintNavigator.tpl');
?>
