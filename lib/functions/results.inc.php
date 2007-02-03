<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @filesource $RCSfile: results.inc.php,v $
 * @version $Revision: 1.38 $
 * @modified $Date: 2007/02/03 22:14:07 $   $Author: schlundus $
 * 
 * @author 	Martin Havlat 
 * @author 	Chad Rosen (original report definition)
 *
 * Functions for Test Reporting and Metrics
 *
 * @author 20051108 - fm - BUGID 82 changes in getTCLink()
 * @author 20050905 - fm - bug in getBugsReport()
 * @author 20050905 - fm - refactoring - remove global coupling
 *
 *
 * @author 20050428 - fm
 * use g_tc_status instead of MAGIC CONSTANTS 'f','b', ecc
 * refactoring of sql (using base_sql)
 * 
 * THIS FILE IS DEPRECATED AND WILL BE DELETED   
 */
#require_once('../../config.inc.php');
#require_once("common.php");
#require_once("builds.inc.php");

/**
* Function send header which initiate MS excel
*/
function DEPR_sendXlsHeader()
{
	header("Content-Disposition: inline; filename=testReport.xls");
	header("Content-Description: PHP Generated Data");
	header("Content-type: application/vnd.ms-excel; name='My_Excel'");
	flush();
}

/**
* Function get Test results Status from character
*
* @param string Test Id
* @param string Build Number
* @return string Status  
*
* @author Francisco Mancardi - 20050905 - refactoring fetch_assoc
*/
function DEPR_getStatus(&$db,$tcId, $buildID)
{
	$sql = " SELECT status FROM results WHERE results.tcid=" . $tcId . 
	       " AND results.build_id=" . $buildID;
	$result = $db->exec_query($sql);
	$myrow = $db->fetch_array($result);
	return $myrow['status'];
}


/**
* Function get Test results Status from character
* @param string $status Status character; e.g. p -> Passed
* @return string Status  
*
* 20050425 - fm
*/
function DEPR_getStatusName($status)
{
	global $g_tc_status;
	
	$desc = '???';
	if (in_array($status,$g_tc_status))
		$desc = array_search($status,$g_tc_status);
	
	return $desc;
}


/**
* Function returns number of Test Cases in the Test Plan
* @param string $tpID Test Plan ID
* @return string Count of test set 
*
* Rev :
*      20060104 - fm - ADODB
*            
*/
function DEPR_getPlanTCNumber(&$db,$tpID)
{
	$sql = "SELECT count(testcase.id) FROM testplans,component,category,testcase WHERE " .
			   "testplans.id =" . $tpID . " AND testplans.id = component.projid " .
			   "and component.id = category.compid and category.id = testcase.catid";
	$result = $db->exec_query($sql);
	$sumTCs = $db->fetch_array($result); 

	return $sumTCs[0];
}

/**
* Function returns number of Test Cases in the Test Plan
* @return string Link of Test ID + Title 
*/
function DEPR_getTCLink($rights, $tcID,$tcversionID, $title, $buildID)
{
	$title = htmlspecialchars($title);
	$suffix = $tcID . ":&nbsp;<b>" . $title. "</b></a>";
	
	$testTitle = '<a href="lib/execute/execSetResults.php?level=testcase&build_id='
				 . $buildID . '&id=' . $tcID.'&version_id='.$tcversionID.'">';
	$testTitle .= $suffix;
		
	return $testTitle;
}


/**
* Function collect build results 
*
* @param string $tpID Test Plan ID; 
* @param string $build Build number
* @return assoc array ('passed'  => total tc Passed, 
                       'failed'  => total tc Failed, 
                       'blocked' => total tc Blocked
*
* 20051002 - fm - refactoring, return type changed
*/
function DEPR_getPlanStatus(&$db,$tpID, $buildID)
{
	global $g_tc_status;

	// MHT 200507 improved SQL
	$base_sql = " SELECT count(results.tcid) AS num_results " .
	            " FROM component,category,testcase,results " .
			        " WHERE component.id = category.compid " .
			        " AND category.id = testcase.catid " . 
			        " AND testcase.id = results.tcid " .
			        " AND component.projid =" . $tpID . 
			        " AND results.build_id = " . $buildID ;

	//Get the total # of passed testcases for the testplan and build
	$sql = $base_sql . " AND status = '" . $g_tc_status['passed'] . "'";
	$passedResult = $db->exec_query($sql);
	$passedTCs = $db->fetch_array($passedResult);
	$totalPassed = $passedTCs[0];

	//Get the total # of failed testcases for the testplan
	$sql = $base_sql . " AND status = '" . $g_tc_status['failed'] . "'";
	$failedResult = $db->exec_query($sql);
	$failedTCs = $db->fetch_array($failedResult);
	$totalFailed = $failedTCs[0];

	//Get the total # of blocked testcases for the testplan
	$sql = $base_sql . " AND status = '" . $g_tc_status['blocked'] . "'";
	$blockedResult = $db->exec_query($sql);
	$blockedTCs = $db->fetch_array($blockedResult);
	$totalBlocked = $blockedTCs[0];

  // 20051002 - fm - assoc
	return array('passed' => $totalPassed, 
	             'failed' => $totalFailed, 
	             'blocked' => $totalBlocked);
}

