<?
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: mine_results.inc.php,v 1.0 
* 
* @author 	Kevin Levy
*
* Functions for Mine Results
*/
////////////////////////////////////////////////////////////////////////////////

require_once('../../config.inc.php');
require_once("common.php");

/**
 * Function createResultsForTestPlan()
* Produces Report based on projectId, startBuild, endBuild, keyword, and owner.  The ability
* to look at results across a range of builds as opposed to 1 build or ALL builds is the 
* primary purpose of this method.  
*
* If startBuild or endBuild not specified - default values are used (range build 0 -> latest build) for
* producing this report.
*
* If keyword specified, report only includes those test cases in the specified test plan which are associated
* with that keyword.  An empty string can be specified for keyword.
*
* If the owner is specified, report only includes those test cases which belong to categories owned by that order. 
* An empty string can be specified for owner.
*
* keyword and owner queries use pattern matching notation (example: select * from testcase where 
* keywork LIKE '%$keyword%') - so this makes it easier to do queries.
*
* @param string testPlanName 
* @param string projectId 
* @param string builds selected - array of builds to be included in query
* @param string keyword - can be empty string
* @param string owner - can be empty string
* @return string returnData - report based on query parameters  
*/
// default start and end builds are specified 
function createResultsForTestPlan($testPlanName, $testPlanID, $buildsArray, $keyword, $owner, $lastStatus)
{
	$totalCasesForTestPlan = 0;
	$totalLastResultPassesForTestPlan = 0;
	$totalLastResultFailuresForTestPlan = 0;
	$totalLastResultBlockedForTestPlan = 0;
	$totalUnexecutedTestCases = 0;
	
	$arrBuilds = getBuilds($testPlanID);
	$commaDelimitedBuilds = null;
	$buildParams = null;
	for($i = 0;$i < sizeof($buildsArray);$i++)
	{
		if ($i)
		{
			$commaDelimitedBuilds .= ",";
			$buildParams .= ",";
		}	
		$commaDelimitedBuilds .= $buildsArray[$i];
		$buildParams .= $arrBuilds[$buildsArray[$i]];
	}

	$testPlanReportHeader = "<table class=\"simple\" style=\"width: 100%; " .
	                        "text-align: center; margin-left: 0px;\">" .
	                        "<tr><th>Test Plan Name</th><th>Builds Selected</th>" .
	                        "<th>Keyword</th><th>Owner</th><th>Last Status</th></tr>";
	$testPlanReportHeader = $testPlanReportHeader . 
	                        "<tr><td>".htmlspecialchars($testPlanName)."</td><td>" . 
	                        htmlspecialchars($buildParams) . "</td><td>".
	                        htmlspecialchars($keyword) . "</td><td>" . htmlspecialchars($owner) . 
	                        "</td><td>".htmlspecialchars($lastStatus)."</td></tr></table>";

	$sql = " SELECT component.id,component.name, component.projid, component.mgtcompid from component ".
	       " WHERE projid='" . $testPlanID . "'";
	$result = do_mysql_query($sql);

	$aggregateComponentDataToPrint = null;
	while($myrow = mysql_fetch_row($result))
	{
		$componentData = createResultsForComponent($myrow[0], $owner, $keyword, 
		                                           $commaDelimitedBuilds, $lastStatus,$myrow,$arrBuilds);
		
		$componentSummary = $componentData[0];
		$totalCasesForTestPlan += $componentSummary[0];
		$totalLastResultPassesForTestPlan += $componentSummary[1];
		$totalLastResultFailuresForTestPlan += $componentSummary[2];
		$totalLastResultBlockedForTestPlan += $componentSummary[3];
		$totalUnexecutedTestCases += $componentSummary[4];
		$testCasesReturnedByQuery = $componentData[2];

		// only print component information if test cases are part of the query
		if (($componentSummary[0] != 0) && $testCasesReturnedByQuery)
		{
			$aggregateComponentDataToPrint .= $componentData[1];
		}
	}
	$summaryOfTestPlanTable = "<table class=\"simple\" style=\"width: 100%; " .
	                          "text-align: center; margin-left: 0px;\"><tr><th># Cases</td>" .
	                          "<th># Passed</td><th># Failed</td><th># Blocked</td><th># Unexecuted</td></tr>";
	$summaryOfTestPlanTable = $summaryOfTestPlanTable . "<tr><td>" . $totalCasesForTestPlan  . 
	                          "</td><td>" . $totalLastResultPassesForTestPlan . "</td><td>" . 
	                          $totalLastResultFailuresForTestPlan . "</td><td>" . 
	                          $totalLastResultBlockedForTestPlan . "</td><td>" . 
	                          $totalUnexecutedTestCases . "</td></tr></table>";
	// The $linksToAllComponents functionality does not work because something keeps screwing up
	// my href values and prepending the root testlink url to the string
	//  return  $testPlanReportHeader . $summaryOfTestPlanTable . $linksToAllComponents .$aggregateComponentDataToPrint;

	if (!$aggregateComponentDataToPrint)
	{
		$aggregateComponentDataToPrint = "no results for this query";
	}
	return array($testPlanReportHeader, $summaryOfTestPlanTable, $aggregateComponentDataToPrint);
}

