<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: results.class.php,v $
 *
 * @version $Revision: 1.8 
 * @modified $Date: 2007/02/20 05:57:42 $ by $Author: kevinlevy $
 *
 *
 * This class is encapsulates most functionality necessary to query the database
 * for results to publish in reports.  It returns data structures to the gui layer in a 
 * manner that are easy to display in smarty templates.  
 *-------------------------------------------------------------------------
 * Revisions:
 * 20070219 - kevinlevy - nearing completion for 1.7 release
 * 20061113 - franciscom - changes to preparenode() interface
 * 20060829 - kevinlevy - development in progress
**/

require_once('treeMenu.inc.php');
// used for bug string lookup
require_once('exec.inc.php');
require_once('../results/timer.php');

class results
{
	// only call get_linked_tcversions() only once, and save it to
	// $this->linked_tcversions
	var $linked_tcversions = null;
	var $suitesSelected = "";	

	// class references passed in by constructor
	var $db = null;
	var $tp = null;
	var $testPlanID = -1;
	var $prodID = -1;
  
	// KL - 20061225 - creating map specifically for owner and keyword
	var $mapOfLastResultByOwner = null;
	var $mapOfLastResultByKeyword = null;
	var $mapOfLastResultByBuild = null;
	
	var $tplanName = null; //isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : null;

	// construct map linking suite ids to execution rows 
	var $SUITE_TYPE_ID = 2;  
	var $executionsMap = null;  
  
	// suiteStructure is an array with pattern : name, id, array 
	// array may contain another array in the same pattern
	// this is used to describe tree structure
	var $suiteStructure = null;
								
	var $ITEM_PATTERN_IN_SUITE_STRUCTURE = 3;
	var $NAME_IN_SUITE_STRUCTURE = 0; 
	var $ID_IN_SUITE_STRUCTURE = 1;
	var $ARRAY_IN_SUITE_STRUCTURE = 2;
	var $flatArray = null;
	// items assoicated with flatArray	
	var $flatArrayIndex = 0;	
	var $depth = 0;	
	var $previousDepth = 0;
    
	// constants for flatArray
	var $ITEM_PATTERN_IN_FLAT_ARRAY = 3;
	var $DEPTH_IN_FLATARRAY  = 0;
	var $NAME_IN_FLATARRAY = 1;
	var $SUITE_ID_IN_FLATARRAY = 2;
  
	// mapOfLastResult is in the following format  
	// array ([suiteId] => array ([tcId] => Array([buildIdLastExecuted][result]))) 
	var $mapOfLastResult = null;
 
	// map test suite id's to array of [total, pass, fail, block, notRun]
	// for cases in that suite
	var $mapOfSuiteSummary = null;

	// map test suite id's to array of [total, pass, fail, block, notRun]
	// for cases in that suite and in all child suites  
	var $mapOfAggregate = null;
  
	// related to $mapOfAggregate creation
	// as we navigate up and down tree, $suiteId's are addded and removed from '$aggSuiteList
	// when totals are added for a suite, we add to all suites listed in $executionsMap
	// suiteIds are are registered and de-registered from aggSuiteList using functions addToAggSuiteList(), removeFromAggSuiteList() 
	var $aggSuiteList  = array(); 
 
	// map test suite id to number of (total, passed, failed, blocked, not run) 
	// only counts test cases in current suite
	var $mapOfTotalCases = null;
	var $mapOfCaseResults = null;
	// array
	// (total cases in plan, total pass, total fail, total blocked, total not run)
	var $totalsForPlan = null;
   
	// map test case ids to array of keywords associated with test case
	var $keywordData = null;

	// TO-DO check this description of this object
	// map of keywordIds to Array (total, passed, failed, blocked, notRun) 
	var $aggregateKeyResults = null;

	// TO-DO check this description of this object    
	// map of ownerIds to Array (total, passed, failed, blocked, notRun) 
	var $aggregateOwnerResults = null;
    
	// TO-DO check this description of this object    
	// map of buildsIds to Array (total, passed, failed, blocked, notRun) 
	var $aggregateBuildResults = null;
    