/**
* Function generates stats based on Test Suites
*
* @param $tpID Test Plan ID
* @param string build ID (optional)
* @return array (component name, $totalTCs, $pass, $fail, $blocked,
*				$notRunTCs, $percentComplete)
* @todo calculate results in db via select count; optimalize SQL requests
*
* Rev :
*       20050807 - fm
*       Added $tpID to remove Global Coupling via $_SESSION
* 
*/
function DEPR_getTestSuiteReport(&$db,$tpID, $buildID = 'all')
{
	global $g_tc_status;
  
	$arrOutput = array();
	
	$sql = " SELECT MGTCOMP.name AS comp_name, COMP.id AS comp_id" .
	       " FROM component COMP, mgtcomponent MGTCOMP" .
	       " WHERE MGTCOMP.id = COMP.mgtcompid " .
	       " AND COMP.projid = " . $tpID;

	$result = $db->exec_query($sql);

	while ($myrow = $db->fetch_array($result)) {

		$testCaseArray = null;
		$sql = " SELECT COUNT(TC.id) AS cnt" .
		       " FROM component COMP, category CAT, testcase TC " .
				   " WHERE COMP.id = CAT.compid " .
				   " AND CAT.id = TC.catid" .
				   " AND COMP.projid = " . $tpID . 
				   " AND COMP.id=" . $myrow['comp_id']; 
				
		// 20050901 - MHT - used generalication
		$totalTCs = $db->fetchFirstRowSingleColumn($sql,'cnt');

    // ------------------------------------------------------------------------------
		//Code to grab the results of the test case execution
    // 20050905 - fm 
		$csBuilds = get_cs_builds($db,$tpID);
  	$sql = " SELECT tcid,status FROM  results,component,category,testcase " .
	  " WHERE component.projid = " . $tpID . 
	  " AND component.id=" . $myrow['comp_id'] . 
	  " AND component.id = category.compid " .
	  " AND category.id = testcase.catid " .
	  " AND testcase.id = results.tcid " ;
	
  	if ($buildID == 'all') 
	  {
  	    $sql .= " AND results.build_id IN (" . $csBuilds . " ) ORDER BY results.build_id";
	  } 
  	else 
  	{
  			$sql .= " AND results.build_id='" . $buildID .	"' ";
  	}
  	// ------------------------------------------------------------------------------
  	
		$totalResult = $db->exec_query($sql);

		//Setting the results to an array.. Only taking the most recent results and displaying them
		while($totalRow = $db->fetch_array($totalResult)){
			// This is a test.. I've got a problem if the user goes and sets a previous p,f,b 
			// value to a 'n' value. 
			// The program then sees the most recent value as an not run. 
			// I think we want the user to then see the most recent p,f,b value
			if($totalRow['status'] != $g_tc_status['not_run']){
				$testCaseArray[$totalRow['tcid']] = $totalRow['status'];
			}
		}

		//This is the code that determines the pass,fail,blocked amounts
		$pass = 0;
		$fail = 0;
		$blocked = 0;
		$notRun = 0;

		//I had to write this code so that the loop before would work.. 
		//I'm sure there is a better way to do it but hell if I know how to figure it out..
		if(count($testCaseArray) > 0){
			foreach($testCaseArray as $tc){

				if($tc == $g_tc_status['passed']){
					$pass++;
				}elseif($tc == $g_tc_status['failed']){
					$fail++;
				}elseif($tc == $g_tc_status['blocked']){
					$blocked++;
				}
				unset($testCaseArray);
			}//end foreach
		}//end if

		//This loop will cycle through the arrays and count the amount of p,f,b,n
		if($totalTCs == 0){
			$percentComplete= 0;
		}else{
			$percentComplete = ($pass + $fail + $blocked) / $totalTCs; //Getting total percent complete
			$percentComplete = round((100 * ($percentComplete)),2); //Rounding the number so it looks pretty
		}
		
		$notRunTCs = $totalTCs - ($pass + $fail + $blocked); //Getting the not run TCs

		array_push($arrOutput, array($myrow['comp_name'], $totalTCs, $pass, $fail, $blocked,
				$notRunTCs, $percentComplete));
	}
	return $arrOutput;
}

/**
* Function generates stats based on Keywords
*
* @param numeric tpID (Test Plan ID)
* @param string build ID (optional)
* @return array $keyword, $totalTCs, $pass, $fail, $blocked,
*				$notRunTCs, $percentComplete
*/

function DEPR_getKeywordsReport(&$db,$tpID, $buildID = 'all')
{
	global $g_tc_status;
  
	$arrOutput = array();
	// MHT 200507 improved SQL
	$sqlKeyword = "SELECT DISTINCT(keywords) FROM component, category, testcase WHERE" .
			" component.projid = " .  $tpID . " AND component.id = category.compid" .
			" AND category.id = testcase.catid ORDER BY keywords";
	$resultKeyword = $db->exec_query($sqlKeyword);

	//Loop through each of the testcases
	$keyArray = null;
	while ($myrowKeyword = $db->fetch_array($resultKeyword))
	{
		$keyArray .= $myrowKeyword[0].",";
	}
	//removed quotes and separate the list
	$keyArray = explode(",",$keyArray);

	//I need to make sure there are elements in the result 2 array. I was getting an error when I didn't check
	if(count($keyArray))
	{
		$keyArray = array_unique ($keyArray);
  }
	
	foreach($keyArray as $key=>$word)
	{
		$testCaseArray = null;
		//For some reason I'm getting a space.. Now I'll ignore any spaces
		if($word != ""){
				
			//Code to grab the entire amount of test cases per testplan
			$keyWord = $word;
			$word = $db->prepare_string($word);
			$sql = " SELECT count(testcase.id) FROM  testplans,component,category,testcase " .
			       " WHERE testplans.id = " . $tpID . " AND testplans.id = component.projid " .
			       " AND component.id = category.compid AND category.id = testcase.catid AND " .
			       " (testcase.keywords LIKE '%,{$word},%' OR testcase.keywords LIKE '{$word},%')";
			$totalTCResult = $db->exec_query($sql);
			$totalTCs = $db->fetch_array($totalTCResult);

			//Code to grab the results of the test case execution

			// KL OCT 14, 2005
			// when buildID is all, we still need to make sure
			// we only get results executed on build_id's which are part
			// of this test plan.  $csBuilds provides a list of 
			// comma delimited builds in the plan and must be used
			// in query statement.
			if ($buildID == 'all') {
			  $csBuilds = get_cs_builds($db,$tpID);
			  $sql = "SELECT tcid,status FROM  results,testplans,component,category,testcase" .
			    " WHERE testplans.id = " . $tpID . " AND testplans.id = component.projid" .
			    " AND component.id = category.compid" .
			    " AND category.id = testcase.catid and testcase.id = results.tcid" .
			    " AND (keywords LIKE '%,{$word},%' OR keywords LIKE '{$word},%') " .
			    " AND (results.build_id IN (" . 
			    $csBuilds . ")) ORDER BY results.build_id";
			} else {
				$sql = "SELECT tcid,status FROM  results,testplans,component,category,testcase" .
					" WHERE testplans.id = " . $tpID . " AND results.build_id = " . $buildID . 
					" AND testplans.id = component.projid" .
					" AND component.id = category.compid" .
					" AND category.id = testcase.catid AND testcase.id = results.tcid" .
					" AND (keywords LIKE '%,{$word},%' OR keywords LIKE '{$word},%')";
			}
			$totalResult = $db->exec_query($sql);

			//Setting the results to an array.. Only taking the most recent results and displaying them
			while($totalRow = $db->fetch_array($totalResult)){

				//This is a test.. I've got a problem if the user goes and sets a 
				//previous p,f,b value to a 'n' value. 
				//The program then sees the most recent value as an not run. 
				//I think we want the user to then see the most recent p,f,b value
				if($totalRow['status'] != $g_tc_status['not_run']){
					$testCaseArray[$totalRow['tcid']] = $totalRow['status'];
				}
			}

			//This is the code that determines the pass,fail,blocked amounts
			$pass = 0;
			$fail = 0;
			$blocked = 0;
			$notRun = 0;

			//I had to write this code so that the loop before would work.. 
			//I'm sure there is a better way to do it but hell if I know how to figure it out..
			if(count($testCaseArray) > 0){
				foreach($testCaseArray as $tc){
					if($tc == $g_tc_status['passed']){
						$pass++;
					}elseif($tc == $g_tc_status['failed']){
						$fail++;
					}elseif($tc == $g_tc_status['blocked']){
						$blocked++;
					}
				}//end for each
			}//end if

			//destroy the testCaseArray variable
			unset($testCaseArray);

			$notRunTCs = $totalTCs[0] - ($pass + $fail + $blocked); //Getting the not run TCs
				
			if($totalTCs[0] == 0){ //if we try to divide by 0 we get an error
				$percentComplete = 0;
			}else{
				$percentComplete = ($pass + $fail + $blocked) / $totalTCs[0]; //Getting total percent complete
				$percentComplete = round((100 * ($percentComplete)),2); //Rounding the number so it looks pretty
			}		

			array_push($arrOutput, array($keyWord, $totalTCs[0], $pass, $fail, $blocked,
					$notRunTCs, $percentComplete));
		}
	}
	return $arrOutput;
}

