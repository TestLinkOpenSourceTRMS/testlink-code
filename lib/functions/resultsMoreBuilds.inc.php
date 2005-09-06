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
	for($i = 0;$i < sizeof($buildsArray);$i++)
	{
		if ($i)
		{
			$commaDelimitedBuilds .= ",";
		}	
		$commaDelimitedBuilds .= $arrBuilds[$buildsArray[$i]];
	}
  
	$testPlanReportHeader = "<table class=\"simple\" style=\"width: 100%; " .
	                        "text-align: center; margin-left: 0px;\">" .
	                        "<tr><th>Test Plan Name</th><th>Builds Selected</th>" .
	                        "<th>Keyword</th><th>Owner</th><th>Last Status</th></tr>";
	$testPlanReportHeader = $testPlanReportHeader . 
	                        "<tr><td>".htmlspecialchars($testPlanName)."</td><td>" . 
	                        htmlspecialchars($commaDelimitedBuilds) . "</td><td>".
	                        htmlspecialchars($keyword) . "</td><td>" . htmlspecialchars($owner) . 
	                        "</td><td>".htmlspecialchars($lastStatus)."</td></tr></table>";

	$sql = " SELECT component.id,component.name, component.projid, component.mgtcompid from component "
	       " WHERE projid='" . $testPlanID . "'";
	$result = do_mysql_query($sql);

	$aggregateComponentDataToPrint = null;
	while($myrow = mysql_fetch_row($result))
	{
		$componentData = createResultsForComponent($myrow[0], $owner, $keyword, 
		                                           $buildsArray, $lastStatus,$myrow,$arrBuilds);
		
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

function createResultsForComponent($componentId, $owner, $keyword, $buildsArray, $lastResult,$myrow,$arrBuilds)
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
	$sql = " SELECT category.id,category.name, category.compid, category.importance, category.risk, "
	       " category.owner, category.mgtcatid, category.CATorder FROM "
	       " category WHERE (category.compid='" . $componentId .  "')";
	if (strlen($owner))
		$sql .= " AND (category.owner = '" . mysql_escape_string($owner) . "');";
	$sql .= " ORDER by CATorder ASC ";
	$result = do_mysql_query($sql);

	$aggregateCategoryDataToPrint = null;;
	while ($myrow = mysql_fetch_row($result))
	{
		$categoryData = createResultsForCategory($myrow[0], $keyword, $buildsArray, $lastResult,$myrow,$arrBuilds);
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




function createResultsForCategory($categoryId, $keyword, $buildsArray, $lastResultToQueryFor,$myrow,$arrBuilds)
{
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
	$sql = "select testcase.id, testcase.title, testcase.summary, testcase.steps, testcase.exresult, testcase.catid, testcase.active, testcase.version, testcase.mgttcid, testcase.keywords, testcase.TCorder from testcase where (catid='" . $categoryId . "') AND (keywords LIKE '%" . $keyword . "%') ";
	
	$sql .= " ORDER by TCorder ASC";
	$result = do_mysql_query($sql);
  
  $testCaseTables;


  while ($myrow = mysql_fetch_row($result)){
    $totalCasesForCategory++;
    $testCaseData = createResultsForTestCase($myrow[0], $buildsArray,$myrow,$arrBuilds);
    $testCaseInfoToPrint = $testCaseData[0];
    $summaryOfTestCaseInfo = $testCaseData[1];
    $lastResult = $summaryOfTestCaseInfo[4];
    if ($lastResult == 'p'){
      $totalLastResultPassesForCategory++;
    }
    elseif ($lastResult == 'f'){
      $totalLastResultFailuresForCategory++;
    }
    elseif ($lastResult == 'b'){
      $totalLastResultBlockedForCategory++;      
    }
    elseif ($lastResult == 'n'){
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
    elseif (($lastResult == 'p') && ($lastResultToQueryFor == 'passed')){
      $testCaseTables = $testCaseTables . $testCaseInfoToPrint;

      $testCasesReturnedByQuery = true;
    }
    elseif (($lastResult == 'f') && ($lastResultToQueryFor == 'failed')){

      $testCaseTables = $testCaseTables . $testCaseInfoToPrint;
      $testCasesReturnedByQuery = true;
    }
    elseif (($lastResult == 'b') && ($lastResultToQueryFor == 'blocked')){

      $testCaseTables = $testCaseTables . $testCaseInfoToPrint;
      $testCasesReturnedByQuery = true;
    }
    elseif (($lastResult == 'n') && ($lastResultToQueryFor == 'unexecuted')){

      $testCaseTables = $testCaseTables . $testCaseInfoToPrint;
      $testCasesReturnedByQuery = true;
    }
  }

  $summaryOfCategoryTable = "<table class=\"simple\" style=\"width: 100%; text-align: center; margin-left: 0px;\"><tr><th># Cases</td><th># Passed</td><th># Failed</td><th># Blocked</td><th># Unexecuted</td></tr>";

  $summaryOfCategoryTable = $summaryOfCategoryTable . "<tr><td>" . $totalCasesForCategory  . "</td><td>" . $totalLastResultPassesForCategory . "</td><td>" . $totalLastResultFailuresForCategory . "</td><td>" . $totalLastResultBlockedForCategory . "</td><td>" . $totalUnexecutedTestCases . "</td></tr></table>";

  // only display an option to expand the category info if there is any test cases which match the query parameters
  $categoryDataToPrint = null;
  if ($testCasesReturnedByQuery)
  {
    $categoryDataToPrint = "<h2 onClick=\"plusMinus_onClick(this);\"><img class=\"plus\" src=\"icons/plus.gif\">" . $categoryHeader . $summaryOfCategoryTable  . "</h2><div class=\"workBack\">" . $testCaseTables . "</div>";
  }
  
  $summaryOfCategory = array($totalCasesForCategory, $totalLastResultPassesForCategory, $totalLastResultFailuresForCategory, $totalLastResultBlockedForCategory, $totalUnexecutedTestCases);
  return array($summaryOfCategory, $categoryDataToPrint, $testCasesReturnedByQuery); 
}

/*
function retrieve_component_table_info($myrow){
  $sql = "select component.id, component.name, component.projid, component.mgtcompid from component where id='" . $componentId . "';";
  $result = do_mysql_query($sql);
  $returnRowArray;
  while ($myrow = mysql_fetch_row($result)){
    $returnRowArray = array($myrow[0],$myrow[1],$myrow[2],$myrow[3]);
  }
  return $returnRowArray;
}
*/
/*
function retrieve_testcase_table_info($testcaseId)
{
  $sql = "select testcase.id, testcase.title, testcase.summary, testcase.steps, testcase.exresult, testcase.catid, testcase.active, testcase.version, testcase.mgttcid, testcase.keywords, testcase.TCorder from testcase where id='" . $testcaseId . "';";
  $result = do_mysql_query($sql);
  $returnRowArray;
  while ($myrow = mysql_fetch_row($result))
  {
    $returnRowArray = array($myrow[0],$myrow[1],$myrow[2],$myrow[3],$myrow[4],$myrow[5],$myrow[6],$myrow[7],$myrow[8],$myrow[9],$myrow[10]);
  }
  return $returnRowArray;
}
*/
/*
function retrieve_category_table_info($categoryId){
  $sql = "select category.id, category.name, category.compid, category.importance, category.risk, category.owner, category.mgtcatid, category.CATorder from category where id='" . $categoryId . "';";
  //  print "sql = $sql";
  //$sql = "select * from category where id='" . $categoryId . "';";
  $result = do_mysql_query($sql);
  $returnRowArray;
  while ($myrow = mysql_fetch_row($result)){
    //print "got into while loop <BR>";
    $returnRowArray = array($myrow[0],$myrow[1],$myrow[2],$myrow[3],$myrow[4],$myrow[5],$myrow[6],$myrow[7]);
    //    print_r($returnRowArray);
  }
  return $returnRowArray;
}
*/
function createResultsForTestCase($tcid, $buildsArray,$myrow,$arrBuilds){
  $testcaseHeader = constructTestCaseInfo($tcid,$myrow);
  $arrayOfResults = retrieveArrayOfResults($tcid, $buildsArray);
  $tableOfResultData = createTableOfTestCaseResults($arrayOfResults,$arrBuilds);
  $summaryOfResultData = createSummaryOfTestCaseResults($arrayOfResults);
  $lastResult = $summaryOfResultData[4];
  $colorToPaintRow;
  $notRunColor = "#ffff00";
  $passedColor = "#90ee90";
  $blockedColor = "#add8e6";
  $failedColor = "#ff0000";
  if ($lastResult == 'p'){
    $colorToPaintRow = $passedColor;
  }
  elseif ($lastResult == 'f'){
    $colorToPaintRow = $failedColor;
  }
  elseif ($lastResult == 'b'){
    $colorToPaintRow = $blockedColor;
  }
  elseif ($lastResult == 'n'){
    $colorToPaintRow = $notRunColor;
  }
  $summaryTable = "<table class=\"simple\" style=\"width: 100%; text-align: center; margin-left: 0px;\">";
  $summaryTable = $summaryTable . "<tr><th># executions</th><th># passed</th><th># failures</th><th># blocked</th></tr>";
  $summaryTable = $summaryTable . "<tr bgcolor='$colorToPaintRow'><td>" . $summaryOfResultData[0]  . "</td><td>" . $summaryOfResultData[1] . "</td><td>" . $summaryOfResultData[2] . "</td><td>" . $summaryOfResultData[3] . "</td></tr></table>";
  $textToDisplay = "<div class=\"workBack\">" . $testcaseHeader . $summaryTable . $tableOfResultData . "</div>"; 
  // return both the text to diplay and the summary of results in order for category to produce
  // an aggregate summary
  return array($textToDisplay, $summaryOfResultData); 
}

/**
 * Function createSummaryOfTestCaseResults
 * @param $arrayOfResults - 2 dimention array containing build number 
 * @return summaryArray [totalexecutions, totalPassed, totalFailed, totalBlocked, lastResult]
 */
function createSummaryOfTestCaseResults($arrayOfResults){
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
      if ($result == 'p'){
	$numberOfPasses++;
	$totalExecutions++;
	$lastResult = 'p';
      }
      elseif ($result == 'f'){
	$numberOfFailures++;
	$totalExecutions++;
	$lastResult = 'f';
      }
      elseif ($result == 'b'){
	$numberOfBlocked++;
	$totalExecutions++;
	$lastResult = 'b';
      }
      elseif ($result == 'n'){
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
  // if case passed paint row green, if failed paint red, if blocked paint blue, if not run paint yellow
  $colorToPaintRow;
  $notRunColor = "#ffff00";
  $passedColor = "#90ee90";
  $blockedColor = "#add8e6";
  $failedColor = "#ff0000";

  $numberOfPasses = 0;
  $numberOfFailures = 0;
  $numberOfBlocked = 0;

  $returnData = $numberOfBuildsWithResults . "<table class=\"simple\" style=\"width: 100%; text-align: center; margin-left: 0px;\"><tr><th>build</th><th>runby</th><th>daterun</th><th>status</th><th>bugs</th><th>notes</th></tr>";

  // if test case was never executed the array will be empty
  // notify user of this
  if (!is_array($arrayOfResults)){
    $returnData = $returnData . "<tr bgcolor=" . $notRunColor . "><td>THIS CASE HAS NOT BEEN EXECUTED</td><td></td><td></td><td></td><td></td><td></td></tr></table>";
    // exit method
    return $returnData;
  }
  $arrayOfBuildNumbersTested = key($arrayOfResults);
  // iterate accross arrayOfResults
  while ($buildTested = key($arrayOfResults)){
    $results_status = $arrayOfResults[$buildTested][3];
    if ($results_status == 'p'){
      $colorToPaintRow = $passedColor;
      $numberOfPasses++;
    }
    elseif ($results_status == 'f'){
      $colorToPaintRow = $failedColor;
      $numberOfFailures++;
    }
    elseif ($results_status == 'b'){
      $colorToPaintRow = $blockedColor;
      $numberOfBlocked++;
    }
    elseif ($results_status == 'n'){
      $colorToPaintRow = $notRunColor;
    }
    $returnData = $returnData . "<tr bgcolor='" . $colorToPaintRow . "'><td>" . htmlspecialchars($arrBuilds[$arrayOfResults[$buildTested][0]])  . "</td><td>" . $arrayOfResults[$buildTested][1] . "</td><td>" . $arrayOfResults[$buildTested][2] . "</td><td>" . $arrayOfResults[$buildTested][3] . "</td><td>" . $arrayOfResults[$buildTested][4]  . "</td><td>" . $arrayOfResults[$buildTested][6] . "</td></tr>";
    next($arrayOfResults);
  }
  $returnData = $returnData . "</table>";
  return $returnData;
}

function retrieveArrayOfResults($tcid, $builds)
{
	$build_list = implode(",",$builds);
	$sql = " SELECT results.build, results.runby, results.daterun, results.status, results.bugs, "
	       " results.tcid, results.notes FROM results WHERE (tcid='" . $tcid . 
	       " ') AND (build IN (" . $build_list . ")) order by build DESC;";
	
	$result = do_mysql_query($sql);
	$arrayOfResultArrays; // multidimensional array - array of all result sets
	
	while ($myrow = mysql_fetch_row($result))
	{
		$results_build = $myrow[0];
		$arrayOfResultArrays[$results_build][0] = $myrow[0];
		$arrayOfResultArrays[$results_build][1] = $myrow[1];
		$arrayOfResultArrays[$results_build][2] = $myrow[2];
		$arrayOfResultArrays[$results_build][3] = $myrow[3];
		$arrayOfResultArrays[$results_build][4] = $myrow[4];
		$arrayOfResultArrays[$results_build][5] = $myrow[5];
		$arrayOfResultArrays[$results_build][6] = $myrow[6];
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