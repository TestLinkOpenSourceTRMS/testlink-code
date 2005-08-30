<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: requirements.inc.php,v $
 * @version $Revision: 1.4 $
 * @modified $Date: 2005/08/30 15:17:25 $ by $Author: havlat $
 *
 * @author Martin Havlat <havlat@users.sourceforge.net>
 * 
 * Functions for support requirement based testing 
 *
 * 20050810	- francisco mancardi - deprecated $_SESSION['product'] removed
 * 20050925 - Martin Havlat - updated global header;
 * 20050929 - Martin Havlat - updated function headers 
 * 
 */
////////////////////////////////////////////////////////////////////////////////

$arrReqStatus = array('v' => 'Valid', 'n' => 'Not testable');

require_once('print.inc.php');

/** 
 * create a new System Requirements Specification 
 * 
 * @param string $title
 * @param string $scope
 * @param string $countReq
 * @param string $type
 * 
 * @version 1.0
 * @author Martin Havlat 
 */
function createReqSpec ($title, $scope, $countReq, $type = 'n')
{
	tLog('Create SRS requested: ' . $title);
	if (strlen($title)) {
		$sql = "INSERT INTO req_spec (id_product, title, scope, type, total_req, id_author, create_date) " .
				"VALUES (" . $_SESSION['productID'] . ",'" . mysql_escape_string($title) . 
				"','" . mysql_escape_string($scope) . "','" . mysql_escape_string($type) . "','" . 
				mysql_escape_string($countReq) . "'," . mysql_escape_string($_SESSION['userID']) . 
				", CURRENT_DATE)";
		$result = do_mysql_query($sql); 
		if ($result) {
			$result = 'ok';
		} else {
			 $result = 'The INSERT request fails with these values:' . 
					$title . ', ' . $scope . ', ' . $countReq;
			tLog('SQL: ' . $sql . ' fails: ' . mysql_error(), 'ERROR');
		}
	} else {
		$result = "You cannot enter an empty title!";
	}
	return $result; 
}


/** 
 * update System Requiements Specification
 *  
 * @param integer $id
 * @param string $title
 * @param string $scope
 * @param string $countReq
 * @param string $type
 * @return string result
 * 
 * @version 1.0
 * @author Martin Havlat 
 */
function updateReqSpec ($id, $title, $scope, $countReq, $type = 'n')
{
	if (strlen($title)) {
		$sql = "UPDATE req_spec SET title='" . mysql_escape_string($title) . 
				"', scope='" . mysql_escape_string($scope) . "', type='" . mysql_escape_string($type) .
				"', total_req ='" . mysql_escape_string($countReq) . "', id_modifier='" . 
				mysql_escape_string($_SESSION['userID']) . "', modified_date=CURRENT_DATE WHERE id=" . $id;
		$result = do_mysql_query($sql); 
		if ($result) {
			$result = 'ok';
		} else {
			 $result = 'The UPDATE request fails with these values:' . 
					$title . ', ' . $scope . ', ' . $countReq;
			tLog('SQL: ' . $sql . ' fails: ' . mysql_error(), 'ERROR');
		}
	} else {
		$result = "You cannot enter an empty title!";
	}
	return $result; 
}

/** 
 * delete System Requirement Specification
 *  
 * @param integer $idSRS
 * @return string result comment
 * 
 * @version 1.0
 * @author Martin Havlat 
 **/
function deleteReqSpec ($idSRS)
{
	// delete requirements and coverage
	$arrReq = getRequirements($idSRS);
	if (sizeof($arrReq))
	{
		foreach ($arrReq as $oneReq) {
			$result = deleteRequirement($oneReq['id']);
		}
	}
		
	// delete specification itself
	$sql = "DELETE FROM req_spec WHERE id=" . $idSRS;
	$result = do_mysql_query($sql); 
	if ($result) {
		$result = 'ok';
	} else {
		$result = 'The DELETE SRS request fails.';
		tLog('SQL: ' . $sql . ' fails: ' . mysql_error(), 'ERROR');
	}
	return $result; 
}

/** 
 * collect information about current list of Requirements Specification
 *  
 * @param string $set range of collection 'product' (default) or 'all' or '<id>'
 * @return assoc_array list of SRS
 * 
 * @version 1.0
 * @author Martin Havlat 
 **/
function getReqSpec($set = 'product')
{
	$sql = "SELECT * FROM req_spec";
	if ($set == 'product') {
		$sql .= " WHERE id_product=" . $_SESSION['productID'] . " ORDER BY title";
	} elseif (intval($set)) {
		$sql .= " WHERE id=" . $set;
	}
	// else all
	
	return selectData($sql);
}