	// $builds_to_query = 'a' will query all build, $builds_to_query = -1 will prevent
	// most logic in constructor from executing/ executions table from being queried
	// if keyword = 0, search by keyword would not be performed
	function results(&$db, &$tp, $suitesSelected = 'all', $builds_to_query = -1, $lastResult = 'a', $keywordId = 0, $owner = null)
	{
		$this->db = $db;	
    	$this->tp = $tp;    
    	$this->suitesSelected = $suitesSelected;  	
    	$this->prodID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    	$this->testPlanID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0 ;
		$this->tplanName = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : null;

		// build suiteStructure and flatArray
		$this->suiteStructure = $this->generateExecTree($keywordId, $owner);
	
		// KL - if no builds are specified, no need to execute the following block of code
		if ($builds_to_query != -1) {
			// retrieve results from executions table

			// KL - 2/01/07 
			// we should NOT build executions map with cases that are just pass/failed/or blocked.
			// we should always populate the executions map with all results 
			// and then programmatically figure out the last result
			// if you just query the executions table for those rows with status = 'p'
			// that is not the way to determine last result
		
			$this->executionsMap = $this->buildExecutionsMap($builds_to_query, 'a', $keywordId, $owner);    
		
			// get keyword id -> keyword name pairs used in this test plan
			$arrKeywords = $tp->get_keywords_map($this->testPlanID); 	
			// get owner id -> owner name pairs used in this test plan
			$arrOwners = get_users_for_html_options($db, null, false);
		
			// get build id -> build name pairs used in this test plan
			$arrBuilds1 = $tp->get_builds($this->testPlanID); 
			$arrBuilds = null;
		
			while ($key = key($arrBuilds1)){
				$currentArray = $arrBuilds1[$key] ;
				$build_id = $currentArray['id'];
				$build_name = $currentArray['name'];
				$arrBuilds[$build_id] = $build_name;
				next($arrBuilds1);
			} // end while
		
			// KL - 20061229 - this call may not be necessary for all reports 
			// only those that require info on results for keywords
			// Map of test case ids to array of associated keywords
			$this->keywordData = $this->getKeywordData($arrKeywords);
	
			// create data object which tallies last result for each test case
			// this function now also creates mapOfLastResultByKeyword
			$this->createMapOfLastResult($this->suiteStructure, $this->executionsMap, $lastResult);
	  
			$this->aggregateKeywordResults = $this->tallyKeywordResults($this->mapOfLastResultByKeyword, $arrKeywords);
		
			$this->aggregateOwnerResults = $this->tallyOwnerResults($this->mapOfLastResultByOwner, $arrOwners);
						
			// create data object which tallies totals for individual suites
			// child suites are NOT taken into account in this step
			$this->createMapOfSuiteSummary($this->mapOfLastResult);
      
			// create data object which tallies totals for suites taking
			// child suites into account
			$this->createAggregateMap($this->suiteStructure, $this->mapOfSuiteSummary);

			$this->totalsForPlan = $this->createTotalsForPlan($this->suiteStructure, $this->mapOfSuiteSummary);

			// must be done after totalsForPlan is performed because the total # of cases is needed
			$this->aggregateBuildResults = $this->tallyBuildResults($this->mapOfLastResultByBuild, $arrBuilds, $this->totalsForPlan);
		} // end if block
	} // end results constructor

	/**
	* tallyKeywordResults(parameter1, parameter2)
	*   
	* parameter1 format:
	* Array ([keyword id] => Array ( [test case id] => result ))
	* example:  
	* Array ( [128] => Array ( [14308] => p [14309] => n [14310] => n [14311] => f [14312] => n [14313] => n ) 
	*			 [11] => Array ( [14309] => n [14310] => n [14311] => f [14312] => n [14313] => n ))
	*
	* output format :
	* Array ( [keyword id] => Array ( total, passed, failed, blocked, not run))       
	*/
	function tallyKeywordResults($keywordResults, $keywordIdNamePairs) {
		if ($keywordResults == null) {
			return;
		}
		$rValue = null;
		while ($keywordId = key($keywordResults)) {
			$arrResults = $keywordResults[$keywordId];
			$totalCases = sizeOf($arrResults);
			$totalPass =0;
			$totalFail =0;
			$totalBlocked =0;
			$totalNotRun =0;
			while ($testcaseId = key($arrResults)) {
				if ($arrResults[$testcaseId] == 'p') {
					$totalPass++;
				}
				elseif($arrResults[$testcaseId] == 'f') {
					$totalFail++;
				}
				elseif($arrResults[$testcaseId] == 'b') {
					$totalBlocked++;
				}
				next($arrResults);
			} // end $testcaseId while
			$totalNotRun = $totalCases - ($totalPass + $totalFail + $totalBlocked);
			$percentCompleted = (($totalCases - $totalNotRun) / $totalCases) * 100;
			$percentCompleted = number_format($percentCompleted,2);		
			$rArray = array($keywordIdNamePairs[$keywordId], $totalCases, $totalPass, $totalFail, $totalBlocked, $totalNotRun, $percentCompleted);
			$rValue[$keywordId] = $rArray;
			next($keywordResults);
		} // end $keywordId while
		return $rValue;
	} // end function 

