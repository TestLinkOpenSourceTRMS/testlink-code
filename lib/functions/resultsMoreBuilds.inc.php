<?
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *$Id: resultsMoreBuilds.inc.php,v 1.36 2005/10/19 05:27:22 kevinlevy Exp $ 
 * 
 * @author Kevin Levy
 *
 */
////////////////////////////////////////////////////////////////////////////////

require_once('../../config.inc.php');
require_once("common.php");

/**
 * Used to create html table that is used to display summary of data for test plan, component, category
 * @param string totalCases
 * @param string totalLastResultPasses
 * @param string totalLastResultFailures
 * @param string totalLastResultBlocked
 * @param string totalUnexecuted
 * @return string summaryOfTestPlanTable - html table 
 */
function createSummaryTable($totalCases, $passedCases, $failedCases, $blockedCases, $unexecutedCases){
  $summaryTable = "<table class=\"simple\" style=\"width: 100%; " .
    "text-align: center; margin-left: 0px;\"><tr><th>" . lang_get('number_cases') . "</td>" .
    "<th>" . lang_get('number_passed') . "</td><th>" . lang_get('number_failed') . "</td><th>" 
    . lang_get('number_blocked') . "</td><th>" . lang_get('number_not_run') . "</td></tr>";
  $summaryTable = $summaryTable . "<tr><td>" . $totalCases  . "</td><td>" . $passedCases . "</td><td>" . 
    $failedCases . "</td><td>" . $blockedCases . "</td><td>" . $unexecutedCases . "</td></tr></table>";
  return $summaryTable;
}

/**
 * @param string testPlanName
 * @param string build_name_set -- comma delimited list of builds selected by user
 * @param string keyword 
 * @param string owner
 * @param string lastStatus 
 * @return string testPlanReportHeader - html table which contains query parameters specified by user
 */
function createTestPlanReportHeader($testPlanName, $build_name_set, 
				    $keyword, $owner, $lastStatus){
  $testPlanReportHeader = "<table class=\"simple\" style=\"width: 100%; " .
    "text-align: center; margin-left: 0px;\">" .
    "<tr><th>" . lang_get('test_plan_name') . "</th><th>" 
    . lang_get('builds_selected') . "</th>" .
    "<th>" . lang_get('keyword') . "</th><th>" . lang_get('owner') 
    . "</th><th>" . lang_get('last_status') . "</th></tr>";
  $testPlanReportHeader = $testPlanReportHeader . 
    "<tr><td>".htmlspecialchars($testPlanName)."</td><td>" . 
    htmlspecialchars($build_name_set) . "</td><td>".
    htmlspecialchars($keyword) . "</td><td>" . htmlspecialchars($owner) . 
    "</td><td>".htmlspecialchars($lastStatus)."</td></tr></table>";
  return $testPlanReportHeader;
}


