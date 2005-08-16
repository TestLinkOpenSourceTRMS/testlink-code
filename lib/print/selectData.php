<?
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @version 	$Id: selectData.php,v 1.2 2005/08/16 18:00:57 franciscom Exp $
* @author 	Martin Havlat
* 
* 	Navigator for print/export functionality. 
*	It builds the javascript tree that allow the user select a required part 
*	test specification.
*
* 	@todo export to XML, PDF
*
*/////////////////////////////////////////////////////////////////////////////////
require('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

// parse wrong type
$type = isset($_GET['type']) ? $_GET['type'] : '';
if ($type != 'product' && $type != 'testSet')
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
);

//process setting for print
if(isset($_POST['setPrefs']))
{
  	if(isset($_POST['header'])) $arrCheckboxes[0]['checked'] = 'y';
  	if(isset($_POST['body'])) $arrCheckboxes[1]['checked'] = 'y';
  	if(isset($_POST['summary'])) $arrCheckboxes[2]['checked'] = 'y';
  	if(isset($_POST['toc'])) $arrCheckboxes[3]['checked'] = 'y';
}

if(isset($_POST['format']) && $_POST['format'] == 'msword') 
   	$selFormat = 'msword';
else
   	$selFormat = 'html';

// generate tree for product test specification
$workPath = 'lib/print/printData.php';
$args = "&format=" . $selFormat . "&header=" . $arrCheckboxes[0]['checked'] . 
			"&body=" . $arrCheckboxes[1]['checked'] . "&summary=" . 
			$arrCheckboxes[2]['checked'] . "&type=" . $type . "&toc=" . 
			$arrCheckboxes[3]['checked'];

// generate tree 
if ($type == 'product')
	$treeString = generateTestSpecTree($workPath, 1, $args);
else if ($type == 'testSet')
	$treeString = generateTestSuiteTree($workPath, 1, $args);

$tree = invokeMenu($treeString);

$smarty = new TLSmarty;
$smarty->assign('title', lang_get('title_tc_print_navigator'));
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