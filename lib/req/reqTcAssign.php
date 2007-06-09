<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: reqTcAssign.php,v $
 * @version $Revision: 1.12 $
 * @modified $Date: 2007/06/09 19:36:23 $  $Author: schlundus $
 * 
 * @author Martin Havlat
 *
 * 20070124 - franciscom
 * use show_help.php to apply css configuration to help pages
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
testlinkInitPage($db);

$action = null;
$sqlResult = null;
$arrAssignedReq = null;
$arrUnassignedReq = null;
$tcTitle = null;

$tc_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$edit = isset($_GET['edit']) ? strings_stripSlashes($_GET['edit']) : null;

$idReq = isset($_POST['req']) ? intval($_POST['req']) : null;
$idReqSpec = isset($_POST['idSRS']) ? intval($_POST['idSRS']) : null;

$doAssign = isset($_POST['assign']);
$doUnassign = isset($_POST['unassign']);

$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

$tmpResult = null;
// add or remove dependencies TC - REQ
if ($doAssign || $doUnassign)
{
	$arrIdReq = array_keys($_POST); // obtain names(id) of REQs
	array_shift($arrIdReq);	// remove idSRS
	array_pop($arrIdReq);	// remove submit button
	
	if (count($arrIdReq))
	{
		foreach ($arrIdReq as $idOneReq)
		{
			if ($doAssign)
				$result = assignTc2Req($db,$tc_id, $idOneReq);
			else if ($doUnassign)
				$result = unassignTc2Req($db,$tc_id, $idOneReq);
			if (!$result)
				$tmpResult .= $idOneReq . ', ';
		}
		if (empty($tmpResult))
			$sqlResult = 'ok';
		else
			$sqlResult = lang_get('req_msg_notupdated_coverage') . $tmpResult;
		
		if ($doAssign)
			$action = 'assigned';
		else if ($doUnassign)
			$action = 'unassigned';
	}
	else
		$sqlResult = lang_get('req_msg_noselect');
}

// redirect if a user doesn't choose test case 
if ($edit == 'testproject' || $edit == 'testsuite')
{
  
	// redirect($_SESSION['basehref'] . $g_rpath['help'] . '/assignReqs.html');
 	redirect($_SESSION['basehref'] . "/lib/general/show_help.php?help=assignReqs&locale={$_SESSION['locale']}");
	exit();
} 
else if($edit == 'testcase')
{
	//get list of ReqSpec (not_empty)
	$get_not_empty=1;
	$arrReqSpec = getOptionReqSpec($db,$tproject_id,$get_not_empty);

  $SRS_qty=count($arrReqSpec);
  
  if( $SRS_qty > 0 )
  {
  	$tc_mgr = new testcase($db);
  	$arrTc = $tc_mgr->get_by_id($tc_id);
  	if ($arrTc)
  	{
  		$tcTitle = $arrTc[0]['name'];
  	
  		//get first ReqSpec if not defined
  		if (!$idReqSpec && $SRS_qty > 0)
  		{
  			reset($arrReqSpec);
  			$idReqSpec = key($arrReqSpec);
  			tLog('Set first SRS ID: ' . $idReqSpec);
  		}
  		
  		if ($idReqSpec)
  		{
  			$arrAssignedReq = getRequirements($db,$idReqSpec, 'assigned', $tc_id);
  			$arrAllReq = getRequirements($db,$idReqSpec);
  			$arrUnassignedReq = array_diff_byId($arrAllReq, $arrAssignedReq);
  		}
  	}
  }  // if( $SRS_qty > 0 )	
}
else
{
	tlog("Wrong GET/POST arguments.", 'ERROR');
	exit();
}

$smarty = new TLSmarty();
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('action', $action);
$smarty->assign('tcTitle',$tcTitle);
$smarty->assign('arrUnassignedReq', $arrUnassignedReq);
$smarty->assign('arrReqSpec', $arrReqSpec);
$smarty->assign('arrAssignedReq', $arrAssignedReq);
$smarty->assign('selectedReqSpec', $idReqSpec);
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 
$smarty->display('reqAssign.tpl');
?>
