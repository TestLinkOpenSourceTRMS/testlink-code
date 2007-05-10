<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @filesource $RCSfile: plan.inc.php,v $
 * @version $Revision: 1.45 $
 * @modified $Date: 2007/05/10 07:07:15 $ $Author: franciscom $
 * @author 	Martin Havlat
 *
 * Functions for management: 
 * Test Plans, Test Case Suites, Milestones, Testers assignment
 *
 * 20070121 - franciscom - deprecated insertTestPlanBuild()
 *                         use testplan->create_build() method
 *
 * 20070119 - franciscom - BUGID 510 
 * 
 */

/** include core functions for collect information about Test Plans */
require_once("plan.core.inc.php"); 


/*
  20051001 - fm - refactoring
  1 -> delete OK or nothing done
  0 -> problems
*/
function deleteCategoriesByComponentIDs(&$db,$comIDs)
{
	$ret_val = 1;
	if(sizeof($comIDs))
	{
		$comIDs = implode(",",$comIDs);
		$sql = "DELETE FROM category WHERE compid IN (" . $comIDs . ")";
		$result = $db->exec_query($sql);
		
		$ret_val = $result ? 1 : 0;
	}
	return $ret_val;
}


function getTestPlanCategories(&$db,$id,&$catIDs)
{
	//Select all of the testplanss components
	$sql = " SELECT category.id FROM component, category " .
	       " WHERE projid=" . $id . " AND component.id=compid";
	$result = $db->exec_query($sql);
	
	$catIDs = null;
	while ($row = $db->fetch_array($result)) 
	{
		$catIDs[] = $row['id'];
	}
	return $result ? 1 : 0;
}

function deleteTestCasesByCategories(&$db,$catIDs)
{
	if (!sizeof($catIDs))
		return 1;
	$catIDs = implode(",",$catIDs);

	$sql = "DELETE FROM testcase WHERE catid IN (".$catIDs.")";	
	$result = $db->exec_query($sql);
	
	return $result ? 1 : 0;
}

function deleteTestPlanRightsForTestPlan(&$db,$id)
{
	$sql = "DELETE FROM testplans_rights WHERE projid = ".$id;	
	$result = $db->exec_query($sql);
	
	return $result ? 1 : 0;
}


function deleteResultsForBuilds(&$db,$id,$builds)
{
	if (!strlen($builds))
	{
		return 1;
	}
	
	//Delete all of the results associated with the testplan		
	$sql = " DELETE FROM results " .
	       " WHERE build.id IN (". $builds . ")". 
				 " AND results.tcid=testcase.id " .
				 " AND testcase.catid=category.id " .
				 " AND category.compid=component.id " .
				 " AND component.projid=".$id;

	$result = $db->exec_query($sql);
	
	return $result ? 1 : 0;
}

function deleteTestPlanPriorityFields(&$db,$id)
{
	$sql = "DELETE FROM priority WHERE projid=" . $id;
	$result = $db->exec_query($sql);
	
	return $result ? 1 : 0;
}

function deleteTestPlanMilestones(&$db,$id)
{
	$sql = "DELETE FROM milestones WHERE testplan_id=" . $id;
	$result = $db->exec_query($sql);
	
	return $result ? 1: 0;
}


function insertTestPlanPriority(&$db,$tplan_id,$risk)
{
	$sql = "INSERT into priorities (testplan_id,risk_importance) 
	        VALUES (" . $tplan_id . ",'" . $risk. "')";
	$result = $db->exec_query($sql);		
	return $result ? 1 : 0;
}


function insertTestPlanUserRight(&$db,$tp_id,$userID)
{
	/*
	$sql = "INSERT INTO testplans_rights (projid,userid) 
	        VALUES ( {$tp_id},{$userID} )";
	$result = $db->exec_query($sql);
	return $result ? 1 : 0;
	*/
	return 1;
}

/**
 * Function-Documentation
 *
 * @param type $db [ref] documentation
 * @param type $projID documentation
 * @param type $name documentation
 * @param type $date documentation
 * @param type $A documentation
 * @param type $B documentation
 * @param type $C documentation
 * @return type documentation
 **/
