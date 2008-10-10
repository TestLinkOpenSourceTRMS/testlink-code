<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: reqTcAssign.php,v $
 * @version $Revision: 1.7 $
 * @modified $Date: 2008/10/10 19:35:12 $  $Author: schlundus $
 * 
 * @author Martin Havlat
 *
 * 20080512 - franciscom - new input argument to control display/hide of close button
 * 20070617 - franciscom - refactoring
 * 20070124 - franciscom
 * use show_help.php to apply css configuration to help pages
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
require_once('requirement_spec_mgr.class.php');
require_once('requirement_mgr.class.php');

testlinkInitPage($db);

$templateCfg = templateConfiguration();

$tproject_mgr = new testproject($db);
$req_spec_mgr = new requirement_spec_mgr($db);
$req_mgr = new requirement_mgr($db);

$user_feedback = null;
$arrAssignedReq = null;
$arrUnassignedReq = null;
$tcTitle = null;
$tmpResult = null;
$args=init_args();
$gui = new stdClass();
$gui->showCloseButton = $args->showCloseButton;

// add or remove dependencies TC - REQ
switch($args->doAction)
{
    case 'assign':
	    $pfn = "assign_to_tcase";
	    break;  

    case 'unassign':
	    $pfn = "unassign_from_tcase";
	    break;  
}

if (!is_null($args->doAction))
{
	$req_ids = array_keys($args->reqIdSet);
	if (count($req_ids))
	{
		foreach ($req_ids as $idOneReq)
		{
			$result = $req_mgr->$pfn($idOneReq,$args->tc_id);

			if (!$result)
				$tmpResult .= $idOneReq . ', ';
		}
		if (!empty($tmpResult))
			$user_feedback = lang_get('req_msg_notupdated_coverage') . $tmpResult;
	}
	else
		$user_feedback = lang_get('req_msg_noselect');
}


// redirect if a user doesn't choose test case 
if ($args->edit == 'testproject' || $args->edit == 'testsuite')
{
	show_instructions('assignReqs');
	exit();
} 
else if($args->edit == 'testcase')
{
	//get list of ReqSpec (not_empty)
	$get_not_empty = 1;
	$arrReqSpec = $tproject_mgr->getOptionReqSpec($args->tproject_id,$get_not_empty);

	$SRS_qty = count($arrReqSpec);
	  
	if($SRS_qty > 0)
	{
	  	$tc_mgr = new testcase($db);
	  	$arrTc = $tc_mgr->get_by_id($args->tc_id);
	  	if ($arrTc)
	  	{
	  		$tcTitle = $arrTc[0]['name'];
	  	
	  		//get first ReqSpec if not defined
	  		if (!$args->idReqSpec && $SRS_qty > 0)
	  		{
	  			reset($arrReqSpec);
	  			$args->idReqSpec = key($arrReqSpec);
	  			tLog('Set first SRS ID: ' . $args->idReqSpec);
	  		}
	  		
	  		if ($args->idReqSpec)
	  		{
	  			$arrAssignedReq = $req_spec_mgr->get_requirements($args->idReqSpec, 'assigned', $args->tc_id);
	  			$arrAllReq = $req_spec_mgr->get_requirements($args->idReqSpec);
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
$smarty->assign('gui', $gui);
$smarty->assign('user_feedback', $user_feedback);
$smarty->assign('tcTitle',$tcTitle);
$smarty->assign('arrUnassignedReq', $arrUnassignedReq);
$smarty->assign('arrReqSpec', $arrReqSpec);
$smarty->assign('arrAssignedReq', $arrAssignedReq);
$smarty->assign('selectedReqSpec', $args->idReqSpec);
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: init_args()

  args:
  
  returns: 

*/
function init_args()
{
    $args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $args->tc_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
    $args->edit = isset($_REQUEST['edit']) ? $_REQUEST['edit'] : null;
    $args->idReq = isset($_REQUEST['req']) ? intval($_REQUEST['req']) : null;
    $args->idReqSpec = isset($_REQUEST['idSRS']) ? intval($_REQUEST['idSRS']) : null;
    $args->reqIdSet = isset($_REQUEST['req_id']) ? $_REQUEST['req_id'] : null;
    $args->showCloseButton = isset($_REQUEST['showCloseButton']) ? 1 : 0;
    $args->doAction = isset($_REQUEST['assign']) ? 'assign' : null;
    if(is_null($args->doAction))
        $args->doAction = isset($_REQUEST['unassign']) ? 'unassign' : null;
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

    return $args;
}
?>