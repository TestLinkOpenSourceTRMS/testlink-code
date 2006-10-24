<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: results.class.php,v $
 *
 * @version $Revision: 1.8 
 * @modified $Date: 2006/10/24 16:42:10 $ by $Author: kevinlevy $
 *
 *
 * This class is encapsulates most functionality necessary to query the database
 * for results to publish in reports.  It returns data structures to the gui layer in a 
 * manner that are easy to display in smarty templates.  
 *-------------------------------------------------------------------------
 * Revisions:
 *
 * 20060829 - kevinlevy - development in progress
**/

require_once('treeMenu.inc.php');

class results
{

  var $suitesSelected = "";	

  // class references passed in by constructor
  var $db = null;
  var $tp = null;
//  var $tree = null;
  var $testPlanID = -1;
  var $prodID = -1;

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
    
  // map test cases id to 2-item array containing last build_id and result
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
  
  // array
  // (total cases in plan, total pass, total fail, total blocked, total not run)
  var $totalsForPlan = null;
	
	//TODO: shallow initialization!  
  function results(&$db,&$tp,$suitesSelected,$builds_to_query = -1,$prodID,$testPlanID)
  {

    //print "builds_to_query = $builds_to_query <BR>";
    $this->db = $db;	
    $this->tp = $tp;    
  //  $this->tree = $tree;
    $this->suitesSelected = $suitesSelected;  	
    $this->prodID = $prodID;
    $this->testPlanID = $testPlanID;
	
    // retrieve results from executions table
    $this->executionsMap = $this->buildExecutionsMap($builds_to_query);    
/**
    // build data objects which can be used to describe the tree structure of the test project 
    $this->suiteStructure = $this->buildSuiteStructure($this->suitesSelected);    
    print "suiteStructure from buildSuiteStructure <BR>";
    print_r($this->suiteStructure);
    print "<BR><BR>";
    print "flatarray from buildSuiteStructure <BR>";
    print_r($this->flatArray);
    print "<BR><BR>";
    
    // KL - 20061022 - call new method I am working on 
   // ONLY FOR COMPARING
	
  $this->suiteStructure = null;
  $this->flatArray = null;
  // items assoicated with flatArray	
   $this->flatArrayIndex = 0;	
   $this->depth = 0;	
    $this->previousDepth = 0;
   // END ONLY FOR COMPARING
*/
    $this->suiteStructure = $this->generateExecTree();
    $this->suiteStructure = $this->suiteStructure[0];
	print_r($this->suiteStructure);
/**		
    print "suiteStructure from generateExecTree <BR>";
    print_r($this->suiteStructure);
    print "<BR><BR>";
    print "flatarray from generateExecTree <BR>";
    print_r($this->flatArray);
    print "<BR>";

*/


    // create data object which tallies last result for each test case
    $this->createMapOfLastResult($this->suiteStructure, $this->executionsMap);
	    
    // create data object which tallies totals for individual suites
    // child suites are NOT taken into account in this step
    $this->createMapOfSuiteSummary($this->mapOfLastResult);
    
    // create data object which tallies totals for suites taking
    // child suites into account
    $this->createAggregateMap($this->suiteStructure, $this->mapOfSuiteSummary);
	
    $this->totalsForPlan = $this->createTotalsForPlan($this->suiteStructure, $this->mapOfSuiteSummary);

 	
  }
   
  function getSuiteList(){
    return $this->executionsMap;
  }
  
  function getSuiteStructure(){
  	return $this->suiteStructure;
  }
  
