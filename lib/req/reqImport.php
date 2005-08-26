<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqImport.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2005/08/26 13:41:17 $ by $Author: havlat $
 * @author Martin Havlat
 * 
 * Import requirements to a specification. 
 * Supported: simple CSV, Doors CSV
 * 
 */
////////////////////////////////////////////////////////////////////////////////

define('TL_IMPORT_LIMIT', '200000'); // in bytes
define('TL_IMPORT_ROW_MAX', '10000'); // in chars

require_once("../../config.inc.php");
require_once("common.php");
require_once('requirementsImport.inc.php');

// init page 
testlinkInitPage();

$idSRS = isset($_GET['idSRS']) ? strings_stripSlashes($_GET['idSRS']) : null;
$importType = isset($_POST['importType']) ? strings_stripSlashes($_POST['importType']) : null;
$CSVfile = TL_TEMP_PATH . "importReq.csv";
/** @var string $importResult declares that import was done */
$importResult = null;
/** @var array $arrImport carries the information about particular imported requirements */
$arrImport = null;


// load and check a data
if (isset($_POST['UploadFile']))
{
	// Contains the full path and filename of the uploaded file as stored on the server.
	$source = isset($HTTP_POST_FILES['uploadedFile']['tmp_name']) ? $HTTP_POST_FILES['uploadedFile']['tmp_name'] : null;
	$arrImport = array();
	tLog("importType=$importType");

	// check the uploaded file
	if ( ($source != 'none') && ($source != '' ))
	{ 
		// store the file
		if (move_uploaded_file($source, $CSVfile))
		{
			// load data to $arrImportSource
			$arrImportSource = loadImportedReq($CSVfile, $importType);
			
			// assess possible conflicts
			if (count($arrImportSource))
			{
				// collect existing req titles in the SRS
				$arrReqTitles = getReqTitles($idSRS);
				
				// compare titles
				$arrImport = compareImportedReqs($arrImportSource, $arrReqTitles);
			}
		}
	}
	 
}
elseif (isset($_POST['executeImport']))
{
	$emptyScope = isset($_POST['noEmpty']) ? strings_stripSlashes($_POST['noEmpty']) : null;
	$conflictSolution = isset($_POST['conflicts']) ? strings_stripSlashes($_POST['conflicts']) : null;
	tLog("INPUT: importType=$importType, CSVfile=$CSVfile, emptyScope=$emptyScope, conflictSolution=$conflictSolution");
	
	$arrImportSource = loadImportedReq($CSVfile, $importType);
				
	if (count($arrImportSource))
	{
		// collect existing req titles in the SRS
		$arrReqTitles = getReqTitles($idSRS);
				
		// process import
		$arrImport = executeImportedReqs($arrImportSource, $arrReqTitles, $conflictSolution, $emptyScope, $idSRS);
	}

	$importResult = lang_get('req_import_finished');
	
}


// collect existing document data
$arrSpec = getReqSpec($idSRS);	

$smarty = new TLSmarty;
$smarty->assign('reqSpec', $arrSpec[0]);
$smarty->assign('arrImport', $arrImport);
$smarty->assign('importResult', $importResult);
$smarty->assign('importType', $importType);
$smarty->assign('uploadedFile', $CSVfile);
$smarty->assign('importLimit', TL_IMPORT_LIMIT);
$smarty->assign('importLimitKB', round(strval(TL_IMPORT_LIMIT) / 1000));
$smarty->display('reqImport.tpl');

?>