/**
* Function generates Metrics based on owner
*
* @return array $owner, $totalTCs, $pass, $fail, $blocked,
*				$notRunTCs, $percentComplete
*/
function DEPR_getOwnerReport(&$db,$tpID)
{
	global $g_tc_status;

	$testCaseArray = null;
	$arrOutput = array();
	$sql = " SELECT category.owner, category.id FROM  testplans,component, category " .
	       " WHERE testplans.id = " . $tpID . " and testplans.id = component.projid " .
	       " AND component.id = category.compid group by owner";
	$result = $db->exec_query($sql);

	while ($myrow = $db->fetch_array($result)) {
		//Code to grab the entire amount of test cases per testplan
		$sql = " SELECT count(testcase.id) FROM  testplans,component,category,testcase " .
		       " WHERE testplans.id =" . $tpID . " AND testplans.id = component.projid " .
		       " AND category.owner ='" . $myrow[0] . "' and component.id = category.compid " .
		       " AND category.id = testcase.catid";
		$totalTCResult = $db->exec_query($sql);
		$totalTCs = $db->fetch_array($totalTCResult);
		$csBuilds = get_cs_builds($db,$tpID);
		//Code to grab the results of the test case execution
		$sql = " SELECT tcid,status FROM  results,testplans,component,category,testcase " .
				   " WHERE testplans.id = " . $tpID . " and category.owner='" . $myrow[0] . 
				   "' AND testplans.id = component.projid and component.id = category.compid" .
				   " AND category.id = testcase.catid AND testcase.id = results.tcid" .
				   " AND results.build_id IN (" . $csBuilds . " ) ORDER BY build_id";
		  	     

		$totalResult = $db->exec_query($sql);

		//Setting the results to an array.. Only taking the most recent results and displaying them
		while($totalRow = $db->fetch_array($totalResult)){
			//This is a test.. 
			// I've got a problem if the user goes and sets a previous p,f,b value to a 'n' value.
			// The program then sees the most recent value as an not run.
			// I think we want the user to then see the most recent p,f,b value
			if($totalRow['status'] != $g_tc_status['not_run']){
				$testCaseArray[$totalRow['tcid']] = $totalRow['status'];
			}
		}

		//This is the code that determines the pass,fail,blocked amounts
		$pass = 0;
		$fail = 0;
		$blocked = 0;
		$notRun = 0;

		//I had to write this code so that the loop before would work.. 
		//I'm sure there is a better way to do it but hell if I know how to figure it out..

		if(count($testCaseArray) > 0){
			//This loop will cycle through the arrays and count the amount of p,f,b,n
			foreach($testCaseArray as $tc){

				if($tc == $g_tc_status['passed']){
					$pass++;
				}elseif($tc == $g_tc_status['failed']){
					$fail++;
				}elseif($tc == $g_tc_status['blocked']){
					$blocked++;
				}
			}//end foreach
		}//end if

		//destroy the testCaseArray variable
		unset($testCaseArray);
		
		$notRunTCs = $totalTCs[0] - ($pass + $fail + $blocked); //Getting the not run TCs
		
		if($totalTCs[0] == 0){ //if we try to divide by 0 we get an error
			$percentComplete = 0;
		}else{
			$percentComplete = ($pass + $fail + $blocked) / $totalTCs[0]; //Getting total percent complete
			$percentComplete = round((100 * ($percentComplete)),2); //Rounding the number so it looks pretty
		}		

		array_push($arrOutput, array($myrow[0], $totalTCs[0], $pass, $fail, $blocked,
				$notRunTCs, $percentComplete));
	}
	return $arrOutput;
}