  function getMapOfSuiteSummary(){
  	return $this->mapOfSuiteSummary;
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
 * 
 *
 */
// KL - 20061023 - no longer using this method in favor of generateExecTree	
/**
  function buildSuiteStructure($suiteId)
  {
  	// do not null this out 
  	$currentNode = null;
	$currentNodeIndex = 0;
  	$children = $this->tree->get_children($suiteId);
  	$suiteFound = false;	
  	
	for ($i = 0 ; $i < count($children); $i++){		
		$currentRow = $children[$i];		
		if ($currentRow['node_type_id'] == $this->SUITE_TYPE_ID) {			
			$suiteFound = true;	
			$changeInDepth = ($this->depth - $this->previousDepth);
			$this->previousDepth = $this->depth;
			// depth only used by flatArrayIndex to help describe the tree
			if (($this->flatArrayIndex % $this->ITEM_PATTERN_IN_FLAT_ARRAY) != $this->DEPTH_IN_FLATARRAY){
				print "ERROR 1 in flatArrayIndex creation in lib/functions/results.class.php <BR>";				
			} 
			$this->flatArray[$this->flatArrayIndex] = $changeInDepth;
			$this->flatArrayIndex++;
			
			
			if (($currentNodeIndex % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) != $this->NAME_IN_SUITE_STRUCTURE){
				print "ERROR 3 in lib/functions/results.class.php/buildSuiteStructure() <BR>";
			}
			$currentNode[$currentNodeIndex] = $currentRow['name'];
			$currentNodeIndex++;
	
			if (($this->flatArrayIndex % $this->ITEM_PATTERN_IN_FLAT_ARRAY) != $this->NAME_IN_FLATARRAY){
				print "ERROR 2 in flatArrayIndex creation in lib/functions/results.class.php <BR>";				
			} 
			$this->flatArray[$this->flatArrayIndex] = $currentRow['name'];
			$this->flatArrayIndex++;		
	
	
			if (($currentNodeIndex % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) != $this->ID_IN_SUITE_STRUCTURE){
				print "ERROR 5 in lib/functions/results.class.php/buildSuiteStructure() curentNodeIndex = " . $currentNodeIndex . "<BR>";
			}
			$currentNode[$currentNodeIndex] = $currentRow['id'];
			$currentNodeIndex++;
	
	
			if (($this->flatArrayIndex % $this->ITEM_PATTERN_IN_FLAT_ARRAY) != $this->SUITE_ID_IN_FLATARRAY){
				print "ERROR 6 in flatArrayIndex creation in lib/functions/results.class.php <BR>";				
			} 
			$this->flatArray[$this->flatArrayIndex] = $currentRow['id'];
			$this->flatArrayIndex++;
			
			$newRowId = $currentRow['id'];			
			// depth must be increased because we are about to call recursively
			$this->depth++;		
			if (($currentNodeIndex % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) != $this->ARRAY_IN_SUITE_STRUCTURE){
				print "ERROR 7 in lib/functions/results.class.php/buildSuiteStructure() <BR>";				
			}						
			$currentNode[$currentNodeIndex] = $this->buildSuiteStructure($newRowId);
			$currentNodeIndex++;						 				
		}		
	}
	$this->depth--;
  	return $currentNode;
  }
*/	
	/**
	 * mapOfLastResult -> arrayOfSuiteIds
	 * 
	 * 
	 * suiteId -> arrayOfTCresults, arrayOfSummaryResults
	 * 
	 * arrayOfTCresults      ->  array of rows containing (buildIdLastExecuted, result) where row id = testcaseId
	 * 	 
	 *
	 * currently it does not account for user expliting marking a case "not run".
	 *  */
	
  function addLastResultToMap($suiteId, $testcase_id, $buildNumber, $result){
  	
  	if ($this->mapOfLastResult && array_key_exists($suiteId, $this->mapOfLastResult)) {
		if (array_key_exists($testcase_id, $this->mapOfLastResult[$suiteId])) {
			$buildInMap = $this->mapOfCaseResults[$testcase_id]['buildNumber'];	
			if ($buildInMap < $buildNumber) {				
				// print "addLastResultMap suiteId = " . $suiteId . " testcase_id = " . $testcase_id . " <BR>";
				$this->mapOfLastResult[$suiteId][$testcase_id] = null;
				$this->mapOfLastResult[$suiteId][$testcase_id] = array("buildIdLastExecuted" => $buildNumber, "result" => $result);
			}	
		}	
		else {
			$this->mapOfLastResult[$suiteId][$testcase_id] = array("buildIdLastExecuted" => $buildNumber, "result" => $result);
		}	
	}

  	else {
  		//$totalCases =  count($this->executionsMap[$suiteId]);
  		$this->mapOfLastResult[$suiteId][$testcase_id] = array("buildIdLastExecuted" => $buildNumber, "result" => $result);  		
  	}  	
  }
  
  /**
   * 
   */
  function createMapOfSuiteSummary(&$mapOfLastResult)
  {
	if ($mapOfLastResult)
	{
	  	while ($suiteId = key($mapOfLastResult))
		{
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
	  		}  		
	  		// print "current suite = " . $suiteId . " total cases = " . $totalCasesInSuite . "<BR>";  		
	  		next($mapOfLastResult);
  		}
	}  	
  }
  
