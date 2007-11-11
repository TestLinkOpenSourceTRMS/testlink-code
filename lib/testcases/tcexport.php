<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: tcexport.php,v $
 *
 * @version $Revision: 1.12 $
 * @modified $Date: 2007/11/11 15:30:56 $ by $Author: franciscom $
 *
 * test case and test suites export
 *
 * 20070113 - franciscom - added logic to create message when there is 
 *                         nothing to export.
 *
 * 20061118 - franciscom - using different file name, depending the
 *                         type of exported elements.
 *
**/
require_once("../../config.inc.php");
require_once("../functions/csv.inc.php");
require_once("../functions/xml.inc.php");
require_once("../keywords/keywords.inc.php");
require_once("../functions/common.php");
testlinkInitPage($db);

$bExport = isset($_POST['export']) ? $_POST['export'] : null;
$bKeywords = isset($_POST['bKeywords']) ? 1 : 0;
$exportType = isset($_POST['exportType']) ? $_POST['exportType'] : null;
$tcase_id = isset($_POST['testcase_id']) ? intval($_POST['testcase_id']) : 0;
$tcversion_id = isset($_POST['tcversion_id']) ? intval($_POST['tcversion_id']) : 0;
$container_id = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : 0;
$bRecursive = isset($_REQUEST['bRecursive']) ? $_REQUEST['bRecursive'] : false;
$testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$testprojectName = $_SESSION['testprojectName'];

$export_filename=isset($_REQUEST['export_filename']) ? $_REQUEST['export_filename'] : null;

$exporting_just_one_tc = 0;
$node_id = $container_id;
$do_it = 1;
$nothing_todo_msg = '';
$check_children = 0;

if($bRecursive)
{
	// Exporting situations:
	// All test suites in test project
	// One test suite 
	$page_title=lang_get('title_tsuite_export');
	$container_description=lang_get('test_suite');

	$fileName = 'testsuites.xml';
	if($node_id == $testproject_id)
	{
		$container_description=lang_get('testproject');
		$page_title=lang_get('title_tsuite_export_all');
	  $fileName = 'all_testsuites.xml';
		$check_children=1; 
		$nothing_todo_msg=lang_get('no_testsuites_to_export');
	}
} 
else
{
	// Exporting situations:
	// All test cases in test suite.
	// One test case.
	$exporting_just_one_tc = ($tcase_id && $tcversion_id);
	$fileName = 'testcases.xml';
	
	if($exporting_just_one_tc)
	{
		$container_description = lang_get('test_case');
		$node_id = $tcase_id;
		$page_title = lang_get('title_tc_export');
	}
	else
	{
		$container_description = lang_get('test_suite');
		$page_title = lang_get('title_tc_export_all');
		$check_children = 1;
		$nothing_todo_msg = lang_get('no_testcases_to_export');
	}
}

$fileName = is_null($export_filename) ? $fileName : $export_filename;


if( $check_children )
{
	// Check if there is something to export
	$tree_mgr = new tree($db);
	
	// 20071111 - franciscom
	$children=$tree_mgr->get_children($node_id, 
	                                  array("testplan" => "exclude_me",
	                                        "requirement_spec" => "exclude_me",
	                                        "requirement" => "exclude_me"));	
	if(count($children)==0)
		$do_it = 0 ;
	else
		$nothing_todo_msg='';
}

$tree_mgr = new tree($db);
$node = $tree_mgr->get_node_hierachy_info($node_id);


if ($bExport)
{
	$tcase_mgr = new testcase($db);
	$tsuite_mgr = new testsuite($db);
	
	$optExport = array(
						'KEYWORDS' => $bKeywords,
					    'RECURSIVE' => $bRecursive
					  );
	
	$pfn = null;
	switch($exportType)
	{
		case 'XML':
			if ($exporting_just_one_tc)
				$pfn = 'exportTestCaseDataToXML';
			else
				$pfn = 'exportTestSuiteDataToXML';				
			break;
	}
	if ($pfn)
	{
		if ($exporting_just_one_tc)
		{
			$optExport['ROOTELEM'] = "<testcases>{{XMLCODE}}</testcases>";
			$content = $tcase_mgr->$pfn($tcase_id,$tcversion_id,null,$optExport);
		}	
		else
		{
			$content = TL_XMLEXPORT_HEADER;
			$content .= $tsuite_mgr->$pfn($container_id,$optExport);
		}
			
		downloadContentsToFile($content,$fileName);
		exit();
	}
}

if( $bRecursive )
{
  // we are importing a testsuite
  $obj_mgr = new testsuite($db);
}
else
{
  $obj_mgr = new testcase($db);
}
$export_file_types=$obj_mgr->get_export_file_types();


$smarty = new TLSmarty();

$smarty->assign('export_filename',$fileName);
$smarty->assign('do_it',$do_it);
$smarty->assign('nothing_todo_msg',$nothing_todo_msg);
$smarty->assign('object_name',$node['name']);
$smarty->assign('page_title',$page_title);
$smarty->assign('productName', $testprojectName);
$smarty->assign('productID', $testproject_id);
$smarty->assign('tcID', $tcase_id);
$smarty->assign('bRecursive',$bRecursive ? 1 : 0);
$smarty->assign('tcVersionID', $tcversion_id);
$smarty->assign('containerID', $container_id);
$smarty->assign('container_description', $container_description);
$smarty->assign('exportTypes',$export_file_types);
$smarty->display('tcexport.tpl');
?>
