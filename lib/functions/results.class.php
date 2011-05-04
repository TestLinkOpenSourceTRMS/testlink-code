<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	results.class.php
 * @package 	TestLink
 * @author 		Kevin Levy, franciscom
 * @copyright 	2004-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 * @uses		config.inc.php 
 * @uses		common.php 
 *
 * @internal revisions
 * 20110415 - Julian - BUGID 4418 - Clean up priority usage within Testlink
 * 20110408 - BUGID 4363: General Test Plan Metrics - Overall Build Status -> 
 * 20110407 - BUGID 4363: General Test Plan Metrics - Overall Build Status -> 
 *						  empty line for a build shown with no test cases assigned to user
 *
 * 20110329 - kinow - 	tallyBuildResults()
 *						BUGID 4333: General Test Plan Metrics % complete did not roundgetAggregateBuildResults
 * 20110326 - franciscom - BUGID 4355: 	General Test Plan Metrics - Build without executed 
 *										test cases are not displayed.
 *
 * 20101019 - eloff - BUGID 3794 - added contribution by rtessier
 * 20100821 - asimon - BUGID 3682
 * 20100721 - asimon - BUGID 3406, 1508: changed for user assignments per build:
 *                                       results_overload(), tallyBuildResults(),
 *                                       getTotalsForBuilds(), createTotalsForBuilds(),
 * 20100518 - franciscom - BUGID 3474: Link to test case in Query Metrics Report is broken if using platforms
 * 20100515 - franciscom - BUGID 3438
 * 20090804 - franciscom - added contributed code getPriority()
 * 20090618 - franciscom - BUGID 0002621 
 * 20090414 - amitkhullar - BUGID: 2374-Show Assigned User in the Not Run Test Cases Report 
 * 20090413 - amitkhullar - BUGID 2267 - SQL Error in linked Test Cases
 * 20090409 - amitkhullar - Created an results_overloaded function for extending the base class
                            results for passing extra parameters.
 * 20090327 - amitkhullar- BUGID 2156 - added option to get latest/all results in Query metrics report.
 * 20080928 - franciscom - some minor refactoring regarding keywords
 * 20080928 - franciscom - refactoring on buildExecutionsMap()
 * 20080602 - franciscom - added logic to manage version using tcversion_number
 * 20080513 - franciscom - getTCLink() added external_id 
 *
 * 20080513 - franciscom - buildExecutionsMap() added external_id in output
 * 20080413 - franciscom - refactoring
 * 20080302 - franciscom - refactored of tally* functions, to manage
 *                         user defined test case statuses.
 *                         created tallyResults() method to remove
 *                         duplicated code. (may be can have performance side effects).
 *
 * 20071101 - franciscom - import_file_types, export_file_types
 * 20071013 - franciscom - changes to fix MSSQL problems
 * 20070916 - franciscom - refactoring to remove global coupling
 *                         changes in constructot interface()
 *
 * 20070825 - franciscom - added node_order in buildExecutionsMap()
 * 20070505 - franciscom - removing timer.php
 * 20070219 - kevinlevy - nearing completion for 1.7 release
 * 20060829 - kevinlevy - development in progress
 **/

/** TBD */
require_once('treeMenu.inc.php');
require_once('users.inc.php');
require_once('exec.inc.php'); // used for bug string lookup

// BUGID 3438
if (config_get('interface_bugs') != 'NO')
{
  require_once(TL_ABS_PATH. 'lib' . DIRECTORY_SEPARATOR . 'bugtracking' .
               DIRECTORY_SEPARATOR . 'int_bugtracking.php');
}
/**
 * This class is encapsulates most functionality necessary to query the database
 * for results to publish in reports.  It returns data structures to the gui layer in a
 * manner that are easy to display in smarty templates.
 * 
 * @package TestLink
 * @author kevinlevy
 */
class results extends tlObjectWithDB
{
	/**
	 * only call get_linked_tcversions() only once, and save it to
	 * $this->linked_tcversions
	 */
	private $linked_tcversions = null;
	private $suitesSelected = "";

	/** @var resource references passed in by constructor */
	var  $db = null;

	/** @var object class references passed in by constructor */
	private $tplanMgr = null;
	private $testPlanID = -1;
	private	$tprojectID = -1;
	private	$testCasePrefix='';

	private $resultsCfg;
	private $testCaseCfg='';
	private $map_tc_status;
	private $tc_status_for_statistics;

	private $mapOfLastResultByOwner = null;
	private $mapOfLastResultByKeyword = null;
	private $mapOfLastResultByBuild = null;

    // Platform contribution
	private $mapOfLastResultByPrio = null;
	private $mapOfLastResultByPlatform = null;
	private $tplanName = null;

	// construct map linking suite ids to execution rows
	private $SUITE_TYPE_ID = 2;
	private $executionsMap = null;

	/**
	* suiteStructure is an array with pattern : name, id, array
	* array may contain another array in the same pattern
	* this is used to describe tree structure
	*/
	private $suiteStructure = null;
    const NAME_POS = 0;
    const ID_POS = 1;
    const ARRAY_POS = 2;
    const ITEM_PATTERN_POS = 3;
    
	private $flatArray = null;

	/**
	* associated with flatArray
	*/
	private $flatArrayIndex = 0;
	private $depth = 0;
	private $previousDepth = 0;
	const DEPTH_FLAT_POS  = 0;
	const NAME_FLAT_POS = 1;
	const SUITE_ID_FLAT_POS = 2;
	const ITEM_PATTERN_FLAT_POS = 3;

	/** mapOfLastResult is in the following format
	* array ([suiteId] => array ([tcId] => Array([buildIdLastExecuted][result])))
	*/
	private $mapOfLastResult = null;

	/**
	* map test suite id's to array of [total, passed, failed, blocked, not_run, other user defined]
	* for cases in that suite
	*/
	private $mapOfSuiteSummary = null;

	/**
	* map test suite id's to array of [total, passed, failed, blocked, not_run, other user defined]
	* for cases in that suite and in all child suites
	*/
	private $mapOfAggregate = null;

	/**
	* related to $mapOfAggregate creation
	* as we navigate up and down tree, $suiteId's are addded and removed from '$aggSuiteList
	* when totals are added for a suite, we add to all suites listed in $executionsMap
	* suiteIds are are registered and de-registered from aggSuiteList using
	* functions addToAggSuiteList(), removeFromAggSuiteList()
	*/
	private $aggSuiteList  = array();

	/**
	* map test suite id to number of (total, passed, failed, blocked, not run)
	* only counts test cases in current suite
	*/
	private $mapOfTotalCases = null;

	private $mapOfCaseResults = null;

	/**
	* array(total cases in plan, total pass, total fail, total blocked, total not run)
	*/
	private $totalsForPlan = null;

	/**
	 * BUGID 3406, 1508: array similar to $totalsForPlan, but with counts for each build in it
	 */
	private $totalsForBuilds = null;
	
	/**
	* map test case ids to array of keywords associated with test case
	*/
	private $keywordData = null;

	/**
	* TO-DO check this description of this object
	* map of keywordIds
	*/
	private $aggregateKeyResults = null;

	/**
	* TO-DO check this description of this object
	* map of ownerIds
	*/
	private $aggregateOwnerResults = null;

	/**
	* TO-DO check this description of this object
	* map of buildsIds
	*/
	private $aggregateBuildResults = null;
	private $aggregatePrioResults = null;
	private $aggregatePlatformResults = null;
	protected $latest_results;

  	var $import_file_types = array("XML" => "XML");
  	var $export_file_types = array("XML" => "XML");
  
	/** 
	 * class constructor 
	 * @param resource &$db reference to database handler
	 * @param object &$tplan_mgr reference to instance of testPlan class
	 **/    
	public function results(&$db, &$tplan_mgr,$tproject_info, $tplan_info,
	                        $suitesSelected = 'all',
	                        $builds_to_query = -1, $platforms_to_query = array(ALL_PLATFORMS), $lastResult = 'a', 
	                        $keywordId = 0, $owner = null,
							$startTime = null, $endTime = null,
							$executor = null, $search_notes_string = null, $linkExecutionBuild = null, 
							&$suiteStructure = null, &$flatArray = null, &$linked_tcversions = null)
	{
		$this->latest_results = 1;

		return $this->results_overload($db, $tplan_mgr,$tproject_info, $tplan_info,
	                        $suitesSelected ,
	                        $builds_to_query , $platforms_to_query, $lastResult,
	                        $keywordId , $owner ,
							$startTime , $endTime ,
							$executor , $search_notes_string, $linkExecutionBuild , 
							$suiteStructure, $flatArray, $linked_tcversions);
	}
	
