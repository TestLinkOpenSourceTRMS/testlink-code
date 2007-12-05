<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: resultsReqs.php,v $
 * @version $Revision: 1.12 $
 * @modified $Date: 2007/12/05 07:47:09 $ by $Author: franciscom $
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
require_once('requirement_spec_mgr.class.php');


testlinkInitPage($db);

$template_dir='results/';

$idSRS = isset($_GET['idSRS']) ? strings_stripSlashes($_GET['idSRS']) : null;
$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;

$tproject_mgr=new testproject($db);
$req_spec_mgr = new requirement_spec_mgr($db); 

//get list of available Req Specification
$arrReqSpec = $tproject_mgr->getOptionReqSpec($tproject_id);

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
	$tplan_mgr = new testplan($db);
	$tcs = $tplan_mgr->get_linked_tcversions($tpID,null,0,1);
	
	// BUGID 1063
	$sql = " SELECT REQ.id, req_coverage.testcase_id,title,status, NH.name AS testcase_name " .
	       " FROM requirements REQ" .
	       " LEFT OUTER JOIN req_coverage ON REQ.id = req_coverage.req_id " .
	       " LEFT OUTER JOIN nodes_hierarchy NH ON req_coverage.testcase_id=NH.id " .
	       " WHERE status = '" . TL_REQ_STATUS_VALID . "' AND srs_id = {$idSRS}"; 
	       
	$reqs = $db->fetchRowsIntoMap($sql,'id',1);
	$execMap = getLastExecutions($db,$tcs,$tpID);
	$arrMetrics = $req_spec_mgr->get_metrics($idSRS);
	$coveredReqs = 0;
	$arrCoverage = getReqCoverage($reqs,$execMap,$coveredReqs);

	$arrMetrics['coveredByTestPlan'] = sizeof($coveredReqs);
	$arrMetrics['uncoveredByTestPlan'] = $arrMetrics['expectedTotal'] - $arrMetrics['coveredByTestPlan'] - $arrMetrics['notTestable'];
}

$smarty = new TLSmarty();


$allow_edit_tc = 0;
if( has_rights($db,"mgt_modify_tc") == 'yes')
{ 
  $allow_edit_tc = 1;
}

$smarty->assign('allow_edit_tc', $allow_edit_tc);
$smarty->assign('tproject_name', $_SESSION['testprojectName'] );
$smarty->assign('tplan_name', $_SESSION['testPlanName'] );
$smarty->assign('arrMetrics', $arrMetrics);
$smarty->assign('arrCoverage', $arrCoverage);
$smarty->assign('arrReqSpec', $arrReqSpec);
$smarty->assign('selectedReqSpec', $idSRS);
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->display($template_dir .'resultsReqs.tpl');
?>