/**
* Function generates Metrics based on priority
*
* @param int tpID (test plan ID)
* @param string build ID (optional)
* @return array 
*
* @author Francisco Mancardi - fm - reduce global coupling
*
*/
// MHT 200507 GENERAL REFACTORIZATION (use array through the function); SQL improve
// KL OCT 14, 2005 - setting $buildID to all is probably causing 
// some problems for us and returning results for cases
// not executed in the same test plan.
function DEPR_getPriorityReport(&$db,$tpID, $buildID = 'all')
{
	global $g_tc_status;
  
	// grabs the defined priority 
	$priority = getPriorityDefine($db,$tpID);
	
	//Initializing variables
	$arrAvailablePriority = array('a','b','c');
	$myResults = array ( 
		'a' => array('priority' => 'A', 'total' => 0, 'pass' => 0, 'fail' => 0, 'blocked' => 0, 
		             'milestone' => '-', 'status' => '-'),
		'b' => array('priority' => 'B', 'total' => 0, 'pass' => 0, 'fail' => 0, 'blocked' => 0, 
		             'milestone' => '-', 'status' => '-'),
		'c' => array('priority' => 'C', 'total' => 0, 'pass' => 0, 'fail' => 0, 'blocked' => 0, 
		             'milestone' => '-', 'status' => '-'),
		'milestone' => 'None', 
		'deadline' => 'None'
	);
	
	//Begin code to display the component
	$sql = " SELECT category.risk, category.id, category.importance " .
			   " FROM testplans,component, category WHERE testplans.id = " .  $tpID . 
			   " AND testplans.id = component.projid " .
			   " AND component.id = category.compid";
	$result = $db->exec_query($sql);
	
	while ($myrow = $db->fetch_array($result)) {
	
		$testCaseArray = null;

		$priStatus = $myrow[2] . $myrow[0]; //Concatenate the importance and priority together
		tLog('Category ID=' . $myrow[1] . ' has priority ' . $priStatus . ' and status ' . $priority[$priStatus]);
		
		//Code to grab the entire amount of test cases per testplan
		$sql = "SELECT count(testcase.id) FROM component,category,testcase WHERE " .
				"component.projid = " . $tpID . " AND category.id=" . 
				$myrow[1] .	" AND component.id = category.compid AND category.id = testcase.catid";
		$totalTCResult = $db->exec_query($sql);
		$totalTCs = $db->fetch_array($totalTCResult);
	
		//Code to grab the results of the test case execution
		if ($buildID == 'all'){
		  $csBuilds  = get_cs_builds($db,$tpID);
		  $sql = "SELECT tcid,status FROM results,component,category,testcase" .
		    " WHERE component.projid = " . $tpID . 
		    " AND category.id=" . $myrow[1] .
		    " AND component.id = category.compid AND category.id = testcase.catid" .
		    " AND testcase.id = results.tcid " .
		    " AND results.build_id IN (" . $csBuilds . 
		    " ) ORDER BY build_id";
		} else {
			$sql = "SELECT tcid,status FROM results,testplans,component,category,testcase" .
				" WHERE component.projid = " . $tpID .
				" AND results.build_id=" . $buildID . 
				" AND category.id=" . $myrow[1] . 
				" AND component.id = category.compid AND category.id = testcase.catid" .
				" AND testcase.id = results.tcid";
		}
		$totalResult = $db->exec_query($sql);
		//Setting the results to an array.. Only taking the most recent results and displaying them
		while($totalRow = $db->fetch_array($totalResult)){
	
			if($totalRow['status'] != $g_tc_status['not_run']){
				$testCaseArray[$totalRow['tcid']] = $totalRow['status'];
			}
		}
	
		//This is the code that determines the pass,fail,blocked amounts
		$pass = 0;
		$fail = 0;
		$blocked = 0;
		$notRun = 0;
	
		//I had to write this code so that the loop before would work.. I'm sure there is a better way to do it but hell if I know how to figure it out..
		if(count($testCaseArray) > 0)	{
			//This loop will cycle through the arrays and count the amount of p,f,b,n
			foreach($testCaseArray as $tc) {
				if($tc == $g_tc_status['passed']){
					$pass++;
				} elseif($tc == $g_tc_status['failed']) {
					$fail++;
				} elseif($tc == $g_tc_status['blocked']) {
					$blocked++;
				}
			}//end foreach
		}//end if
		unset($testCaseArray);
		
		//This next section figures out how many priority A,B or C test cases there and adds them together
		$myResults[$priority[$priStatus]]['total'] = $myResults[$priority[$priStatus]]['total'] + $totalTCs[0];
		$myResults[$priority[$priStatus]]['pass'] = $myResults[$priority[$priStatus]]['pass'] + $pass;
		$myResults[$priority[$priStatus]]['fail'] = $myResults[$priority[$priStatus]]['fail'] + $fail;
		$myResults[$priority[$priStatus]]['blocked'] = $myResults[$priority[$priStatus]]['blocked'] + $blocked;
		
	}
	
	foreach ($arrAvailablePriority as $i)
	{
		$myResults[$i]['withStatus'] = $myResults[$i]['pass'] + $myResults[$i]['fail'] + 
				$myResults[$i]['blocked'];
		//Getting the not run TCs
		$myResults[$i]['notRun'] = $myResults[$i]['total'] - ($myResults[$i]['withStatus']); 
		
		if($myResults[$i]['total'] == 0)
		{
			$myResults[$i]['percentComplete'] = 0;
	
		}else
		{
			$myResults[$i]['percentComplete'] = round((100 * ($myResults[$i]['withStatus'] / $myResults[$i]['total'])),2); //Rounding the number so it looks pretty
		}
	}

	//This next section gets the milestones information
	$sql = " SELECT name,date,A,B,C FROM milestones " .
	       " WHERE testplan_id=" . $tpID . " AND to_days(date) >= to_days(now()) " .
			   " order by date limit 1";
	$result = $db->exec_query($sql); //Run the query
	$numRows = $db->num_rows($result); //How many rows
	
	//Check to see if there are any milestone rows
	if($numRows > 0){
	
		$currentMilestone = $db->fetch_array($result);
	
		$myResults['milestone'] = $currentMilestone[0];
		$myResults['deadline'] = $currentMilestone[1];
		$myResults['a']['milestone'] = $currentMilestone[2]; // $MA
		$myResults['b']['milestone'] = $currentMilestone[3];
		$myResults['c']['milestone'] = $currentMilestone[4];
	
		//This next section figures out if the status is red yellow or green..
		//Check to see if milestone is set to zero. Will cause division error
		foreach ($arrAvailablePriority as $i)
		{
			//	MHT 200507	removed from condition:		 ||| $myResults[$i]['total'] == 0) {
			if(intval($myResults[$i]['milestone']) > 0) 
			{
				$relStatus = $myResults[$i]['percentComplete'] / $myResults[$i]['milestone'];
				if($relStatus >= 0.9) {
					$myResults[$i]['status'] = "<font color='#669933'>GREEN</font>";
				}
				elseif($relStatus >= 0.8) {
					$myResults[$i]['status'] = '<font color="#FFCC00">YELLOW</font>';
				}
				else{
					$myResults[$i]['status'] = '<font color="#FF0000">RED</font>';
				}
			}
		} 
	}

	// MHT: smarty template maintains this as ordered array
	return array($myResults['a'], $myResults['b'], $myResults['c'], 
	             'milestone' => $myResults['milestone'], 
		           'deadline' => $myResults['deadline']);

} // priority


/**
* Function return array of defined priorities that the user has assigned for the current Test Plan
*
* @return array 
*/
// MHT 200507 refactorization, improved sql query
function DEPR_getPriorityDefine(&$db,$tpID)
{
	$sql = "SELECT riskImp,priority FROM priority WHERE priority.projid = " . $tpID;
	return $db->fetchColumnsIntoMap($sql,'riskImp','priority');
}

