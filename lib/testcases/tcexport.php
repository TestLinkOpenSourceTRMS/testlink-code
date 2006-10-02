<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: tcexport.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2006/10/02 17:36:56 $ by $Author: schlundus $
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
$productName = $_SESSION['testprojectName'];

if ($bExport)
{
	$tcase_mgr = new testcase($db);
	$tsuite_mgr = new testsuite($db);
	
	$optExport = array(
						'KEYWORDS' => $bKeywords,
					    'RECURSIVE' => $bRecursive,
					  );
	
	$pfn = null;
	switch($exportType)
	{
		case 'XML':
			if ($tcase_id && $tcversion_id)	
				$pfn = 'exportTestCaseDataToXML';
			else
				$pfn = 'exportTestSuiteDataToXML';				
			$fileName = 'testcase.xml';
			break;
	}
	if ($pfn)
	{
		if ($tcase_id && $tcversion_id)
			$content = $tcase_mgr->$pfn($tcase_id,$tcversion_id,null,$optExport);
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
$smarty->assign('productName', $productName);
$smarty->assign('productID', $testproject_id);
$smarty->assign('tcID', $tcase_id);
$smarty->assign('bRecursive',$bRecursive ? 1 : 0);
$smarty->assign('tcVersionID', $tcversion_id);
$smarty->assign('containerID', $container_id);
$smarty->assign('exportTypes',$g_tcImportTypes);
$smarty->display('tcexport.tpl');
?>