	/**
	 * $builds_to_query = 'a' will query all build, $builds_to_query = -1 will prevent
	 * most logic in constructor from executing/ executions table from being queried
	 * if keyword = 0, search by keyword would not be performed
	 * @author kevinlevy
	 *
	 * @internal Revisions:
	 *      20100720 - asimon - BUGID 3406, 1508: added logic to get result counts on build level
	 * 	    20090327 - amitkhullar - added parameter $latest_results to get the latest results only.	
	 *      20071013 - franciscom - changes to fix MSSQL problems
	 *                 $startTime = "0000-00-00 00:00:00" -> null
  	 *                 $endTime = "9999-01-01 00:00:00" -> null
  	 *
	 *      20070916 - franciscom - interface changes
	 */
	public function results_overload(&$db, &$tplan_mgr,$tproject_info, $tplan_info,
	                        $suitesSelected = 'all',
	                        $builds_to_query = -1, $platforms_to_query = array(ALL_PLATFORMS), $lastResult = 'a',
	                        $keywordId = 0, $owner = null,
							$startTime = null, $endTime = null,
							$executor = null, $search_notes_string = null, $linkExecutionBuild = null, 
							&$suiteStructure = null, &$flatArray = null, &$linked_tcversions = null)
	{
		$tstartOn = microtime(true);
		//echo ($tstartOn) . '<br>';

		$this->resultsCfg = config_get('results');
		$this->testCaseCfg = config_get('testcase_cfg');

  		$this->db = $db;
  		
  		tlObjectWithDB::__construct($db);
  		$this->tplanMgr = $tplan_mgr;
  		$this->map_tc_status = $this->resultsCfg['status_code'];
    

    	// TestLink standard configuration is (at least for me)
    	// not_run not available at user interface level on execution feature as choice.
    	//
    	// if( !isset($dummy['not_run']) )
    	// {
    	//     $dummy['not_run']=$this->map_tc_status['not_run'];
    	// }

    	// This will be used to create dynamically counters if user add new status
    	foreach( $this->resultsCfg['status_label_for_exec_ui'] as $tc_status_verbose => $label)
    	{
        	$this->tc_status_for_statistics[$tc_status_verbose] = $this->map_tc_status[$tc_status_verbose];
    	}
    	if( !isset($this->resultsCfg['status_label_for_exec_ui']['not_run']) )
    	{
        	$this->tc_status_for_statistics['not_run'] = $this->map_tc_status['not_run'];  
    	}
   
    	$this->suitesSelected = $suitesSelected;
    	$this->tprojectID = $tproject_info['id'];
    	$this->testCasePrefix = $tproject_info['prefix'];
    
    	$this->testPlanID = $tplan_info['id'];
		$this->tplanName  = $tplan_info['name'];

    	$this->suiteStructure = $suiteStructure;
		$this->flatArray = $flatArray;
		$this->linked_tcversions = $linked_tcversions;
		
		// build suiteStructure and flatArray
		if (($this->suiteStructure == null) && ($this->flatArray == null) && ($this->linked_tcversions == null))
		{
		    list($this->suiteStructure,$this->linked_tcversions) = $this->generateExecTree($db,$keywordId, $owner);
		}
	

		// KL - if no builds are specified, no need to execute the following block of code
		if ($builds_to_query != -1) {
			// retrieve results from executions table
    
			// get keyword id -> keyword name pairs used in this test plan
			$keywords_in_tplan = $tplan_mgr->get_keywords_map($this->testPlanID,'ORDER BY keyword');

			// KL - 20061229 - this call may not be necessary for all reports
			// only those that require info on results for keywords
			// Map of test case ids to array of associated keywords
			$this->keywordData = null;
			if(!is_null($keywords_in_tplan))
			{
			    $this->keywordData = $this->getKeywordData(array_keys($keywords_in_tplan));
      		} 
			//$tplan_mgr->get_keywords_tcases($this->testPlanID);
			// get owner id -> owner name pairs used in this test plan
			$arrOwners = getUsersForHtmlOptions($db);

			// create data object which tallies last result for each test case
			// this function now also creates mapOfLastResultByKeyword ???

			// KL - 2/01/07
			// we should NOT build executions map with cases that are just pass/failed/or blocked.
			// we should always populate the executions map with all results
			// and then programmatically figure out the last result
			// if you just query the executions table for those rows with status = $this->map_tc_status['passed']
			// that is not the way to determine last result
			$all_results = $this->latest_results;
			
			$tstart = microtime(true);
			//echo ($tstart) . '<br>';
			$this->executionsMap = $this->buildExecutionsMap($builds_to_query, $platforms_to_query, 'a', $keywordId,
			                                                 $owner, $startTime, $endTime, $executor,
			                                                 $search_notes_string, $linkExecutionBuild,
			                                                 $all_results);

			$tend = microtime(true);
			//echo ($tend) . '<br>';
            //echo ($tend - $tstart) . ' seconds<br>';
            // new dBug($this->executionsMap);
            
			$this->createMapOfLastResult($this->suiteStructure, $this->executionsMap, $lastResult);
			$this->aggregateKeywordResults = $this->tallyKeywordResults($this->mapOfLastResultByKeyword, $keywords_in_tplan);
			$this->aggregateOwnerResults = $this->tallyOwnerResults($this->mapOfLastResultByOwner, $arrOwners);
			
			// create data object which tallies totals for individual suites
			// child suites are NOT taken into account in this step
			$this->createMapOfSuiteSummary($this->mapOfLastResult);

			// create data object which tallies totals for suites taking
			// child suites into account
			$this->createAggregateMap($this->suiteStructure, $this->mapOfSuiteSummary);
   			$this->totalsForPlan = $this->createTotalsForPlan($this->suiteStructure);

			// must be done after totalsForPlan is performed because the total # of cases is needed
			// BUGID 3682
   			$arrBuilds = $tplan_mgr->get_builds($this->testPlanID, testplan::GET_ACTIVE_BUILD);
			
			// BUGID 3406, BUGID 1508 - we need the totals per build here, not for the whole plan anymore
			$this->totalsForBuilds = $this->createTotalsForBuilds($arrBuilds);
			$this->aggregateBuildResults = $this->tallyBuildResults($this->mapOfLastResultByBuild,
			                                                        $arrBuilds, $this->totalsForBuilds);


		} // end if block

			$tendOn = microtime(true);
			//echo ($tendOn) . '<br>';
            //echo ($tendOn - $tstartOn) . ' seconds<br>';
	} // end results constructor


  	/** 
  	 * get_export_file_types
  	 * @return array map of
  	 *       key: export file type code
  	 *       value: export file type verbose description
  	 */
	function get_export_file_types()
	{
    	return $this->export_file_types;
	}


  	/**
  	 * get_import_file_types
  	 * @return array map of
  	 *       key: export file type code
  	 *       value: export file type verbose description
  	 */
	function get_import_file_types()
	{
    	return $this->import_file_types;
  	}

	/**
	 * tallyKeywordResults($keywordResults, $keywordIdNamePairs)
	 * 
	 * @param array $keywordResults Array ([keyword id] => Array ( [test case id] => result ))
	 * 		example:
	 * 		<code>
	 * 		Array ( [128] => Array ( [14308] => p [14309] => n [14310] => n [14311] => f [14312] => n [14313] => n )
	 *			 [11] => Array ( [14309] => n [14310] => n [14311] => f [14312] => n [14313] => n ))
	 * 		</code>
	 *
	 * @return array map indexed using Keyword ID, where each element is a map with following structure:
	 *		<code>
	 *          [keyword_name] => K1
	 *          [total_tc] => 2
	 *          [percentage_completed] => 100.00
  	 *          [details] => Array
  	 *              (
  	 *                  [passed] => Array
  	 *                      (
  	 *                          [qty] => 1
  	 *                          [percentage] => 50.00
  	 *                      )
  	 *                  [failed] => Array
  	 *                      (
  	 *                          [qty] => 1
  	 *                          [percentage] => 50.00
  	 *                      )
  	 *                  [blocked] => Array
  	 *                      (
  	 *                          [qty] => 0
  	 *                          [percentage] => 0.00
  	 *                      )
  	 *                  [unknown] => Array
  	 *                      (
  	 *                          [qty] => 0
  	 *                          [percentage] => 0.00
  	 *                      )
  	 *                  [not_run] => Array
  	 *                      (
  	 *                          [qty] => 0
  	 *                          [percentage] => 0.00
  	 *                      )
  	 *              )
  	 *      )
	 *		</code>
	 * IMPORTANT: keys on details map dependends of configuration map $tlCfg->results['status_label']
	 */
	private function tallyKeywordResults($keywordResults, $keywordIdNamePairs)
	{                                   
		if ($keywordResults == null)
		{
			return null;
		}

		$rValue = null;
	  	foreach($keywordResults as $keywordId => $results)
	  	{
	    	$item_name = 'keyword_name';
      		$element = $this->tallyResults($results,sizeOf($results),$item_name);
      		
		  	$element[$item_name] = $keywordIdNamePairs[$keywordId];
			$rValue[$keywordId] = $element;
		}

		return $rValue;
	}