function DEPR_getPriority($priorityStatus, $dependencies)
{
	return $dependencies[$priorityStatus];
}

/**
* Function generates Build Metrics based on category
*
* @param string build ID 
* @return array 
*/
function DEPR_getBuildMetricsCategory(&$db,$tpID, $buildID)
{
	global $g_tc_status;
	
	$arrOutput = array();
	// grabs the defined priority 
	$dependencies = getPriorityDefine($db,$tpID);

	$sql = " SELECT MGTCOMP.name AS comp_name, COMP.id comp_id " .
	       " FROM testplans TP, component COMP, mgtcomponent MGTCOMP " .
		     " WHERE TP.id = COMP.projid" .
		     " AND MGTCOMP.id = COMP.mgtcompid " .
		     " AND TP.id = " . $tpID;

	$result = $db->exec_query($sql);


	while ($myrow = $db->fetch_array($result)) 
	{
		
		$categoryQuery = " SELECT MGTCAT.name AS cat_name, CAT.id AS cat_id, risk, importance " .
		                 " FROM testplans TP, component COMP, category CAT, mgtcategory MGTCAT " .
		                 " WHERE TP.id = COMP.projid " .
		                 " AND COMP.id = CAT.compid " .
		                 " AND MGTCAT.id = CAT.mgtcatid " .
		                 " AND TP.id = " . $tpID . 
		                 " AND COMP.id =" . $myrow['comp_id'];

		
		$categoryResult = $db->exec_query($categoryQuery);
	
		while ($categoryRow = $db->fetch_array($categoryResult)) {
			
						$catAllSql = " SELECT count(TC.id) AS num_tc " .
			             " FROM testplans TP , component COMP, category CAT, testcase TC" .
			             " WHERE TP.id = COMP.projid " .
			             " AND COMP.id = CAT.compid " .
			             " AND CAT.id = TC.catid " .
			             " AND TP.id = " . $tpID . 
			             " AND COMP.id =" . $myrow['comp_id'] . 
			             " AND CAT.id=" . $categoryRow['cat_id'];
             
			             
			$catTotalResult = $db->exec_query($catAllSql);
			$totalRow = $db->fetch_array($catTotalResult);
			
			// 20050425 - fm
			$base_sql = " SELECT count(testcase.id) " .
		            " FROM testplans,component,category,testcase,results " .
			          " WHERE testplans.id = " . $tpID .
			          " AND testplans.id = component.projid " .
			          " AND component.id = category.compid " .
			          " AND category.id = testcase.catid " .
			          " AND component.id =" . $myrow['comp_id'] .
			          " AND testcase.id = results.tcid " .
			          " AND results.build_id=" . $buildID . 
			          " AND category.id=" . $categoryRow['cat_id'];
			
			
			//Passed TCs per category
			$sql = $base_sql . " AND results.status='" . $g_tc_status['passed'] ."'";
			$passedResult = $db->exec_query($sql);
			$passedRow = $db->fetch_array($passedResult);
	
			//Failed TCs per category
			$sql = $base_sql . " and results.status='" . $g_tc_status['failed'] ."'";
			$failedResult = $db->exec_query($sql);
			$failedRow = $db->fetch_array($failedResult);

			//Blocked TCs per category
			$sql = $base_sql . " and results.status='" . $g_tc_status['blocked'] ."'";
			$blockedResult = $db->exec_query($sql);
			$blockedRow = $db->fetch_array($blockedResult);
	
	
			//Not Run TCs per category
			$notRun = $totalRow[0] - ($passedRow[0] + $failedRow[0] + $blockedRow[0]);
			if($totalRow[0] == 0) { //if we try to divide by 0 we get an error
				$percentComplete = 0;
			} else {
				//Getting total percent complete
				$percentComplete = ($passedRow[0] + $failedRow[0] + $blockedRow[0]) / $totalRow[0]; 
				$percentComplete = round((100 * ($percentComplete)),2); //Rounding the number so it looks pretty
			}
	
			//Determining Priority from risk and importance
			$priorityStatus = $categoryRow['importance'] . $categoryRow['risk'];
			$priority = getPriority($priorityStatus, $dependencies);
	
			//save
			array_push($arrOutput, array($myrow['comp_name'] . ' / ' . $categoryRow['cat_name'], 
					$categoryRow[2], $categoryRow[3] , $priority, $totalRow[0], 
					$passedRow[0], $failedRow[0], $blockedRow[0], $notRun, 
					$percentComplete));
	
		}
	}//END WHILE
	return $arrOutput;

} // END function getMetricsCategory

/**
* Function generates Build Metrics based on component
*
* @param string build ID 
* @return array 
*/
function DEPR_getBuildMetricsComponent(&$db,$tpID,$buildID)
{
	global $g_tc_status;

	$arrOutput = array();

	$sql = " SELECT MGTCOMP.name AS comp_name, COMP.id comp_id " .
	       " FROM testplans TP, component COMP, mgtcomponent MGTCOMP " .
		     " WHERE TP.id = COMP.projid" .
		     " AND MGTCOMP.id = COMP.mgtcompid " .
		     " AND TP.id = " . $tpID;
			
			
	$result = $db->exec_query($sql);

	while ($myrow = $db->fetch_array($result)) 
	{
		$componentName = $myrow['comp_name'];
	
			$sql = " SELECT count(TC.id) AS num_tc" .
			     " FROM testplans TP , component COMP, category CAT, testcase TC" .
			     " WHERE TP.id = COMP.projid " .
			     " AND COMP.id = CAT.compid " .
			     " AND CAT.id = TC.catid " .
			     " AND TP.id = " . $tpID . 
			     " AND COMP.id =" . $myrow['comp_id'];

	
		$totalResult = $db->exec_query($sql);
		$totalRow = $db->fetch_array($totalResult);
		
		//Passed TCs per component
		$base_sql = " SELECT count(testcase.id) " .
		            " FROM testplans,component,category,testcase,results " . 
		            " WHERE testplans.id = " . $tpID  .
		            " AND testplans.id = component.projid " .
		            " AND component.id = category.compid " .
		            " AND category.id = testcase.catid " .
		            " AND component.id =" . $myrow['comp_id'] . 
		            " AND testcase.id = results.tcid " .
		            " AND results.build_id=" . $buildID;
		
		$sql = $base_sql .  " and results.status='" . $g_tc_status['passed'] . "'";
		$passedResult = $db->exec_query($sql);
		$passedRow = $db->fetch_array($passedResult);
	
		//Failed TCs per component
		$sql = $base_sql .  " and results.status='" . $g_tc_status['failed'] . "'";
		$failedResult = $db->exec_query($sql);
		$failedRow = $db->fetch_array($failedResult);
	
		//Blocked TCs per component
		$sql = $base_sql .  " and results.status='" . $g_tc_status['blocked'] . "'";
		$blockedResult = $db->exec_query($sql);
		$blockedRow = $db->fetch_array($blockedResult);

		//Not Run TCs per component
		$notRun = $totalRow['num_tc'] - ($passedRow[0] + $failedRow[0] + $blockedRow[0]);
		if(!$totalRow['num_tc']) 
		{
			$percentComplete = 0;
		}
		else
		{
			//Getting total percent complete
			$percentComplete = ($passedRow[0] + $failedRow[0] + $blockedRow[0]) / $totalRow['num_tc']; 
			$percentComplete = round((100 * ($percentComplete)),2); //Rounding the number so it looks pretty
		}

		// save	
		array_push($arrOutput, array($componentName, $totalRow['num_tc'], 
					                       $passedRow[0], $failedRow[0], $blockedRow[0], $notRun, 
					                       $percentComplete));
	
	}//END WHILE
	return $arrOutput;

} // END function getMetricsComponent