	/**
	* tallyOwnerResults(parameter1, parameter2)
	*   returns keyword results
	* parameter1 format:
	* Array ([owner id] => Array ( [test case id] => result ))
	* output format :
	* Array ( [owner id] => Array ( total, passed, failed, blocked, not run))       
	*/
	function tallyOwnerResults($ownerResults, $ownerIdNamePairs) {
		if ($ownerResults == null) {
			return;
		}
		$rValue = null;
		while ($ownerId = key($ownerResults)) {
			$arrResults = $ownerResults[$ownerId];
			$totalCases = sizeOf($arrResults);
			$totalPass =0;
			$totalFail =0;
			$totalBlocked =0;
			$totalNotRun =0;
			while ($testcaseId = key($arrResults)) {
				if ($arrResults[$testcaseId] == 'p') {
					$totalPass++;
				}
				elseif($arrResults[$testcaseId] == 'f') {
					$totalFail++;
				}
				elseif($arrResults[$testcaseId] == 'b') {
					$totalBlocked++;
				}
				next($arrResults);
			} // end $testcaseId while
			$totalNotRun = $totalCases - ($totalPass + $totalFail + $totalBlocked);
			$percentCompleted = 0;
			if ($totalCases > 0) {
				$percentCompleted = (($totalCases - $totalNotRun) / $totalCases) * 100;
			}
			$percentCompleted = number_format($percentCompleted,2);		
			if ($ownerId == -1) {
				$name = lang_get('unassigned');
			}
			else
			{
				$name = $ownerIdNamePairs[$ownerId];
			}
			$rArray = array($name, $totalCases, $totalPass, $totalFail, $totalBlocked, $totalNotRun, $percentCompleted);
			$rValue[$ownerId] = $rArray;
			next($ownerResults);
		} // end $ownerId while
		return $rValue;
	} // end function

	/**
	* tallyBuildResults(parameter1, parameter2, parameter3)
	*   returns keyword results
	* parameter1 format:
	* Array ([owner id] => Array ( [test case id] => result ))
	* output format :
	* Array ( [owner id] => Array ( total, passed, failed, blocked, not run))       
	*/
	function tallyBuildResults($buildResults, $arrBuilds, $finalResults) {
		$totalCases = $finalResults['total'];
		if ($buildResults == null) {
			return;
		}
		$rValue = null;
		while ($buildId = key($arrBuilds)) {
			if (array_key_exists($buildId, $buildResults)) {
			   $arrResults = $buildResults[$buildId];
			}
			else {
				$arrResults = array();
			}
			$totalPass =0;
			$totalFail =0;
			$totalBlocked =0;
			$totalNotRun =0;
			while ($testcaseId = key($arrResults)) {
				if ($arrResults[$testcaseId] == 'p') {
					$totalPass++;
				}
				elseif($arrResults[$testcaseId] == 'f') {
					$totalFail++;
				}
				elseif($arrResults[$testcaseId] == 'b') {
					$totalBlocked++;
				}
				next($arrResults);
			} //end $testcaseId while
			$totalNotRun = $totalCases - ($totalPass + $totalFail + $totalBlocked);
			$percentCompleted = 0;
			if ($totalCases != 0) {
				$percentCompleted = (($totalCases - $totalNotRun) / $totalCases) * 100;
			}
			$percentCompleted = number_format($percentCompleted,2);		
			$percentPass = 0;
			$percentFail = 0;
			$percentBlocked = 0;
			if ($totalCases != 0) {
				$percentPass = (($totalPass) / $totalCases) * 100;
				$percentFail = (($totalFail) / $totalCases) * 100;
				$percentBlocked = (($totalBlocked) / $totalCases) * 100;
			}	
			$percentFail = number_format($percentFail,2);			
			$percentPass = number_format($percentPass,2);			
			$percentBlocked = number_format($percentBlocked,2);		
			$rArray = array($arrBuilds[$buildId], $totalCases, $totalPass, $percentPass, $totalFail, $percentFail, $totalBlocked, $percentBlocked, $totalNotRun, $percentCompleted);
			$rValue[$buildId] = $rArray;
			next($arrBuilds);
		} // end $buildId while
		return $rValue;
	} // end function

	function getAggregateKeywordResults() {
		return $this->aggregateKeywordResults;
	}

	function getAggregateOwnerResults() {
		return $this->aggregateOwnerResults;
	}
	
	function getAggregateBuildResults() {
		return $this->aggregateBuildResults;
	}

	// TO-DO- rename getExecutionsMap() (resultsTC.php is 1 file (may not be only file) that references this method)
	function getSuiteList(){
		return $this->executionsMap;
	}
  
	function getSuiteStructure(){
		return $this->suiteStructure;
	}
  
	function getMapOfSuiteSummary(){
		return $this->mapOfSuiteSummary;
	}

	function getMapOfLastResult() {
		return $this->mapOfLastResult;
	}  

	function getAggregateMap(){
		return $this->mapOfAggregate;
	}
  
	function getTotalsForPlan(){
		return $this->totalsForPlan;
	}
  
	/**
	* single-dimension array
	* with pattern level, suite name, suite id
	*/
	function getFlatArray(){
		return $this->flatArray;
	}

