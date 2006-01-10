<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqImport.php,v $
 * @version $Revision: 1.9 $
 * @modified $Date: 2006/01/10 19:59:28 $ by $Author: schlundus $
 * @author Martin Havlat
 * 
 * Import requirements to a specification. 
 * Supported: simple CSV, Doors CSV
 * 
 */
////////////////////////////////////////////////////////////////////////////////
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirementsImport.inc.php');
testlinkInitPage($db);

$idSRS = isset($_GET['idSRS']) ? strings_stripSlashes($_GET['idSRS']) : null;
$importType = isset($_POST['importType']) ? strings_stripSlashes($_POST['importType']) : null;
$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
$CSVfile = TL_TEMP_PATH . "importReq-".session_id().".csv";

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
				$arrReqTitles = getReqTitles($db,$idSRS);
				
				// compare titles
				$arrImport = compareImportedReqs($arrImportSource, $arrReqTitles);
			}
		}
	}
	//20051015 - am - if no file was given, we cancel import
	else 
		$importType = '';
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
		$arrReqTitles = getReqTitles($db,$idSRS);
				
		// process import
		$arrImport = executeImportedReqs($db,$arrImportSource, $arrReqTitles, 
		                                 $conflictSolution, $emptyScope, $idSRS, $userID);
	}
	$importResult = lang_get('req_import_finished');
}
// collect existing document data
// fm - mybug after refactoring
$arrSpec = getReqSpec($db,$_SESSION['productID'],$idSRS);	

$smarty = new TLSmarty;
$smarty->assign('reqSpec', $arrSpec[0]);
$smarty->assign('arrImport', $arrImport);
$smarty->assign('importResult', $importResult);
$smarty->assign('importType', $importType);
$smarty->assign('uploadedFile', $CSVfile);
$smarty->assign('importLimit', TL_IMPORT_LIMIT);
$smarty->assign('importLimitKB', round(strval(TL_IMPORT_LIMIT) / 1024));
$smarty->display('reqImport.tpl');

?>