/** @todo add build relation */
/*
20050911 - fm - bug due to fetch_assoc
*/
function DEPR_getBugsReport(&$db,$tpID, $buildID = 'all')
{
	global $g_bugInterfaceOn;
	global $g_bugInterface;
	
	$arrOutput = array();

	$sql = " SELECT title, MGTCOMP.name AS comp_name, MGTCAT.name AS cat_name, TC.id, mgttcid" .
			   " FROM testplans TP, component COMP, category CAT, mgtcomponent MGTCOMP, mgtcategory MGTCAT, testcase TC " .
			   " WHERE MGTCOMP.id = COMP.mgtcompid " .
			   " AND MGTCAT.id=CAT.mgtcatid" .
			   " AND TP.id=COMP.projid" .
			   " AND COMP.id=CAT.compid " .
			   " AND CAT.id=TC.catid" .
			   " AND TP.id=" . $tpID . 
			   " ORDER BY TC.id";
			   
			   
	$result = $db->exec_query($sql);
	
	while ($myrow = $db->fetch_array($result)) {
		$bugString = null;
		$sqlBugs = "SELECT bug FROM bugs WHERE tcid=" . $myrow['id'];
		$resultBugs = $db->exec_query($sqlBugs);
		while ($myrowBug = $db->fetch_array($resultBugs))
		{
			if (!is_null($bugString))
			{
				$bugString .= ","; 
			}	
			$bugID = $myrowBug[0];
			if($g_bugInterfaceOn)
			{
				$bugString .= $g_bugInterface->buildViewBugLink($bugID);
			}	
			else
			{
				$bugString .= $bugID;
			}	
		}
		// 20051201 - scs - fixed double escaping of TCTitle
		array_push($arrOutput, array($myrow['comp_name'] . ' / ' . $myrow['cat_name'], 
				       $myrow['mgttcid'] . ': ' . $myrow['title'], $bugString));

		if($bugString != "") {
			unset($bugString);
		}
	}

  return $arrOutput;
}

/**
* get % completed TCs
*
* @param integer $total
* @param integer $run = $totalPassed + $totalFailed + $totalBlocked
* @return real $percentageCompleted
*/
function DEPR_getPercentageCompleted($total, $run)
{
	if($total == 0)	{
		$percentComplete = 0;
	} else {
		//rounded total percent completed
		$percentComplete = round((100 * $run / $total ),2); 
	}
	return $percentComplete;
}


/**
* create Test Suite list (Component)
*
* @return array associated $id => $name
*
* @author Francisco Mancardi - fm - reduce global coupling
*/
function DEPR_listTPComponent(&$db,$tpID)
{
	$suites = array();
	$sqlCom = " SELECT COMP.id,MGTCOMP.name " .
	          " FROM component COMP, mgtcomponent MGTCOMP, testplans TP" .
			      " WHERE COMP.projid=TP.id " .
			      " AND MGTCOMP.id=COMP.mgtcompid " .
			      " AND TP.id=" . $tpID;
	$result = $db->exec_query($sqlCom);

	//Build the options for the select box			
	while ($myrow = $db->fetch_array($result)){
		$suites[$myrow['id']] = $myrow['name'];
	}
	return $suites;
}


// ---- FUNCTIONS FOR MAIL -------------------------------------------------------------
/**
* Takes all of the priority info and puts it in a variable.. 
*
* @author Francisco Mancardi - 20050905 - add parameter
*
*/
function DEPR_reportGeneralStatus(&$db,$tpID)
{
	$arrData = getPriorityReport($db,$tpID);
	
	// array('A', $totalA, $AStatus, $passA, $failA, $blockedA, $notRunTCsA, $percentCompleteA, $MA),
	

	$msgBody = null;
	//foreach ($arrData['values'] as $priority)
	foreach ($arrData as $priority)
	{
	  if( is_array($priority) )
	  {
   		$msgBody .= " Priority " . $priority['priority'] . " Test Cases\n\n";
   		$msgBody .= " Total: "   . $priority['total'] . "\n";
   		$msgBody .= " Passed: " . $priority['pass'] . "\n";
   		$msgBody .= " Failed: " . $priority['fail'] . "\n";
   		$msgBody .= " Blocked: " . $priority['blocked'] . "\n";
   		$msgBody .= " Not Run: " . $priority['notRun'] . "\n";
   		$msgBody .= " Percentage complete: " . $priority['percentComplete'] . "\n";
   		
   		if ($priority['milestone'] != '-')
   		{
   			$msgBody .= " Percentage complete against current Milestone: " . $priority['percentComplete'] . "\n";
   			$msgBody .= " Status against current Milestone: " . $priority['milestone'] . "\n\n";
   		}
		}
	}
 
	return $msgBody;
}