	/**
	* function addLastResultToMap()
	* author - KL
	*
	* Creates $this->mapOfLastResult - which provides information on the last result 
	* for each test case.
	* 
	* $this->mapOfLastResult is an array of suite ids 
	* each suiteId -> arrayOfTCresults, arrayOfSummaryResults
	* arrayOfTCresults      ->  array of rows containing (buildIdLastExecuted, result) where row id = testcaseId
	* 	 
	* currently it does not account for user expliting marking a case "not run".
	*
	*  
	*/ 	
	function addLastResultToMap($suiteId, $testcase_id, $buildNumber, $result, $tcversion_id, 
                              $execution_ts, $notes, $suiteName, $executions_id, $name, $tester_id, $feature_id, $assigner_id = -1, $lastResultToTrack){
		if ($buildNumber) {
			$this->mapOfLastResultByBuild[$buildNumber][$testcase_id] = $result;
		}
	
		$sql = "select user_id from user_assignments where feature_id = "  . $feature_id ;
		$owner_row =  $this->db->fetchFirstRow($sql,'testcase_id', 1);
		$owner_id = $owner_row['user_id'];
		if ($owner_id == '') {
			$owner_id = -1;
		}
		$associatedKeywords = null;
		if ($this->keywordData != null) {
			if (array_key_exists($testcase_id, $this->keywordData)) {
				$associatedKeywords = $this->keywordData[$testcase_id];
			}
		}
	
		// handle case where suite has already been added to mapOfLastResult						  
		if ($this->mapOfLastResult && array_key_exists($suiteId, $this->mapOfLastResult)) {
			// handle case where both suite and test case have been added to elmapOfLastResult
			if (array_key_exists($testcase_id, $this->mapOfLastResult[$suiteId])) {
				// print "test case already in mapOfLastResult <BR>";
				$buildInMap = $this->mapOfCaseResults[$testcase_id]['buildNumber'];	
				if ($buildInMap < $buildNumber) {				
					// print "build in map less than current build number <BR>";
					if (($lastResultToTrack == 'a') || ($lastResultToTrack == $result)) {	
						// owner assignments
						$this->mapOfLastResultByOwner[$owner_id][$testcase_id] = $result;
						// keyword assignments
						for ($i=0 ; $i < sizeof($associatedKeywords); $i++){
							$this->mapOfLastResultByKeyword[$associatedKeywords[$i]][$testcase_id] = $result;
						}
						// mapOfLastResult assignments
						$this->mapOfLastResult[$suiteId][$testcase_id] = null;
						$this->mapOfLastResult[$suiteId][$testcase_id] = array("buildIdLastExecuted" => $buildNumber, 
				                                                       "result" => $result, 
																	   "tcversion_id" => $tcversion_id, 
				                                                       "execution_ts" => $execution_ts, 
																	   "notes" => $notes, 
				                                                       "suiteName" => $suiteName, 
				                                                       "executions_id" => $executions_id, 
				                                                       "name" => $name, 
																	   "tester_id" => $tester_id);
					} // end if -- $lastResultToTrack logic
					// cancel previous result for this build
					else {
						//print "cancel out previous result! <BR>";
						// owner assignments
						unset($this->mapOfLastResultByOwner[$owner_id][$testcase_id]);
						// keyword assignments
						for ($i=0 ; $i < sizeof($associatedKeywords); $i++){
							unset($this->mapOfLastResultByKeyword[$associatedKeywords[$i]][$testcase_id]);
						} // end for
						unset($this->mapOfLastResult[$suiteId][$testcase_id]);
					} // end else
				} // end if -- $buildInMap < $buildNumber 
			} // end array_key_exists if
			// handle case where suite is in mapOfLastResult but test case has not been added
			else {
				if (($lastResultToTrack == 'a') || ($lastResultToTrack == $result)) {
					// owner assignments
					$this->mapOfLastResultByOwner[$owner_id][$testcase_id] = $result;
					// keyword assignments
					for ($i=0 ; $i < sizeof($associatedKeywords); $i++){
						$this->mapOfLastResultByKeyword[$associatedKeywords[$i]][$testcase_id] = $result;
					}
					// mapOfLastResult assignment
					$this->mapOfLastResult[$suiteId][$testcase_id] = array("buildIdLastExecuted" => $buildNumber, 
			                                                        "result" => $result, 
																	"tcversion_id" => $tcversion_id, 
			                                                        "execution_ts" => $execution_ts, 
																	"notes" => $notes, 
			                                                        "suiteName" => $suiteName, 
			                                                        "executions_id" => $executions_id, 
			                                                        "name" => $name, 
																	"tester_id" => $tester_id);
				} // end if -- last result logic
			} // end else	
		} // end if
		// handle case where suite has not been added to mapOfLastResult
		else {
			if (($lastResultToTrack == 'a') || ($lastResultToTrack == $result)) {
				// owner assignments
				$this->mapOfLastResultByOwner[$owner_id][$testcase_id] = $result;	
				// keyword assignments
				for ($i=0 ; $i < sizeof($associatedKeywords); $i++){
					$this->mapOfLastResultByKeyword[$associatedKeywords[$i]][$testcase_id] = $result;
				}
				// mapOfLastResult assignments
				$this->mapOfLastResult[$suiteId][$testcase_id] = array("buildIdLastExecuted" => $buildNumber, 
  		                                                       "result" => $result, 
															   "tcversion_id" => $tcversion_id, 
  		                                                       "execution_ts" => $execution_ts, 
															   "notes" => $notes, 
  		                                                       "suiteName" => $suiteName, 
  		                                                       "executions_id" => $executions_id, 
  		                                                       "name" => $name, 
															   "tester_id" => $tester_id);  		
			} // end if -- last result logic
		} // end else  	
	} // end function
  
