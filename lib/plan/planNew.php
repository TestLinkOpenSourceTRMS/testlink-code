<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: planNew.php,v $
 *
 * @version $Revision: 1.19 $
 * @modified $Date: 2006/04/26 07:07:55 $ $Author: franciscom $
 *
 * Purpose:  Add new or edit existing Test Plan 
 *
 * 20051120 - fm - adding test plan filter by product behaivour
 * 20050915 - fm - refactoring function name
 * 20051125 - scs - added checking for duplicate tp names
 * 20060113 - scs - adding editing of tps
*/
require('../../config.inc.php');
require_once("../functions/common.php");
require_once("plan.inc.php");
require_once("../../third_party/fckeditor/fckeditor.php");
require_once('../functions/testplan.class.php'); // 20060319 - franciscom

testlinkInitPage($db);

$tplan_mgr = New testplan($db);

// ----------------------------------------------------------------------
// 20060101 - fm
$of = new fckeditor('notes') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet = 'TL_Medium';
$of->Value = null;
// ----------------------------------------------------------------------

$sqlResult = null;
$args = init_args($_REQUEST,$_SESSION);
$tpName = null;
$bActive = 0;
$bNewTestPlan = isset($_POST['newTestPlan']) ? $_REQUEST['newTestPlan'] : 0;
$bEditTestPlan = isset($_POST['editTestPlan']) ? $_POST['editTestPlan'] : 0;

//get testplan info
if($args->tpID && !($bNewTestPlan || $bEditTestPlan))
{
	$tpInfo = getAllTestPlans($db,$args->testprojectID,TP_ALL_STATUS,FILTER_BY_PRODUCT,$args->tpID);
	if (sizeof($tpInfo))
	{
		$tpInfo = $tpInfo[0];
		$notes = $tpInfo['notes'];
		$of->Value = $notes;
		$tpName = $tpInfo['name'];
		$bActive = $tpInfo['active'];
	}
}
else if($bNewTestPlan || $bEditTestPlan) 
{
	$of->Value = $args->notes;
	$tpName = $args->name;
	$bActive = ($args->active == 'on') ? 1 :0 ;
	
	if (!strlen($args->name))
		$sqlResult = lang_get('warning_empty_tp_name');
	else
	{
		$tp_id = 0;
		$sqlResult = 'ok';
		
		//20051125 - scs - added checking for duplicate tp names
		$plans = getAllTestPlans($db,$args->testprojectID,null,1);
		$bDuplicate = false;
		$num_plans = sizeof($plans);
		for($idx = 0; $idx < $num_plans; $idx++)
		{
			if ($plans[$idx]['name'] == $args->name)
			{
				//if we edit the edited tp must be skipped!
				if ($bNewTestPlan || ($bEditTestPlan && ($args->tpID != $plans[$idx]['id'])))
				{
					$bDuplicate = true;
					break;
				}
			}
		}
		if (!$bDuplicate)
		{
			if ($bNewTestPlan)
			{
				// 20060319 - franciscom
				$tp_id = $tplan_mgr->create($args->name,$args->notes,$args->testprojectID);
				
				if ($tp_id == 0)
					$sqlResult = $db->error_msg();
				$result = insertTestPlanPriorities($db, $tp_id);
				
				if($args->rights == 'on')
					$result = insertTestPlanUserRight($db, $tp_id,$args->userID);
		    
				if($args->copy) 
					copy_deep_testplan($db, $args->source_tpid, $tp_id);
			}
			else
			{
				if (!updateTestPlan($db,$args->tpID,$args->name,$args->notes,$bActive))
				{
					$sqlResult = lang_get('update_tp_failed1'). $tpName . lang_get('update_tp_failed2').": " . 
					                  $db->error_msg() . "<br />";
				}
				else
				{
					if (isset($_SESSION['testPlanId']) && ($args->tpID == $_SESSION['testPlanId']))
						$_SESSION['testPlanName'] = $args->name;
				}
			}
		}
		else
			$sqlResult = lang_get('duplicate_tp_name');
	}
	//if all was ok, the gui is cleared	
	if ($sqlResult == 'ok')
	{
		$args->tpID = 0;
		$tpName = '';
		$bActive = 1;
		$of->Value = null;
	}
}

$smarty = new TLSmarty();
$smarty->assign('tpID',$args->tpID);
$smarty->assign('tpName', $tpName);
$smarty->assign('tpActive', $bActive);
$smarty->assign('prod_name', $args->testprojectName);
$smarty->assign('arrPlan', getAllActiveTestPlans($db,$args->testprojectID,FILTER_BY_PRODUCT));
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('notes', $of->CreateHTML());
$smarty->display('planNew.tpl');

/*
 * INITialize page ARGuments, using the $_REQUEST and $_SESSION
 * super-global hashes.
 * Important: changes in HTML input elements on the Smarty template
 *            must be reflected here.
 *
 *  
 * @parameter hash request_hash the $_REQUEST
 * @parameter hash session_hash the $_SESSION
 * @return    object with html values tranformed and other
 *                   generated variables.
 *
 * 20060103 - fm 
*/
function init_args($request_hash, $session_hash)
{
	$request_hash = strings_stripSlashes($request_hash);
	
	$nullable_keys = array('name','notes','rights','active');
	foreach($nullable_keys as $value)
	{
		$args->$value = isset($request_hash[$value]) ? $request_hash[$value] : null;
	}
	
	$intval_keys = array('copy' => 0,'tpID' => 0, 'tpID' => 0);
	foreach($intval_keys as $key => $value)
	{
		$args->$key = isset($request_hash[$key]) ? intval($request_hash[$key]) : $value;
	}
	$args->source_tpid = $args->copy;
	$args->copy = ($args->copy > 0) ? TRUE : FALSE;
	
	$args->testprojectID   = $session_hash['testprojectID'];
	$args->testprojectName = $session_hash['testprojectName'];
	$args->userID      = $session_hash['userID'];
	
	return $args;
}
?>