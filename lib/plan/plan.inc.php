<?
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @filesource $RCSfile: plan.inc.php,v $
 * @version $Revision: 1.4 $
 * @modified $Date: 2005/09/09 08:36:07 $
 * @author 	Martin Havlat
 *
 * Functions for management: 
 * Test Plans, Test Case Suites, Milestones, Testers assignment
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
	$sql = "UPDATE category set importance ='" . $_INPUT['importance'] . "', risk ='" .  
			$_INPUT['risk'] . "', owner='" . $_INPUT['owner'] . "' where id='" . 
			$_INPUT['id'] . "'";
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
 * @param 	string 	identification number of Test Suite / container
 * @return 	array	list of parameters
 */
function getTestSuiteParameters($testSuite)
{
	$output = array();
	$sqlCAT = "select id, name, importance, risk, owner from category where id ='" . 
			$testSuite . "' order by CATorder";
	$resultCAT = @do_mysql_query($sqlCAT);

	while($row = mysql_fetch_array($resultCAT)){ //loop through all categories
		array_push($output,array('id'=>$row['id'], 'name'=>$row['name'],
				'importance'=>$row['importance'], 'risk'=>$row['risk'],
				'owner'=>$row['owner']));
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
// $sql = "UPDATE project SET active='" . mysql_escape_string($active) . 
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

function getProjectComponents($id,&$cInfo)
{
	$sql = "SELECT * FROM component WHERE projid=" . $id;
	$result = do_mysql_query($sql);

	$cInfo = null;
	while ($row = mysql_fetch_row($result)) 
		$cInfo[] = $row;
	
	return $result ? 1 : 0;
}

function getProjectComponentIDs($id,&$comIDs)
{
	$cInfo = null;
	$result = getProjectComponents($id,$cInfo);
	if ($result)
	{
		for($i = 0 ; $i < sizeof($cInfo);$i++)
			$comIDs[] = $cInfo[$i][0];
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

function getProjectCategories($id,&$catIDs)
{
	//Select all of the projects components
	$sql = "SELECT category.id FROM component, category WHERE projid=".$id." AND component.id=compid";
	$result = do_mysql_query($sql);
	
	$catIDs = null;
	while ($row = mysql_fetch_row($result)) 
		$catIDs[] = $row[0];
	
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

function deleteTestPlanBuilds($id)
{
	$sql = "DELETE FROM build WHERE projid=" . $id;
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


function insertPlan(&$id,$name,$notes,$tpID)
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
	$sql = "INSERT INTO component (name,projid,mgtcompid) VALUES ('" . 
					mysql_escape_string($name) . "'," . $projID . "," . $mgtCompID . ")";
	
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

function getProjectMileStones($projID,&$mileStones)
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
function insertTestPlanBuild($build,$testplanID,$notes = '')
{
	$sql = " INSERT INTO build (projid,name,note) " .
	       " VALUES ('". $testplanID . "','" . mysql_escape_string($build) . "','" . 
	       mysql_escape_string($notes) . "')";
	       
	$result = do_mysql_query($sql);
	$buildID = 0;
	if ($result)
	{
		$id = mysql_insert_id();
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
	}
	
	return $buildID;
}


function getAllTestPlanComponentCategories($testPlanID,$compID,&$categories)
{
	$query = " SELECT category.id, category.name, importance, risk, owner " .
	         " FROM component,category " .
	         " WHERE component.projid = " .	$testPlanID  . 
	         " AND component.id = " . $compID . 
	         " AND category.compid = component.id ORDER BY component.name,CATorder";
	         
	$result = do_mysql_query($query);
	
	$categories = null;
	if ($result)
	{
		while($row = mysql_fetch_array($result))
			$categories[] = $row;
	}
	
	return $result ? 1 : 0;
}


function getCategoriesTestcases($catIDs,&$tcIDs)
{
	$tcIDs = null;
	if (!sizeof($catIDs))
		return 1;
		
	$catIDList = implode(",",$catIDs);
	$sql = "SELECT id FROM testcase WHERE catid IN ({$catIDList})";
	$result = do_mysql_query($sql);

	if ($result)
	{
		while ($row = mysql_fetch_array($result))
			$tcIDs[] = $row['id'];
	}
	return $result ? 1 : 0;
}
?>