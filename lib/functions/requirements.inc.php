<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: requirements.inc.php,v $
 * @version $Revision: 1.29 $
 * @modified $Date: 2006/04/07 20:15:26 $ by $Author: schlundus $
 *
 * @author Martin Havlat <havlat@users.sourceforge.net>
 * 
 * Functions for support requirement based testing 
 *
 * Revisions:
 *
 * 20051002 - francisco mancardi - Changes in createTcFromRequirement()
 * 20050906 - francisco mancardi - reduce global coupling 
 * 20050901 - Martin Havlat - updated metrics/results related functions 
 * 20050830 - francisco mancardi - changes in printSRS()
 * 20050829 - Martin Havlat - updated function headers 
 * 20050825 - Martin Havlat - updated global header;
 * 20051025 - MHT - corrected introduced bug with insert TC (Bug 197)
 *
 * 
 */
////////////////////////////////////////////////////////////////////////////////

define('TL_REQ_STATUS_VALID', 'v');
define('TL_REQ_STATUS_NOTTESTABLE', 'n');

$arrReqStatus = array(TL_REQ_STATUS_VALID => lang_get('req_state_valid'), 
					  TL_REQ_STATUS_NOTTESTABLE => lang_get('req_state_not_testable'),
					  );

require_once('print.inc.php');
require_once("../testcases/archive.inc.php");

/** 
 * update System Requirements Specification
 *  
 * @param integer $id
 * @param string $title
 * @param string $scope
 * @param string $countReq
 * @param integer $user_id
 * @param string $type
 * @return string result
 * 
 * @author Martin Havlat 
 */
function updateReqSpec(&$db,$id, $title, $scope, $countReq, $user_id, $type = 'n')
{
	$result = 'ok';
	if (checkRequirementTitle($title,$result))
	{
		$sql = "UPDATE req_specs SET title='" . $db->prepare_string($title) . 
				"', scope='" . $db->prepare_string($scope) . "', type='" . $db->prepare_string($type) .
				"', total_req ='" . $db->prepare_string($countReq) . "', modifier_id='" . 
				$db->prepare_string($user_id) . "', modification_ts=CURRENT_DATE WHERE id=" . $id;
		if (!$db->exec_query($sql))
			$result = lang_get('error_updating_reqspec');
	}
	
	return $result; 
}

/** 
 * delete System Requirement Specification
 *  
 * @param integer $srs_id
 * @return string result comment
 * 
 * @author Martin Havlat 
 **/
function deleteReqSpec (&$db,$srs_id)
{
	// delete requirements and coverage
	$arrReq = getRequirements($db,$srs_id);
	if (sizeof($arrReq))
	{
		foreach ($arrReq as $oneReq) {
			$result = deleteRequirement($db,$oneReq['id']);
		}
	}
		
	// delete specification itself
	$sql = "DELETE FROM req_specs WHERE id=" . $srs_id;
	$result = $db->exec_query($sql); 
	if ($result) {
		$result = 'ok';
	} else {
		$result = 'The DELETE SRS request fails.';
		tLog('SQL: ' . $sql . ' fails: ' . $db->error_msg(), 'ERROR');
	}
	return $result; 
}


/** 
 * get list of all SRS for the current product 
 * 
 * @return associated array List of titles according to IDs
 * 
 * @author Martin Havlat 
 **/
function getOptionReqSpec(&$db,$testproject_id)
{
	$sql = "SELECT id,title FROM req_specs WHERE testproject_id=" . $testproject_id . 
			" ORDER BY title";
	
	return $db->fetchColumnsIntoMap($sql,'id','title');
}


/** 
 * collect information about current list of Requirements in req. Specification
 *  
 * @param string $srs_id ID of req. specification
 * @param string range = ["all" (default), "assigned"] (optional)
 * 			"unassign" is not implemented because requires subquery 
 * 			which is not available in MySQL 4.0.x
 * @param string Test case ID - required if assigned or unassigned scope is used
 * @return assoc_array list of requirements
 * 
 * @author Martin Havlat 
 */
