<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: reqexport.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/11/19 21:02:56 $ by $Author: franciscom $
 *
 * This page this allows users to export requirements. 
 *
**/
require_once("../../config.inc.php");
require_once("csv.inc.php");
require_once("xml.inc.php");
require_once("common.php");
require_once("requirements.inc.php");
require_once('requirement_spec_mgr.class.php');

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

$req_spec_mgr = new requirement_spec_mgr($db);
$export_types=$req_spec_mgr->get_export_file_types();


$smarty = new TLSmarty();
$smarty->assign('idSRS', $idSRS);
$smarty->assign('importTypes',$export_types);
$smarty->display('reqexport.tpl');
?>