/**
 * Function createResultsForTestPlan()
 * Produces Report based on projectId, startBuild, endBuild, keyword, 
 * and owner.  The ability
 * to look at results across a range of builds as opposed to
 * 1 build or ALL builds is the 
 * primary purpose of this method.  
 *
 * If startBuild or endBuild not specified - default values are used 
 * (range build 0 -> latest build) for
 * producing this report.
 *
 * If keyword specified, report only includes those test cases in the 
 * specified test plan which are associated
 * with that keyword.  An empty string can be specified for keyword.
 *
 * If the owner is specified, report only includes those test cases 
 * which belong to categories owned by that order. 
 * An empty string can be specified for owner.
 *
 * keyword and owner queries use pattern matching notation 
 * (example: select * from testcase where 
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
function createResultsForTestPlan($testPlanName, $testPlanID, 
				  $buildsArray, $keyword, $owner, $lastStatus, $xls, $componentsSelected)
{
  //  print "xls = $xls <BR>";
  print_r($componentsSelected);

  $totalCasesForTestPlan = 0;
  $totalLastResultPassesForTestPlan = 0;
  $totalLastResultFailuresForTestPlan = 0;
  $totalLastResultBlockedForTestPlan = 0;
  $totalUnexecutedTestCases = 0;

  // comma delimited list of build.id's for this testplan
  // build.id field is primary key of table and unknown to user
  $build_id_set = null;
  // comma delimited list of build.name's for this testplan
  // build.name field is created by user and how user can identify build
  $build_name_set = null;

  // list of ALL (id, name) pairs for the test plan
  $arrAllBuilds = getBuilds($testPlanID," ORDER BY build.name ");

  // debug - kl - 10022005
  // other results and execution pages have a different build set
  // print_r($arrAllBuilds);
  //  print "<BR>";
  
  // debug - kl - 10012005
  //print_r($arrAllBuilds);
  for($i = 0;$i < sizeof($buildsArray);$i++)
    {
      if ($i)
	{
	  $build_id_set .= ",";
	  $build_name_set .= ",";
	}
      $build_id_set .= $buildsArray[$i];
      $build_name_set .= $arrAllBuilds[$buildsArray[$i]];
    }

  // debug
  // print "build_id_set = $build_id_set <BR>";
  // print "build_name_set = $build_name_set <BR>";

  $testPlanReportHeader = 
    createTestPlanReportHeader($testPlanName, $build_name_set, $keyword, $owner, $lastStatus);

  // 20050915 - fm - added mgtcomponent
  $sql = " SELECT component.id, mgtcomponent.name, component.projid, component.mgtcompid " .
         " FROM component,mgtcomponent ".
         " WHERE component.mgtcompid = mgtcomponent.id " . 
         " AND projid=" . $testPlanID;
         
  $result = do_mysql_query($sql);

  $aggregateComponentDataToPrint = null;
  while($myrow = mysql_fetch_row($result))
    {
      $componentData = createResultsForComponent($myrow[0], $owner, $keyword, 
						 $build_id_set, $lastStatus,$myrow,$arrAllBuilds);
      
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

  $summaryOfTestPlanTable = 
    createSummaryTable($totalCasesForTestPlan, $totalLastResultPassesForTestPlan, 
		       $totalLastResultFailuresForTestPlan,$totalLastResultBlockedForTestPlan,
		       $totalUnexecutedTestCases);

  if (!$aggregateComponentDataToPrint)
    {
      $aggregateComponentDataToPrint = "no results for this query";
    }
  return array($testPlanReportHeader, $summaryOfTestPlanTable, $aggregateComponentDataToPrint);
}

function createResultsForComponent($componentId, $owner, $keyword, $build_id_set, 
				   $lastResult,$myrow,$arrAllBuilds)
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
  
  $sql = " SELECT CAT.id, MGTCAT.name, CAT.compid, CAT.importance, CAT.risk, " .
           " CAT.owner, CAT.mgtcatid, CAT.CATorder" .
           " FROM category CAT, mgtcategory MGTCAT " .
           " WHERE MGTCAT.id = CAT.mgtcatid " .
           " AND CAT.compid=" . $componentId;
      
  if (strlen($owner))
  {
    $sql .= " AND CAT.owner = '" . mysql_escape_string($owner) . "'";
  }  
  $sql .= " ORDER BY MGTCAT.CATorder ASC ";
  $result = do_mysql_query($sql);

  $aggregateCategoryDataToPrint = null;
  while ($myrow = mysql_fetch_row($result))
    {
      $categoryData = createResultsForCategory($myrow[0], $keyword, $build_id_set, $lastResult,$myrow,$arrAllBuilds);
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

  $summaryOfComponentTable = createSummaryTable($totalCasesForComponent, $totalLastResultPassesForComponent, $totalLastResultFailuresForComponent, $totalLastResultBlockedForComponent, $totalUnexecutedTestCases);
  
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

function createResultsForCategory($categoryId, $keyword, $build_id_set, $lastResultToQueryFor,$myrow,$arrAllBuilds)
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
  
  $categoryHeader = lang_get('category_header') . htmlspecialchars($categoryName) . " " . lang_get('owner_header') . htmlspecialchars($owner);
  $sql = " SELECT testcase.id, testcase.title, testcase.summary, testcase.steps, " .
           " testcase.exresult, testcase.catid, testcase.active, testcase.version, " .
           " testcase.mgttcid, testcase.keywords, testcase.TCorder " .
    " FROM testcase WHERE (catid='" . $categoryId . "') AND (keywords LIKE '%" . $keyword . "%') ";
  
  $sql .= " ORDER by TCorder ASC";

  // debug - kl - 10012005
  // print "sql = $sql <BR>";

  $result = do_mysql_query($sql);
  
  $testCaseTables;
  $tcInfo = null;
  $tcIDList = null;
  while ($myrow = mysql_fetch_row($result))
    {
      $totalCasesForCategory++;
      $tcID = $myrow[0];
      $tcInfo[$tcID] = $myrow;
      if($tcIDList)
	$tcIDList .= ",";
      $tcIDList .= $tcID;
    }
  $build_list = str_replace(",","','",mysql_escape_string($build_id_set));
  $sql = " SELECT results.build_id, results.runby, results.daterun, results.status, results.bugs, " .
         " results.tcid, results.notes " .
         " FROM results WHERE tcid IN (" . $tcIDList . ")".
         " AND (results.build_id IN ('" . $build_list . "')) ORDER BY results.build_id DESC;";

  // debug block - kl 09252005
  // print "sql = $sql <BR>";
   
  $sqlBuildResult = do_mysql_query($sql);

  $tcBuildInfo = null;
  //I need the num results so I can do the check below on not run test cases
  while($myrowTC = mysql_fetch_row($sqlBuildResult))
    {
      $tcID = $myrowTC[5];
      $status = $myrowTC[3];
      $build = $myrowTC[0];
      // debug - kl - 10022005 
      // delete this line (this only print 13)
      // print "xx tcID = $tcID, status = $status, build = $build <BR>"; 
      $tcBuildInfo[$tcID][$build] = $myrowTC;
      if ($status == $notRunStatus || isset($tcStatusInfo[$tcID]))
	continue;
      $tcStatusInfo[$tcID] = $status;
    }
  //while ($myrow = mysql_fetch_row($result)){

  $lastResult = 'n';
  $lastResultHasBeenSet = false;

  foreach ($tcInfo as $tcID => $myrow)
    {
      // debug - kl - 10022005 
      // print "where last result is set - tcStatusInfo[tcID] = $tcStatusInfo[$tcID] <BR>";
      
      // if results is not set, set to n
      $lastResult = isset($tcStatusInfo[$tcID]) ? $tcStatusInfo[$tcID] : 'n';
      
      $results = isset($tcBuildInfo[$tcID]) ? $tcBuildInfo[$tcID] : null;

      // kl - 09252005 - I don't think $results is being populated correctly
      
      // debug - kl - 20051002
      // print "tcid = $myrow[0] <BR>";

      $testCaseData = createResultsForTestCase($myrow[0], $myrow, $arrAllBuilds, $results, $lastResult);
      $testCaseInfoToPrint = $testCaseData[0];
      $summaryOfTestCaseInfo = $testCaseData[1];

      // debug - kl - 20051002
      // lastResult is being printed incorrectly here
      // print "lastResult = $lastResult <BR>";

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
        
      if ($lastResultToQueryFor == lang_get('last_status_any')){
	$testCaseTables = $testCaseTables . $testCaseInfoToPrint;
	$testCasesReturnedByQuery = true;
      }
      elseif (($lastResult == $g_tc_status['passed']) && ($lastResultToQueryFor == lang_get('last_status_passed'))){
	$testCaseTables = $testCaseTables . $testCaseInfoToPrint;
	$testCasesReturnedByQuery = true;
      }
      elseif (($lastResult == $g_tc_status['failed']) && ($lastResultToQueryFor == lang_get('last_status_failed'))){

	$testCaseTables = $testCaseTables . $testCaseInfoToPrint;
	$testCasesReturnedByQuery = true;
      }
      elseif (($lastResult == $g_tc_status['blocked']) && ($lastResultToQueryFor == lang_get('last_status_blocked'))){

	$testCaseTables = $testCaseTables . $testCaseInfoToPrint;
	$testCasesReturnedByQuery = true;
      }
      elseif (($lastResult == $g_tc_status['not_run']) && ($lastResultToQueryFor == lang_get('last_status_not_run'))){

	$testCaseTables = $testCaseTables . $testCaseInfoToPrint;
	$testCasesReturnedByQuery = true;
      }
    }
  
  $summaryOfCategoryTable = createSummaryTable($totalCasesForCategory, $totalLastResultPassesForCategory, $totalLastResultFailuresForCategory, $totalLastResultBlockedForCategory, $totalUnexecutedTestCases);

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

function createResultsForTestCase($tcid, $myrow,$arrAllBuilds,$arrayOfResults,$lastResult)
{
  $testcaseHeader = constructTestCaseInfo($tcid,$myrow);
  $summaryOfResultData = null;
  $tableOfResultData = createTableOfTestCaseResults($arrayOfResults,$arrAllBuilds,$summaryOfResultData);
  $className = getTCClassNameByStatus($lastResult);
  
  $summaryTable = "<table class=\"simple white\">";
  $summaryTable .= "<tr class=\"black\"><th>" . lang_get('number_executions') . "</th><th>" . lang_get('number_passed') . "</th><th>" . lang_get('number_failed') . "</th><th>" . lang_get('number_blocked') . "</th></tr>";
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
 * Function createTableOfTestCaseResults
 * @param $arrayOfResults - 2 dimention array containing build number 
 * mapped to result row [buildNumber][resultRowArray] 
 * @return $returnData consists of 2 types :
 *         1.)the first type is a string which is an html table for a single test case which 
 *            describes how many times the case has been run, passed, failed, blocked 
 *         2.) an array of integers : array($numberOfPasses+$numberOfFailures+$numberOfBlocked,
 *                                           $numberOfPasses,$numberOfFailure,$numberOfBlocked)
 */