/** 
 * get list of all SRS for the current product 
 * 
 * @return associated array List of titles according to IDs
 * 
 * @version 1.0
 * @author Martin Havlat 
 **/
function getOptionReqSpec()
{
	$sql = "SELECT id,title FROM req_spec WHERE id_product=" . $_SESSION['productID'] . 
			" ORDER BY title";
	
	return selectOptionData($sql);
}


/** 
 * collect information about current list of Requirements in req. Specification
 *  
 * @param string $idSRS ID of req. specification
 * @param string range = ["all" (default), "assigned"] (optional)
 * 			"unassign" is not implemented because requires subquery 
 * 			which is not available in MySQL 4.0.x
 * @param string Test case ID - required if assigned or unassigned scope is used
 * @return assoc_array list of requirements
 * 
 * @version 1.0
 * @author Martin Havlat 
 */
function getRequirements($idSRS, $range = 'all', $idTc = null)
{
	if ($range == 'all') {
		$sql = "SELECT * FROM requirements WHERE id_srs=" . $idSRS . " ORDER BY title";
	}
	elseif ($range == 'assigned') {
		$sql = "SELECT requirements.* FROM requirements,req_coverage WHERE id_srs=" . 
				$idSRS . " AND req_coverage.id_req=requirements.id AND " . 
				"req_coverage.id_tc=" . $idTc . " ORDER BY title";
	}

	return selectData($sql);
}

/** 
 * function allows to obtain unassigned requirements 
 * 
 * @version 1.0
 * @author Martin Havlat 
 **/
// MHT: I'm not able find a simple SQL (subquery is not supported 
// in MySQL 4.0.x); probably temporary table should be used instead of the next
function array_diff_byId ($arrAll, $arrPart)
{
	// solve empty arrays
	if (!count($arrAll)) {
		return array();
	}
	if (!count($arrPart)) {
		return $arrAll;
	}

	$arrTemp = array();
	$arrTemp2 = array();

	// converts to associated arrays
	foreach ($arrAll as $penny) {
		$arrTemp[$penny['id']] = $penny;
	}
	foreach ($arrPart as $penny) {
		$arrTemp2[$penny['id']] = $penny;
	}
	
	// exec diff
	$arrTemp3 = array_diff_assoc($arrTemp, $arrTemp2);
	
	$arrTemp4 = null;
	// convert to numbered array
	foreach ($arrTemp3 as $penny) {
		$arrTemp4[] = $penny;
	}
	return $arrTemp4;
}

function getSRSCoverage($idSRS, $idPlan)
{
	$output = array('covered' => array(), 'uncovered' => array(), 'nottestable' => array());
	
	// get requirements
	$sql = "SELECT id,title FROM requirements WHERE id_srs=" . $idSRS . 
			" AND status='v' ORDER BY title";
	$arrReq = selectData($sql);

	// get not-testable requirements
	$sql = "SELECT id,title FROM requirements WHERE id_srs=" . $idSRS . 
			" AND status='n' ORDER BY title";
	$output['nottestable'] = selectData($sql);
	
	// get coverage
	if (sizeof($arrReq))
	{
		foreach ($arrReq as $req) 
		{
			$arrCoverage = getSuite4Req($req['id'], $idPlan);
	
			if (count($arrCoverage) > 0) {
				// add information about coverage
				$req['coverage'] = $arrCoverage;
				$output['covered'][] = $req;
			} else {
				$output['uncovered'][] = $req;
			}
		}
	}	
	return $output;
}


/**
 * get requirement coverage metrics
 * @param integer $idSRS
 * @return array results
 * @author havlatm
 */
function getReqMetrics_general($idSRS)
{
	$output = array();
	
	$sql = "SELECT count(*) FROM requirements WHERE id_srs=" . $idSRS . 
			" AND status='n'";
	$output['notTestable'] = do_mysql_selectOne($sql);

	$sql = "SELECT count(*) FROM requirements WHERE id_srs=" . $idSRS;
	$output['total'] = do_mysql_selectOne($sql);

	$sql = "SELECT req_total FROM req_spec WHERE id_srs=" . $idSRS;
	$output['expectedTotal'] = do_mysql_selectOne($sql);;
	
	if ($output['expectedTotal'] == 'n/a') {
		$output['expectedTotal'] = $output['total'];
	}
	
	$sql = "SELECT DISTINCT requirements.id FROM requirements, req_coverage WHERE" .
				" requirements.id_srs=" . $idSRS .
				" AND requirements.id=req_coverage.id_req";
	$result = do_mysql_query($sql);
	if (!empty($result)) {
		$output['covered'] = mysql_num_rows($result);
	}

	$output['uncovered'] = $output['expectedTotal'] - $output['covered'] 
			- $output['notTestable'];

	return $output;
}

