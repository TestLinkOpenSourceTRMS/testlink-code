<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: tcexport.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2006/08/29 20:26:20 $ by $Author: schlundus $
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
$exportType = isset($_POST['exportType']) ? $_POST['exportType'] : null;
$tcase_id = isset($_POST['tcID']) ? intval($_POST['tcID']) : 0;
$tcversion_id = isset($_POST['tcVersionID']) ? intval($_POST['tcVersionID']) : 0;

$testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$productName = $_SESSION['testprojectName'];

if ($bExport)
{
	$tcase_mgr = new testcase($db);

	$pfn = null;
	switch($exportType)
	{
		case 'XML':
			$pfn = 'exportTestCaseDataToXML';
			$fileName = 'testcase.xml';
			break;
	}
	if ($pfn)
	{
		$content = $tcase_mgr->$pfn($tcase_id,$tcversion_id);
		downloadContentsToFile($content,$fileName);
		exit();
	}
}

$smarty = new TLSmarty();
$smarty->assign('productName', $productName);
$smarty->assign('productID', $testproject_id);
$smarty->assign('importTypes',$g_keywordImportTypes);
$smarty->display('keywordsexport.tpl');
?>