  /**
   * 
   */
  function createAggregateMap(&$suiteStructure, &$mapOfSuiteSummary)
  {  
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
  }
  
  /**
   * iterates over top level suites and adds up totals using data from mapOfAggregate
   */
  function createTotalsForPlan() 
  {
  	$total_sum = 0;
  	$pass_sum = 0;
  	$fail_sum = 0;
  	$blocked_sum = 0;
  	$notRun_sum = 0;
  		
  	for ($i = 0 ; $i < count($this->suiteStructure) ; $i++) {  		
  		
  		if (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) ==  $this->ID_IN_SUITE_STRUCTURE) {  			
  			$suiteId = $this->suiteStructure[$i];
  			
  			//print "suiteId = $suiteId <BR>";
  			$resultsForSuite = isset($this->mapOfAggregate[$suiteId]) ? $this->mapOfAggregate[$suiteId] : 0;
  			//print_r($resultsForSuite);
  			$total_sum += $resultsForSuite['total'];
  			$pass_sum += $resultsForSuite['pass'];
  			$fail_sum += $resultsForSuite['fail'];
  			$blocked_sum += $resultsForSuite['blocked'];
  			$notRun_sum += $resultsForSuite['notRun'];
  			
  		} // end if
  			
  	}
  	return array("total" => $total_sum, "pass" => $pass_sum, "fail" => $fail_sum, 
  	             "blocked" => $blocked_sum, "notRun" => $notRun_sum); 	
  }
  
  /**
   * 
   */
 function addResultsToAggregate($t, $p, $f, $b, $nr) 
 {
  	//print "<BR>";
  	//print_r($this->aggSuiteList);
  	//print "<BR>";
  	for ($i = 0 ; $i < count($this->aggSuiteList); $i++){
  	  	$suiteId = $this->aggSuiteList[$i];
  	  	//print "suiteId = " . $suiteId . "<BR>";
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
  }
  
  /**
   * 
   */
  function createMapOfLastResult(&$suiteStructure, &$executionsMap){  
   	$totalCases = 0;
  	$passed = 0;
  	$failed = 0;
  	$blocked = 0;
  	
  	for ($i = 0; $i < count($suiteStructure); $i++){  		
  		if (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) == $this->NAME_IN_SUITE_STRUCTURE) {
  			$totalCases = 0;
  			$passed = 0;
  			$failed = 0;
  			$blocked = 0;  			
  		} // end elseif
  		
  		elseif (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) ==  $this->ID_IN_SUITE_STRUCTURE) {  			
  			$suiteId = $suiteStructure[$i];
  			// print "suite id = $suiteId <BR>";
  			//print_r($executionsMap[$suiteId]);  			
			$totalCases = isset($executionsMap[$suiteId]) ? count($executionsMap[$suiteId]) : 0;
  			$caseId = null;
  			$build = null;
  			$result = null;
  			  			
  			// iterate across all executions for this suite
  			for ($j = 0 ; $j < $totalCases; $j++) {
  				$currentExecution = $executionsMap[$suiteId][$j];
  				//print_r($currentExecution);
  				$caseId = $currentExecution['testcaseID'];
  				$build = $currentExecution['build_id'];
  				$result = $currentExecution['status'];
  				$this->addLastResultToMap($suiteId, $caseId, $build, $result);
  			}
  		} // end elseif 
  		
  		elseif (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) ==  $this->ARRAY_IN_SUITE_STRUCTURE){
  			if (is_array($suiteStructure[$i])){
  				//print "array found <BR>";
  				$childSuite = $suiteStructure[$i];
  				$summaryTreeForChild = $this->createMapOfLastResult($childSuite, $executionsMap);
  			}
  			else {
  				//print "no array <BR>"; 				
  			}  			
  		}   // end elseif	
  	}
  }
	
  /**  
   * Builds $executionsMap map. $executionsMap contains all execution information for suites and test cases.
   * 
   * 
   * $executionsMap = [testsuite_id_1_array, test_suite_id_2_array, ...]
   * 
   * testsuite_id_1_array = []
   *
   */
  function buildExecutionsMap($builds_to_query){
    // first make sure we initialize the executionsMap
    // otherwise duplicate executions will be added to suites
    $executionsMap = null;  
    $linked_tcversions = $this->tp->get_linked_tcversions($_SESSION['testPlanId']);
    /**
    print "\$linked_tcversions = <BR>";
	print_r($linked_tcversions);
	print "<BR>";
	print "<BR>";
    */
    while ($testcaseID = key($linked_tcversions)){
	//print "\$testcaseID = $testcaseID <BR>";
      $info = $linked_tcversions[$testcaseID];
            
      $testsuite_id = $info['testsuite_id'];
		
      $currentSuite = null;
      if (!$executionsMap || !(array_key_exists($testsuite_id, $executionsMap))){
	    $currentSuite = array();
      }
      else {
		$currentSuite = $executionsMap[$testsuite_id];
	  }

      //$notSure2 = $info[1];
      //$tc_id = $info[tc_id];
      $tcversion_id = $info['tcversion_id'];
      //$notSure3 = $info[3];
      $executed = $info['executed'];
      $executionExists = true;
	/**
	print "\$tcversion_id = $tcversion_id <BR>";
	print "\$executed = $executed <BR>";
	print "<BR><BR>";	
	*/
      if ($tcversion_id != $executed){
			//print "test case \$tcversion_id $tcversion_id not executed <BR>";
       		// this test case not been executed in this test plan
			$executionExists = false;
			// print "test case version id " . $tcversion_id . " not executed <BR>";
			// print "executed = $executed <BR>";
			$infoToSave = array(testcaseID => $testcaseID, tcversion_id => $tcversion_id, 
			                    build_id => '', tester_id => '', execution_ts => '', status => 'n', notes => '');
			array_push($currentSuite, $infoToSave);			
      }

      // select * from executions where tcversion_id = $executed;

      if ($executionExists) {
	  	// NOTE TO SELF - this is where we can include the searching of results
		// over multiple test plans - by modifying this select statement slightly
		// to include multiple test plan ids

		$sql = "SELECT * FROM executions " .
			   "WHERE tcversion_id = $executed AND testplan_id = $_SESSION[testPlanId] ";
		if ($builds_to_query != -1)
			$sql .= " AND build_id IN ($builds_to_query) ";
		$execQuery = $this->db->fetchArrayRowsIntoMap($sql,'id');
		// -----------------------------------------------------------
		
		if ($execQuery)
		{
		    while($executions_id = key($execQuery)){
		    	//print "in the loop <BR>";
		  		$notSureA = $execQuery[$executions_id];
		  		$exec_row = $notSureA[0];
		  		$build_id = $exec_row['build_id'];
		  		$tester_id = $exec_row['tester_id'];
		  		$execution_ts = $exec_row['execution_ts'];
		  		$status = $exec_row['status'];
		  		$testplan_id = $exec_row['testplan_id'];
		  		$notes = $exec_row['notes'];
	
		  		$infoToSave = array('testcaseID' => $testcaseID, 'tcversion_id' => $tcversion_id, 'build_id' => $build_id, 'tester_id' => $tester_id, 'execution_ts' => $execution_ts, 'status' => $status, 'notes' => $notes);
		
			    //print_r($infoToSave);
		  		array_push($currentSuite, $infoToSave);
		  		next($execQuery);
			} // end while		
		} // end if($execQuery)
		// HANDLE scenario where execution does not exist		
		else {
			$infoToSave = array(testcaseID => $testcaseID, tcversion_id => $tcversion_id, 
			build_id => '', tester_id => '', execution_ts => '', status => 'n', notes => '');
			array_push($currentSuite, $infoToSave);			

		}
      } // end if($executionExists)
      $executionsMap[$testsuite_id] = $currentSuite;
      next($linked_tcversions);
    } 
    return $executionsMap;
  } // end function
  
  /**
   * return map of suite id to suite name pairs of top level suites
   */
  function getTopLevelSuites(){
  /** iterates over top level suites and adds up totals using data from mapOfAggregate
   */
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

function generateExecTree($keyword_id = 0,$bForPrinting = false,$tc_id = 0)
{
	// $menustring = null;
	// print "generateExecTree() <BR>";
	
	$tplan_mgr = $this->tp;
	$tproject_mgr = new testproject($this->db);
	
	$tree_manager = $tplan_mgr->tree_manager;
	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);
	$test_spec = $tree_manager->get_subtree($this->prodID,array('testplan'=>'exclude me'),
	                                                     array('testcase'=>'exclude my children'),null,null,true);

	$tp_tcs = $tplan_mgr->get_linked_tcversions($this->testPlanID,$tc_id,$keyword_id);
	if (is_null($tp_tcs)) { 
		$tp_tcs = array();
	}
	// KL - TO-DO pass in test plan name
	$test_spec['name'] = "test_spec_name";
	$test_spec['id'] = $this->prodID;
	$test_spec['node_type_id'] = $hash_descr_id['testproject'];
	// KL - not sure what to name this var
	$suiteStructure = null;

	if($test_spec)
	{
		$tck_map = null;
		if($keyword_id) {
			$tck_map = $tproject_mgr->get_keywords_tcases($this->prodid,$keyword_id);
		}
		// KL - comment back in when we add prepareNode() to this class
		$testcase_count = prepareNode($test_spec,$hash_id_descr,$tck_map,$tp_tcs,$bForPrinting);
		//print "\$testcase_count = $testcase_count <BR>";
		$test_spec['testcase_count'] = $testcase_count;
		$getArguments = "getArguments";
		$menuUrl = "menuUrl";
		$currentNode = null;
		$currentNodeIndex = 0;
		$suiteStructure = $this->processExecTreeNode(1,$test_spec,$getArguments,$hash_id_descr,1,$menuUrl,$bForPrinting);
	}
	return $suiteStructure;	
} // end generateExecTree