	/**
	*  Create statistics on each suite
	*/
	function createMapOfSuiteSummary(&$mapOfLastResult) {
		if ($mapOfLastResult) {
			while ($suiteId = key($mapOfLastResult)) {
				$totalCasesInSuite = count($mapOfLastResult[$suiteId]);  		
				$totalPass = 0;
				$totalFailed = 0;
				$totalBlocked = 0;
				$totalNotRun = 0;  		
				while ($testcase_id = key ($mapOfLastResult[$suiteId])) {
					$currentResult =  $mapOfLastResult[$suiteId][$testcase_id]['result'];
					if ($currentResult == 'p'){
						$totalPass++;
					} 	
					elseif($currentResult == 'f'){
						$totalFailed++;
					} 	
					elseif($currentResult == 'b'){
						$totalBlocked++;
					} 	
					elseif($currentResult == 'n'){
						$totalNotRun++;
					}  			
					$this->mapOfSuiteSummary[$suiteId] =  array('total' => $totalCasesInSuite, 
	  			                                            'pass' => $totalPass, 'fail' => $totalFailed, 
	  			                                            'blocked' => $totalBlocked, 'notRun' => $totalNotRun);
					next($mapOfLastResult[$suiteId]);
				} // end $testcase_id while  		
				next($mapOfLastResult);
			} // end $suiteId while
		} // end if  	
	} // end function
  