/**
 * get requirement coverage metrics for a Test Plan
 * @param integer $idSRS
 * @param integer $idTestPlan
 * @return array results
 * @author havlatm
 */
function getReqMetrics_testPlan($idSRS, $idTestPlan)
{
	$output = getReqMetrics_general($idSRS);
	
	$sql = "SELECT DISTINCT requirements.id FROM requirements,testcase," .
			"req_coverage,category,component WHERE requirements.id_srs=" . $idSRS .
				" AND component.projid=" . $idTestPlan /*$_SESSION['testPlanId']*/ .
				" AND category.compid=component.id AND category.id=testcase.catid" .
				" AND testcase.mgttcid = req_coverage.id_tc AND id_req=requirements.id"; 
	$result = do_mysql_query($sql);
	if (!empty($result)) {
		$output['coveredByTestPlan'] = mysql_num_rows($result);
	}
	
	$output['coveredTestPlan'] = $_SESSION['testPlanName'];
	$output['uncoveredByTestPlan'] = $output['expectedTotal'] 
			- $output['coveredByTestPlan'] - $output['notTestable'];

	return $output;
}


/** 
 * collect information about one Requirement
 *  
 * @param string $idREQ ID of req.
 * @return assoc_array list of requirements
 */
function getReqData($idReq)
{
	$output = array();
	
	$sql = "SELECT * FROM requirements WHERE id=" . $idReq;
	$result = do_mysql_query($sql);
	if (!empty($result)) {
		$output = mysql_fetch_array($result);
	}
	
	return $output;
}

/** collect coverage of Requirement 
 * @param string $idREQ ID of req.
 * @return assoc_array list of test cases [id, title]
 */
function getTc4Req($idReq)
{
	$sql = "SELECT mgttestcase.id,mgttestcase.title FROM mgttestcase, req_coverage " .
			"WHERE req_coverage.id_req=" . $idReq . 
			" AND req_coverage.id_tc=mgttestcase.id";
	
	return selectData($sql);
}


/** collect coverage of Requirement for Test Suite
 * @param string $idREQ ID of req.
 * @param string $idPlan ID of Test Plan
 * @return assoc_array list of test cases [id, title]
 */
function getSuite4Req($idReq, $idPlan)
{
	$sql = "SELECT testcase.id,testcase.title FROM testcase,req_coverage,category," .
				"component WHERE component.projid=" . $idPlan .
				" AND category.compid=component.id AND category.id=testcase.catid" .
				" AND testcase.mgttcid = req_coverage.id_tc AND id_req=" . 
				$idReq . " ORDER BY title";
	
	return selectData($sql);
}

/** 
 * collect coverage of TC
 *  
 * @param string $idTC ID of req.
 * @param string SRS ID (optional)
 * @return assoc_array list of test cases [id, title]
 */
function getReq4Tc($idTc, $idSRS = 'all')
{
	$sql = "SELECT requirements.id,requirements.title FROM requirements, req_coverage " .
			"WHERE req_coverage.id_tc=" . $idTc . 
			" AND req_coverage.id_req=requirements.id";
	// if only for one specification is required
	if ($idSRS != 'all') {
		$sql .= " AND requirements.id_srs=" . $idSRS;
	}

	return selectData($sql);
}

/** 
 * create a new Requiement 
 * 
 * @param string $title
 * @param string $scope
 * @param integer $idSRS
 * @param char $status
 * @param char $type
 * 
 * @author Martin Havlat 
 **/
function createRequirement ($title, $scope, $idSRS, $status = 'v', $type = 'n')
{
	if (strlen($title)) {
		$sql = "INSERT INTO requirements (id_srs, title, scope, status, type, id_author, create_date)" .
				" VALUES (" . $idSRS . ",'" . mysql_escape_string($title) . 
				"','" . mysql_escape_string($scope) . "','" . mysql_escape_string($status) . 
				"','" . mysql_escape_string($type) ."'," . mysql_escape_string($_SESSION['userID']) . 
				", CURRENT_DATE)";
		$result = do_mysql_query($sql); 
		$result = $result ? 'ok' : 'The INSERT request fails with these values:' . 
			$title . ', ' . $scope . ', ' . $status;
	} else {
		$result = "You cannot enter an empty title!";
	}
	return $result; 
}


