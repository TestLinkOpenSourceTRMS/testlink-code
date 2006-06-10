<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqImport.php,v $
 * @version $Revision: 1.14 $
 * @modified $Date: 2006/06/10 20:22:20 $ by $Author: schlundus $
 * @author Martin Havlat
 * 
 * Import requirements to a specification. 
 * Supported: simple CSV, Doors CSV, XML
 * 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
require_once('xml.inc.php');
require_once('csv.inc.php');
testlinkInitPage($db);

$idSRS = isset($_GET['idSRS']) ? strings_stripSlashes($_GET['idSRS']) : null;
$importType = isset($_POST['importType']) ? strings_stripSlashes($_POST['importType']) : null;
$emptyScope = isset($_POST['noEmpty']) ? strings_stripSlashes($_POST['noEmpty']) : null;
$conflictSolution = isset($_POST['conflicts']) ? strings_stripSlashes($_POST['conflicts']) : null;
$bUpload = isset($_POST['UploadFile']);
$bExecuteImport = isset($_POST['executeImport']);

$tprojectID = $_SESSION['testprojectID'];
$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;

$fileName = TL_TEMP_PATH . "importReq-".session_id().".csv";

$tproject = new testproject($db);
$importResult = null;
$arrImport = null;

if ($bUpload)
{
	$source = isset($HTTP_POST_FILES['uploadedFile']['tmp_name']) ? $HTTP_POST_FILES['uploadedFile']['tmp_name'] : null;
	$arrImport = array();

	if (($source != 'none') && ($source != '' ))
	{ 
		if (move_uploaded_file($source, $fileName))
			$arrImport = doImport($db,$userID,$idSRS,$fileName,$importType,$emptyScope,$conflictSolution,false);
	}
	else 
		$importType = '';
}
else if ($bExecuteImport)
{
	$arrImport = doImport($db,$userID,$idSRS,$fileName,$importType,$emptyScope,$conflictSolution,true);
	$importResult = lang_get('req_import_finished');
}

$arrSpec = $tproject->getReqSpec($tprojectID,$idSRS);

$smarty = new TLSmarty;
$smarty->assign('reqFormatStrings',$g_reqFormatStrings);
$smarty->assign('importTypes',$g_reqImportTypes);
$smarty->assign('reqSpec', $arrSpec[0]);
$smarty->assign('arrImport', $arrImport);
$smarty->assign('importResult', $importResult);
$smarty->assign('importType', $importType);
$smarty->assign('uploadedFile', $fileName);
$smarty->assign('importLimit', TL_IMPORT_LIMIT);
$smarty->assign('importLimitKB', round(strval(TL_IMPORT_LIMIT) / 1024));
$smarty->display('reqImport.tpl');
?>