	/**
	*  tallies total cases, total pass/fail/blocked/not run for each suite
	*  it takes into account sub-suite tallies within that suite
	*  ex : if suite A contains suites A.1 and A.2, this function
	*  adds up A.1, A.2, and test cases within A
	*/
	function createAggregateMap(&$suiteStructure, &$mapOfSuiteSummary) {  
  		for ($i = 0; $i < count($suiteStructure); $i++ ) {  			  			
  			$suiteId = 0;
  			if (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) == $this->NAME_IN_SUITE_STRUCTURE) {
  				
  			}	  			
  			elseif (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) ==  $this->ID_IN_SUITE_STRUCTURE) {  					
  				// register a suite that we will use to increment aggregate results for
  				$suiteId = $suiteStructure[$i];
  				array_push($this->aggSuiteList, $suiteId);
 				
 				if ($mapOfSuiteSummary && array_key_exists($suiteId, $mapOfSuiteSummary)) {
 					$summaryArray = $mapOfSuiteSummary[$suiteId];
 					$this->addResultsToAggregate($summaryArray['total'], 
 					                             $summaryArray['pass'], 
 					                             $summaryArray['fail'], 
 					                             $summaryArray['blocked'], 
 					                             $summaryArray['notRun']);
 				}
 			} 
			elseif (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) ==  $this->ARRAY_IN_SUITE_STRUCTURE) {
  				if (is_array($suiteStructure[$i])){
  					// go get child totals
  					$newSuiteStructure = $suiteStructure[$i];
  					$this->createAggregateMap($newSuiteStructure, $mapOfSuiteSummary);
				}  	
				// it's very important to pop a suite off the list at this point
				// and only this point
				array_pop($this->aggSuiteList);
  			}
  		} // end for   		
	} // end function
  
	/**
	* iterates over top level suites and adds up totals using data from mapOfAggregate
	*/
	function createTotalsForPlan() {
		$total_sum = 0;
		$pass_sum = 0;
		$fail_sum = 0;
		$blocked_sum = 0;
		$notRun_sum = 0;
		for ($i = 0 ; $i < count($this->suiteStructure) ; $i++) {  		
			if (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) ==  $this->ID_IN_SUITE_STRUCTURE) {  			
				$suiteId = $this->suiteStructure[$i];
				$resultsForSuite = isset($this->mapOfAggregate[$suiteId]) ? $this->mapOfAggregate[$suiteId] : 0;
				$total_sum += $resultsForSuite['total'];
				$pass_sum += $resultsForSuite['pass'];
				$fail_sum += $resultsForSuite['fail'];
				$blocked_sum += $resultsForSuite['blocked'];
				$notRun_sum += $resultsForSuite['notRun'];
			} // end if
		}
		return array("total" => $total_sum, "pass" => $pass_sum, "fail" => $fail_sum, 
  	             "blocked" => $blocked_sum, "notRun" => $notRun_sum); 	
	} // end function
  
	/**
	* 
	*/
	function addResultsToAggregate($t, $p, $f, $b, $nr) 
	{
		for ($i = 0 ; $i < count($this->aggSuiteList); $i++){
			$suiteId = $this->aggSuiteList[$i];
			$currentSuite = null;  
			$total = 0;
			$pass = 0;
			$fail = 0;
			$blocked = 0;
			$notRun = 0;	
			if ($this->mapOfAggregate && array_key_exists($suiteId, $this->mapOfAggregate)) {
				$currentSuite = $this->mapOfAggregate[$suiteId];
				$total =  $currentSuite['total'] + $t;
				$pass = $currentSuite['pass'] + $p;
				$fail = $currentSuite['fail'] + $f;
				$blocked = $currentSuite['blocked'] + $b;
				$notRun = $currentSuite['notRun'] + $nr ;  		
	  			$currentSuite = array('total' => $total, 'pass' => $pass, 'fail' => $fail, 
  			                      'blocked' => $blocked, 'notRun' => $notRun);	
			}
			else {
				$currentSuite = array('total' => $t, 'pass' => $p, 'fail' => $f, 'blocked' => $b, 'notRun' => $nr);	
			}  	  	
			$this->mapOfAggregate[$suiteId] = $currentSuite;
		} // end for loop  	 	
	} // end function
  
	/**
	* 
	*/
	function getKeywordData($keywordsInPlan) {
		// limit the sql query to just those keys in this test plan
		if ($keywordsInPlan == null) {
			return;
		}
		$keys = implode(array_keys($keywordsInPlan), ',');
		$sql = "select testcase_id, keyword_id from testcase_keywords where keyword_id IN ($keys)";
		$tempKeywordStructure =  $this->db->fetchRowsIntoMap($sql,'testcase_id', 1);
		$returnMap = null;
		while ($testcase_id = key($tempKeywordStructure)){
			$arrayOfData = $tempKeywordStructure[$testcase_id];		
			$arrKeyIds = array();
			for ($i = 0 ;$i < sizeof($arrayOfData); $i++) {
				$keywordId = $arrayOfData[$i]['keyword_id'];
				array_push($arrKeyIds, $keywordId);
			}
			$returnMap[$testcase_id] = $arrKeyIds;
			next($tempKeywordStructure);
		} // end while
		return $returnMap;
	} // end function
  
	/**
	* 
	*/
	function createMapOfLastResult(&$suiteStructure, &$executionsMap, $lastResult){  
		$suiteName = null;
		for ($i = 0; $i < count($suiteStructure); $i++){  		
			if (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) == $this->NAME_IN_SUITE_STRUCTURE) {
				$suiteName = $suiteStructure[$i];		
			} 
			elseif (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) ==  $this->ID_IN_SUITE_STRUCTURE) {  			
				$suiteId = $suiteStructure[$i];
				$totalCases = isset($executionsMap[$suiteId]) ? count($executionsMap[$suiteId]) : 0;  	
				for ($j = 0 ; $j < $totalCases; $j++) {
					$currentExecution = $executionsMap[$suiteId][$j];
					$this->addLastResultToMap($suiteId, $currentExecution['testcaseID'], $currentExecution['build_id'], 
					$currentExecution['status'], $currentExecution['tcversion_id'], $currentExecution['execution_ts'], 
					$currentExecution['notes'], $suiteName, $currentExecution['executions_id'], 
					$currentExecution['name'], $currentExecution['tester_id'], $currentExecution['feature_id'], 
					$currentExecution['assigner_id'], $lastResult); 
				} 
	  		}  
			elseif (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) ==  $this->ARRAY_IN_SUITE_STRUCTURE){
				if (is_array($suiteStructure[$i])){
					$childSuite = $suiteStructure[$i];
					$summaryTreeForChild = $this->createMapOfLastResult($childSuite, $executionsMap, $lastResult);
				}  			
			}   // end elseif	
		} // end for loop
	} // end function
	
	/**  
	* Builds $executionsMap map. $executionsMap contains all execution information for suites and test cases.
	* 
	* 
	* $executionsMap = [testsuite_id_1_array, test_suite_id_2_array, ...]
	* 
	* testsuite_id_1_array = []
	* all test cases are included, even cases that have not been executed yet
	*/
	function buildExecutionsMap($builds_to_query, $lastResult = 'a', $keyword = 0, $owner = null){
		// first make sure we initialize the executionsMap
		// otherwise duplicate executions will be added to suites
		$executionsMap = null;
		while ($testcaseID = key($this->linked_tcversions)){
			$info = $this->linked_tcversions[$testcaseID]; 
			$testsuite_id = $info['testsuite_id'];
			$currentSuite = null;
			if (!$executionsMap || !(array_key_exists($testsuite_id, $executionsMap))){
				$currentSuite = array();
			}
			else {
				$currentSuite = $executionsMap[$testsuite_id];
			}
			$tcversion_id = $info['tcversion_id'];
			$sql = "select name from nodes_hierarchy where id = $testcaseID ";
			$results = $this->db->fetchFirstRow($sql);
			$name = $results['name'];
			$executed = $info['executed'];
			$executionExists = true;
			if ($tcversion_id != $executed){
				$executionExists = false;
				if (($lastResult == 'a') || ($lastResult == 'n')) {
					// Initialize information on testcaseID to be "not run"
					$infoToSave = array('testcaseID' => $testcaseID, 
					'tcversion_id' => $tcversion_id, 
					'build_id' => '', 
					'tester_id' => '', 
					'execution_ts' => '', 
					'status' => 'n', 
					'executions_id' => '',
					'notes' => '', 
					'name' => $name,
					'assigner_id' => $info['assigner_id'],
					'feature_id' => $info['feature_id']);
					array_push($currentSuite, $infoToSave);			
				}	  
			}
			if ($executionExists) {
				// TO-DO - this is where we can include the searching of results
				// over multiple test plans - by modifying this select statement slightly
				// to include multiple test plan ids	
				$sql = "SELECT * FROM executions " .
				   "WHERE tcversion_id = $executed AND testplan_id = $_SESSION[testPlanId] ";			   
				if (($lastResult == 'p') || ($lastResult == 'f') || ($lastResult == 'b')){
					$sql .= " AND status = '" . $lastResult . "' ";
				}
				if (($builds_to_query != -1) && ($builds_to_query != 'a')) { 
					$sql .= " AND build_id IN ($builds_to_query) ";
				}	
				$execQuery = $this->db->fetchArrayRowsIntoMap($sql,'id');
				if ($execQuery)
				{
					$executions_id = null;
					while($executions_id = key($execQuery)){
						$notSureA = $execQuery[$executions_id];
						$exec_row = $notSureA[0];
						$testplan_id = $exec_row['testplan_id'];
						// TO-DO use localizedTS
						//$localizedTS = localize_dateOrTimeStamp(null,$dummy,'timestamp_format',$execution_ts);
						// TO-DO - fix bugString call when bug database is not configured
						$bugString = $this->buildBugString($this->db, $executions_id);
						// TO-DO - only add bugString if it's needed - build logic into results contructor
						// to pass this request in	
						$infoToSave = array('testcaseID' => $testcaseID, 
									'tcversion_id' => $tcversion_id, 
									'build_id' => $exec_row['build_id'], 
									'tester_id' => $exec_row['tester_id'], 
									'execution_ts' => $exec_row['execution_ts'], 
									'status' => $exec_row['status'], 
									'notes' => $exec_row['notes'], 
									'executions_id' => $executions_id, 
									'name' => $name, 
									'bugString' => $bugString,									
									'assigner_id' => $info['assigner_id'],
									'feature_id' => $info['feature_id']);
						if ($lastResult != 'n') {
							array_push($currentSuite, $infoToSave);
						}
						next($execQuery);
					} // end while		
				} // end if($execQuery)
				// HANDLE scenario where execution does not exist		          
				elseif (($lastResult == 'a') || ($lastResult == 'n')) {
					$infoToSave = array('testcaseID' => $testcaseID, 
					'tcversion_id' => $tcversion_id, 
					'build_id' => '', 
					'tester_id' => '', 
					'execution_ts' => '', 
					'executions_id' => '',
					'status' => 'n',
					'name' => $name, 
					'notes' => '',
					'assigner_id' => $info['assigner_id'],
					'feature_id' => $info['feature_id']);
					array_push($currentSuite, $infoToSave);			
				} 
			} // end if($executionExists)
			$executionsMap[$testsuite_id] = $currentSuite;
			next($this->linked_tcversions);
		} // end $testcaseId while
		return $executionsMap;
	} // end function
  
	/**
	* TO-DO - figure out what file to include so i don't have
	* to redefine this
	* builds bug information for execution id
	* written by Andreas, being implemented again by KL
	*/
	function buildBugString(&$db,$execID) {
		$bugString = null;
	    $bugsOn = config_get('bugInterfaceOn');
	    if ($bugsOn == null || $bugsOn == false){
		  return $bugString;
		}
		$bugs = get_bugs_for_exec($db,config_get('bugInterface'),$execID);
		if ($bugs) {
			foreach($bugs as $bugID => $bugInfo) {
				$bugString .= $bugInfo['link_to_bts']."<br />";
			}
		}
		return $bugString;
	} // end function

	/**
	* return map of suite id to suite name pairs of all suites
	*/
	function getAllSuites() {
		$returnList = null;
		$name = null;
		$suiteId = null;
		for ($i = 0 ; $i < sizeof($this->flatArray); $i++) {
			if (($i % $this->ITEM_PATTERN_IN_FLAT_ARRAY) == $this->NAME_IN_FLATARRAY) {
				$name = $this->flatArray[$i];		
			}
			elseif (($i % $this->ITEM_PATTERN_IN_FLAT_ARRAY) == $this->SUITE_ID_IN_FLATARRAY) {
				$suiteId = $this->flatArray[$i];
				$returnList[$i] = array('name' => $name, 'id' => $suiteId);
			}
		} 
		return $returnList;
	} // end function

	/**
	* return map of suite id to suite name pairs of top level suites
	* iterates over top level suites and adds up totals using data from mapOfAggregate
	*/
	function getTopLevelSuites(){
		$returnList = null;
		$name = null;
		$suiteId = null;
		for ($i = 0 ; $i < count($this->suiteStructure) ; $i++) {  		
			if (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) == $this->NAME_IN_SUITE_STRUCTURE) {
				$name = $this->suiteStructure[$i];  			
			} // end if
			else if (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) ==  $this->ID_IN_SUITE_STRUCTURE) {  			
				$suiteId = $this->suiteStructure[$i];
				$returnList[$i] = array('name' => $name, 'id' => $suiteId);
			} // end else if
		} // end for loop
		return $returnList;
	} // end function getTopLevelSuites

	/**
	* generateExecTree()
	* KL took this code from menuTree.inc.php.
	* Builds both $this->flatArray and $this->suiteStructure
	* 
	* Builds a multi-dimentional array which represents the tree structure.
	* Specifically an array is returned in the following pattern 
	* every 3rd index is null if suite does not contain other suites
	* or array of same pattern if it does contain suites
	*	
	*  suite[0] = suite id
	*	suite[1] = suite name
	*	suite[2] = array() of child suites or null 
	*	suite[3] = suite id
	*	suite[4] = suite name
	*	suite[5] = array() of child suites or null 
	*
	*/
	function generateExecTree($keyword_id = 0, $owner = null) {
		$tplan_mgr = $this->tp;
		$tproject_mgr = new testproject($this->db);	
		$tree_manager = $tplan_mgr->tree_manager;
		$tcase_node_type = $tree_manager->node_descr_id['testcase'];
		$hash_descr_id = $tree_manager->get_available_node_types();
		$hash_id_descr = array_flip($hash_descr_id);
		$test_spec = $tree_manager->get_subtree($this->prodID,array('testplan'=>'exclude me'),
	                                                     array('testcase'=>'exclude my children'),null,null,true);

		// KL - 20061111 - I do not forsee having to pass a specific test case id into this method
		$DEFAULT_VALUE_FOR_TC_ID = 0;
		$tp_tcs = $tplan_mgr->get_linked_tcversions($this->testPlanID,$DEFAULT_VALUE_FOR_TC_ID,$keyword_id, null, $owner);
		$this->linked_tcversions = &$tp_tcs;
		if (is_null($tp_tcs)) { 
			$tp_tcs = array();
		}
		$test_spec['name'] = $this->tplanName;
		$test_spec['id'] = $this->prodID;
		$test_spec['node_type_id'] = $hash_descr_id['testproject'];
		$suiteStructure = null;
		if($test_spec) {
			$tck_map = null;
			if($keyword_id) {
				$tck_map = $tproject_mgr->get_keywords_tcases($this->prodID,$keyword_id);
			}	
			// testcase_count is required to skip components which don't have cases in the plan
			$count = array();
			$bForPrinting = 0;
			$testcase_count = prepareNode($db,$test_spec,$hash_id_descr,$count,$tck_map,$tp_tcs,$bForPrinting,$owner);
			$test_spec['testcase_count'] = $testcase_count;	
			// $menuUrl = "menuUrl";
			$currentNode = null;
			$currentNodeIndex = 0;
			$suiteStructure = $this->processExecTreeNode(1,$test_spec,$hash_id_descr);
		} 
		return $suiteStructure;	
	} // end generateExecTree

	/**
	* parent_suite_name is used to construct the full hierachy name of the suite
	* ex: "A->A.A->A.A.A"
	*/ 
	function processExecTreeNode($level,&$node,$hash_id_descr,$parent_suite_name = '')
	{
		$currentNode = null;
		$currentNodeIndex = 0;
		$suiteFound = false;

		if (isset($node['childNodes']) && $node['childNodes'] ) {
			$childNodes = $node['childNodes'];
			for($i = 0;$i < sizeof($childNodes);$i++) {
				$current = $childNodes[$i];
				if (!$current) {
					continue;
				}
				$nodeDesc = $hash_id_descr[$current['node_type_id']];
				$id = $current['id'];
				$parentId = $current['parent_id'];
				if (($parentId == $this->prodID) && ($this->suitesSelected != 'all')) {
					if (!in_array($id, $this->suitesSelected)){
						// skip processing of this top level suite
						continue;
					}
				} //end if
				$name = filterString($current['name']);
				if (($id) && ($name) && ($nodeDesc == 'testsuite')) {
					if ($parent_suite_name) {
						$hierarchySuiteName = $parent_suite_name  . " / " . $name;
					}
					else {
						$hierarchySuiteName = $current['name'];
					}	
					/** flat array logic */
					$CONSTANT_DEPTH_ADJUSTMENT = 2;
					$this->depth = $level - $CONSTANT_DEPTH_ADJUSTMENT  ;
					$changeInDepth = $this->depth - $this->previousDepth;
					$this->previousDepth = $this->depth;
					// depth only used by flatArrayIndex to help describe the tree
					$this->flatArray[$this->flatArrayIndex] = $changeInDepth;
					$this->flatArrayIndex++;
					$this->flatArray[$this->flatArrayIndex] = $hierarchySuiteName;
					$this->flatArrayIndex++;		
					$this->flatArray[$this->flatArrayIndex] = $id;
					$this->flatArrayIndex++;
					/** end flat array logic */					
					/** suiteStructure logic */
					$currentNode[$currentNodeIndex] = $hierarchySuiteName;
					$currentNodeIndex++;	
					$currentNode[$currentNodeIndex] = $id;
					$currentNodeIndex++;							
					$currentNode[$currentNodeIndex] = $this->processExecTreeNode($level+1,$current,$hash_id_descr,$hierarchySuiteName);
					$currentNodeIndex++;	
					/** end suiteStructure logic */
				} // end if 
			} // end for
		} // end if
		return $currentNode;
	} //end function
} // end class result
?>