function createTableOfTestCaseResults($arrayOfResults,$arrAllBuilds,&$returnArray){
    $returnData = "<table class=\"simple white\">" .
    "<tr class=\"black\"><th>" . lang_get('build') . "</th><th>" . lang_get('runby') . "</th><th>" . lang_get('daterun') . "</th>" .
    "<th>" . lang_get('status') . "</th><th>" . lang_get('bugs') . "</th><th>" . lang_get('notes') . "</th></tr>";

  // if test case was never executed the array will be empty
  // notify user of this
  if (!is_array($arrayOfResults))
    {
      // debug block - kl - 09252005
      //      print "createTableOfTestCaseResults - arrayOfResults is empty <BR>";
      $returnData .= "<tr class=\"black\"><td>" . lang_get('case_not_run_warning') . "</td><td></td><td>" .
	"</td><td></td><td></td><td></td></tr></table>";
      // update return array and exit method
      $returnArray = array(0,0,0,0);
      // debug - kl - 10022005
      // print_r($returnArray);
      // print "<BR>";
     return $returnData;
    }
  
  global $g_tc_status;
  $numberOfPasses = 0;
  $numberOfFailures = 0;
  $numberOfBlocked = 0;
  
  // debug block - kl - 09252005
  //$printThis2 = key($arrayOfResults);
  //print "<BR> key of arrayOfResults = $printThis2 <BR>";

  // iterate accross arrayOfResults
  while ($buildTested = key($arrayOfResults))
    {
      // debug - kl - 10022005
      // print "buildTested = $buildTested <BR>";
      $one = $arrayOfResults[$buildTested][3];
      $two = $arrayOfResults[$buildTested][2];
      $results_status = $arrayOfResults[$buildTested][3];
      // debug block -kl -09252005
      // print "one = $one <BR>";
      // print "two = $two <BR>";
      // print "result_status = $results_status<BR>";

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
      $resultInfo = $arrayOfResults[$buildTested];
      $data = "<tr class=\"" . $className . "\"><td>" .
	htmlspecialchars($arrAllBuilds[$resultInfo[0]])  . 
	"</td><td>" . $resultInfo[1] . 
	"</td><td>" . $resultInfo[2] .
	"</td><td>" . $resultInfo[3] .
	"</td><td>" . $resultInfo[4] . 
	"</td><td>" . $resultInfo[6] . 
	"</td></tr>";
      $returnData .= $data;
      next($arrayOfResults);
      
    } // end while block
  // kl - 09252005 debug block
  // $printThis = $g_tc_status['passed'];
  // print "<BR> g_tc_status = $printThis <BR>";
  //  print "<BR> passes = $numberOfPasses, failures = $numberOfFailures, blocked = $numberOfBlocked<BR>";

  $returnArray = array($numberOfPasses+$numberOfFailures+$numberOfBlocked,$numberOfPasses,$numberOfFailures,
		       $numberOfBlocked);
  
  
  $returnData .= "</table>";
  // debug - kl - 10022005 
  
  // print_r($returnArray);
  // print "<BR>";
  return $returnData;
}


function constructTestCaseInfo($tcid,$myrow)
{
  $title = $myrow[1];
  $mgttcid = $myrow[8];
  
  return $mgttcid . ": " . $title ;
}

// 20050915 - fm
// 20050912 - added by kl
function getArrayOfComponentNames($tpID)
{

  $sql = " SELECT mgtcomponent.name, mgtcomponent.id " . 
         " FROM component,mgtcomponent " .
         " WHERE component.mgtcompid = mgtcomponent.id " .
         " AND projid=" . $tpID;

  $result = do_mysql_query($sql);
  $arrayOfComponentNames = array();
  while($myrow = mysql_fetch_row($result)) 
  {
    // 10182005 - kl - debug
    //print "$myrow[1] $myrow[0] <BR>";
    $arrayOfComponentNames[$myrow[1]] =  $myrow[0];
  }
  // 10182005 - kl - debug
  //print_r($arrayOfComponentNames);
  return $arrayOfComponentNames;
}


?>