<?
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @filesource $RCSfile: plan.inc.php,v $
 * @version $Revision: 1.9 $
 * @modified $Date: 2005/09/21 10:32:01 $
 * @author 	Martin Havlat
 *
 * Functions for management: 
 * Test Plans, Test Case Suites, Milestones, Testers assignment
 *
 * @author Francisco Mancardi - 20050914 - refactoring
 */
////////////////////////////////////////////////////////////////////////////////

/** include core functions for collect information about Test Plans */
require_once("plan.core.inc.php"); 


/**
 * Update priority and owner of test suite/category
 *
 */
function updateSuiteAttributes($_INPUT)
{
	$sql = "UPDATE category SET importance ='" . $_INPUT['importance'] . "', risk ='" .  
			   $_INPUT['risk'] . "', owner='" . $_INPUT['owner'] . "' " .
			   " WHERE id=" . $_INPUT['id'];
	$result = do_mysql_query($sql);
	
	if ($result){
		return 'ok';
	} else {
		return $sqlResult;
	}
}

/**
 * Get actual priority and owner of test suite/category
 *
 * @param 	string 	identification number 
 * @return 	array	list of parameters
 *
 * 20050914 - fm - using name and CATorder from mgtcategory 
 */
function getTP_category_info($catID)
{
	$output = array();
	$sql = " SELECT category.id, mgtcategory.name, importance, risk, owner " .
	       " FROM category, mgtcategory " .
	       " WHERE category.mgtcatid = mgtcategory.id " .
	       " AND category.id =" . $catID . " ORDER BY mgtcategory.CATorder";
	       
	      
	$result = @do_mysql_query($sql);

	while($row = mysql_fetch_assoc($result))
	{ 
		/* array_push($output,array('id'=>$row['id'], 'name'=>$row['name'],
				'importance'=>$row['importance'], 'risk'=>$row['risk'],
				'owner'=>$row['owner']));
		*/
		$output[]=$row;		
	}
	return $output;
}


// 20050809 - fm
// changes must be made due to active field type changed to boolean
//
function updateTestPlan($id,$name,$notes,$p_active)
{

// 20050810 - fm	
$active = to_boolean($p_active);
	
// 20050809 - fm 	
$sql = "UPDATE project SET active='" . $active . 
	       "', name='" . mysql_escape_string($name) . "', notes='" . 
	       mysql_escape_string($notes). "' WHERE id=" . $id;
	$result = do_mysql_query($sql);
	
	return $result ? 1 : 0;
}

function deleteTestPlan($id)
{
	$sql = "DELETE FROM project WHERE id=" . $id;
	$result = do_mysql_query($sql);

	return $result ? 1 : 0;
}
function deleteTestPlanComponents($id)
{
	$sql = "DELETE FROM component WHERE projid=" . $id;
	$result = do_mysql_query($sql);

	return $result ? 1 : 0;
}

/*
20050915 - fm - refactoring mgtcomponent

*/
function getTestPlanComponents($id,&$cInfo)
{
	$sql = " SELECT component.id, mgtcomponent.name,component.projid, mgtcompid " .
	       " FROM component, mgtcomponent " .
	       " WHERE component.mgtcompid = mgtcomponent.id " .
	       " AND projid=" . $id;
	$result = do_mysql_query($sql);

	$cInfo = null;
	while ($row = mysql_fetch_array($result)) 
	{
		$cInfo[] = $row;
	}
	return $result ? 1 : 0;
}

function getTestPlanComponentIDs($id,&$comIDs)
{
	$cInfo = null;
	$result = getTestPlanComponents($id,$cInfo);
	if ($result)
	{
		$num_loops = sizeof($cInfo);
		for($i = 0 ; $i < $num_loops ;$i++)
		{
			$comIDs[] = $cInfo[$i][0];
		}	
	}
	return $result ? 1 : 0;
}


function deleteCategoriesByComponentIDs($comIDs)
{
	if (!sizeof($comIDs))
		return 1;
	
	$comIDs = implode(",",$comIDs);
	$sql = "DELETE FROM category WHERE compid IN (" . $comIDs . ")";

	$result = do_mysql_query($sql);
	
	return $result ? 1 : 0;
}


function getTestPlanCategories($id,&$catIDs)
{
	//Select all of the projects components
	$sql = " SELECT category.id FROM component, category " .
	       " WHERE projid=" . $id . " AND component.id=compid";
	$result = do_mysql_query($sql);
	
	$catIDs = null;
	while ($row = mysql_fetch_assoc($result)) 
	{
		$catIDs[] = $row['id'];
	}
	return $result ? 1 : 0;
}

