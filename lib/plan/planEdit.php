<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: planEdit.php,v 1.4 2005/09/15 17:00:14 franciscom Exp $ */
/* Purpose:  ability to edit and delete projects */
/* TODO: I need to add the deletion of project rights
 *	I need to delete the projects builds
 */
require('../../config.inc.php');
require("../functions/common.php");
require("plan.inc.php");
require("../functions/builds.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

// if requested an update
$generalResult = null;
if(isset($_POST['editTestPlan']))
{
	//It is necessary to turn the $_POST map into a number valued array so i can loop through it
	$newArray = extractInput(true);
	
	$test = array_pop($newArray);
	$i = 0;

	while ($i < (count($newArray))) //Loop for the entire size of the array
	{
		$id = ($newArray[$i]);
		$name = $newArray[$i + 1];
		$notes = ($newArray[$i + 2]);
		$active = ($newArray[$i + 3]);
		$safeName = htmlspecialchars($name);
		
		$editResult = '';
		if(isset($newArray[$i + 4] ) && ($newArray[$i + 4] == 'on')) //if the user has selected to delete the project
		{
			$i = $i + 5;

			//Select all of the projects priority fields
			if (!deleteTestPlanPriorityFields($id))
				$editResult .= lang_get('delete_tp_priority_failed1'). $safeName. lang_get('delete_tp_priority_failed2') . ": <br />".mysql_error()."<br />";

			//Select all of the projects milestones
			if (!deleteTestPlanMilestones($id))
				$editResult .= lang_get('delete_tp_milestones_failed1'). $safeName.lang_get('delete_tp_milestones_failed2') . ": <br />".mysql_error()."<br />";
	
			//Select all of the projects builds
			$builds = getBuilds($id);
			//SCHLUNDUS
			$buildIDList = sizeof($builds) ? array_keys($builds) : null;
			$buildIDList = sizeof($builds) ? implode(",",$buildIDList) : null;

			$catIDs = null;
			getTestPlanCategories($id,$catIDs);

      // 20050914 - fm 
			$tcIDs = getCategories_TC_ids($catIDs);
			
			//Delete all of the bugs associated with the project
			if (sizeof($catIDs) && strlen($buildIDList))
			{
				if (sizeof($tcIDs))
				{
					$tcIDList = implode(",",$tcIDs);
					$query = "DELETE FROM bugs WHERE tcid IN ({$tcIDList}) AND build IN ({$buildIDList})";
					if (!do_mysql_query($query))
						$editResult .= lang_get('delete_tp_bugs_failed1').$safeName.lang_get('delete_tp_bugs_failed2').": <br />".mysql_error()."<br />";
				}
			}
			
			//Delete all of the builds
			if (!deleteTestPlanBuilds($id))
				$editResult .= lang_get('delete_tp_builds_failed1').$safeName.lang_get('delete_tp_builds_failed2').": <br />".mysql_error()."<br />";
			
			if (sizeof($tcIDs))
			{
				$tcIDList = implode(",",$tcIDs);
				$query = "DELETE FROM results WHERE tcid IN ({$tcIDList})";
				$result = do_mysql_query($query);
				if (!$result)
					$editResult .= lang_get('delete_tp_results_failed1').$safeName.lang_get('delete_tp_results_failed2') . ": <br />".mysql_error()."<br />";

			}
			deleteTestCasesByCategories($catIDs);

			$comIDs = null;
			getTestPlanComponentIDs($id,$comIDs);
			deleteCategoriesByComponentIDs($comIDs);
			
			//Delete the components
			if (!deleteTestPlanComponents($id))
				$editResult .= lang_get('delete_tp_comp_failed1').$safeName. lang_get('delete_tp_comp_failed2').": <br />" .	mysql_error() . "<br />";

			if (!deleteTestPlanRightsForProject($id))
				$editResult .= lang_get('delete_tp_rights_failed1').$safeName.lang_get('delete_tp_rights_failed2').": <br />" .	mysql_error() . "<br />";
				
			//Finally delete the test plan
			if (!deleteTestPlan($id))
				$editResult .= lang_get('delete_tp_data_failed1').$safeName.lang_get('delete_tp_data_failed2').": <br /> ". mysql_error()."<br />";
			
			if ($editResult == '')
				$generalResult .= lang_get('delete_tp_succeeded1').$safeName. lang_get('delete_tp_succeeded2')."<br />";
			else
				$generalResult .= lang_get('delete_tp_failed1').$safeName. lang_get('delete_tp_failed2').": <br />" . $editResult . "<br />";
		}
		else //if the user has edited the data
		{
			$i = $i + 4;

			if (updateTestPlan($id,$name,$notes,$active))
				$generalResult .= lang_get('update_tp_succeeded1'). $safeName . lang_get('update_tp_succeeded2')."<br />";
			else
				$generalResult .= lang_get('update_tp_failed1'). $safeName . lang_get('update_tp_failed2').": " . mysql_error() . "<br />";
		} // if delete
	} // while POST array
}

$smarty = new TLSmarty;
$smarty->assign('editResult',$generalResult);
$smarty->assign('arrPlan', getAllTestPlans());
$smarty->display('planEdit.tpl');
?>
