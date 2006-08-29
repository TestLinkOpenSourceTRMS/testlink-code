<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: tcImport.php,v $
 *
 * @version $Revision: 1.11 $
 * @modified $Date: 2006/08/29 19:41:38 $
 *
 * @author	Martin Havlat
 * @author	Chad Rosen
 *
 * This page manages the importation of product data from a csv file.
*/
require('../../config.inc.php');
require_once('common.php');
require_once('import.inc.php');
require_once('csv.inc.php');
require_once('xml.inc.php');
testlinkInitPage($db);

$source = isset($HTTP_POST_FILES['uploadedFile']['tmp_name']) ? $HTTP_POST_FILES['uploadedFile']['tmp_name'] : null;
$bImport = isset($_POST['import']) ? 1 : 0;
$importType = isset($_POST['importType']) ? $_POST['importType'] : null;
$location = isset($_POST['location']) ? strings_stripSlashes($_POST['location']) : null; 

$testproject_id = $_SESSION['testprojectID'];
$productName = $_SESSION['testprojectName'];
$dest = TL_TEMP_PATH . session_id()."-importtcs.csv";

// check the uploaded file
if (($source != 'none') && ($source != ''))
{ 
	// store the file
	if (move_uploaded_file($source, $dest))
	{
		switch($importType)
		{
			/*
			case 'CSV':
				$pfn = "importKeywordDataFromCSV";
				break;
			*/
			case 'XML':
				$pfn = "importTCDataFromXML";
				break;
		}
		if ($pfn)
		{
			/*
			$keywordData = $pfn($dest);
			$tproject = new testproject($db);
			$sqlResult = $tproject->addKeywords($testproject_id,$keywordData);
			header("Location: keywordsView.php");
			*/
			exit();		
		}
	}
} 
					
$smarty = new TLSmarty();
$smarty->assign('keywordFormatStrings',$g_tcFormatStrings);
$smarty->assign('importTypes',$g_tcImportTypes);
$smarty->assign('productName', $productName);
$smarty->assign('productID', $testproject_id);
$smarty->assign('importLimitKB',TL_IMPORT_LIMIT / 1024);
$smarty->display('keywordsimport.tpl');
?>