	/**
	 *
	 * @param array $ownerResults format:
	 * <code>Array ([owner id] => Array ( [test case id] => result ))</code>
	 *
	 * @return array map
	 * Example of output format :
	 * <code>
	 *          [tester_name] => johndoe
	 *          [total_tc] => 2
	 *          [percentage_completed] => 100.00
	 *          [details] => Array
	 *              (
	 *                  [passed] => Array
	 *                      (
	 *                          [qty] => 1
	 *                          [percentage] => 50.00
	 *                      )
	 *                  [failed] => Array
	 *                      (
	 *                          [qty] => 1
	 *                          [percentage] => 50.00
	 *                      )
	 *                  [blocked] => Array
	 *                      (
	 *                          [qty] => 0
	 *                          [percentage] => 0.00
	 *                      )
	 *                  [unknown] => Array
	 *                      (
	 *                          [qty] => 0
	 *                          [percentage] => 0.00
	 *                      )
	 *                  [not_run] => Array
	 *                      (
	 *                          [qty] => 0
	 *                          [percentage] => 0.00
	 *                      )
	 *              )
	 *      )
	 * </code>
	 *
	 * IMPORTANT: keys on details map dependends of configuration map $tlCfg->results['status_label']
	 */
	private function tallyOwnerResults($ownerResults, $ownerIdNamePairs)
	{                     
		if ($ownerResults == null)
		{
			return;
		}

		$no_tester_assigned = lang_get('unassigned');
		$rValue = null;
    	foreach($ownerResults as $ownerId => $results)
    	{
	    	$item_name = 'tester_name';
			// Calculates number of tc assignes to user
			$num = 0;
			foreach($results as $tc_id => $tc_info) 
			{
				foreach ($tc_info as $platform_id => $platform) 
				{
					$num++;
				}
			}
			$element = $this->tallyResults($results,$num,$item_name);
		  	$element[$item_name] = ($ownerId == -1) ? $no_tester_assigned : $ownerIdNamePairs[$ownerId];
			$rValue[$ownerId] = $element;
		}

		return $rValue;
	}


	/**
	 *
	 * 
	 * @internal revisions:
	 * 20110407 - BUGID 4363: General Test Plan Metrics - Overall Build Status -> 
	 *						  empty line for a build shown with no test cases assigned to user
	 * 20110329 - franciscom - BUGID 4333: General Test Plan Metrics % complete did not round
	 * 20110326 - franciscom - BUGID 4355
	 * 20100721 - asimon - BUGID 3406, 1508
	 */
	private function tallyBuildResults($lastExecByBuild, $arrBuilds, $execTotalsByBuild)
	{                     
		// if ($lastExecByBuild == null)
		// {
		// 	return null;
		// }
		
		$na_string = lang_get('not_aplicable');
		$rValue = null;
		foreach($arrBuilds as $buildId => $buildInfo)
		{
			$item_name='build_name';
			$results = array();
			$totalCases = $execTotalsByBuild[$buildId]['total'];
			if( isset($lastExecByBuild[$buildId]) && $totalCases > 0 )
			{
				$results = $lastExecByBuild[$buildId];
			}

			$element = $this->tallyResults($results,$totalCases,$item_name);
			if (!is_null($element))
			{
				$element[$item_name]=$buildInfo['name'];
				$rValue[$buildId] = $element;
				
				$not_run_percentage = $na_string;
				$rValue[$buildId]['percentage_completed'] = $na_string;
				if( $totalCases > 0 )
				{
					// BUGID 3406 - BUGID 1508 
					// here we need to insert the correct "not run" value now
					// and the percentages need to be re-calculated after that of course
					$not_run_count = $execTotalsByBuild[$buildId]['not_run'];
					$not_run_percentage = number_format($not_run_count / $totalCases * 100, 2);
					                      
					$rValue[$buildId]['details']['not_run']['qty'] = $not_run_count;
					$rValue[$buildId]['details']['not_run']['percentage'] = $not_run_percentage;
                	
					// BUGID 4333: General Test Plan Metrics % complete did not round
					$rValue[$buildId]['percentage_completed'] = number_format( 100 - $not_run_percentage);
				}
			}
		} // end  foreach
		
		unset($element);       
		unset($results);
		return $rValue;
	}

	/**
	 * Aggregates results based on priority.
	 * @see aggregateOwnerResults()
	 * @param $prioResults array
	 * <code> Array ([priority] => Array ( [tc_id] => Array( [platform_id] => result ))) </code>
	 *
	 * @return array map
	 * <code>Array ( [priority] => Array ( total, passed, failed, blocked, not run))</code>
	 */
	private function tallyPriorityResults($prioResults)
	{                     
		if ($prioResults == null)
		{
			return null;
		}

		$urgencyCfg = config_get('urgency');
		$rValue = null;
		foreach($prioResults as $prio => $results)
		{
			$item_name='priority';
			// Calculates number of tc in this prio
			$num = 0;
			foreach($results as $tc_id => $tc_info) {
				foreach ($tc_info as $platform_id => $platform) {
					$num++;
				}
			}
			$element=$this->tallyResults($results,$num,$item_name);
			if (!is_null ($element))
			{
				$element[$item_name]=lang_get($urgencyCfg["code_label"][$prio]);
				$rValue[$prio] = $element;
			}
		} // end  foreach
		// Sort returning array from high to low priority
		krsort($rValue);

		unset($element);
		unset($results);

		return $rValue;
	}

	/**
	 * Aggregates results based on platform.
	 * @see aggregateOwnerResults()
	 * @param $prioResults array
	 * <code> Array ([platform] => Array ( [tc_id] => Array( [platform_id] => result ))) </code>
	 *
	 * @return array map
	 * <code>Array ( [priority] => Array ( total, passed, failed, blocked, not run))</code>
	 */
	// private function tallyPlatformResults($platformResults, $platforms)
	// {                     
	// 	echo __FUNCTION__;
	// 	if ($platformResults == null)
	// 	{
	// 		return null;
	// 	}
    // 
	// 	$rValue = null;
	// 	foreach($platformResults as $platform_id => $results)
	// 	{
	// 		$item_name='platform';
	// 		// Calculates number of tc in this platform.
	// 		$num = 0;
	// 		foreach($results as $tc_id => $tc_info) {
	// 			foreach ($tc_info as $platform) {
	// 				$num++;
	// 			}
	// 		}
	// 		$element=$this->tallyResults($results,$num,$item_name);
	// 		if (!is_null ($element))
	// 		{
	// 			$element[$item_name]=$platforms[$platform_id];
	// 			$rValue[$platform_id] = $element;
	// 		}
	// 	} // end  foreach
    // 
	// 	unset($element);
	// 	unset($results);
    // 
	// 	return $rValue;
	// }

	/**
	 * @return array total / pass / fail / blocked / not run results for each keyword id
	 */
	public function getAggregateKeywordResults() {
		return $this->aggregateKeywordResults;
	}

	/**
	 * @return array total / pass / fail / blocked / not run results for each owner id
	 * unassigned test cases show up under owner id = -1
	 */
	public function getAggregateOwnerResults() {
		return $this->aggregateOwnerResults;
	}

	/**
	 * @return array total / pass / fail / blocked / not run results for each build id
	 */
	public function getAggregateBuildResults() {
		return $this->aggregateBuildResults;
	}

	/**
	 * Aggregates and returns results grouped by priority
	 * @return array total / pass / fail / blocked / not run results for each build id
	 */
	public function getAggregatePriorityResults()
	{
		if ($this->aggregatePrioResults == null) {
			$this->aggregatePrioResults = $this->tallyPriorityResults($this->mapOfLastResultByPrio);
		}
		return $this->aggregatePrioResults;
	}

	/**
	 * Aggregates and returns results grouped by platform
	 * @return array total / pass / fail / blocked / not run results for each build id
	 */
	// public function getAggregatePlatformResults()
	// {
	// 	if ($this->aggregatePlatformResults == null) 
	// 	{
	// 		$platforms = $this->tplanMgr->getPlatforms($this->testPlanID,'map');
	// 		$this->aggregatePlatformResults = $this->tallyPlatformResults($this->mapOfLastResultByPlatform, $platforms);
	// 	}
	// 	return $this->aggregatePlatformResults;
	// }

	/**
	 * @TODO rename this method to getExecutionsMap()
	 * (resultsTC.php is 1 file (may not be only file) that references this method)
	 * @return array
	 */
	public function getSuiteList(){
		return $this->executionsMap;
	}