function createResultsForComponent($componentId, $owner, $keyword, $commaDelimitedBuilds, $lastResult,$myrow,$arrBuilds)
{
	$totalCasesForComponent = 0;
	$totalLastResultPassesForComponent = 0;
	$totalLastResultFailuresForComponent = 0;
	$totalLastResultBlockedForComponent = 0;
	$totalUnexecutedTestCases = 0;
	// flags found test cases which match query
	$testCasesReturnedByQuery = false;

	$componentRowArray = array($myrow[0],$myrow[1],$myrow[2],$myrow[3]);
	$componentName = $componentRowArray[1];
	$componentHeader = "Component :"  . $componentName ;
	
	// @toDo I'm not sure if I should use this LIKE in my sql statement
	$sql = " SELECT category.id,category.name, category.compid, category.importance, category.risk, " .
	       " category.owner, category.mgtcatid, category.CATorder FROM " .
	       " category WHERE (category.compid='" . $componentId .  "')";
	if (strlen($owner))
		$sql .= " AND (category.owner = '" . mysql_escape_string($owner) . "');";
	$sql .= " ORDER by CATorder ASC ";
	$result = do_mysql_query($sql);

	$aggregateCategoryDataToPrint = null;;
	while ($myrow = mysql_fetch_row($result))
	{
		$categoryData = createResultsForCategory($myrow[0], $keyword, $commaDelimitedBuilds, $lastResult,$myrow,$arrBuilds);
		$categorySummary = $categoryData[0];
		$totalCasesForComponent += $categorySummary[0];
		$totalLastResultPassesForComponent += $categorySummary[1];
		$totalLastResultFailuresForComponent += $categorySummary[2];
		$totalLastResultBlockedForComponent += $categorySummary[3];
		$totalUnexecutedTestCases += $categorySummary[4];
		$categoryDataToPrint = $categoryData[1];
		
		// do not reset this value each time!
		// only flag once if we find any category with a test case
		if ($categoryData[2]){
		  $testCasesReturnedByQuery = $categoryData[2];
		}

		// only print category information if test cases are part of the query
		if ($categorySummary[0] != 0)
		{
			$aggregateCategoryDataToPrint .= $categoryDataToPrint;
		}
	}

	$summaryOfComponentTable = "<table class=\"simple\" style=\"width: 100%; " .
	                           "text-align: center; margin-left: 0px;\"><tr><th># Cases</td>" .
	                           "<th># Passed</td><th># Failed</td><th># Blocked</td><th># Unexecuted</td></tr>";
	$summaryOfComponentTable = $summaryOfComponentTable . "<tr><td>" . $totalCasesForComponent  . "</td><td>" . 
	                           $totalLastResultPassesForComponent . "</td><td>" . 
	                           $totalLastResultFailuresForComponent . "</td><td>" . 
	                           $totalLastResultBlockedForComponent . "</td><td>" . 
	                           $totalUnexecutedTestCases . "</td></tr></table>";
	
	$summaryOfComponentArray = array($totalCasesForComponent, $totalLastResultPassesForComponent,
	                                 $totalLastResultFailuresForComponent, $totalLastResultBlockedForComponent, 
	                                 $totalUnexecutedTestCases);

	if ($testCasesReturnedByQuery)
	{
		$componentDataToPrint = "<h2 onClick=\"plusMinus_onClick(this);\"><img class=\"plus\" src=\"icons/plus.gif\">" . 
		                        $componentHeader  . $summaryOfComponentTable . "</h2><div class=\"workBack\">" .  
		                        $aggregateCategoryDataToPrint . "</div>";
	}
	else
	{
		$componentDataToPrint = $componentHeader . $summaryOfComponentTable;
	}
	return array($summaryOfComponentArray, $componentDataToPrint, $testCasesReturnedByQuery);
}