function processExecTreeNode($level,&$node,$getArguments,$hash_id_descr,$tc_action_enabled,$linkto,$bForPrinting)
{
	$currentNode = null;
	$currentNodeIndex = 0;

	$nodeDesc = $hash_id_descr[$node['node_type_id']];
	$nodeInfo = $this->getSuiteInfoIfItExists($node,$nodeDesc,$tc_action_enabled,$bForPrinting, $currentNode, $currentNodeIndex);

	//$nodeIsTS = 0;
	if ($nodeInfo[0] == TS){
		/** flat array logic */
		//$changeInDepth = $this->depth - $this->previousDepth;
		//$this->previousDepth = $this->depth;
		// depth only used by flatArrayIndex to help describe the tree
	
		//$this->flatArray[$this->flatArrayIndex] = $changeInDepth;
		/**
		$CONSTANT_DEPTH_ADJUSTMENT = 2;
		$this->flatArray[$this->flatArrayIndex] = $level - $CONSTANT_DEPTH_ADJUSTMENT  ;
		$this->flatArrayIndex++;
		$this->flatArray[$this->flatArrayIndex] = $nodeInfo[2];
		$this->flatArrayIndex++;		
		$this->flatArray[$this->flatArrayIndex] = $nodeInfo[1];
		$this->flatArrayIndex++;
		*/
		/** end flat array logic */

		/** suite structure logic */
		$currentNode[$currentNodeIndex] = $nodeInfo[2];
		$currentNodeIndex++;
		$currentNode[$currentNodeIndex] = $nodeInfo[1];
		$currentNodeIndex++;
		/** end suite structure logic */
	}
	if (isset($node['childNodes']) && $node['childNodes'] )
	{
		print "$nodeInfo[2] has child nodes <BR>";
		$childNodes = $node['childNodes'];
		for($i = 0;$i < sizeof($childNodes);$i++)
		{
			$current = $childNodes[$i];
			if(is_null($current)) {
				continue;
			}
			$currentNode[$currentNodeIndex] = $this->processExecTreeNode($level+1,$current,$getArguments,$hash_id_descr,$tc_action_enabled,$linkto,$bForPrinting);
			$currentNodeIndex++;
		}
	}
	return $currentNode;
}

// KL - 20061021
function getSuiteInfoIfItExists($current,$nodeDesc,$tc_action_enabled,$bForPrinting, &$currentNode, $currentNodeIndex)
{
	//$menustring = "['";
	$name = filterString($current['name']);
	//$buildLinkTo = 1;
	//$pfn = "testCase";
	//$testcase_count = isset($current['testcase_count']) ? $current['testcase_count'] : 0;	
	//$versionID = 0;
	
	$returnValue = array();
	$returnType = null;	
	if($nodeDesc == 'testproject')
	{
		$returnType = "TP";
	}
	else if ($nodeDesc == 'testsuite')
	{
		$returnType = "TS";
	}
	else if ($nodeDesc == 'testcase')
	{
		$returnType = "TC";
	}
		
	$returnValue[0] = $returnType;
	$returnValue[1] = $current['id'];
	$returnValue[2] = $name;	

	return $returnValue;
}

/**
function jtree_renderTestSpecTreeNodeOnClose2($current,$nodeDesc)
{
	//$menustring =  "],";
	//return $menustring;
	return null;
}
*/


} // end class result


?>