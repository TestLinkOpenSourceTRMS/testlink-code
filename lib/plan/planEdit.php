<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: planEdit.php,v 1.13 2006/01/09 07:19:06 franciscom Exp $ */
/* Purpose:  ability to edit and delete testplanss */
/* TODO: I need to add the deletion of testplans rights
 *	I need to delete the testplanss builds
 *
 *
 * @author Francisco Mancardi - 20051009 - bug in delete from bugs
 * 20051119 - scs - it was possible to save empty tp-names
 * @author Francisco Mancardi - 20051120 - filtering TP by Product
 */
require('../../config.inc.php');
require("../functions/common.php");
require("plan.inc.php");
require("../functions/builds.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage($db);

// 20051120 - fm
// The current selected Product
$prod->id   = $_SESSION['productID'];
$prod->name = $_SESSION['productName'];


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
		if(isset($newArray[$i + 4] ) && ($newArray[$i + 4] == 'on')) //if the user has selected to delete the testplans
		{
			$i = $i + 5;

			//Select all of the testplanss priority fields
			if (!deleteTestPlanPriorityFields($id))
				$editResult .= lang_get('delete_tp_priority_failed1'). $safeName. lang_get('delete_tp_priority_failed2') . 
				               ": <br />".$db->error_msg()."<br />";

			//Select all of the testplanss milestones
			if (!deleteTestPlanMilestones($id))
			{
				$editResult .= lang_get('delete_tp_milestones_failed1'). $safeName.lang_get('delete_tp_milestones_failed2') . 
				               ": <br />" . $db->error_msg()."<br />";
	    }
	    
			//Select all of the testplanss builds
			// 20051002 - fm - order by
			$builds = getBuilds($db,$id, " ORDER BY build.name ");
			
			//SCHLUNDUS
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
			
			if ($editResult == '')
				$generalResult .= lang_get('delete_tp_succeeded1').$safeName. lang_get('delete_tp_succeeded2')."<br />";
			else
				$generalResult .= lang_get('delete_tp_failed1').$safeName. lang_get('delete_tp_failed2').": <br />" . 
				                  $editResult . "<br />";
		}
		else //if the user has edited the data
		{
			$i = $i + 4;
			
			if (strlen($name))
			{
				if (updateTestPlan($db,$id,$name,$notes,$active))
					$generalResult .= lang_get('update_tp_succeeded1'). $safeName . lang_get('update_tp_succeeded2')."<br />";
				else
					$generalResult .= lang_get('update_tp_failed1'). $safeName . lang_get('update_tp_failed2').": " . 
					                  $db->error_msg() . "<br />";
			}
			else
				$generalResult .= lang_get('update_tp_failed1'). $safeName . lang_get('update_tp_failed2').": " . 
								lang_get('warning_empty_tp_name')."<br />";
				
		} // if delete
	} // while POST array
}

$smarty = new TLSmarty;
$smarty->assign('editResult',$generalResult);
// 20051120 - fm
//$smarty->assign('arrPlan', getAllTestPlans());
$smarty->assign('arrPlan', getAllTestPlans($db,$prod->id,TP_ALL_STATUS,FILTER_BY_PRODUCT));
$smarty->display('planEdit.tpl');
?>