/** 
 * update Requirement 
 * 
 * @param integer $id
 * @param string $title
 * @param string $scope
 * @param string $status
 * @param string $type
 * 
 * @author Martin Havlat 
 **/
function updateRequirement ($id, $title, $scope, $status, $type)
{
	if (strlen($title)) {
		$sql = "UPDATE requirements SET title='" . mysql_escape_string($title) . 
				"', scope='" . mysql_escape_string($scope) . "', status='" . mysql_escape_string($status) . 
				"', type='" . mysql_escape_string($type) . 
				"', id_modifier=" . mysql_escape_string($_SESSION['userID']) . 
				", modified_date=CURRENT_DATE WHERE id=" . $id;
		$result = do_mysql_query($sql); 
		if ($result) {
			$result = 'ok';
		} else {
			 $result = 'The UPDATE request fails with these values:' . 
					$title . ', ' . $scope;
			tLog('SQL: ' . $sql . ' fails: ' . mysql_error(), 'ERROR');
		}
	} else {
		$result = "You cannot enter an empty title!";
	}
	return $result; 
}

/** 
 * delete Requirement
 *  
 * @param integer $id
 * 
 * @version 1.0
 * @author Martin Havlat 
 **/
function deleteRequirement($id)
{
	// delete dependencies with test specification
	$sql = "DELETE FROM req_coverage WHERE id_req=" . $id;
	$result = do_mysql_query($sql); 
	if ($result) {
		// delete req itself
		$sql = "DELETE FROM requirements WHERE id=" . $id;
		$result = do_mysql_query($sql); 
	}
	if ($result) {
		$result = 'ok';
	} else {
		$result = 'The DELETE REQ request fails.';
		tLog('SQL: ' . $sql . ' fails: ' . mysql_error(), 'ERROR');
	}
	return $result; 
}

/** 
 * print Requirement Specification 
 * 
 * @param integer $idSRS
 * 
 * @version 1.0
 * @author Martin Havlat 
 **/
function printSRS($idSRS)
{
	$arrSpec = getReqSpec($idSRS);
	
	$output = printHeader($arrSpec[0]['title']);
	$output .= printFirstPage($arrSpec[0]['title']);
	$output .= "<h2>" . lang_get('scope') . "</h2>\n<div>" . $arrSpec[0]['scope'] . "</div>\n";
	$output .= printRequirements($idSRS);
	$output .= "\n</body>\n</html>";

	echo $output;
}

/** 
 * print Requirement for SRS 
 * 
 * @param integer $idSRS
 * 
 * @version 1.0
 * @author Martin Havlat 
 **/
function printRequirements($idSRS)
{
	$arrReq = getRequirements($idSRS);
	
	$output = "<h2>" . lang_get('reqs') . "</h2>\n<div>\n";
	if (count($arrReq) > 0) {
		foreach ($arrReq as $REQ) {
			$output .= '<h3>' . $REQ['title'] . "</h3>\n<div>" . $REQ['scope'] . "</div>\n";
		}
	} else {
		$output .= '<p>' . lang_get('none') . '</p>';
	}
	$output .= "\n</div>";

	return $output;
}


/** 
 * assign requirement and test case
 * @param integer test case ID
 * @param integer requirement ID
 * @return integer 1 = ok / 0 = problem
 * 
 * @version 1.0
 * @author Martin Havlat 
 */
function assignTc2Req($idTc, $idReq)
{
	$output = 0;
	tLog("assignTc2Req TC:" . $idTc . ' and REQ:' . $idReq);
	
	if ($idTc && $idReq)
	{
		$sql = 'SELECT COUNT(*) FROM req_coverage WHERE id_req=' . $idReq . 
				' AND id_tc=' . $idTc;
		$result = do_mysql_query($sql);

		if (mysql_result($result,0) == 0) {
	
			// create coverage dependency
			$sqlReqCov = 'INSERT INTO req_coverage (id_req,id_tc) VALUES ' .
					"(" . $idReq . "," . $idTc . ")";
			$resultReqCov = do_mysql_query($sqlReqCov);
			// collect results
			if (mysql_affected_rows() == 1) {
				$output = 1;
				tLog('Dependency was created between TC:' . $idTc . ' and REQ:' . $idReq, 'INFO');
			}
			else
			{
				tLog("Dependency wasn't created between TC:" . $idTc . ' and REQ:' . $idReq .
					"\t" . mysql_error(), 'ERROR');
			}
		}
		else
		{
			$output = 1;
			tLog('Dependency already exists between TC:' . $idTc . ' and REQ:' . $idReq, 'INFO');
		}
	}
	else {
		tLog('Wrong input values', 'ERROR');
	}
	return $output;
}


