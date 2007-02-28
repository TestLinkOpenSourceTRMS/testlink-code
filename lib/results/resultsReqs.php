<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: resultsReqs.php,v $
 * @version $Revision: 1.8 $
 * @modified $Date: 2007/02/28 07:56:09 $ by $Author: kevinlevy $
 * @author Martin Havlat
 * 
 * Report requirement based results
 *
 * 20060104 - fm - BUGID 0000311: Requirements based Report shows errors 
 *
 * 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');

testlinkInitPage($db);

$idSRS = isset($_GET['idSRS']) ? strings_stripSlashes($_GET['idSRS']) : null;
$prodID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;

//get list of available Req Specification
$arrReqSpec = getOptionReqSpec($db,$prodID);

//set the first ReqSpec if not defined via $_GET
if (!$idSRS && count($arrReqSpec))
{
	reset($arrReqSpec);
	$idSRS = key($arrReqSpec);
	tLog('Set a first available SRS ID: ' . $idSRS);
}

$arrCoverage = null;
$arrMetrics =  null;
if(!is_null($idSRS))
{
	$tp = new testplan($db);
	$tcs = $tp->get_linked_tcversions($tpID,null,0,1);
	
	$sql = "SELECT id,testcase_id,title,status FROM requirements LEFT OUTER JOIN req_coverage ON requirements.id = req_coverage.req_id WHERE status = 'v' AND srs_id = {$idSRS}"; 
	$reqs = $db->fetchRowsIntoMap($sql,'id',1);
	$execMap = getLastExecutions($db,$tcs,$tpID);
	$arrMetrics = getReqMetrics_general($db,$idSRS);
	$coveredReqs = 0;
	$arrCoverage = getReqCoverage($reqs,$execMap,$coveredReqs);

	$arrMetrics['coveredByTestPlan'] = sizeof($coveredReqs);
	$arrMetrics['uncoveredByTestPlan'] = $arrMetrics['expectedTotal'] - $arrMetrics['coveredByTestPlan'] - $arrMetrics['notTestable'];
}

$smarty = new TLSmarty();
$smarty->assign('arrMetrics', $arrMetrics);
$smarty->assign('arrCoverage', $arrCoverage);
$smarty->assign('arrReqSpec', $arrReqSpec);
$smarty->assign('selectedReqSpec', $idSRS);
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->display('resultsReqs.tpl');
?>
