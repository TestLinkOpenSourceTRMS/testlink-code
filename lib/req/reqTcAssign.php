<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: reqTcAssign.php,v $
 * @version $Revision: 1.6 $
 * @modified $Date: 2006/02/15 08:50:19 $
 * 
 * @author Martin Havlat
 * 
 * print a req. specification.
 * 
 */
////////////////////////////////////////////////////////////////////////////////
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
require_once('../testcases/archive.inc.php');

// init page 
testlinkInitPage($db);

$action = null;
$sqlResult = null;
$arrAssignedReq = null;
$arrUnassignedReq = null;

$idTc = isset($_GET['data']) ? intval($_GET['data']) : null;
$edit = isset($_GET['edit']) ? strings_stripSlashes($_GET['edit']) : null;

$idReq = isset($_POST['req']) ? intval($_POST['req']) : null;
$idReqSpec = isset($_POST['idSRS']) ? intval($_POST['idSRS']) : null;

$doAssign = isset($_POST['assign']);
$doUnassign = isset($_POST['unassign']);

// 20050906 - fm
$prodID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

// add or remove dependencies TC - REQ
if ($doAssign || $doUnassign) {
	
	$arrIdReq = array_keys($_POST); // obtain names(id) of REQs
	array_shift($arrIdReq);	// remove idSRS
	array_pop($arrIdReq);	// remove submit button
	
	if (count($arrIdReq) > 0) {
		foreach ($arrIdReq as $idOneReq) {
			if ($doAssign) {
				$result = assignTc2Req($idTc, $idOneReq);
			} elseif ($doUnassign) {
				$result = unassignTc2Req($idTc, $idOneReq);
			}
			if (!$result) {
				$tmpResult .= $idOneReq . ', ';
			}
		}
		if (empty($tmpResult)) {
			$sqlResult = 'ok';
		} else {
			$sqlResult = lang_get('req_msg_notupdated_coverage') . $tmpResult;
		}
		$action = 'assigned';
	} else {
		$sqlResult = lang_get('req_msg_noselect');
	}
}


// redirect if a user doesn't choose test case 
if ($edit == 'product' || $edit == 'component' || $edit ==  'category')
{
	redirect($_SESSION['basehref'] . $g_rpath['help'] . '/assignReqs.html');
	exit();
} 
//If the user has chosen a testcase
else if($edit == 'testcase')
{
	//get list of ReqSpec
	$arrReqSpec = getOptionReqSpec($db,$prodID);

	//get TC title
	$arrTc = getTestcase($db,$idTc);
	
	//get first ReqSpec if not defined
	if (!$idReqSpec && count($arrReqSpec)) {
		reset($arrReqSpec);
		$idReqSpec = key($arrReqSpec);
		tLog('Set first SRS ID: ' . $idReqSpec);
	}
	
	// collect REQ data
	if ($idReqSpec) {
		// get assigned REQs
		$arrAssignedReq = getRequirements($db,$idReqSpec, 'assigned', $idTc);
		$arrAllReq = getRequirements($db,$idReqSpec);

		// get unassigned REQs
		$arrUnassignedReq = array_diff_byId($arrAllReq, $arrAssignedReq);
	}
	
}
else
{
	tlog("Wrong GET/POST arguments.", 'ERROR');
	exit();
}

$smarty = new TLSmarty;
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('action', $action);
$smarty->assign('tcTitle', $arrTc['title']);
$smarty->assign('arrUnassignedReq', $arrUnassignedReq);
$smarty->assign('arrReqSpec', $arrReqSpec);
$smarty->assign('arrAssignedReq', $arrAssignedReq);
$smarty->assign('selectedReqSpec', $idReqSpec);
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 
$smarty->display('reqAssign.tpl');
?>
