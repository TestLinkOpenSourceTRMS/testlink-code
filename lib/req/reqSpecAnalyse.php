<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqSpecAnalyse.php,v $
 * @version $Revision: 1.4 $
 * @modified $Date: 2005/09/07 06:23:36 $ by $Author: franciscom $
 * @author Martin Havlat
 * 
 * Analyse coverage of a req. specification.
 * 
 * revision:
 * 20050901 - MHT - removed TestPlan related data; file header update
 */
////////////////////////////////////////////////////////////////////////////////

require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');

// init page 
testlinkInitPage();

$idSRS = isset($_GET['idSRS']) ? strings_stripSlashes($_GET['idSRS']) : null;

// 20050906 - fm
$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;

//get list of ReqSpec
$arrReqSpec = getOptionReqSpec($prodID);

//get first ReqSpec if not defined
if (!$idSRS && count($arrReqSpec)) {
	reset($arrReqSpec);
	$idSRS = key($arrReqSpec);
	tLog('Set a first available SRS ID: ' . $idSRS);
}

// collect REQ data
$arrCoverage = getReqCoverage_general($idSRS);
$arrMetrics = getReqMetrics_general($idSRS);

$smarty = new TLSmarty;
$smarty->assign('arrMetrics', $arrMetrics);
$smarty->assign('arrCoverage', $arrCoverage);
$smarty->assign('arrReqSpec', $arrReqSpec);
$smarty->assign('selectedReqSpec', $idSRS);
$smarty->assign('modify_req_rights', has_rights("mgt_modify_req")); 
$smarty->display('reqSpecAnalyse.tpl');
?>
