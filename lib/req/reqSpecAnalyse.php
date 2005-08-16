<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: reqSpecAnalyse.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:57 $
 * @author Martin Havlat
 * 
 * Analyse coverage of a req. specification.
 * 
 */
////////////////////////////////////////////////////////////////////////////////

require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');

// init page 
testlinkInitPage();

$idSRS = isset($_GET['idSRS']) ? strings_stripSlashes($_GET['idSRS']) : null;

//get list of ReqSpec
$arrReqSpec = getOptionReqSpec();

//get first ReqSpec if not defined
if (!$idSRS && count($arrReqSpec)) {
	reset($arrReqSpec);
	$idSRS = key($arrReqSpec);
	tLog('Set a first available SRS ID: ' . $idSRS);
}

// collect REQ data
$arrCoverage = getSRSCoverage($idSRS, $_SESSION['testPlanId']);
$arrMetrics = getReqCoverageMetrics($idSRS);

$smarty = new TLSmarty;
$smarty->assign('arrMetrics', $arrMetrics);
$smarty->assign('arrCoverage', $arrCoverage);
$smarty->assign('arrReqSpec', $arrReqSpec);
$smarty->assign('selectedReqSpec', $idSRS);
$smarty->assign('modify_req_rights', has_rights("mgt_modify_req")); 
$smarty->display('reqSpecAnalyse.tpl');
?>
