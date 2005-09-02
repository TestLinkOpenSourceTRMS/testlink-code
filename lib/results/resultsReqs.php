<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: resultsReqs.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2005/09/02 09:54:37 $ by $Author: havlat $
 * @author Martin Havlat
 * 
 * Report requirement based results
 * 
 */
////////////////////////////////////////////////////////////////////////////////

require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
require_once('results.inc.php');

// init page 
testlinkInitPage();

$idSRS = isset($_GET['idSRS']) ? strings_stripSlashes($_GET['idSRS']) : null;

//get list of available Req Specification
$arrReqSpec = getOptionReqSpec();

//set the first ReqSpec if not defined via $_GET
if (!$idSRS && count($arrReqSpec)) {
	reset($arrReqSpec);
	$idSRS = key($arrReqSpec);
	tLog('Set a first available SRS ID: ' . $idSRS);
}

// collect REQ data
$arrCoverage = getReqCoverage_testPlan($idSRS, $_SESSION['testPlanId']);
$arrMetrics = getReqMetrics_testPlan($idSRS, $_SESSION['testPlanId']);

$smarty = new TLSmarty;
$smarty->assign('arrMetrics', $arrMetrics);
$smarty->assign('arrCoverage', $arrCoverage);
$smarty->assign('arrReqSpec', $arrReqSpec);
$smarty->assign('selectedReqSpec', $idSRS);
$smarty->display('resultsReqs.tpl');
?>