function deleteTestCasesByCategories($catIDs)
{
	if (!sizeof($catIDs))
		return 1;
	$catIDs = implode(",",$catIDs);

	$sql = "DELETE FROM testcase WHERE catid IN (".$catIDs.")";	
	$result = do_mysql_query($sql);
	
	return $result ? 1 : 0;
}


/*

20050921 - fm - refactoring build.buildid -> build.id
20050910 - fm - bug missing argument $buildID

*/
function deleteTestPlanBuilds($tpID, $buildID)
{
	
	$sql = "DELETE FROM build " .
	       "WHERE projid=" . $tpID . " AND build.id=" . $buildID;
	$result = do_mysql_query($sql);
	
	return $result ? 1: 0;		
}

function deleteTestPlanRightsForProject($id)
{
	$sql = "DELETE FROM projrights WHERE projid = ".$id;	
	$result = do_mysql_query($sql);
	
	return $result ? 1 : 0;
}

function deleteResultsForBuilds($id,$builds)
{
	//todo: hat does the SQL statement below???
	return 1;
	//SCHLUNDUS
	if (!strlen($builds))
		return 1;
	
	//Delete all of the results associated with the project		
	$sql = "DELETE FROM results WHERE build IN (". $builds . ")". 
				" AND results.tcid=testcase.id AND testcase.catid=" .
				"category.id AND category.compid=component.id AND " .
				"component.projid=".$id;
	$result = do_mysql_query($sql);
	
	return $result ? 1 : 0;
}

function deleteTestPlanPriorityFields($id)
{
	$sql = "DELETE FROM priority WHERE projid=" . $id;
	$result = do_mysql_query($sql);
	
	return $result ? 1 : 0;
}

function deleteTestPlanMilestones($id)
{
	$sql = "DELETE FROM milestone WHERE projid=" . $id;
	$result = do_mysql_query($sql);
	
	return $result ? 1: 0;
}


function insertTestPlan(&$id,$name,$notes,$tpID)
{
	$sql = "INSERT INTO project (name,notes,prodID) VALUES ('" . 
	       mysql_escape_string($name) . "','" . 
	       mysql_escape_string($notes) . "'," . $tpID .")";
	$result = do_mysql_query($sql);
	
	$id = 0;
	if ($result)
		$id =  mysql_insert_id();
	
	return $result ? 1 : 0;
}

function insertTestPlanPriorities($projID)
{
	//Create the priority table
	$arrSql = array('L1', 'L2', 'L3','M1', 'M2', 'M3','H1', 'H2', 'H3');
	
	$result = 1;
	foreach ($arrSql as $risk)
		$result = $result && insertTestPlanPriority($projID,$risk);
	
	return $result ? 1 : 0;
}

function insertTestPlanPriority($projID,$risk)
{
	$sql = "INSERT into priority (projid,riskImp) values (" . $projID . ",'" . $risk. "')";
	$result = do_mysql_query($sql);		
	
	return $result ? 1 : 0;
}


function insertTestPlanUserRight($projID,$userID)
{
	$sql = "INSERT INTO projrights (projid,userid) values (".$projID.",".$userID.")";
	$result = do_mysql_query($sql);
  
	return $result ? 1 : 0;
}

function insertTestPlanComponent($projID,$name,$mgtCompID)
{
	
	/*
	$sql = "INSERT INTO component (name,projid,mgtcompid) VALUES ('" . 
					mysql_escape_string($name) . "'," . $projID . "," . $mgtCompID . ")";
	*/
	// 20050915 - fm
	$sql = " INSERT INTO component (projid,mgtcompid) " .
	       " VALUES (" . $projID . "," . $mgtCompID . ")";
	
	
	$resultCom = do_mysql_query($sql);
	$compID = 0;
	if ($resultCom)
	{
		//Grab the id of the project just entered so that the priority table can be filled out
		$compID = mysql_insert_id(); 
	}	
	
	return $compID;
}

function insertTestPlanMileStone($projID,$name,$date,$A,$B,$C)
{
	$sql = "INSERT INTO milestone (projid,name,date,A,B,C) VALUES (" . 
			$projID . ",'" . mysql_escape_string($name) . "','" . mysql_escape_string($date) . "'," . $A . "," . 
			$B . "," . $C . ")";
	$result = do_mysql_query($sql);
	
	return $result ? 1 : 0;
}