function insertTestPlanMileStone(&$db,$testplan_id,$name,$date,$A,$B,$C)
{
	$sql = "INSERT INTO milestones (testplan_id,name,date,A,B,C) VALUES (" . 
			$testplan_id . ",'" . $db->prepare_string($name) . "','" . $db->prepare_string($date) . "'," . $A . "," . 
			$B . "," . $C . ")";
	$result = $db->exec_query($sql);
	
	return $result ? 1 : 0;
}

/**
 * Function-Documentation
 *
 * @param type $db [ref] documentation
 * @param type $id documentation
 * @param type $name documentation
 * @param type $date documentation
 * @param type $A documentation
 * @param type $B documentation
 * @param type $C documentation
 * @return type documentation
 **/
function updateMileStone(&$db,$id,$name,$date,$A,$B,$C)
{
	$sql = "UPDATE milestones SET name='" . $db->prepare_string($name) . "', " .
	       " date='" . $db->prepare_string($date) . "', " . 
	       " A=" . $A . ", B=" . $B . ", C=" . $C . " WHERE id=" . $id;
	$result = $db->exec_query($sql);
		
	return $result ? 1 : 0;
}

/**
 * Function-Documentation
 *
 * @param type $db [ref] documentation
 * @param type $id documentation
 * @return type documentation
 **/
function deleteMileStone(&$db,$id)
{
	$sql = "DELETE FROM milestones WHERE id=" . $id;
	$result = $db->exec_query($sql);
	
	return $result ? 1 : 0;
}

// 20070119 - franciscom - BUGID 510
//
function getTestPlanMileStones(&$db,$projID,&$mileStones,$mileStoneID = null)
{
	$sql = " SELECT id,name AS title,date,A AS apriority, " .
	       " B AS bpriority, C AS cpriority FROM milestones " .
	       " WHERE testplan_id = " . $projID ;
	        
	if (!is_null($mileStoneID))
		$sql .= " AND id = " . $mileStoneID;		   
		   
	$sql .= " ORDER BY date";
	       
	$mileStones = null;
	$result = $db->exec_query($sql);
	if ($result)
	{
		while ($myrow = $db->fetch_array($result))
		{
			$mileStones[] =  $myrow;
		}
	}

	return $result ? 1 : 0;
}


function getUsersOfPlan(&$db,$id)
{
	$show_realname = config_get('show_realname');
	$arrUsers = array();
	
	
	$sql = " SELECT users.id,login,testplans_rights.projid ";
	if ($show_realname)
	{
	  $sql .= " ,first,last "; 
	}
	$sql .= " FROM users LEFT OUTER JOIN testplans_rights ON 
	          testplans_rights.userid = users.id AND projid = ".$id . " ORDER BY users.login";

	$result = $db->exec_query($sql);
	while ($myrow = $db->fetch_array($result))
	{ 
		$checked = '';
		if ($myrow['projid'])
		{
			$checked = 'checked="checked"';
		}	
  	
  	// 20051222 - fm 
  	$fullname =$myrow['login'];
  	if ($show_realname)
	  {
			$fullname = format_username($myrow);
		}
		
		$arrUsers[]  = array(
								'id'      => $myrow['id'], 
								'name'    => $fullname,
								'checked' => $checked,
							);
	}
	return ($arrUsers);
}

/*
 delete from all tables related to Test Plan (tescase, results, bugs, category)
 using category id
 
 $catID

 20051001 - fm
*/
function del_category_deep(&$db,$catID)
{
	// bugs
	$sql = " DELETE FROM bugs " .
	       " WHERE tcid IN (SELECT id FROM testcase WHERE catid=" . $catID . ")";
	$result = $db->exec_query($sql);
	       
	// results
	$sql = " DELETE FROM results " .
	       " WHERE tcid IN (SELECT id FROM testcase WHERE catid=" . $catID . ")";
	$result = $db->exec_query($sql);
	
	// testcases
	$sql = " DELETE FROM testcase  WHERE catid =" . $catID;
	$result = $db->exec_query($sql);
	       
	//category
	$sql = "DELETE FROM category WHERE id=" . $catID;
	$result = $db->exec_query($sql);
}