	/**
	 * @return array array which describes suite hierachy
	 */
	public function getSuiteStructure(){
		return $this->suiteStructure;
	}

	public function getLinkedTCVersions(){
	    return $this->linked_tcversions;
	}

	/**
	 * @return array
	 */
	public function getMapOfSuiteSummary(){
		return $this->mapOfSuiteSummary;
	}

	/**
	 * @return array
	 */
	public function getMapOfLastResult() {
		return $this->mapOfLastResult;
	}

	/**
	* @return array
	*/
	public function getAggregateMap(){
		return $this->mapOfAggregate;
	}

	/**
	 * @return array
	 */
	public function getTotalsForPlan()
	{
		return $this->totalsForPlan;
	}

	/**
	 * BUGID 3406, 1508
	 * @author Andreas Simon
	 * @return array
	 */
	public function getTotalsForBuild()
	{
		return $this->totalsForBuilds;
	}
	
	/**
	 * @return array single-dimension array with pattern level, suite name, suite id
	 */
	public function getFlatArray(){
		return $this->flatArray;
	}

	/**
	 * @param array $results map: key tc_id  => value status_code
	 * @return mixed array or null
	 */
	private function tallyResults($results,$totalCases,$item_name=null)
	{
		
		static $na_string;
		static $code_verbose;
		if( is_null($na_string) ) 
		{
			$na_string = lang_get('not_aplicable');
			$code_verbose = array_flip($this->tc_status_for_statistics);
		}

		// initalization area
		// Because I want to have this as first key on map, just for easy reading.
		// Value will be setted by caller.
		if( !is_null($item_name) )
		{
			$element[$item_name]='';
		}
		$element['total_tc'] = $totalCases;
		$element['percentage_completed'] = 0;
		$element['details']=array();

		foreach($this->tc_status_for_statistics as $status_verbose => $status_code)
		{
			$total[$status_verbose]=0;
			$percentage[$status_verbose]=0;

			$element['details'][$status_verbose]['qty'] = 0;
			$element['details'][$status_verbose]['percentage'] = 0;
		}

		if ($results == null)
		{
			// 20110326 - return a clean structure with all members
			return $element;  // >>-----> Brute force bye bye. 
		}
		
		// Normal processing
		$dummy=0;
		foreach($results as $tc_id => $tc_info)
		{
			foreach((array)$tc_info as $platform_id => $result_code) 
			{
				// Check if user has configured and add not_run.
				// Standard TestLink behavior:
				// 1. do not show not_run as a choice on execute pages
				// 2. do not save on DB not_run executions
				//
				if( $code_verbose[$result_code] !='' && $code_verbose[$result_code] != 'not_run')
				{
					$total[$code_verbose[$result_code]]++;
					$dummy++;
				}
			}
		}
		
		// not_run is an special status
		$total['not_run'] = abs($totalCases - $dummy);
		$percentage['not_run'] = ($totalCases != 0) ? 
		                         number_format((($total['not_run']) / $totalCases) * 100,2) : $na_string;
		
		$percentCompleted = 0;
		if ($totalCases != 0)
		{
			$percentCompleted = (($totalCases - $total['not_run']) / $totalCases) * 100;
			
			foreach($this->tc_status_for_statistics as $status_verbose => $status_code)
			{
				$percentage[$status_verbose]=(($total[$status_verbose]) / $totalCases) * 100;
				$percentage[$status_verbose]=number_format($percentage[$status_verbose],2);
			}
		}
		$percentCompleted = number_format($percentCompleted,2);
		
		$element['total_tc']=$totalCases;
		$element['percentage_completed']=$percentCompleted;
		
		$element['details']=array();
		foreach($total as $status_verbose => $counter)
		{
			$element['details'][$status_verbose]['qty']=$counter;
			$element['details'][$status_verbose]['percentage']=$percentage[$status_verbose];
		}
		
		return $element;
	} // end function




	function tallyResults2($results,$totalCases)
	{
		if ($results == null)
		{
			return null;
		}
		
		// not_run is an special status
		$total['not_run'] = abs($totalCases - $dummy);
		$percentage['not_run']=number_format((($total['not_run']) / $totalCases) * 100,2);
		
		$keySet = array_keys($results);
		foreach($keySet as $keyID)
		{
			$results[$keyID]['percentage_completed'] = 0;
			$totalCases = $results[$keyID]['total_tc'];
			$target = &$results[$keyID]['details']; 
			if ($totalCases != 0)
			{
				$results[$keyID]['percentage_completed'] = 
						number_format((($totalCases - $target['not_run']['qty']) / $totalCases) * 100,2);
						
			}
			foreach($target as $status_verbose => $qty)
			{
				$target[$status_verbose]['percentage']=(($target[$status_verbose]['qty']) / $totalCases) * 100;
				$target[$status_verbose]['percentage']=number_format($target[$status_verbose]['percentage'],2);
			}
		}
		return $results;
	} // end function



	/**
	 * Gets the user for a specific feature_id
	 * @author amitkhullar
	 * 
	 * @return integer User identifier
	 */
	function getUserForFeature($feature_id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$sql = "/* $debugMsg */ ";
		$sql .= " SELECT user_id FROM {$this->tables['user_assignments']} " .
		        " user_assignments WHERE feature_id = "  . $feature_id ;
		$owner_row =  $this->db->fetchFirstRow($sql);
		$owner_id = $owner_row['user_id'] == '' ? -1 : $owner_row['user_id'];
		return $owner_id;
	}	// end function

	
	/**
	 * Fill $this->mapOfLastResult - which provides information on the last result
	 * for each test case.
	 *
	 * $this->mapOfLastResult is an array of suite ids
	 * each suiteId      -> arrayOfTCresults, arrayOfSummaryResults
	 * arrayOfTCresults  ->  array of rows containing (buildIdLastExecuted, result) where row id = testcaseId
	 *
	 * currently it does not account for user expliting marking a case "not run".
	 *
	 * @author kevinlevy
	 *
	 * @return void
	 *
	 * @internal Revisions:
	 * 
	 *      20070919 - franciscom - interface changes
	 *      20070825 - franciscom - added node_order
	 */
	private function addLastResultToMap($suiteId, $suiteName, $exec,$lastResultToTrack)
	{
		$testcase_id = $exec['testcaseID'];
		$platform_id = $exec['platform_id'];
		$external_id = $exec['external_id'];
		$buildNumber = $exec['build_id'];
		$result = $exec['status'];
		$tcversion_id = $exec['tcversion_id'];
		$execution_ts = $exec['execution_ts'];
		$notes = $exec['notes'];
		$executions_id = $exec['executions_id'];
		$name = $exec['name'];
		$tester_id = $exec['tester_id'];
		$feature_id = $exec['feature_id'];
		$assigner_id = $exec['assigner_id'];
		
		// Maybe this check is not more needed, because $exec has been changed using same logic
		if( isset($exec['tcversion_number']) && !is_null($exec['tcversion_number']) )
		{
			$version=$exec['tcversion_number'];
		}
		else
		{
			$version=$exec['version'];
		}
		
		// new dBug($testcase_id);
		// new dBug($platform_id);
		
		//echo '$exec <br>';		new dBug($exec);
		if ($buildNumber)
		{
			$this->mapOfLastResultByBuild[$buildNumber][$testcase_id][$platform_id] = $result;
		}
		
		
		$owner_id = $this->getUserForFeature($feature_id);
		$associatedKeywords = null;
		if ($this->keywordData != null && array_key_exists($testcase_id, $this->keywordData))
		{
			$associatedKeywords = $this->keywordData[$testcase_id];
		}
		
		$doInsert = true;
		// handle case where suite has already been added to mapOfLastResult
		if ($this->mapOfLastResult && array_key_exists($suiteId, $this->mapOfLastResult))
		{
			$doInsert = false;
			// handle case where both suite and test case have been added to elmapOfLastResult
			if (array_key_exists($testcase_id, $this->mapOfLastResult[$suiteId]))
			{
				// handle case where all of suite, test case and platform have been added to mapOfLastResult
				if (array_key_exists($platform_id, $this->mapOfLastResult[$suiteId][$testcase_id]))
				{
					$buildInMap = $this->mapOfCaseResults[$testcase_id]['buildNumber'];
					$execIDInMap = $this->mapOfCaseResults[$testcase_id]['execID'];
					if (($buildInMap < $buildNumber) || ($buildInMap == $buildNumber && $execIDInMap < $executions_id))
					{
						$doInsert = true;
					}	
			    }
				else 
				{
					$doInsert = true;
				}
			}
			else 
			{
				$doInsert = true;
			}
		}
		
		if ($doInsert)
		{
			$this->mapOfLastResultByOwner[$owner_id][$testcase_id][$platform_id] = $result;
			$prio = $this->getPriority($this->testPlanID, $tcversion_id);
			
			$this->mapOfLastResultByPrio[$prio][$testcase_id][$platform_id] = $result;

			// 20090804 - Eloff
			// This structure might look weird, but is needed to reuse aggregation code
			// $this->mapOfLastResultByPlatform[$platform_id][$testcase_id][$platform_id] = $result;

			$qta_loops=sizeof($associatedKeywords);
			for ($i = 0;$i <$qta_loops ; $i++)
			{
				$this->mapOfLastResultByKeyword[$associatedKeywords[$i]][$testcase_id] = $result;
			}
			
			$this->mapOfCaseResults[$testcase_id]['buildNumber'] = $buildNumber;
			$this->mapOfCaseResults[$testcase_id]['execID'] = $executions_id;
			
			$this->mapOfLastResult[$suiteId][$testcase_id][$platform_id] = array("buildIdLastExecuted" => $buildNumber,
				"result" => $result,
				"tcversion_id" => $tcversion_id,
				"platform_id" => $platform_id,
				"external_id" => $external_id,
				"version" => $version,
				"execution_ts" => $execution_ts,
				"notes" => $notes,
				"suiteName" => $suiteName,
				"executions_id" => $executions_id,
				"name" => $name,
				"tester_id" => $tester_id);
		}
		
		// echo '$this->mapOfLastResultByBuild'; new dBug($this->mapOfLastResultByBuild);
		
		
	} // end function