function updateMileStone($id,$name,$date,$A,$B,$C)
{
	$sql = "UPDATE milestone SET name='" . mysql_escape_string($name) . "', " .
	       " date='" . mysql_escape_string($date) . "', " . 
	       " A=" . $A . ", B=" . $B . ", C=" . $C . " WHERE id=" . $id;
	$result = do_mysql_query($sql);
		
	return $result ? 1 : 0;
}

function deleteMileStone($id)
{
	$sql = "DELETE FROM milestone WHERE id=" . $id;
	$result = do_mysql_query($sql);
	
	return $result ? 1 : 0;
}

function getTestPlanMileStones($projID,&$mileStones)
{
	// load existing milestones
	$sql = " SELECT id,name,date,A,B,C " .
	       " FROM milestone " .
	       " WHERE projid=" . $projID . 
	       " AND to_days(date) >= to_days(now()) ORDER BY date";
	       
	$result = do_mysql_query($sql);
	
	$mileStones = null;
	if ($result)
	{
		while ($myrow = mysql_fetch_row($result))
		{
			$mileStones[] =  array( 'id' => $myrow[0], 
									  'title' => $myrow[1], 
									  'date' => $myrow[2], 
									  'A' => $myrow[3], 
									  'B' => $myrow[4], 
									  'C' => $myrow[5]
									 );
		}//END WHILE
	}

	return $result ? 1 : 0;
}
function getUsersOfPlan($id,&$arrUsers)
{
	// query users
	$arrUsers = array();
	$sql = "SELECT user.id,login,projrights.projid " . 
	       "FROM user LEFT OUTER JOIN projrights ON projrights.userid = user.id AND projid = ".$id;
	$result = do_mysql_query($sql);
	while ($myrow = mysql_fetch_row($result))
	{ 
		$checked = '';
		if ($myrow[2])
			$checked = 'checked="checked"';
		$arrUsers[]  = array(
								'id' => $myrow[0], 
								'name' => $myrow[1],
								'checked' => $checked,
							);
	}
	return $result ? 1 : 0;
}


// 20050815 - scs - $notes now became a default parameter
// 20050905 - scs - function now returns the build value
// 20050909 - fm - From Project to TestPlan
// 20050921 - fm - refactoring build
function insertTestPlanBuild($buildName,$testplanID,$notes = '')
{
	$sql = " INSERT INTO build (projid,name,note) " .
	       " VALUES ('". $testplanID . "','" . mysql_escape_string($buildName) . "','" . 
	       mysql_escape_string($notes) . "')";
	       
	$result = do_mysql_query($sql);
	$new_build_id = 0;
		
	if ($result)
	{
		$new_build_id = mysql_insert_id();
	
	  /*
		$query = "SELECT MAX(build)+1 FROM build";
		$result = do_mysql_query($query);
		if ($result)
		{
			$row = mysql_fetch_row($result);
			$buildID = $row[0];
			if ($id >= $buildID)
				$buildID = $id;
			$query = "UPDATE build SET build = {$buildID} WHERE id = $id";
			$result = do_mysql_query($query);
		}
		*/
	}
	
	
	return $new_build_id;
}

/*
20050914 - fm - 
using also mgtcategory
changed return type

*/

function getAllTestPlanComponentCategories($testPlanID,$compID)
{
	$aCategories = array();
	$query = " SELECT CAT.id, MGTCAT.name, importance, risk, owner " .
	         " FROM component COMP, category CAT, mgtcategory MGTCAT " .
	         " WHERE CAT.mgtcatid = MGTCAT.id " .
	         " AND CAT.compid = COMP.id " .
	         " AND COMP.projid = " .	$testPlanID  . 
	         " AND COMP.id = " . $compID . 
	         " ORDER BY MGTCOMP.name, MGTCAT.CATorder";
	         
	$result = do_mysql_query($query);
	
	if ($result)
	{
		while($row = mysql_fetch_array($result))
		{
			$aCategories[] = $row;
		}	
	}
	
	return ($aCategories);
}


/*
20050914 - fm - 
using also mgtcategory
changed return type
*/
function getCategories_TC_ids($catIDs,&$tcIDs)
{
	$tcIDs = array();
	if (sizeof($catIDs) > 0)
	{
  	$catIDList = implode(",",$catIDs);
  	$sql = "SELECT id FROM testcase WHERE catid IN ({$catIDList})";
  	$result = do_mysql_query($sql);
  
  	if ($result)
  	{
  		while ($row = mysql_fetch_array($result))
  			$tcIDs[] = $row['id'];
  	}
  }	
	return($tcIDs);
}
?>