/*
 delete from all tables related to Test Plan 
 (tescase, results, bugs, category,component)
 using the component id.
 
 $compID

 20051001 - fm
*/
function del_component_deep(&$db,$compID)
{
	//Select all of the categories from the component
	$sql = " SELECT category.id AS catid " .
	       " FROM category WHERE compid=" . $compID;
	$result = $db->exec_query($sql);

	while($myrow = $db->fetch_array($result))
	{
		del_category_deep($myrow['catid']);
	}
	
	//component
	$sql = "DELETE FROM component WHERE id=" . $compID;
	$result = $db->exec_query($sql);
}


/*
 20051006 - fm 
*/
function updateTestPlanBuild(&$db,$buildID,$buildName,$notes)
{
	$sql = " UPDATE builds " .
	       " SET name='" . $db->prepare_string($buildName) . "'," .  
	       "     notes='" . $db->prepare_string($notes) . "'" .
	       " WHERE id=" . $buildID ;
	       
	$result = $db->exec_query($sql);
	return $result ? 1 : 0;
}



/* 
  delete from all tables related to Test Plan Info
 (tescase, results, bugs, category,component) 
 using the component SPECIFICATION ID.
 
   20051208 - fm 
*/
function del_tp_info_by_mgtcomp(&$db,$mgtcomp_id)
{
  // ----------------------------------------------------------------------------
  // get the list of components id in test plan table 
  // that are related to a component in the spec component table (mgtcomponent)
  $sql = " SELECT component.id AS comp_id FROM component
	         WHERE component.mgtcompid={$mgtcomp_id} ";

  $result = $db->exec_query($sql);  
  
  while($row = $db->fetch_array($result))
	{ 
    del_component_deep($row['comp_id']);
	}  
	
	
}

/* 
  delete from all tables related to Test Plan Info
 (tescase, results, bugs, category) 
 using the category SPECIFICATION ID.
 
   20051208 - fm 
*/
function del_tp_info_by_mgtcat(&$db,$mgtcat_id)
{
	// ----------------------------------------------------------------------------
	// get the list of components id in test plan table 
	// that are related to a component in the spec component table (mgtcomponent)
	$sql = " SELECT category.id AS cat_id FROM category
	WHERE category.mgtcatid={$mgtcat_id} ";
	
	$result = $db->exec_query($sql);  
	
	while($row = $db->fetch_array($result))
	{ 
	del_category_deep($row['cat_id']);
	}  
}

/**
 * Checks the milestone parameter for correctness
 *
 * @param string $name the name for the milestone
 * @param string $date the milestone date
 * @param int $A the A-Val [0,100]
 * @param int $B the B-Val [0,100]
 * @param int $C the C-Val [0,100]
 * @return string returns null on success, an error msg else
 **/
function checkMileStone($name,$date,$A,$B,$C)
{
	$sqlResult = null;
	if (preg_match("/\D/",$A) || preg_match("/\D/",$B) || preg_match("/\D/",$C)
		|| !strlen($A) || !strlen($B) || !strlen($C))
		$sqlResult = lang_get("warning_invalid_percentage_value");	
	else if (intval($A) > 100 || intval($B) > 100 || intval($C) > 100)
		$sqlResult = lang_get("warning_invalid_percentage_value");	
	else if ((intval($A) + intval($B) + intval($C)) > 100)
		$sqlResult = lang_get("warning_percentage_value_higher_than_100");	
	else if (strlen($name))
	{
		if(strlen($date))
		{
			$s1 = strtotime($date." 23:59:59");
			$s2 = strtotime("now");
			if ($s1 < $s2)
				$sqlResult = lang_get('warning_milestone_date');
		}
		else
			$sqlResult = lang_get("warning_enter_valid_date");
	}
	else
		$sqlResult = lang_get("warning_empty_milestone_name");
		
	return $sqlResult;
}



?>
