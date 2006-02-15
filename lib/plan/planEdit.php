<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: planEdit.php,v $
 *
 * @version $Revision: 1.16 $
 * @modified $Date: 2006/02/15 08:49:20 $ $Author: franciscom $
 *
 * Purpose:  ability to edit and delete testplans
 *
 * 20051009 - fm - bug in delete from bugs
 * 20051119 - scs - it was possible to save empty tp-names
 * 20051120 - fm - filtering TP by Product
 * 20060114 - scs - removed bulk update
 */
require('../../config.inc.php');
require("../functions/common.php");
require("plan.inc.php");
require("../functions/builds.inc.php");
testlinkInitPage($db);

$prodID = $_SESSION['testprojectID'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$bDelete = isset($_GET['deleteTP']) ? intval($_GET['deleteTP']) : 0;

$generalResult = null;
$editResult = null;
if($bDelete)
{
	$safeName = "";
	$tpInfo = getAllTestPlans($db,null,null,null,$id);
	if (sizeof($tpInfo))
	{
		$tpInfo = $tpInfo[0];
		$safeName = htmlspecialchars($tpInfo['name']);
	}
	if (!deleteTestPlanPriorityFields($db,$id))
		$editResult .= lang_get('delete_tp_priority_failed1'). $safeName. lang_get('delete_tp_priority_failed2') . 
		               ": <br />".$db->error_msg()."<br />";

	if (!deleteTestPlanMilestones($db,$id))
	{
		$editResult .= lang_get('delete_tp_milestones_failed1'). $safeName.lang_get('delete_tp_milestones_failed2') . 
		               ": <br />" . $db->error_msg()."<br />";
	}
   
	// 20051002 - fm - added order by
	$builds = getBuilds($db,$id, " ORDER BY build.name ");
	
	$buildIDList = sizeof($builds) ? array_keys($builds) : null;
	$buildIDList = sizeof($builds) ? implode(",",$buildIDList) : null;

	$catIDs = null;
	getTestPlanCategories($db,$id,$catIDs);

	// 20050914 - fm 
	$tcIDs = getCategories_TC_ids($db,$catIDs);
	
	//Delete all of the bugs associated with the testplans
	if (sizeof($catIDs) && strlen($buildIDList))
	{
		if (sizeof($tcIDs))
		{
			$tcIDList = implode(",",$tcIDs);
			// 20051009 - fm - my bug
			$query = "DELETE FROM bugs WHERE tcid IN ({$tcIDList}) AND build_id IN ({$buildIDList})";
			if (!$db->exec_query($query))
			{
				$editResult .= lang_get('delete_tp_bugs_failed1').$safeName.lang_get('delete_tp_bugs_failed2').
				               ": <br />". $db->error_msg()."<br />";
			}	               
		}
	}
	
	//Delete all of the builds
	if (!deleteTestPlanBuilds($db,$id))
	{
		$editResult .= lang_get('delete_tp_builds_failed1').$safeName.lang_get('delete_tp_builds_failed2').
		               ": <br />".$db->error_msg()."<br />";
	}
	if (sizeof($tcIDs))
	{
		$tcIDList = implode(",",$tcIDs);
		$query = "DELETE FROM results WHERE tcid IN ({$tcIDList})";
		$result = $db->exec_query($query);
	
		if (!$result)
		{
			$editResult .= lang_get('delete_tp_results_failed1').$safeName.lang_get('delete_tp_results_failed2') . 
			               ": <br />". $db->error_msg()."<br />";
      }
	}
	deleteTestCasesByCategories($db,$catIDs);

    // 20051001 - fm
	$comIDs = getTestPlanComponentIDs($db,$id);
	deleteCategoriesByComponentIDs($db,$comIDs);
	
	//Delete the components
	if (!deleteTestPlanComponents($db,$id))
	{
		$editResult .= lang_get('delete_tp_comp_failed1').$safeName. 
		               lang_get('delete_tp_comp_failed2').": <br />" .	$db->error_msg() . "<br />";
    } 
	
	if (!deleteTestPlanRightsForTestPlan($db,$id))
	{
		$editResult .= lang_get('delete_tp_rights_failed1'). $safeName . 
		               lang_get('delete_tp_rights_failed2').": <br />" .	$db->error_msg() . "<br />";
	}	
	
	//Finally delete the test plan
	if (!deleteTestPlan($db,$id))
	{
		$editResult .= lang_get('delete_tp_data_failed1'). $safeName . 
		               lang_get('delete_tp_data_failed2').": <br /> ". $db->error_msg()."<br />";
	}
	else
	{
		//unset the session tp if its deleted
		if (isset($_SESSION['testPlanId']) && ($_SESSION['testPlanId'] = $id))
		{
			$_SESSION['testPlanId'] = 0;
			$_SESSION['testPlanName'] = null;
		}
	}
	if ($editResult == '')
		$generalResult .= lang_get('delete_tp_succeeded1').$safeName. lang_get('delete_tp_succeeded2')."<br />";
	else
		$generalResult .= lang_get('delete_tp_failed1').$safeName. lang_get('delete_tp_failed2').": <br />" . 
		                  $editResult . "<br />";
}

$smarty = new TLSmarty;
$smarty->assign('editResult',$generalResult);
$smarty->assign('arrPlan', getAllTestPlans($db,$prodID,TP_ALL_STATUS,FILTER_BY_PRODUCT));
$smarty->display('planEdit.tpl');
?>