	/**
	 *  Creates statistics on each suite
	 *  @return void
	 */
	private function createMapOfSuiteSummary(&$mapOfLastResult)
	{
		if ($mapOfLastResult)
		{
			$code_verbose=array_flip($this->tc_status_for_statistics);
			$code_verbose[$this->map_tc_status['not_run']]='not_run';
			
			foreach($mapOfLastResult as $suiteId => $tcaseResults)
			{
				foreach($this->tc_status_for_statistics as $status_verbose => $status_code)
				{
					$total[$status_verbose]=0;
				}
				$total['not_run']=0;
				$total['total'] = 0;
				foreach($tcaseResults as $testcase_id => $platformResults) {
				
					foreach($platformResults as $platform_id => $value)
				{
						$currentResult =  $value['result'];
					$status_verbose=$code_verbose[$currentResult];
					$total[$status_verbose]++;
						$total['total']++;
				}
				
				$this->mapOfSuiteSummary[$suiteId] =  $total;
			}
			}
		} // end if
	} // end function
	
	/**
	 *  tallies total cases, total pass/fail/blocked/not run for each suite
	 *  it takes into account sub-suite tallies within that suite
	 *  ex : if suite A contains suites A.1 and A.2, this function
	 *  adds up A.1, A.2, and test cases within A
	 */
	private function createAggregateMap(&$suiteStructure, &$mapOfSuiteSummary)
	{
		$loop_qty=count($suiteStructure);
		for ($idx = 0; $idx < $loop_qty; $idx++ )
		{
			$suiteId = 0;
			$tpos=$idx % self::ITEM_PATTERN_POS;
			if ($tpos ==  self::ID_POS)
			{
				// register a suite that we will use to increment aggregate results for
				$suiteId = $suiteStructure[$idx];
				array_push($this->aggSuiteList, $suiteId);
				
				if ($mapOfSuiteSummary && array_key_exists($suiteId, $mapOfSuiteSummary))
				{
					$summaryArray = $mapOfSuiteSummary[$suiteId];
					$this->addResultsToAggregate($summaryArray);
				}
			}
			elseif ($tpos ==  self::ARRAY_POS)
			{
				if (is_array($suiteStructure[$idx]))
				{
					// go get child totals
					$newSuiteStructure = $suiteStructure[$idx];
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
	private function createTotalsForPlan($suiteStructure)
	{
		$counters['total']=0;
		$counters['not_run']=0;
		foreach($this->tc_status_for_statistics as $status_verbose => $status_code)
		{
			$counters[$status_verbose]=0;
		}
		
		$loop_qty=count($suiteStructure);
		for ($idx = 0 ; $idx < $loop_qty ; $idx++)
		{
			if (($idx % self::ITEM_PATTERN_POS) ==  self::ID_POS)
			{
				
				$suiteId = $suiteStructure[$idx];
				$resultsForSuite = isset($this->mapOfAggregate[$suiteId]) ? $this->mapOfAggregate[$suiteId] : null;
				if( !is_null($resultsForSuite) )
				{
					foreach($counters as $code_verbose => $value)
					{
						$counters[$code_verbose]+=$resultsForSuite[$code_verbose];
					}
				}
			} // end if
		}
		
		return $counters;
	} // end function

	/**
	 * Important Notice:
	 * Totals are created using ONLY test cases that HAVE TESTER ASSIGNED.
	 *
	 * Example:
	 * Create Test Plan with 6 test cases
	 * Create Build B1 (DBID=3), assign to testers just 4.
	 * Create Build B2 (DBID=5), assign to testers just 2.
	 * DO NOT EXECUTE test cases in ANY BUILD.	
	 *
	 * output will be
	 * counter[3] = array('total' => 4,'not_run' => 4,'passed' => 0,'failed' => 0,'blocked' => 0)
	 * counter[5] = array('total' => 2,'not_run' => 2,'passed' => 0,'failed' => 0,'blocked' => 0)
	 *
	 *
	 * IMPORTANT QUESTION: 
	 * How this have to work when there are platforms defined ?
	 *
	 *
	 * For BUGID 3406, 1508: New function to get counts on build level instead of testplan level
	 * 
	 * @author Andreas Simon
	 * @param array $arrBuilds Array with information about the builds for this testplan.
	 * @return array $counters Array similar to $this->totalsForPlan, but with correct numbers per build 
	 *
	 * @internal revisions
	 * 20110407 - franciscom - BUGID 4363
	 */
	private function createTotalsForBuilds($arrBuilds) 
	{
		$counters = array();
		$buildSet = array_keys($arrBuilds);
		$qty['exec'] = $this->tplanMgr->assignment_mgr->getExecAssignmentsCountByBuild($buildSet);
		$qty['notRun'] = $this->tplanMgr->assignment_mgr->getNotRunAssignmentsCountByBuild($buildSet);

		$allZero = true;
		foreach($qty as $key => $value)
		{
			$fZero[$key] = is_null($value);
			$allZero = $allZero && $fZero[$key];
		}

		foreach($buildSet as $build_id)
		{
			$counters[$build_id] = $this->totalsForPlan;
			if($allZero)
			{
				foreach($counters[$build_id] as &$cc)
				{
					$cc = 0;
				}
			}
			$counters[$build_id]['total'] = isset($qty['exec'][$build_id]['qty']) ? $qty['exec'][$build_id]['qty'] : 0;
			$counters[$build_id]['not_run'] = isset($qty['notRun'][$build_id]['qty']) ? $qty['notRun'][$build_id]['qty'] : 0;
		}
		return $counters;
	} // end of method
	
	/**
	 * @return array map with following keys:
	 *		'total','not_run', other keys depends of status configured by user
	 *		If users leave untouched TestLink Factory configuration:
	 *		'total','not_run','passed','failed','blocked'
	 */
	private function addResultsToAggregate($results)
	{
		$loop_qty=count($this->aggSuiteList);
		
		
		for ($idx = 0 ; $idx < $loop_qty ; $idx++)
		{
			$suiteId = $this->aggSuiteList[$idx];
			$currentSuite = null;
			if ($this->mapOfAggregate && array_key_exists($suiteId, $this->mapOfAggregate))
			{
				$currentSuite = $this->mapOfAggregate[$suiteId];
			}
			else
			{
				$currentSuite['total'] = 0;
				foreach($this->tc_status_for_statistics as $status_verbose => $status_code)
				{
					$currentSuite[$status_verbose]=0;
				}
				$currentSuite['not_run'] = 0;
			}
			
			foreach($currentSuite as $key => $value)
			{
				$currentSuite[$key] += $results[$key];
			}
			
			$this->mapOfAggregate[$suiteId] = $currentSuite;
		} 
	} // end function


	/**
	 * @return array map testcase_id, keyword_id
	 */
	private function getKeywordData($keywordsInPlan) 
	{
		$CUMULATIVE=1;
		
		// limit the sql query to just those keys in this test plan
		if ($keywordsInPlan == null) {
			return null;
		}
		
		$keys = implode(',',$keywordsInPlan);
		$sql = " SELECT testcase_id, keyword_id" .
			   " FROM {$this->tables['testcase_keywords']}" .
			   " WHERE keyword_id IN ($keys)";
		
		$returnMap =  $this->db->fetchColumnsIntoMap($sql,'testcase_id', 'keyword_id',$CUMULATIVE);
		
		return $returnMap;
	} // end function


	/**
	 *
	 */
	private function createMapOfLastResult(&$suiteStructure, &$executionsMap, $lastResult)
	{
		$suiteName = null;
		$totalSuites = count($suiteStructure);
		for ($i = 0; $i < $totalSuites; $i++)
		{
			if (($i % self::ITEM_PATTERN_POS) == self::NAME_POS) 
			{
				$suiteName = $suiteStructure[$i];
			}
			elseif (($i % self::ITEM_PATTERN_POS) ==  self::ID_POS) 
			{
				$suiteId = $suiteStructure[$i];
				if( isset($executionsMap[$suiteId]) )
				{
					$totalCases = count($executionsMap[$suiteId]);
					for ($j = 0 ; $j < $totalCases; $j++) 
					{
						$cexec = $executionsMap[$suiteId][$j];
						$this->addLastResultToMap($suiteId, $suiteName,$cexec,$lastResult);
					}
				}
			}
			elseif (($i % self::ITEM_PATTERN_POS) ==  self::ARRAY_POS)
			{
				if (is_array($suiteStructure[$i]))
				{
					$childSuite = $suiteStructure[$i];
					$summaryTreeForChild = $this->createMapOfLastResult($childSuite, $executionsMap, $lastResult);
				}
			}   // end elseif
		} // end for loop
	} // end function

	/**
	 * Builds $executionsMap map. 
	 * $executionsMap contains all execution information for suites and test cases.
	 *
	 *
	 * $executionsMap = [testsuite_id_1_array, test_suite_id_2_array, ...]
	 *
	 * testsuite_id_1_array = []
	 * all test cases are included, even cases that have not been executed yet
	 *
	 * @internal Revisions:
	 * 
	 *  20100518 - franciscom - BUGID 3474: Link to test case in Query Metrics Report is broken if using platforms
	 *	20090302 - amitkhullar - added a parameter $all_results to get latest results (0) only otherwise 
	 * 				all results are displayed in reports (1). 
	 *	20080928 - franciscom - seems that adding a control to avoid call to buildBugString()
	 *				reduces dramatically memory usage();
	 *				IMPORTANT:
	 *				we need to refactor this method, because if we had to call buildBugsString()
	 *				probably will crash again.
	 *				We need to think about function that got bug for all test cases, with one
	 *				call, may be is the solution.
	 *
	 *	20070916 - franciscom - removed session coupling
	 *				added node_order
	 */
	private function buildExecutionsMap($builds_to_query, $platforms_to_query, $lastResult = 'a', $keyword = 0,
	                                    $owner = null, $startTime, $endTime, $executor,
	                                    $search_notes_string, $executeLinkBuild, $all_results = 1)
	{
		$searchBugs= config_get('bugInterfaceOn');
		
		// first make sure we initialize the executionsMap
		// otherwise duplicate executions will be added to suites
		$executionsMap = null;
		
		// map for adding SuiteName directly to every TC
		$allSuites = $this->getAllSuites();
		
		// for execution link
		$canExecute = has_rights($this->db,"tp_execute");
		
		// Build Sql additional filters
		$sqlFilters='';
		if( !is_null($startTime) )
		{
			$sqlFilters .= " AND execution_ts > '{$startTime}'";
		}
		
		if( !is_null($endTime) )
		{
			$sqlFilters .= " AND execution_ts < '{$endTime}' ";
		}
		
		if (($lastResult == $this->map_tc_status['passed']) || ($lastResult == $this->map_tc_status['failed']) ||
				($lastResult == $this->map_tc_status['blocked'])){
			$sqlFilters .= " AND status = '" . $lastResult . "' ";
		}
		if (($builds_to_query != -1) && ($builds_to_query != 'a')) {
			$sqlFilters .= " AND build_id IN ($builds_to_query) ";
		}
        // BUGID 2023
		if (!is_null($executor) && $executor != '' && $executor != TL_USER_ANYBODY) 
		{
			$sqlFilters .= " AND tester_id = $executor ";
		}
		if ($search_notes_string != null) {
			$sqlFilters .= " AND notes LIKE '%" . $search_notes_string ."%' ";
		}
		
		$suffixLink = htmlspecialchars($this->testCasePrefix . $this->testCaseCfg->glue_character);
		
		$queryCounter = 0;
		$usersForFeatures = $this->getUsersForfeatures($this->testPlanID);
		$has_no_platform = (sizeof($platforms_to_query) == 0);
		foreach($this->linked_tcversions as $tcase_info)
		{
			foreach($tcase_info as $index => $info) 
			{
				// If no platforms added yet, size will be 0.
				// In this case, check if platform_id is 0; if so,
				// it means platforms haven't been added yet,
				// so include all executions.
				if ($has_no_platform) 
				{
					if ($info['platform_id'] != 0) 
					{
						continue;
					}
				} else if ($platforms_to_query[0] != ALL_PLATFORMS &&
							array_search($info['platform_id'], $platforms_to_query) === false) {
					continue;
				}
				$testcaseID = $info['tc_id'];
				$executionExists = true;
				$currentSuite = null;
				if (!$executionsMap || !(array_key_exists($info['testsuite_id'], $executionsMap)))
				{
					$currentSuite = array();
				}
				else 
				{
					$currentSuite = $executionsMap[$info['testsuite_id']];
				}
				
				$version = $info['version'];
				if(isset($info['tcversion_number']) && !is_null($info['tcversion_number']))
				{
					$version = $info['tcversion_number'];
				}
			                                        ;
				$owner_id = $usersForFeatures[$info['feature_id']];

				// BUGID - 2374: Show Assigned User in the Not Run Test Cases Report 
				$infoToSave = array('testcaseID' => $testcaseID,
									'platform_id' => $info['platform_id'],
									'testcasePrefix' => $this->testCasePrefix . $this->testCaseCfg->glue_character,
									'external_id' => $info['external_id'],
									'tcversion_id' => $info['tcversion_id'],
									'version' => $version,
									'build_id' => '',
									'tester_id' => $owner_id,
									'execution_ts' => '',
									'status' => $this->map_tc_status['not_run'],
									'executions_id' => '',
									'notes' => '',
									'bugString' => '',
									'name' => $info['name'],
									'suiteName' => $allSuites[$info['testsuite_id']],
									'assigner_id' => $info['assigner_id'],
									'feature_id' => $info['feature_id'],
									'execute_link' => '');
							
				if ($info['tcversion_id'] != $info['executed'])
				{
					$executionExists = false;
					if (($lastResult == 'a') || ($lastResult == $this->map_tc_status['not_run'])) 
					{
						// Initialize information on testcaseID to be "not run"
						// echo 'NOTRUN - DEBUG- Added Not Run info <br>';
						array_push($currentSuite, $infoToSave);
					}
				}
			
				if ($executionExists) 
				{
					// TO-DO - this is where we can include the searching of results
					// over multiple test plans - by modifying this select statement slightly
					// to include multiple test plan ids
					$sql = "SELECT * FROM {$this->tables['executions']} " .
						   "WHERE tcversion_id = " . $info['executed'] . " AND testplan_id = $this->testPlanID ";
					if( isset($platforms_to_query[0]) && $platforms_to_query[0] != ALL_PLATFORMS) 
					{
						$sql .= "AND platform_id = " . $info['platform_id'];
					}
					
					$sql .= $sqlFilters;
					
					// mht: fix 966
					// mike_h - 20070806 - when ordering executions by the timestamp, 
					// the results are represented correctly in the report "Test Report".
					// amitkhullar - BUGID 2156 - added option to get latest/all results in Query metrics report.				
					
					if ($all_results == 0)
					{
						$sql .= " ORDER BY execution_ts DESC limit 1";
					}
					else 				
					{
						$sql .= " ORDER BY execution_ts ASC ";
						
					}
					  //echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";

					$execQuery = $this->db->fetchArrayRowsIntoMap($sql,'id');
					if( $queryCounter == 0)
					{
					  //echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";
					}
					$queryCounter++;
					
					if ($execQuery)
					{
						if ($lastResult != $this->map_tc_status['not_run']) 
						{
							foreach($execQuery as $executions_id => $execInfo)
							{
								$exec_row = $execInfo[0];
								
								$infoToSave['version'] = $exec_row['tcversion_number'];
								$infoToSave['build_id'] = $exec_row['build_id'];
								$infoToSave['platform_id'] = $exec_row['platform_id'];
								$infoToSave['tester_id'] = $exec_row['tester_id'];
								$infoToSave['status'] = $exec_row['status'];
								$infoToSave['notes'] = $exec_row['notes'];
								$infoToSave['executions_id'] = $executions_id;
								//@todo: Refactor for this code - BUGID 2242 
								$infoToSave['bugString'] = $searchBugs ? $this->buildBugString($this->db, $executions_id) : '';
								
								$dummy = null;
								$infoToSave['execution_ts'] = localize_dateOrTimeStamp(null, $dummy,'timestamp_format',
									                                                   $exec_row['execution_ts']);
								//-amitkhullar - BugID:2267
								$prefixLink = '<a href="lib/execute/execSetResults.php?level=testcase&build_id=' . $infoToSave['build_id'];
								
								// 20100518 - franciscom - BUGID 3474: Link to test case in Query Metrics Report is broken if using platforms
								$prefixLink .= '&platform_id=' . $exec_row['platform_id'];
								$infoToSave['execute_link'] = $prefixLink . "&id={$testcaseID}&version_id=" . $info['tcversion_id'] . 
															  "&tplan_id=" . $this->testPlanID . '">' .  
									                          $suffixLink . $info['external_id'] . ":&nbsp;<b>" .  
									                          htmlspecialchars($info['name']). "</b></a>";
								
								array_push($currentSuite, $infoToSave);
							} // end foreach
						}  
					} // end if($execQuery)
            	
					elseif (($lastResult == 'a') || ($lastResult == $this->map_tc_status['not_run'])) 
					{
						// HANDLE scenario where execution does not exist
						array_push($currentSuite, $infoToSave);
					}
				} // end if($executionExists)
			
				$executionsMap[$info['testsuite_id']] = $currentSuite;
			} // foreach platform
		} // end foreach
		
		unset($infoToSave);
		
		//echo 'Query Counter:' . $queryCounter . '<br>';
		return $executionsMap;
	} // end function

	/**
	 * @TODO - figure out what file to include so i don't have
	 * to redefine this
	 * builds bug information for execution id
	 * written by Andreas, being implemented again by KL
	 */
	function buildBugString(&$db,$execID) 
	{
		$bugString = null;
		if (!$execID)
		{
			return $bugString;
		}
		
		$bug_interface = config_get("bugInterface");
		$bugs = get_bugs_for_exec($db,$bug_interface,$execID);
		if ($bugs) 
		{
			foreach($bugs as $bugID => $bugInfo) 
			{
				$bugString .= $bugInfo['link_to_bts']."<br />";
			}
		}
		
		return $bugString;
	} // end function

	/**
	 * @return array map of suite id to suite name
	 *
	 * @internal Revisions:
	 * 20090727 - Eloff - changed structured of returned array to
	 *            array($suiteId => $suiteName, ...)
	 */
	public function getAllSuites() 
	{
		$returnList = null;
		$name = null;
		$suiteId = null;
		$loop2do = sizeof($this->flatArray);
		for ($i = 0 ; $i < $loop2do; $i++) {
			if (($i % self::ITEM_PATTERN_FLAT_POS) == self::NAME_FLAT_POS) {
				$name = $this->flatArray[$i];
			}
			elseif (($i % self::ITEM_PATTERN_FLAT_POS) == self::SUITE_ID_FLAT_POS) {
				$suiteId = $this->flatArray[$i];
				$returnList[$suiteId] = $name;
			}
		}
		return $returnList;
	} // end function

	/**
	 * return map of suite id to suite name pairs of top level suites
	 * iterates over top level suites and adds up totals using data from mapOfAggregate
	 */
	public function getTopLevelSuites()
	{
		$returnList = null;
		$name = null;
		$suiteId = null;
		for ($i = 0 ; $i < count($this->suiteStructure) ; $i++) 
		{
			if (($i % self::ITEM_PATTERN_POS) == self::NAME_POS) 
			{
				$name = $this->suiteStructure[$i];
			}
			else if (($i % self::ITEM_PATTERN_POS) ==  self::ID_POS) 
			{
				$suiteId = $this->suiteStructure[$i];
				$returnList[$i] = array('name' => $name, 'id' => $suiteId);
			}
		} // end for loop
		
		return $returnList;
	} // end function getTopLevelSuites


	/**
	 * initializes linked_tcversions object
	 *
	 * Builds a multi-dimentional array which represents the tree structure.
	 * Specifically an array is returned in the following pattern
	 * every 3rd index is null if suite does not contain other suites
	 * or array of same pattern if it does contain suites
	 *
	 * KL took this code from menuTree.inc.php.
	 * Builds both $this->flatArray and $this->suiteStructure
	 *
	 * @param resource &$db reference to database handler
	 * 
	 * @return array structured map
	 * 
	 *  suite[0] = suite id
	 *	suite[1] = suite name
	 *	suite[2] = array() of child suites or null
	 *	suite[3] = suite id
	 *	suite[4] = suite name
	 *	suite[5] = array() of child suites or null
	 *
	 */
	private function generateExecTree(&$db,$keyword_id = 0, $owner = null) 
	{
		$RECURSIVE_MODE = true;
		$tplan_mgr = $this->tplanMgr;
		$tproject_mgr = new testproject($this->db);
		$tree_manager = $tplan_mgr->tree_manager;
		$hash_descr_id = $tree_manager->get_available_node_types();
	
	    $test_spec = $tproject_mgr->get_subtree($this->tprojectID,$RECURSIVE_MODE);

		$filters = array('keyword_id' => $keyword_id, 'assigned_to' => $owner);
	    // $options = array('output' => 'mapOfArray'); // needed to have platform info
	    $options = array('output' => 'mapOfMap'); // needed to have platform info
		$tplan_tcversions = $tplan_mgr->get_linked_tcversions($this->testPlanID,$filters,$options);
		
		
		
		// $this->linked_tcversions = &$tp_tcs;
		if (is_null($tplan_tcversions)) {
			$tplan_tcversions = array();
		}
		$test_spec['name'] = $this->tplanName;
		$test_spec['id'] = $this->tprojectID;
		$test_spec['node_type_id'] = $hash_descr_id['testproject'];
		$suiteStructure = null;
		$tck_map = null;
		if($keyword_id)
		{
			$tck_map = $tproject_mgr->get_keywords_tcases($this->tprojectID,$keyword_id);
		}
		$hash_id_descr = array_flip($hash_descr_id);
		
		$testcase_count = $this->removeEmptySuites($test_spec,$hash_id_descr,$tck_map,$tplan_tcversions,$owner);
		
		// $mem[]=self::memory_status(__CLASS__,__FILE__,__FUNCTION__,__LINE__);
	   	// $xmem=current($mem);
	    // echo "<pre>debug 20080928 - \ - " . __FUNCTION__ . " --- "; print_r($xmem['msg']); echo "</pre>";  
		// ob_flush();flush();
			                              
		$suiteStructure = $this->processExecTreeNode(1,$test_spec,$hash_id_descr);
		
		return array($suiteStructure,$tplan_tcversions);
	}
	
	/**
	 * parent_suite_name is used to construct the full hierachy name of the suite
	 * ex: "A->A.A->A.A.A"
	 */
	private function processExecTreeNode($level,&$node,$hash_id_descr,$parent_suite_name = '')
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
				if (($parentId == $this->tprojectID) && ($this->suitesSelected != 'all')) {
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
					/* flat array logic */
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
					/* end flat array logic */
					/* suiteStructure logic */
					$currentNode[$currentNodeIndex] = $hierarchySuiteName;
					$currentNodeIndex++;
					$currentNode[$currentNodeIndex] = $id;
					$currentNodeIndex++;
					$currentNode[$currentNodeIndex] = $this->processExecTreeNode($level+1,$current,$hash_id_descr,$hierarchySuiteName);
					$currentNodeIndex++;
					/* end suiteStructure logic */
				}
			} // end for
		}
		
		return $currentNode;
	}

	/**
	 * 
	 * @return string Link of Test ID + Title
	 */
	function getTCLink($rights, $tcID, $tcExternalID,$tcversionID, $title, $buildID,$tplanID)
	{
		$title = htmlspecialchars($title);
		$suffix = htmlspecialchars($this->testCasePrefix . $this->testCaseCfg->glue_character . $tcExternalID) .
			":&nbsp;<b>" . $title. "</b></a>";
		//BUGID 2267
		$testTitle = '<a href="lib/execute/execSetResults.php?level=testcase&build_id='
			. $buildID . '&id=' . $tcID.'&version_id='.$tcversionID.'&tplan_id=' . $tplanID.'">';
		$testTitle .= $suffix;
		
		return $testTitle;
	}


	/**
	 * Function returns prioritized test result counter
	 * 
	 * @param timestamp $milestoneDate - (optional) milestone deadline
	 * @return array with three priority counters
	 */
	// public function getPrioritizedResults($milestoneDate = null)
	// {
	// 	$output = array (HIGH=>0,MEDIUM=>0,LOW=>0);
	// 	
	// 	for($urgency=1; $urgency <= 3; $urgency++)
	// 	{
	// 		for($importance=1; $importance <= 3; $importance++)
	// 		{	
	// 			$sql = "SELECT COUNT(DISTINCT(TPTCV.id )) " .
	// 				" FROM {$this->tables['testplan_tcversions']} TPTCV " .
	// 				" JOIN {$this->tables['executions']} E ON " .
	// 				" TPTCV.tcversion_id = E.tcversion_id " .
	// 				" JOIN {$this->tables['tcversions']} TCV ON " .
	// 				" TPTCV.tcversion_id = TCV.id " .
	// 				" WHERE TPTCV.testplan_id = {$this->testPlanID} " .
	// 				" AND TPTCV.platform_id = E.platform_id " .
	// 				" AND E.testplan_id = {$this->testPlanID} " .
	// 				" AND NOT E.status = '{$this->map_tc_status['not_run']}' " . 
	// 				" AND TCV.importance={$importance} AND TPTCV.urgency={$urgency}";
	// 			
	// 			if( !is_null($milestoneDate) )
	// 				$sql .= " AND execution_ts < '{$milestoneDate}'";
	// 			
	// 			$tmpResult = $this->db->fetchOneValue($sql);
	// 			// parse results into three levels of priority
	// 			if (($urgency*$importance) >= $this->priorityLevelsCfg[HIGH])
	// 			{
	// 				$output[HIGH] = $output[HIGH] + $tmpResult;
	// 				tLog("getPrioritizedResults> Result-priority HIGH: $urgency, $importance = " . $output[HIGH]);
	// 			}
	// 			elseif (($urgency*$importance) >= $this->priorityLevelsCfg[MEDIUM])
	// 			{
	// 				$output[MEDIUM] = $output[MEDIUM] + $tmpResult;	
	// 				tLog("getPrioritizedResults> Result-priority MEDIUM: $urgency, $importance = " . $output[MEDIUM]);
	// 			}
	// 			else
	// 			{
	// 				$output[LOW] = $output[LOW] + $tmpResult;
	// 				tLog("getPrioritizedResults> Result-priority LOW: $urgency, $importance = " . $output[LOW]);
	// 			}	
	// 		}
	// 	}
	// 	
	// 	return $output;
	// }


	/**
	 * Function returns prioritized test case counter (in Test Plan)
	 * 
	 * @return array with three priority counters
	 */
	public function getPrioritizedTestCases()
	{
		$output = array (HIGH=>0,MEDIUM=>0,LOW=>0);
		
		/** @TODO - REFACTOR IS OUT OF STANDARD MAGIC NUMBERS */
		for($urgency=1; $urgency <= 3; $urgency++)
		{
			for($importance=1; $importance <= 3; $importance++)
			{	
				// get total count of related TCs
				$sql = "SELECT COUNT( TPTCV.id ) FROM {$this->tables['testplan_tcversions']} TPTCV " .
						" JOIN {$this->tables['tcversions']} TCV ON TPTCV.tcversion_id = TCV.id " .
						" WHERE TPTCV.testplan_id = " . $this->testPlanID .
			    		" AND TCV.importance={$importance} AND TPTCV.urgency={$urgency}";

				$tmpResult = $this->db->fetchOneValue($sql);

				//BUGID 4418 - clean up priority usage
				$priority = priority_to_level($urgency*$importance);
				$output[$priority] = $output[$priority] + $tmpResult;
			}
		}
					
		return $output;
	}


	/** 
	 * utility method to trace memory usage
	 * @TODO havlatm: this function should be in some parent
	 */
	function memory_status($pclass,$pfile,$pfunction,$pline)
	{
		$status=array('msg'=>'','mem'=>0,'peak'=>0);
		
		if( function_exists('memory_get_usage') )
		{
			$status['mem']=memory_get_usage();
			$status['peak']=memory_get_peak_usage();
			
			$status['msg']='<pre> Class:' . $pclass . ' - File:' . $pfile . '<br>';
			$status['msg'] .= 'Function:' . $pfunction . ' - Line:' . $pline . ' - ';
			$status['msg'] .="Mem Usage: ". $status['mem'] ." | Peak: ". $status['peak'] .'<br> </pre>';
		}
		
		return $status;
	}

	
	/**
     * 
     *
     */
	function removeEmptySuites(&$node,$hash_id_descr,$tck_map = null,$tplan_tcases = null,$assignedTo = 0)
	{
		$tcase_counters = 0;
		$node_type = $hash_id_descr[$node['node_type_id']];
		if ($node_type == 'testcase')
		{

			$nodeID = $node['id'];
			$tcase_counters = 1;
			if ($tck_map && !isset($tck_map[$nodeID]))
			{
				$tcase_counters = 0;
			}
			else if ($tplan_tcases)
			{
				if (!isset($tplan_tcases[$nodeID]))
				{
					$tcase_counters = 0;
				}
				else if ($assignedTo)
				{
					foreach ($tplan_tcases[$nodeID] as $index => $info)
					{
						if ($info['user_id'] == $assignedTo)
						{
							$has_assigned = true;
						}
					}
					
					if (!$has_assigned)
				    {
					  $tcase_counters = 0;
				    }	
			}
			}
			$node = null;
			return $tcase_counters;
		}
		else if ($node_type == 'testsuite' || $node_type == 'testproject')
		{
			if (isset($node['childNodes']) && $node['childNodes'])
			{
				$childNodes = &$node['childNodes'];
				$nSize = sizeof($childNodes);
				for($idx = 0;$idx < $nSize;$idx++)
				{
					$current = &$childNodes[$idx];
					// I use set an element to null to filter out leaf menu items
					if(is_null($current))
					{
						continue;
					}
					$tcCount = $this->removeEmptySuites($current,$hash_id_descr,
						                                $tck_map,$tplan_tcases,$assignedTo);
					$tcase_counters += $tcCount;
				}
				
				if ((!is_null($tck_map) || !is_null($tplan_tcases)) && 
						!$tcase_counters && ($node_type != 'testproject'))
				{
					$node = null;
				}
			}
			else if ($tplan_tcases)
			{
				$node = null;
			}	
		}
		
		return $tcase_counters;
	}


	/**
	 * Returns priority (urgency * importance) as HIGH, MEDUIM or LOW depending on value
	 * @return HIGH, MEDIUM or LOW
	 */
	public function getPriority($tplan_id, $tcversion_id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		
		$sql = "/* $debugMsg */ ";
		$sql .=	" SELECT (urgency * importance) AS priority " .
		        " FROM {$this->tables['testplan_tcversions']} TPTCV " .
			    " JOIN {$this->tables['tcversions']} TCV ON TPTCV.tcversion_id = TCV.id " .
			    " WHERE TPTCV.testplan_id = {$tplan_id} AND TPTCV.tcversion_id = {$tcversion_id}";
		$prio = $this->db->fetchOneValue($sql);
		
		//BUGID 4418 - clean up priority usage
		return priority_to_level($prio);
	}



	function getUsersForFeatures($tplan_id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$sql = "/* $debugMsg */ ";

		$sql .=	" SELECT COALESCE(UA.user_id,-1) AS user_id, TPTCV.id AS feature_id " .
				" FROM {$this->tables['testplan_tcversions']} TPTCV " .
				" LEFT OUTER JOIN {$this->tables['user_assignments']} UA " .
				" ON UA.feature_id = TPTCV.id " .
				" WHERE TPTCV.testplan_id={$tplan_id}";

		$rs =  $this->db->fetchRowsIntoMap($sql,'feature_id');
		return $rs;
	}	// end function



} // ---- end class result -----

/**
 * 
 * @package 	TestLink
 * @TODO havlatm: what is that?
 */
class newResults extends results
{
	public function newResults(&$db, &$tplan_mgr,$tproject_info, $tplan_info,
	                        $suitesSelected = 'all',
	                        $builds_to_query = -1, $platforms_to_query = array(ALL_PLATFORMS),
							$lastResult = 'a', $latest_results_arg = 1, $keywordId = 0,
							$owner = null, $startTime = null, $endTime = null,
							$executor = null, $search_notes_string = null, $linkExecutionBuild = null, 
							&$suiteStructure = null, &$flatArray = null, &$linked_tcversions = null)
	{

		$this->latest_results = $latest_results_arg;
		return $this->results_overload($db, $tplan_mgr,$tproject_info, $tplan_info,
	                        $suitesSelected ,
	                        $builds_to_query , $platforms_to_query , $lastResult,
	                        $keywordId , $owner ,
							$startTime , $endTime ,
							$executor , $search_notes_string, $linkExecutionBuild , 
							$suiteStructure, $flatArray, $linked_tcversions);
	}
}
?>
