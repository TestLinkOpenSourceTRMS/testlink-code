<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 
 *
 * Filename $RCSfile: planEdit.php,v $
 *
 * @version $Revision: 1.24 $
 * @modified $Date: 2007/01/02 22:02:33 $ by $Author: franciscom $
 *
 * Purpose:  ability to edit and delete test plans
 *-------------------------------------------------------------------------
 * Revisions:
 * 	20061119 - mht - refactorization
 *
 */
require('../../config.inc.php');
require_once("../functions/common.php");
require_once("plan.inc.php");
require_once('../functions/testplan.class.php'); // 20060319 - franciscom
testlinkInitPage($db);

// the page could [list,create,update,delete,view,empty]
$action = isset($_GET['action']) ? $_GET['action'] : "list";
tLog("Edit Test plan: " + $action, 'INFO');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$bNewTestPlan = isset($_POST['newTestPlan']) ? $_REQUEST['newTestPlan'] : 0;
$bEditTestPlan = isset($_POST['editTestPlan']) ? $_POST['editTestPlan'] : 0;

$tplan_mgr = new testplan($db);
$generalResult = null;

// from planNew
$tpName = null;
$bActive = 0;

	// 20060101 - fm
	require_once("../../third_party/fckeditor/fckeditor.php");
	$of = new fckeditor('notes') ;
	$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
	$of->ToolbarSet = 'TL_Medium';
	$of->Value = null;

// delete test plan
if ($action == "delete")
{
	tLog("requested delete Test plan id=" + $id, 'INFO');
    $tplan_mgr->delete($id);
    
	//unset the session tp if its deleted
	if (isset($_SESSION['testPlanId']) && ($_SESSION['testPlanId'] = $id))
	{
		$_SESSION['testPlanId'] = 0;
		$_SESSION['testPlanName'] = null;
	}
}
	

//get testplan info
//if($args->tpID && !($bNewTestPlan || $bEditTestPlan))
elseif ($action == "view" || $action == "empty")
{
	$args = init_args($_REQUEST,$_SESSION);

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
elseif ($bNewTestPlan || $bEditTestPlan) 
{
	$args = init_args($_REQUEST,$_SESSION);

	$of->Value = $args->notes;
	$tpName = $args->testplan_name;
	$bActive = ($args->active == 'on') ? 1 :0 ;
	
	if (!strlen($args->testplan_name))
		$generalResult = lang_get('warning_empty_tp_name');
	else
	{
		$tp_id = 0;
		$generalResult = 'ok';
		
		//20051125 - scs - added checking for duplicate tp names
		$plans = getAllTestPlans($db,$args->testprojectID,null,1);
		$bDuplicate = false;
		$num_plans = sizeof($plans);
		for($idx = 0; $idx < $num_plans; $idx++)
		{
			if ($plans[$idx]['name'] == $args->testplan_name)
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
				$tp_id = $tplan_mgr->create($args->testplan_name,$args->notes,$args->testprojectID);
				
				if ($tp_id == 0)
				{
					$generalResult = $db->error_msg();
				}	
				else
        {
				$result = insertTestPlanPriorities($db, $tp_id);
				
				if($args->rights == 'on')
					$result = insertTestPlanUserRight($db, $tp_id,$args->userID);
		    
				if($args->copy) 
					//copy_deep_testplan($db, $args->source_tpid, $tp_id);
 					$tplan_mgr->copy_as($args->source_tpid, $tp_id);

			  }
			
			}
			else
			{
			  // 20060805 - franciscom - function call replaced with method call.
				if (!$tplan_mgr->update($args->tpID,$args->testplan_name,$args->notes,$bActive))
				{
					$generalResult = lang_get('update_tp_failed1'). $tpName . lang_get('update_tp_failed2').": " . 
					                  $db->error_msg() . "<br />";
				}
				else
				{
					if (isset($_SESSION['testPlanId']) && ($args->tpID == $_SESSION['testPlanId']))
						$_SESSION['testPlanName'] = $args->testplan_name;
				}
			}
		}
		else
			$generalResult = lang_get('duplicate_tp_name');
	}
	//if all was ok, the gui is cleared	
	if ($generalResult == 'ok')
	{
		$args->tpID = 0;
		$tpName = '';
		$bActive = 1;
		$of->Value = null;
		if ($action == "create"){
			$generalResult = lang_get('testplan_result_created');
		}
		elseif ($action == "update"){
			$generalResult = lang_get('testplan_result_updated');
		}
	}
}

// ----------------------------------------------------------------------
// render GUI
$smarty = new TLSmarty();

if ($action == "list" || $action == "delete" || $action == "create" || $action == "update")
{
	$smarty->assign('editResult',$generalResult);
	$smarty->assign('arrPlan', getAllTestPlans($db, $_SESSION['testprojectID'], 
		TP_ALL_STATUS, FILTER_BY_PRODUCT));
	$smarty->display('planEdit.tpl');
}

elseif ($action == "view" || $action == "empty")
{
	$smarty->assign('tpID',$args->tpID);
	$smarty->assign('tpName', $tpName);
	$smarty->assign('tpActive', $bActive);
	$smarty->assign('prod_name', $args->testprojectName);
	$smarty->assign('arrPlan', getAllActiveTestPlans($db,$args->testprojectID,FILTER_BY_PRODUCT));
	$smarty->assign('sqlResult', $generalResult);
	$smarty->assign('notes', $of->CreateHTML());
	$smarty->display('planNew.tpl');
}
else
{
	die("Invalid action parameter");
}	

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
	$args = null;
	$request_hash = strings_stripSlashes($request_hash);
	
	$nullable_keys = array('testplan_name','notes','rights','active');
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
