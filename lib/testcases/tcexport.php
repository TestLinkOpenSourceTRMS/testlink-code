<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: tcexport.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2006/10/23 06:42:22 $ by $Author: franciscom $
 *
 * This page this allows users to export keywords. 
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


$node_id=$container_id;
if($bRecursive)
{
  // Exporting situations:
  // All test suites in test project
  // One test suite 
  $page_title=lang_get('title_tsuite_export') . TITLE_SEP;
  if( $node_id == $testproject_id )
  {
     $page_title=lang_get('title_tsuite_export_all') . TITLE_SEP . 
                 lang_get('title_testproject') . TITLE_SEP;  
  }
} 
else
{
  // Exporting situations:
  // All test cases in test suite.
  // One test case.
  $exporting_just_one_tc=($tcase_id && $tcversion_id);
  
  if($exporting_just_one_tc)
  {
    $node_id=$tcase_id;
    $page_title=lang_get('title_tc_export'). TITLE_SEP;
  }
  else
  {
    $page_title=lang_get('title_tc_export_all') . TITLE_SEP .
                lang_get('title_testsuite') . TITLE_SEP;  
  }
}

$tree_mgr=New tree($db);
$node=$tree_mgr->get_node_hierachy_info($node_id);




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
			$fileName = 'testcase.xml';
			break;
	}
	if ($pfn)
	{
		if ($exporting_just_one_tc)
		{
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

$smarty = new TLSmarty();
$smarty->assign('object_name',$node['name']);

$smarty->assign('page_title',$page_title);
$smarty->assign('productName', $testprojectName);
$smarty->assign('productID', $testproject_id);
$smarty->assign('tcID', $tcase_id);
$smarty->assign('bRecursive',$bRecursive ? 1 : 0);
$smarty->assign('tcVersionID', $tcversion_id);
$smarty->assign('containerID', $container_id);
$smarty->assign('exportTypes',$g_tcImportTypes);
$smarty->display('tcexport.tpl');
?>