/** 
 * UNassign requirement and test case
 * @param integer test case ID
 * @param integer requirement ID
 * @return integer 1 = ok / 0 = problem
 * 
 * @version 1.0
 * @author Martin Havlat 
 */
function unassignTc2Req($idTc, $idReq)
{
	$output = 0;
	tLog("unassignTc2Req TC:" . $idTc . ' and REQ:' . $idReq);

	// create coverage dependency
	$sqlReqCov = 'DELETE FROM req_coverage WHERE id_req=' . $idReq . 
			' AND id_tc=' . $idTc;
	$resultReqCov = do_mysql_query($sqlReqCov);

	// collect results
	if (mysql_affected_rows() == 1) {
		$output = 1;
		tLog('Dependency was deleted between TC:' . $idTc . ' and REQ:' . $idReq, 'INFO');
	}
	else {
		tLog("Dependency wasn't deleted between TC:" . $idTc . ' and REQ:' . $idReq .
				"\n" . $sqlReqCov. "\n" . mysql_error(), 'ERROR');
	}

	return $output;
}



/** 
 * function generate testcases with name and summary for requirements
 * @param array or integer list of REQ id's 
 * @return string Result description
 * 
 * @version 1.0
 * @author Martin Havlat 
 */
function createTcFromRequirement($mixIdReq)
{
	require_once("../testcases/archive.inc.php");
	
	tLog('createTcFromRequirement started');
	$output = null;
	if (is_array($mixIdReq)) {
		$arrIdReq = $mixIdReq;
	} else {
		$arrIdReq = array($mixIdReq);
	}
	
	//find component
	$sqlCOM = "SELECT id FROM mgtcomponent WHERE name='TODO' AND " .
			"prodid=" . $_SESSION['productID'];
	$resultCOM = do_mysql_query($sqlCOM);
	if (mysql_num_rows($resultCOM) == 1) {
		$idCom = mysql_result($resultCOM,0);
	}
	else {
		// not found -> create
		tLog('Component TODO was not found.');
		$sqlInsertCOM = 'INSERT INTO mgtcomponent (name,scope,prodid) VALUES ' .
				"('TODO','Test Cases generated from Requirements'," . 
				$_SESSION['productID'] . ")";
		$resultCOM = do_mysql_query($sqlInsertCOM);
		if (mysql_affected_rows()) {
			$resultCOM = do_mysql_query($sqlCOM);
			if (mysql_num_rows($resultCOM) == 1) {
				$idCom = mysql_result($resultCOM,0);
			} else {
				tLog('Component TODO was not found again! ' . mysql_error());
			}
		} else {
			tLog(mysql_error(), 'ERROR');
		}
	}
	tLog('createTcFromRequirement: $idCom=' . $idCom);

	//find category
	$sqlCAT = "SELECT id FROM mgtcategory WHERE name='TODO' AND " .
			"compid=" . $idCom;
	$resultCAT = do_mysql_query($sqlCAT);
	if ($resultCAT && (mysql_num_rows($resultCAT) == 1)) {
		$idCat = mysql_result($resultCAT,0);
	}
	else {
		// not found -> create
		$sqlInsertCAT = 'INSERT INTO mgtcategory (name,objective,compid) VALUES ' .
				"('TODO','Test Cases generated from Requirements'," .
				$idCom . ")";
		$resultCAT = do_mysql_query($sqlInsertCAT);
		$resultCAT = do_mysql_query($sqlCAT);
		if (mysql_num_rows($resultCAT) == 1) {
			$idCat = mysql_result($resultCAT,0);
		} else {
			die(mysql_error());
		}
	}
	tLog('createTcFromRequirement: $idCat=' . $idCat);

	//create TC
	foreach ($arrIdReq as $execIdReq) 
	{
		//get data
		tLog('proceed: $execIdReq=' . $execIdReq);
		$reqData = getReqData($execIdReq);

		tLog('$reqData:' . implode(',',$reqData));
		
		// create TC
		$tcID =  insertTestcase($idCat,$reqData['title'],"Verify requirement: \n" . 
				$reqData['scope'],$_SESSION['user'],null,null);
		
		// create coverage dependency
		if (!assignTc2Req($tcID, $reqData['id'])) {
			$output = 'Test case: ' . $reqData['title'] . "was not created </br>";
		}
	}

	return (!$output) ? 'ok' : $output;
}
?>