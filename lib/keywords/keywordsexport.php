<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsexport.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2005/12/29 21:03:09 $ by $Author: schlundus $
 *
 * This page this allows users to export keywords. 
 *
**/
require_once("../../config.inc.php");
require_once("../functions/csv.inc.php");
require_once("../functions/xml.inc.php");
require_once("../functions/common.php");
require_once("keywords.inc.php");
testlinkInitPage();

$bExport = isset($_POST['export']) ? $_POST['export'] : null;
$exportType = isset($_POST['exportType']) ? $_POST['exportType'] : null;

$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;
$productName = $_SESSION['productName'];

if ($bExport)
{
	$keywords = selectKeywords($db,$prodID);
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


$smarty = new TLSmarty;
$smarty->assign('productName', $productName);
$smarty->assign('productID', $prodID);
$smarty->assign('importTypes',$g_keywordImportTypes);
$smarty->display('keywordsexport.tpl');

?>