function createResultsForCategory($categoryId, $keyword, $commaDelimitedBuilds, $lastResultToQueryFor,$myrow,$arrBuilds)
{
	global $g_tc_status;
	
	$totalCasesForCategory = 0;
	$totalLastResultPassesForCategory = 0;
	$totalLastResultFailuresForCategory = 0;
	$totalLastResultBlockedForCategory = 0;
	$totalUnexecutedTestCases = 0;

	// this needs to be initialized outside the while loop
	// otherwise it keeps getting set to false even though there is a prior
	// result that matches the specified query
	$testCasesReturnedByQuery = false;
	
	$categoryRowArray = array($myrow[0],$myrow[1],$myrow[2],$myrow[3],$myrow[4],$myrow[5],$myrow[6],$myrow[7]);
	$categoryName = $categoryRowArray[1];
	$owner = $categoryRowArray[5];

  
	$categoryHeader = "Category = " . htmlspecialchars($categoryName) . " Owner = " . htmlspecialchars($owner);
	$sql = " SELECT testcase.id, testcase.title, testcase.summary, testcase.steps, " .
	       " testcase.exresult, testcase.catid, testcase.active, testcase.version, " .
	       " testcase.mgttcid, testcase.keywords, testcase.TCorder " .
	       " FROM testcase WHERE (catid='" . $categoryId . "') AND (keywords LIKE '%" . $keyword . "%') ";
	
	$sql .= " ORDER by TCorder ASC";
	$result = do_mysql_query($sql);
  
  $testCaseTables;


  while ($myrow = mysql_fetch_row($result)){
    $totalCasesForCategory++;
    $testCaseData = createResultsForTestCase($myrow[0], $commaDelimitedBuilds,$myrow,$arrBuilds);
    $testCaseInfoToPrint = $testCaseData[0];
    $summaryOfTestCaseInfo = $testCaseData[1];
    $lastResult = $summaryOfTestCaseInfo[4];
    if ($lastResult == $g_tc_status['passed']){
      $totalLastResultPassesForCategory++;
    }
    elseif ($lastResult == $g_tc_status['failed']){
      $totalLastResultFailuresForCategory++;
    }
    elseif ($lastResult == $g_tc_status['blocked']){
      $totalLastResultBlockedForCategory++;      
    }
    elseif ($lastResult == $g_tc_status['not_run']){
      $totalUnexecutedTestCases++;
    }
    
    /*
     * here is where I use the lastResult parameter to dictate which cases we should show to the user
     * This is a nice feature so that the user can look at just test cases which when last executed failed,
     * where blocked, passed, or view all caes that were not executed.
    */

    // additionally track if category contains any test cases returned by query

        
    if ($lastResultToQueryFor == 'any'){
      $testCaseTables = $testCaseTables . $testCaseInfoToPrint;

      $testCasesReturnedByQuery = true;
    }
    elseif (($lastResult == $g_tc_status['passed']) && ($lastResultToQueryFor == 'passed')){
      $testCaseTables = $testCaseTables . $testCaseInfoToPrint;

      $testCasesReturnedByQuery = true;
    }
    elseif (($lastResult == $g_tc_status['failed']) && ($lastResultToQueryFor == 'failed')){

      $testCaseTables = $testCaseTables . $testCaseInfoToPrint;
      $testCasesReturnedByQuery = true;
    }
    elseif (($lastResult == $g_tc_status['blocked']) && ($lastResultToQueryFor == 'blocked')){

      $testCaseTables = $testCaseTables . $testCaseInfoToPrint;
      $testCasesReturnedByQuery = true;
    }
    elseif (($lastResult == $g_tc_status['not_run']) && ($lastResultToQueryFor == 'unexecuted')){

      $testCaseTables = $testCaseTables . $testCaseInfoToPrint;
      $testCasesReturnedByQuery = true;
    }
  }

  $summaryOfCategoryTable = "<table class=\"simple\" style=\"width: 100%; " .
                            "text-align: center; margin-left: 0px;\"><tr>" .
                            "<th># Cases</td><th># Passed</td><th># Failed</td>" .
                            "<th># Blocked</td><th># Unexecuted</td></tr>";

  $summaryOfCategoryTable = $summaryOfCategoryTable . "<tr><td>" . $totalCasesForCategory  . "</td><td>" . 
                            $totalLastResultPassesForCategory . "</td><td>" . 
                            $totalLastResultFailuresForCategory . "</td><td>" . 
                            $totalLastResultBlockedForCategory . "</td><td>" . 
                            $totalUnexecutedTestCases . "</td></tr></table>";

  // only display an option to expand the category info if there is any test cases which match the query parameters
  $categoryDataToPrint = null;
  if ($testCasesReturnedByQuery)
  {
    $categoryDataToPrint = "<h2 onClick=\"plusMinus_onClick(this);\"><img class=\"plus\" src=\"icons/plus.gif\">" . 
                           $categoryHeader . $summaryOfCategoryTable  . "</h2><div class=\"workBack\">" . 
                           $testCaseTables . "</div>";
  }
  
  $summaryOfCategory = array($totalCasesForCategory, $totalLastResultPassesForCategory, 
                             $totalLastResultFailuresForCategory, $totalLastResultBlockedForCategory, 
                             $totalUnexecutedTestCases);
  return array($summaryOfCategory, $categoryDataToPrint, $testCasesReturnedByQuery); 
}