function DEPR_reportBuildStatus(&$db,$tpID, $buildID,$buildName)
{
	global $g_tc_status;
	
	$sql = " SELECT count(testcase.id) " .
	       " FROM testplans,component,category,testcase WHERE testplans.id =" . $tpID . 
	       " AND testplans.id = component.projid AND component.id = category.compid AND category.id = testcase.catid";
	       
	$sumResult = $db->exec_query($sql);
	$sumTCs = $db->fetch_array($sumResult); 
	$total = $sumTCs[0];

	$base_sql = "SELECT count(results.tcid) " .
              "FROM testplans,component,category,testcase,results " .
              "WHERE testplans.id =" . $tpID . 
              " AND testplans.id = component.projid " .
              " AND component.id = category.compid " .
              " AND category.id = testcase.catid " .
              " AND testcase.id = results.tcid " .
              " AND results.build_id = " . $buildID;
              
              
  
	//Get the total # of passed testcases for the testplan and build
	$sql = $base_sql . " AND status ='" . $g_tc_status['passed'] . "'";
	$passedResult = $db->exec_query($sql);
	$passedTCs = $db->fetch_array($passedResult);
	$totalPassed = $passedTCs[0];

	//Get the total # of failed testcases for the testplan
	$sql = $base_sql . " AND status ='" . $g_tc_status['failed'] . "'";
	$failedResult = $db->exec_query($sql);
	$failedTCs = $db->fetch_array($failedResult);
	$totalFailed = $failedTCs[0];

	//Get the total # of blocked testcases for the testplan
	$sql = $base_sql . " AND status ='" . $g_tc_status['blocked'] . "'";
	$blockedResult = $db->exec_query($sql);
	$blockedTCs = $db->fetch_array($blockedResult);
	$totalBlocked = $blockedTCs[0];

	//total # of testcases not run
	$run = $totalPassed + $totalFailed + $totalBlocked;
	$notRun = $total - $run;
	$percentComplete = getPercentageCompleted($total, $run);

	
	$msgBody = lang_get("trep_status_for_build").": " . $buildName . "\n\n";
	$msgBody .= lang_get("trep_total").": " . $total . "\n";
	$msgBody .= lang_get("trep_passed").": " . $totalPassed . "\n";
	$msgBody .= lang_get("trep_failed").": " . $totalFailed . "\n";
	$msgBody .= lang_get("trep_blocked").": " . $totalBlocked . "\n";
	$msgBody .= lang_get("trep_not_run").": " . $notRun . "\n";
	$msgBody .= lang_get("trep_comp_perc").": " . $percentComplete. "%\n\n";

	
	return $msgBody;
}

function DEPR_reportSuiteBuildStatus(&$db,$tpID, $comID, $buildID,$buildName)
{
	global  $g_tc_status;  
	
	$sql = " SELECT count(testcase.id) " .
	       " FROM testplans,component,category,testcase WHERE testplans.id =" . $tpID . 
	       " AND testplans.id = component.projid AND component.id = category.compid AND " .
	       " category.id = testcase.catid and component.id=" . $comID;
	       
	$sumResult = $db->exec_query($sql);
	$sumTCs = $db->fetch_array($sumResult); 
	$total = $sumTCs[0];

	//Get the total # of passed testcases for the testplan and build
	$base_sql = "SELECT count(results.tcid) " .
	            "FROM testplans,component,category,testcase,results " .
	            "WHERE testplans.id =" . $tpID . 
	            " AND testplans.id = component.projid " .
	            " AND component.id = category.compid " .
	            " AND category.id = testcase.catid " .
	            " AND testcase.id = results.tcid " .
	            " AND results.build_id = " . $buildID .
	            " AND component.id=" . $comID;
	            
	            
	
	$sql = $base_sql . " AND status ='" . $g_tc_status['passed'] . "'";
	$passedResult = $db->exec_query($sql);
	$passedTCs = $db->fetch_array($passedResult);
	$totalPassed = $passedTCs[0];

	//Get the total # of failed testcases for the testplan
	$sql = $base_sql . " AND status ='" . $g_tc_status['failed'] . "'";
	$failedResult = $db->exec_query($sql);
	$failedTCs = $db->fetch_array($failedResult);
	$totalFailed = $failedTCs[0];

	//Get the total # of blocked testcases for the testplan
	$sql = $base_sql . " AND status ='" . $g_tc_status['blocked'] . "'";
	$blockedResult = $db->exec_query($sql);
	$blockedTCs = $db->fetch_array($blockedResult);
	$totalBlocked = $blockedTCs[0];

	//total # of testcases not run
	$run = $totalPassed + $totalFailed + $totalBlocked;
	$notRun = $total - $run;
	$percentComplete = getPercentageCompleted($total, $run);

  // 20050918 - fm - refactoring
	$sqlCOMName = " SELECT MGTCOMP.name AS comp_name" .
	              " FROM component COMP, mgtcomponent MGTCOMP " .
	              " WHERE COMP.mgtcompid = MGTCOMP.id" .
	              " AND COMP.id=" . $comID;

	
	
	$resultCOMName = $db->exec_query($sqlCOMName);
	$COMName = $db->fetch_array($resultCOMName);

	$msgBody = lang_get("trep_status_for_ts") . " " . $COMName['comp_name'] . " in Build: " . $buildName . "\n\n";
	$msgBody .= lang_get("trep_total").": " . $total . "\n";
	$msgBody .= lang_get("trep_passing").": " . $totalPassed . "\n";
	$msgBody .= lang_get("trep_failing").": " . $totalFailed . "\n";
	$msgBody .= lang_get("trep_blocked").": " . $totalBlocked . "\n";
	$msgBody .= lang_get("trep_not_run").": " . $notRun . "\n";
	$msgBody .= lang_get("trep_comp_perc").": " . $percentComplete. "%\n\n";
	
	return $msgBody;
}

