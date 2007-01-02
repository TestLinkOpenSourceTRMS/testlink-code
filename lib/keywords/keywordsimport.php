<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Scope: Import keywords page
 *
 * Filename $RCSfile: keywordsimport.php,v $
 * @version $Revision: 1.8 $
 * @modified $Date: 2007/01/02 12:23:36 $ by $Author: havlat $
 *
 * Revisions:
 * 20070102 - MHT - Fixed typo error, updated header
 * 
 */
require('../../config.inc.php');
require_once('common.php');
require_once('import.inc.php');
require_once('csv.inc.php');
require_once('xml.inc.php');
testlinkInitPage($db);

$source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;
$bImport = isset($_POST['import']) ? 1 : 0;
$importType = isset($_POST['importType']) ? $_POST['importType'] : null;
$location = isset($_POST['location']) ? strings_stripSlashes($_POST['location']) : null; 

$testproject_id = $_SESSION['testprojectID'];
$productName = $_SESSION['testprojectName'];
$dest = TL_TEMP_PATH . session_id()."-importkeywords.".$importType;

// check the uploaded file
if (($source != 'none') && ($source != ''))
{ 
	// store the file
	if (move_uploaded_file($source, $dest))
	{
		switch($importType)
		{
			case 'CSV':
				$pfn = "importKeywordDataFromCSV";
				break;
			case 'XML':
				$pfn = "importKeywordDataFromXML";
				break;
		}
		if ($pfn)
		{
			$keywordData = $pfn($dest);
			$tproject = new testproject($db);
			$sqlResult = $tproject->addKeywords($testproject_id,$keywordData);
			header("Location: keywordsView.php");
			exit();		
		}
	}
} 
					
$smarty = new TLSmarty();
$smarty->assign('keywordFormatStrings',$g_keywordFormatStrings);
$smarty->assign('importTypes',$g_keywordImportTypes);
$smarty->assign('productName', $productName);
$smarty->assign('productID', $testproject_id);
$smarty->assign('importLimitKB',TL_IMPORT_LIMIT / 1024);
$smarty->display('keywordsimport.tpl');
?>