function createResultsForTestCase($tcid, $commaDelimitedBuilds,$myrow,$arrBuilds)
{
	$testcaseHeader = constructTestCaseInfo($tcid,$myrow);
	$arrayOfResults = retrieveArrayOfResults($tcid, $commaDelimitedBuilds);
	$tableOfResultData = createTableOfTestCaseResults($arrayOfResults,$arrBuilds);
	$summaryOfResultData = createSummaryOfTestCaseResults($arrayOfResults);
  
	$className = getTCClassNameByStatus($summaryOfResultData[4]);
	
	$summaryTable = "<table class=\"simple white\">";
	$summaryTable .= "<tr class=\"black\"><th># executions</th><th># passed</th><th># failures</th><th># blocked</th></tr>";
	$summaryTable .= "<tr class=\"{$className}\"><td>" . $summaryOfResultData[0]  . 
	                "</td><td>" . $summaryOfResultData[1] . "</td><td>" . $summaryOfResultData[2] . "</td><td>" . 
	                $summaryOfResultData[3] . "</td></tr></table>";
					
	$textToDisplay = "<div class=\"workBack\">" . $testcaseHeader . $summaryTable . $tableOfResultData . "</div>"; 
	// return both the text to diplay and the summary of results in order for category to produce
	// an aggregate summary
	return array($textToDisplay, $summaryOfResultData); 
}

function getTCClassNameByStatus($status)
{
	global $g_tc_status;

	$className = "bgPurple";
	$notRunColor = "bgBlack";
	$passedColor = "bgGreen";
	$blockedColor = "bgBlue";
	$failedColor = "bgRed";
	
	switch($status)
	{
		case $g_tc_status['passed']:
			$className = $passedColor;
			break;
		case $g_tc_status['failed']:
			$className = $failedColor;
			break;
		case $g_tc_status['blocked']:
			$className = $blockedColor;
			break;
		case $g_tc_status['not_run']:
			$className = $notRunColor;
			break;
		default:
	}
	return $className;
}

/**
 * Function createSummaryOfTestCaseResults
 * @param $arrayOfResults - 2 dimention array containing build number 
 * @return summaryArray [totalexecutions, totalPassed, totalFailed, totalBlocked, lastResult]
 */