// 20051106 - fm
// build_id
function DEPR_reportSuiteStatus(&$db,$tpID, $comID)
{
	global $g_tc_status;
  
	//Code to grab the entire amount of test cases per testplan
	$sql = " SELECT count(testcase.id) " .
	       " FROM testplans,component,category,testcase WHERE testplans.id = " . $tpID . 
	       " AND component.id=" . $comID . " AND testplans.id = component.projid " .
	       " AND component.id = category.compid AND category.id = testcase.catid";
	       
	$totalTCResult = $db->exec_query($sql);
	$totalTCs = $db->fetch_array($totalTCResult);

	//Code to grab the results of the test case execution
	//
	// 20051106 - fm - bug build.id
	$sql = " SELECT tcid,status " .
	       " FROM results,testplans,component,category,testcase " .
	       " WHERE testplans.id =" . $tpID . 
	       " AND component.id=" . $comID . 
	       " AND testplans.id = component.projid AND component.id = category.compid " .
	       " AND category.id = testcase.catid AND testcase.id = results.tcid " .
	       " ORDER BY build_id";
	       
	$totalResult = $db->exec_query($sql);

	//Setting the results to an array.. Only taking the most recent results and displaying them
	while($totalRow = $db->fetch_array($totalResult))	{
		if($totalRow[1] != $g_tc_status['not_run']){
			$testCaseArray[$totalRow[0]] = $totalRow[1];
		}
	}

	//This is the code that determines the pass,fail,blocked amounts
	$pass = 0;
	$fail = 0;
	$blocked = 0;
	$notRun = 0;

	if(count($testCaseArray) > 0) {
		foreach($testCaseArray as $tc) {
			if($tc == $g_tc_status['passed']) {
				$pass++;
			} elseif($tc == $g_tc_status['failed']) {
				$fail++;
			} elseif($tc ==  $g_tc_status['blocked']) {
				$blocked++;
			}

			unset($testCaseArray);
		}//end foreach
	}//end if

	$run = $pass + $fail + $blocked;
	$notRun = $totalTCs[0] - $run;
	$percentComplete = getPercentageCompleted($totalTCs[0], $run);

	//Grab the component's name
		$sqlCOMName = " SELECT MGTCOMP.name AS comp_name" .
	              " FROM component COMP, mgtcomponent MGTCOMP " .
	              " WHERE COMP.mgtcompid = MGTCOMP.id" .
	              " AND COMP.id=" . $comID;

	
	
	$resultCOMName = $db->exec_query($sqlCOMName);
	$COMName = $db->fetch_array($resultCOMName);

	$msgBody = lang_get("trep_status_for_ts") .": ". $COMName['comp_name'] . "\n\n";
	$msgBody .= lang_get("trep_total").": " . $totalTCs[0] . "\n";
	$msgBody .= lang_get("trep_passed").": " . $pass . "\n";
	$msgBody .= lang_get("trep_failed").": " . $fail . "\n";
	$msgBody .= lang_get("trep_blocked").": " . $blocked . "\n";
	$msgBody .= lang_get("trep_not_run").": " . $notRun . "\n";
	$msgBody .= lang_get("trep_comp_perc").": " . $percentComplete. "%\n\n";

	return $msgBody;
}

/**
 * get last result for test case (order by build)
 * 
 * @param integer $idSuiteTC (in Test Plan)
 * @return string last test result
 * @author martin havlat
 **/
function DEPR_getLastResult(&$db,$idSuiteTC)
{
	global $g_tc_status;
	
	$sql = "SELECT status FROM results WHERE tcid = " . $idSuiteTC . " AND status <> '" . 
				$g_tc_status['not_run'] . "' ORDER BY results.build_id DESC LIMIT 1";
	$result = $db->fetchFirstRowSingleColumn($sql,'status');

	// add not run result if any other result is not available
	if (is_null($result))
	{
		$result = $g_tc_status['not_run'];
	}
	
	tLog('getLastResult: ID SpecTC ' . $idSuiteTC . ' result = ' . $result);
	return $result;
}

/**
 * get report based on requirements
 * all related TC results are collected for each REQ; if one of them failed -> REQ failed
 * Req status priority: 1. Failed, 2. Blocked, 3. Not tested, 4. Passed
 * E.g. REQ has two TC (blocked and passed) and final result is Blocked.
 * 
 * @param integer $idSRS
 * @param integer $tpID
 * @return array Results (idReq, titleReq, tcList, reqResult) in fourth internal arrays: 
 * 		failed, passed, blocked, not_run REQ (include related TC)
 * @author martin havlat
 */
function DEPR_getReqCoverage_testPlan(&$db,$idSRS, $tpID)
{
	global $g_tc_status, $g_tc_status_css;
	
	
	$output = array('passed' => array(), 'failed' => array(), 
				          'blocked' => array(), 'not_run' => array());
	
	// get requirements
	$sql = "SELECT id,title FROM requirements WHERE srs_id=" . $idSRS . 
			   " AND status='v' ORDER BY title";
			   
	$arrReq = selectData($db,$sql);

	// parse each valid requirement
	if (sizeof($arrReq))
	{
		foreach ($arrReq as $req) 
		{
			tLog('getReqCoverage_testPlan - Process '.$req['id'].' - '.$req['title']);
			// init counters
			$counterFail = 0;
			$counterBlocked = 0;
			$counterPassed = 0;
			$counterNotRun = 0;
			$sTCList = '';

			// get coverage
			$arrCoverage = getSuite4Req($db,$req['id'], $tpID);
			
			// select result with highest priority
			if (count($arrCoverage) > 0) {
				foreach ($arrCoverage as $tmpTC)
				{
					// get last results
					$tcResult = getLastResult($db,$tmpTC['id']);
					tLog('Last result for '.$tmpTC['title'].' is '.$tcResult);
					
					// parse particular TC
					if ($tcResult == $g_tc_status['failed']) {
						$counterFail++;
						$htmlClass = $g_tc_status_css['failed'];
					} elseif ($tcResult == $g_tc_status['blocked']) {
						$counterBlocked++;
						$htmlClass = $g_tc_status_css['blocked'];
					} elseif ($tcResult == $g_tc_status['passed']) {
						$counterPassed++;
						$htmlClass = $g_tc_status_css['passed'];
					} elseif ($tcResult == $g_tc_status['not_run']) {
						$counterNotRun++;
						$htmlClass = $g_tc_status_css['not_run'];
					} else {
						tLog('getReqCoverage_testPlan: Invalid $tcResult', 'ERROR');
					}
					$sTCList .= '<span class="' . $htmlClass . '">' . $tmpTC['title'] . '</span>, ';
				}
				
				// add collored list of TC into output without the last comma
				$req['tcList'] = substr($sTCList, 0, -2);
				tLog("Counters: f=$counterFail, b=$counterBlocked, n=$counterNotRun, p=$counterPassed");
				
				// add req to result group according to a TC result with the highest priority result
				if ($counterFail) {
					$output['failed'][] = $req;
				} elseif ($counterBlocked) {
					$output['blocked'][] = $req;
				} elseif ($counterNotRun) {
					$output['not_run'][] = $req;
				} elseif ($counterPassed) {
					$output['passed'][] = $req;
				} 
			} 
			else 
			{
				// not designed TC means automatically not run
				$output['not_run'][] = $req;
			}
		}
	}	
	return $output;
}
?>
