<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: reqexport.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2006/04/08 19:53:56 $ by $Author: schlundus $
 *
 * This page this allows users to export requirements. 
 *
**/
require_once("../../config.inc.php");
require_once("../functions/csv.inc.php");
require_once("../functions/xml.inc.php");
require_once("../functions/common.php");
require_once("../functions/requirements.inc.php");
testlinkInitPage($db);

$bExport = isset($_POST['export']) ? $_POST['export'] : null;
$exportType = isset($_POST['exportType']) ? $_POST['exportType'] : null;
$idSRS = isset($_GET['idSRS']) ? $_GET['idSRS'] : null;
if (is_null($idSRS))
	$idSRS = isset($_POST['idSRS']) ? $_POST['idSRS'] : null;

if ($bExport)
{
	$reqData = getRequirements($db,$idSRS);
	$pfn = null;
	switch($exportType)
	{
		case 'csv':
			$pfn = "exportReqDataToCSV";
			$fileName = 'reqs.csv';
			break;
		case 'XML':
			$pfn = "exportReqDataToXML";
			$fileName = 'reqs.xml';
			break;
	}
	if ($pfn)
	{
		$content = $pfn($reqData);
		downloadContentsToFile($content,$fileName);
		exit();
	}
}

//export to csv doors is not support
$exportTypes = $g_reqImportTypes;
unset($exportTypes['csv_doors']);

$smarty = new TLSmarty();
$smarty->assign('idSRS', $idSRS);
$smarty->assign('importTypes',$exportTypes);
$smarty->display('reqexport.tpl');
?>