function createSummaryOfTestCaseResults($arrayOfResults){
	
	global $g_tc_status;
	
	
  $totalExecutions = 0;
  $numberOfPasses = 0;
  $numberOfFailures = 0;
  $numberOfBlocked = 0;
  $result;
  $lastResult = 'n';
  
  // do not enter this block if there are no results
  // if there are no results, this var will not be an array
  if (is_array($arrayOfResults)){
    // retrieve keys - which are build numbers, ordered in incrementing order
    $arrayOfBuildNumbersTested = key($arrayOfResults);
    // iterate across arrayOfResults
    while ($buildTested = key($arrayOfResults)){
      $result = $arrayOfResults[$buildTested][3];
   if ($result == $g_tc_status['passed']){
	$numberOfPasses++;
	$totalExecutions++;
	$lastResult = $result;
      }
      elseif ($result == $g_tc_status['failed']){
	$numberOfFailures++;
	$totalExecutions++;
	$lastResult = $result;
      }
      elseif ($result == $g_tc_status['blocked']){
	$numberOfBlocked++;
	$totalExecutions++;
	$lastResult = $result;
      }
      elseif ($result == $g_tc_status['not_run']){
	// don't increment anything if test case not marked as executed
	// also do not set the lastResult
      }
      next($arrayOfResults);
    }
  }
    
  $returnArray = array($totalExecutions,$numberOfPasses,$numberOfFailures,$numberOfBlocked,$lastResult);
  return $returnArray;
}


/**
 * Function createTableOfTestCaseResults
 * @param $arrayOfResults - 2 dimention array containing build number 
 * mapped to result row [buildNumber][resultRowArray] 
 * @return $returnData table of test case results
 */

function createTableOfTestCaseResults($arrayOfResults,$arrBuilds){
	
	global $g_tc_status;
	
	//  if case passed paint row green, if failed paint red, if blocked paint blue, if not run paint yellow
	$notRunColor = "bgBlack";

	$numberOfPasses = 0;
	$numberOfFailures = 0;
	$numberOfBlocked = 0;

	$returnData = $numberOfBuildsWithResults . 
                "<table class=\"simple white\">" .
                "<tr class=\"black\"><th>build</th><th>runby</th><th>daterun</th><th>status</th><th>bugs</th><th>notes</th></tr>";

	// if test case was never executed the array will be empty
	// notify user of this
	if (!is_array($arrayOfResults))
	{
		$returnData .= "<tr class=\"" . $notRunColor . "\"><td>THIS CASE HAS NOT BEEN EXECUTED</td><td></td><td>" .
		              "</td><td></td><td></td><td></td></tr></table>";
		// exit method
		return $returnData;
	}
	$arrayOfBuildNumbersTested = key($arrayOfResults);
	// iterate accross arrayOfResults
	while ($buildTested = key($arrayOfResults))
	{
		$results_status = $arrayOfResults[$buildTested][3];
		$className = getTCClassNameByStatus($results_status);
	
		switch($results_status)
		{
			case $g_tc_status['passed']:
				$numberOfPasses++;
				break;
			case $g_tc_status['failed']:
				$numberOfFailures++;
				break;
			case $g_tc_status['blocked']:
				$numberOfBlocked++;
				break;
		}
	  
		$data = "<tr class=\"" . $className . "\"><td>" .
		htmlspecialchars($arrBuilds[$arrayOfResults[$buildTested][0]])  . 
		"</td><td>" . $arrayOfResults[$buildTested][1] . 
		"</td><td>" . $arrayOfResults[$buildTested][2] .
		"</td><td>" . $arrayOfResults[$buildTested][3] .
		"</td><td>" . $arrayOfResults[$buildTested][4] . 
		"</td><td>" . $arrayOfResults[$buildTested][6] . 
		"</td></tr>";
		$returnData .= $data;
		next($arrayOfResults);
	}
	$returnData = $returnData . "</table>";
	return $returnData;
}

function retrieveArrayOfResults($tcid, $build_list)
{
	$build_list = str_replace(",","','",mysql_escape_string($build_list));
	$sql = " SELECT results.build, results.runby, results.daterun, results.status, results.bugs, " .
	       " results.tcid, results.notes FROM results WHERE (tcid='" . $tcid . 
	       " ') AND (build IN ('" . $build_list . "')) order by build DESC;";

	$arrayOfResultArrays = null;
	$result = do_mysql_query($sql);
	if ($result)
	{
		while ($myrow = mysql_fetch_row($result))
		{
			$arrayOfResultArrays[$myrow[0]]= $myrow;
		}
	}	
	return $arrayOfResultArrays;
}

function constructTestCaseInfo($tcid,$myrow)
{
	$title = $myrow[1];
	$mgttcid = $myrow[8];
	
	return $mgttcid . ": " . $title ;
}
?>