function getRequirements(&$db,$srs_id, $range = 'all', $testcase_id = null)
{
	if ($range == 'all') {
		$sql = "SELECT * FROM requirements WHERE srs_id=" . $srs_id . " ORDER BY title";
	}
	elseif ($range == 'assigned') {
		$sql = "SELECT requirements.* FROM requirements,req_coverage WHERE srs_id=" . 
				$srs_id . " AND req_coverage.req_id=requirements.id AND " . 
				"req_coverage.testcase_id=" . $testcase_id . " ORDER BY title";
	}

	return $db->get_recordset($sql);
}

/** 
 * function allows to obtain unassigned requirements 
 * 
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

/**
 * get analyse based on requirements and test specification
 * 
 * @param integer $srs_id
 * @return array Coverage in three internal arrays: covered, uncovered, nottestable REQ
 * @author martin havlat
 */
function getReqCoverage_general(&$db,$srs_id)
{
	$output = array('covered' => array(), 'uncovered' => array(), 'nottestable' => array());
	
	// get requirements
	$sql_common = "SELECT id,title FROM requirements WHERE srs_id=" . $srs_id;
	$sql = $sql_common . " AND status='v' ORDER BY title";
	$arrReq = $db->get_recordset($sql);

	// get not-testable requirements
	$sql = $sql_common . " AND status='" . NON_TESTABLE_REQ . "' ORDER BY title";
	$output['nottestable'] = $db->get_recordset($sql);
	
	// get coverage
	if (sizeof($arrReq))
	{
		foreach ($arrReq as $req) 
		{
			// collect TC for REQ
			$arrCoverage = getTc4Req($db,$req['id']);
	
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
 * 
 * @param integer $srs_id
 * @return array results
 * @author havlatm
 */
function getReqMetrics_general(&$db,$srs_id)
{
	$output = array();
	
	// get nottestable REQs
	$sql = "SELECT count(*) AS cnt FROM requirements WHERE srs_id=" . $srs_id . 
			" AND status='n'";
	$output['notTestable'] = $db->fetchFirstRowSingleColumn($sql,'cnt');

	$sql = "SELECT count(*) AS cnt FROM requirements WHERE srs_id=" . $srs_id;
	$output['total'] = $db->fetchFirstRowSingleColumn($sql,'cnt');
	tLog('Count of total REQ in DB for srs_id:'.$srs_id.' = '.$output['total']);

	$sql = "SELECT total_req FROM req_specs WHERE id=" . $srs_id;
	$output['expectedTotal'] = $db->fetchFirstRowSingleColumn($sql,'total_req');
	tLog(' Redefined Count of total REQ in DB for srs_id:'.$srs_id.' = '.$output['total']);
	
	if ($output['expectedTotal'] == 'n/a') {
		$output['expectedTotal'] = $output['total'];
	}
	
	$sql = "SELECT DISTINCT requirements.id FROM requirements, req_coverage WHERE" .
				" requirements.srs_id=" . $srs_id .
				" AND requirements.id=req_coverage.req_id";
	$result = $db->exec_query($sql);
	if (!empty($result)) {
		$output['covered'] = $db->num_rows($result);
	}

	$output['uncovered'] = $output['expectedTotal'] - $output['covered'] 
			- $output['notTestable'];

	return $output;
}

/**
 * get requirement coverage metrics for a Test Plan
 * 
 * @param integer $srs_id
 * @param integer $idTestPlan
 * @return array Results
 * @author havlatm
 */
function getReqMetrics_testPlan(&$db,$srs_id, $idTestPlan)
{
	$output = getReqMetrics_general($db,$srs_id);
	$output['coveredByTestPlan'] = 0;
	
	$sql = "SELECT DISTINCT requirements.id FROM requirements,testcase," .
			"req_coverage,category,component WHERE requirements.srs_id=" . $srs_id .
				" AND component.projid=" . $idTestPlan .
				" AND category.compid=component.id AND category.id=testcase.catid" .
				" AND testcase.mgttcid = req_coverage.testcase_id AND req_id=requirements.id" .
				" AND requirements.status = 'v'"; 
	$result = $db->exec_query($sql);
	if (!empty($result)) {
		$output['coveredByTestPlan'] = $db->num_rows($result);
	}

	$output['uncoveredByTestPlan'] = $output['expectedTotal'] 
			- $output['coveredByTestPlan'] - $output['notTestable'];

	return $output;
}


/** 
 * collect information about one Requirement
 *  
 * @param string $req_id ID of req.
 * @return assoc_array list of requirements
 */
function getReqData(&$db,$req_id)
{
	$sql = "SELECT * FROM requirements WHERE id=" . $req_id;

	return $db->fetchFirstRow($sql);
}

/** collect coverage of Requirement 
 * @param string $req_id ID of req.
 * @return assoc_array list of test cases [id, title]
 */
function getTc4Req(&$db,$req_id)
{
	$sql = "SELECT nodes_hierarchy.id,nodes_hierarchy.name 
	        FROM nodes_hierarchy, req_coverage
			    WHERE req_coverage.testcase_id = nodes_hierarchy.id
			    AND  req_coverage.req_id={$req_id}"; 
	return selectData($db,$sql);
}


/** collect coverage of Requirement for Test Suite
 * @param string $req_id ID of req.
 * @param string $idPlan ID of Test Plan
 * @return assoc_array list of test cases [id, title]
 * @author martin havlat
 */
function getSuite4Req(&$db,$req_id, $idPlan)
{
	$sql = "SELECT testcase.id,testcase.title FROM testcase,req_coverage,category," .
				"component WHERE component.projid=" . $idPlan .
				" AND category.compid=component.id AND category.id=testcase.catid" .
				" AND testcase.mgttcid = req_coverage.testcase_id AND req_id=" . 
				$req_id . " ORDER BY title";
	
	return $db->get_recordset($sql);
}

/** 
 * collect coverage of TC
 *  
 * @param string $testcase_id ID of req.
 * @param string SRS ID (optional)
 * @return assoc_array list of test cases [id, title]
 */
function getReq4Tc(&$db,$testcase_id, $srs_id = 'all')
{
	$sql = "SELECT requirements.id,requirements.title FROM requirements, req_coverage " .
			"WHERE req_coverage.testcase_id=" . $testcase_id . 
			" AND req_coverage.req_id=requirements.id";
	// if only for one specification is required
	if ($srs_id != 'all') {
		$sql .= " AND requirements.srs_id=" . $srs_id;
	}

	return $db->get_recordset($sql);
}
/**
 * Function-Documentation
 *
 * @param type $title documentation
 * @param type $result [ref] documentation
 * @return type documentation
 *
 * @author Andreas Morsing <schlundus@web.de>
 * @since 12.03.2006, 22:04:20
 *
 **/
function checkRequirementTitle($title,&$result)
{
	$bSuccess = 0;
	if (!strlen($title))
		$result = lang_get("warning_empty_req_title");
	else
		$bSuccess = 1;
		
	return $bSuccess;
}
/** 
 * creates a new Requiement 
 * 
 * @param string $title
 * @param string $scope
 * @param integer $srs_id
 * @param integer $user_id
 
 * @param char $status
 * @param char $type
 * 
 * @author Martin Havlat 
 **/
function createRequirement(&$db,$title, $scope, $srs_id, $user_id, 
                           $status = TL_REQ_STATUS_VALID, $type = 'n', $req_doc_id = null)
{
	$result = 'ok';
	if (checkRequirementTitle($title,$result))
	{
		$sql = "INSERT INTO requirements (srs_id, req_doc_id, title, scope, status, type, author_id, creation_ts)" .
				" VALUES (" . $srs_id . ",'" . $db->prepare_string($req_doc_id) .  
				"','" . $db->prepare_string($title) . "','" . $db->prepare_string($scope) . 
				 "','" . $db->prepare_string($status) . "','" . $db->prepare_string($type) .
				 "'," . $db->prepare_string($user_id) . ", CURRENT_DATE)";

		if (!$db->exec_query($sql))
		 	$result = lang_get('error_inserting_req');
	}
	 
	return $result; 
}


/** 
 * update Requirement 
 * 
 * @param integer $id
 * @param string $title
 * @param string $scope
 * @param integer $user_id
 
 * @param string $status
 * @param string $type
 * 
 * @author Martin Havlat 
 **/
function updateRequirement(&$db,$id, $title, $scope, $user_id, $status, $type, $reqDocId=null)
{
	$result = 'ok';
	if (checkRequirementTitle($title,$result))
	{
		$sql = "UPDATE requirements SET title='" . $db->prepare_string($title) . 
				"', scope='" . $db->prepare_string($scope) . "', status='" . 
				$db->prepare_string($status) . 
				"', type='" . $db->prepare_string($type) . 
				"', modifier_id='" . $db->prepare_string($user_id) . 
				"', req_doc_id='" . $db->prepare_string($reqDocId) .
				"', modification_ts=CURRENT_DATE WHERE id=" . $id;	
		if (!$db->exec_query($sql))
		 	$result = lang_get('error_updating_req');
	}
	
	return $result; 
}

/** 
 * delete Requirement
 *  
 * @param integer $id
 * 
 * @author Martin Havlat 
 **/
function deleteRequirement(&$db,$id)
{
	// delete dependencies with test specification
	$sql = "DELETE FROM req_coverage WHERE req_id=" . $id;
	$result = $db->exec_query($sql); 
	if ($result)
	{
		$sql = "DELETE FROM requirements WHERE id=" . $id;
		$result = $db->exec_query($sql); 
	}
	if ($result)
		$result = deleteAttachmentsFor($db,$id,"requirements");

	if (!$result)
		$result = lang_get('error_deleting_req');
	else
		$result = 'ok';
		
	return $result; 
}

/** 
 * print Requirement Specification 
 *
 * @param integer $srs_id
 * @param string $prodName
 * @param string $user_id
 * @param string $base_href
 *
 * @author Martin Havlat
 *  
 * @version 1.2 - 20050905
 * @author Francisco Mancardi
 *
 * @version 1.1 - 20050830
 * @author Francisco Mancardi
 *
 **/
function printSRS(&$db,&$tproject,$srs_id, $prodName, $testproject_id, $user_id, $base_href)
{
	$arrSpec = $tproject->getReqSpec($testproject_id,$srs_id);
	
	$title = $arrSpec[0]['title'];
	$output =  printHeader($title,$base_href);
	$output .= printFirstPage($db,$title,$prodName,'',$user_id);
	$output .= "<h2>" . lang_get('scope') . "</h2>\n<div>" . $arrSpec[0]['scope'] . "</div>\n";
	$output .= printRequirements($db,$srs_id);
	$output .= "\n</body>\n</html>";

	return $output;
}

/** 
 * print Requirement for SRS 
 * 
 * @param integer $srs_id
 * 
 * @author Martin Havlat 
 * 20051125 - scs - added escaping of req names
 * 20051202 - scs - fixed 241
 **/
function printRequirements(&$db,$srs_id)
{
	$arrReq = getRequirements($db,$srs_id);
	
	$output = "<h2>" . lang_get('reqs') . "</h2>\n<div>\n";
	if (count($arrReq))
	{
		foreach ($arrReq as $REQ)
		{
			$output .= '<h3>' .htmlspecialchars($REQ["req_doc_id"]). " - " . 
						htmlspecialchars($REQ['title']) . "</h3>\n<div>" . 
						$REQ['scope'] . "</div>\n";
		}
	}
	else
		$output .= '<p>' . lang_get('none') . '</p>';

	$output .= "\n</div>";

	return $output;
}


/** 
 * assign requirement and test case
 * @param integer test case ID
 * @param integer requirement ID
 * @return integer 1 = ok / 0 = problem
 * 
 * @author Martin Havlat 
 */
function assignTc2Req(&$db,$testcase_id, $req_id)
{
	$output = 0;
	tLog("assignTc2Req TC:" . $testcase_id . ' and REQ:' . $req_id);
	
	if ($testcase_id && $req_id)
	{
		$sql = 'SELECT COUNT(*) AS num_cov FROM req_coverage WHERE req_id=' . $req_id . 
				' AND testcase_id=' . $testcase_id;
		$result = $db->exec_query($sql);

    $row=$db->fetch_array($result);
		if ($row['num_cov'] == 0) {
	
			// create coverage dependency
			$sqlReqCov = 'INSERT INTO req_coverage (req_id,testcase_id) VALUES ' .
					"(" . $req_id . "," . $testcase_id . ")";
			$resultReqCov = $db->exec_query($sqlReqCov);
			// collect results
			if ($db->affected_rows() == 1) {
				$output = 1;
				tLog('Dependency was created between TC:' . $testcase_id . ' and REQ:' . $req_id, 'INFO');
			}
			else
			{
				tLog("Dependency wasn't created between TC:" . $testcase_id . ' and REQ:' . $req_id .
					"\t" . $db->error_msg(), 'ERROR');
			}
		}
		else
		{
			$output = 1;
			tLog('Dependency already exists between TC:' . $testcase_id . ' and REQ:' . $req_id, 'INFO');
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
 * @author Martin Havlat 
 */
function unassignTc2Req(&$db,$testcase_id, $req_id)
{
	$output = 0;
	tLog("unassignTc2Req TC:" . $testcase_id . ' and REQ:' . $req_id);

	// create coverage dependency
	$sqlReqCov = 'DELETE FROM req_coverage WHERE req_id=' . $req_id . 
			' AND testcase_id=' . $testcase_id;
	$resultReqCov = $db->exec_query($sqlReqCov);

	// collect results
	if ($db->affected_rows() == 1) {
		$output = 1;
		tLog('Dependency was deleted between TC:' . $testcase_id . ' and REQ:' . $req_id, 'INFO');
	}
	else {
		tLog("Dependency wasn't deleted between TC:" . $testcase_id . ' and REQ:' . $req_id .
				"\n" . $sqlReqCov. "\n" . $db->error_msg(), 'ERROR');
	}

	return $output;
}



/** 
 * function generate testcases with name and summary for requirements
 * @author Martin Havlat 
 *
 * @param numeric prodID
 * @param array or integer list of REQ id's 
 * @return string Result description
 * 
 *
 * @author Francisco Mancardi - reduce global coupling
 * @author Francisco Mancardi
 * interface changes added $srs_id
 * use new configuration parameter
 * 20051025 - MHT - corrected introduced bug with insert TC
 *
 * 20060110 - fm - user_id
 */
function createTcFromRequirement(&$db,&$tproject,$mixIdReq, $testproject_id, $srs_id, $user_id)
{
	//global $g_req_cfg;
	//global $g_field_size;
  // 20060110 - fm 
	$g_req_cfg = config_get('req_cfg');
	$g_field_size = config_get('field_size');
	$auto_category_name = $g_req_cfg->default_category_name;
	$auto_component_name = $g_req_cfg->default_component_name;

	tLog('createTcFromRequirement started:'.$mixIdReq.','.$testproject_id.','.$srs_id.','.$user_id);
	$output = null;
	if (is_array($mixIdReq)) {
		$arrIdReq = $mixIdReq;
	} else {
		$arrIdReq = array($mixIdReq);
	}
	if ( $g_req_cfg->use_req_spec_as_category_name )
	{
	  // SRS Title
	  $arrSpec = $tproject->getReqSpec($testproject_id,$srs_id);
	  $auto_category_name = substr($arrSpec[0]['title'],0,$g_field_size->category_name);
	}
	
	//find component
	$sqlCOM = " SELECT id FROM mgtcomponent " .
	          " WHERE name='" . $auto_component_name . "' " .
	          " AND prodid=" . $testproject_id;
	          
	$resultCOM = $db->exec_query($sqlCOM);
  if ($db->num_rows($resultCOM) == 1) {
		$row = $db->fetch_array($resultCOM);
		$idCom = $row['id'];
	}
	else {
		// not found -> create
		tLog('Component:' . $auto_component_name . ' was not found.');
		$sqlInsertCOM = " INSERT INTO mgtcomponent (name,scope,prodid) " .
		                " VALUES (" . "'" . $db->prepare_string($auto_component_name) . "'," .
		                              "'" . $db->prepare_string($g_req_cfg->scope_for_component) . "'," .  
		                $testproject_id . ")";
		                
		$resultCOM = $db->exec_query($sqlInsertCOM);
		if ($db->affected_rows()) {
			$resultCOM = $db->exec_query($sqlCOM);
			if ($db->num_rows($resultCOM) == 1) {
				$row = $db->fetch_array($resultCOM);
				$idCom = $row['id'];
			} else {
				tLog('Component:' . $auto_component_name . 
				     ' was not found again! ' . $db->error_msg());
			}
		} else {
			tLog($db->error_msg(), 'ERROR');
		}
	}
	tLog('createTcFromRequirement: $idCom=' . $idCom);

	//find category
	$sqlCAT = " SELECT id FROM mgtcategory " .
	          " WHERE name='" . $db->prepare_string($auto_category_name) . "' " .
	          " AND compid=" . $idCom;
	          
	$resultCAT = $db->exec_query($sqlCAT);
	if ($resultCAT && ($db->num_rows($resultCAT) == 1)) {
		$row = $db->fetch_array($resultCAT);
		$idCat = $row['id'];
	}
	else {
		// not found -> create
		// 20060110 - fm - added config,data,tools
		$sqlInsertCAT = " INSERT INTO mgtcategory (name,objective,compid,config,data,tools) " .
		                " VALUES (" . "'" . 
		                $db->prepare_string($auto_category_name) . "'," . "'" . 
		                $db->prepare_string($g_req_cfg->objective_for_category) . "'," .
		                $idCom .  ",'','','')";
				                     
		$resultCAT = $db->exec_query($sqlInsertCAT);
		$resultCAT = $db->exec_query($sqlCAT);
		if ($db->num_rows($resultCAT) == 1) {
			$row = $db->fetch_array($resultCAT);
		  $idCat = $row['id'];
		} else {
			die($db->error_msg());
		}
	}
	tLog('createTcFromRequirement: $idCat=' . $idCat);

	//create TC
	foreach ($arrIdReq as $execIdReq) 
	{
		//get data
		tLog('proceed: $execIdReq=' . $execIdReq);
		$reqData = getReqData($db,$execIdReq);

		tLog('$reqData:' . implode(',',$reqData));
		
		// create TC
		// 20051025 - MHT - corrected input parameters order
		/* 
		  // 20060110 - fm
		  function insertTestcase(&$db,$catID,$title,$summary,$steps,
                             $outcome,$user_id,$tcOrder = null,$keywords = null)
    */
		
		$tcID =  insertTestcase($db,$idCat, $reqData['title'], "Verify requirement: \n" . 
				                    $reqData['scope'], null, null, $user_id,null,null);
		
		// create coverage dependency
		if (!assignTc2Req($db,$tcID, $reqData['id'])) {
			$output = 'Test case: ' . $reqData['title'] . "was not created </br>";
		}
	}

	return (!$output) ? 'ok' : $output;
}


?>