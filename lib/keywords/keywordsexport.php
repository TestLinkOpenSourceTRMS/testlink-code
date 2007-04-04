<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsexport.php,v $
 *
 * @version $Revision: 1.9 $
 * @modified $Date: 2007/04/04 19:54:49 $ by $Author: schlundus $
 *
 * This page this allows users to export keywords. 
 *
**/
require_once("../../config.inc.php");
require_once("../functions/csv.inc.php");
require_once("../functions/xml.inc.php");
require_once("../functions/common.php");
require_once("keywords.inc.php");
testlinkInitPage($db);

$bExport = isset($_POST['export']) ? $_POST['export'] : null;
$exportType = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : null;

$testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$testprojectName = $_SESSION['testprojectName'];
if ($bExport || !is_null($exportType) )
{
	$tproject = new testproject($db);
	$keywords = $tproject->getKeywords($testproject_id);
	$pfn = null;
	switch($exportType)
	{
		case 'CSV':
			$pfn = "exportKeywordDataToCSV";
			$fileName = 'keywords.csv';
			break;
		case 'XML':
			$pfn = "exportKeywordDataToXML";
			$fileName = 'keywords.xml';
			break;
	}
	if ($pfn)
	{
		$content = $pfn($keywords);
		downloadContentsToFile($content,$fileName);
		exit();
	}
}


$smarty = new TLSmarty();
$smarty->assign('testprojectName', $testprojectName);
$smarty->assign('testprojectID', $testproject_id);
$smarty->assign('importTypes',$g_keywordImportTypes);
$smarty->display('keywordsexport.tpl');
?>