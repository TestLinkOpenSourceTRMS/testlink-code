<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsimport.php,v $
 *
 * @modified $Date: 2006/01/05 07:30:34 $
 *
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

$prodID = $_SESSION['productID'];
$productName = $_SESSION['productName'];
$dest = TL_TEMP_PATH . session_id()."-importkeywords.csv";

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
			$sqlResult = importKeywords($db,$prodID,$keywordData);
			header("Location: keywordsView.php");
			exit();		
		}
	}
} 
					
$smarty = new TLSmarty();
$smarty->assign('keywordFormatStrings',$g_keywordFormatStrings);
$smarty->assign('importTypes',$g_keywordImportTypes);
$smarty->assign('productName', $productName);
$smarty->assign('productID', $prodID);
$smarty->assign('importLimitKB',TL_IMPORT_LIMIT / 1024);
$smarty->display('keywordsimport.tpl');
?>