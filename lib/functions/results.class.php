<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: results.class.php,v $
 *
 * @version $Revision: 1.8
 * @modified $Date: 2008/11/22 10:44:33 $ by $Author: franciscom $
 *
 *-------------------------------------------------------------------------
 * Revisions:
 *
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
require_once("../../config.inc.php");
require_once('common.php');
require_once('treeMenu.inc.php');
require_once('users.inc.php');
require_once('exec.inc.php'); // used for bug string lookup

/**
* @author kevinlevy
* This class is encapsulates most functionality necessary to query the database
* for results to publish in reports.  It returns data structures to the gui layer in a
* manner that are easy to display in smarty templates.
*/
class results
{
	/*
	* only call get_linked_tcversions() only once, and save it to
	* $this->linked_tcversions
	*/
	private $linked_tcversions = null;
	private $suitesSelected = "";

	// class references passed in by constructor
	private $db = null;
	private $tplanMgr = null;
	private $testPlanID = -1;
	private	$tprojectID = -1;
	private	$testCasePrefix='';

  private $priorityLevelsCfg='';
	private $resultsCfg;
	private $testCaseCfg='';
	private $map_tc_status;
	private $tc_status_for_statistics;


	/**
	* KL - 20061225 - creating map specifically for owner and keyword
	*/
	private $mapOfLastResultByOwner = null;
	private $mapOfLastResultByKeyword = null;
	private $mapOfLastResultByBuild = null;
	private $tplanName = null;

	/**
	* construct map linking suite ids to execution rows
	*/
	private $SUITE_TYPE_ID = 2;
	private $executionsMap = null;

	/**
	* suiteStructure is an array with pattern : name, id, array
	* array may contain another array in the same pattern
	* this is used to describe tree structure
	*/
	private $suiteStructure = null;

	private $ITEM_PATTERN_IN_SUITE_STRUCTURE = 3;
	private $NAME_IN_SUITE_STRUCTURE = 0;
	private $ID_IN_SUITE_STRUCTURE = 1;
	private $ARRAY_IN_SUITE_STRUCTURE = 2;
	private $flatArray = null;

	/**
	* assoicated with flatArray
	*/
	private $flatArrayIndex = 0;

	/**
	* assoicated with flatArray
	*/
	private $depth = 0;

	/**
	* assoicated with flatArray
	*/
	private $previousDepth = 0;

	/**
	* constants for flatArray
	*/
	private $ITEM_PATTERN_IN_FLAT_ARRAY = 3;
	private $DEPTH_IN_FLATARRAY  = 0;
	private $NAME_IN_FLATARRAY = 1;
	private $SUITE_ID_IN_FLATARRAY = 2;

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


  var $import_file_types = array("XML" => "XML");
  var $export_file_types = array("XML" => "XML");


	/**
	* $builds_to_query = 'a' will query all build, $builds_to_query = -1 will prevent
	* most logic in constructor from executing/ executions table from being queried
	* if keyword = 0, search by keyword would not be performed
	* @author kevinlevy
	*
	* rev :
	*      20071013 - franciscom - changes to fix MSSQL problems
	*                 $startTime = "0000-00-00 00:00:00" -> null
  *                 $endTime = "9999-01-01 00:00:00" -> null
  *
	*      20070916 - franciscom - interface changes
	*/
	public function results(&$db, &$tplan_mgr,$tproject_info, $tplan_info,
	                        $suitesSelected = 'all',
	                        $builds_to_query = -1, $lastResult = 'a',
	                        $keywordId = 0, $owner = null,
							            $startTime = null, $endTime = null,
							            $executor = null, $search_notes_string = null, $linkExecutionBuild = null,
							            &$suiteStructure = null, &$flatArray = null, &$linked_tcversions = null)
	{
	    
      // $mem=array();		
      // $mem[]=self::memory_status(__CLASS__,__FILE__,__FUNCTION__,__LINE__);
		  
		$this->priorityLevelsCfg = config_get('priority_levels');
    	$this->resultsCfg = config_get('results');
    	$this->testCaseCfg = config_get('testcase_cfg');

		$this->db = $db;
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
		    $this->suiteStructure = $this->generateExecTree($db,$keywordId, $owner);
		}
    // $mem[]=self::memory_status(__CLASS__,__FILE__,__FUNCTION__,__LINE__);
    // $xmem=current($mem);
    // echo "<pre>debug 20080928 - \ - " . __FUNCTION__ . " --- "; print_r($xmem['msg']); echo "</pre>";  
    // ob_flush();flush();

		// KL - if no builds are specified, no need to execute the following block of code
		if ($builds_to_query != -1) {
			// retrieve results from executions table

     
			// get keyword id -> keyword name pairs used in this test plan
			$keywords_in_tplan = $tplan_mgr->get_keywords_map($this->testPlanID,'ORDER BY keyword');

			// KL - 20061229 - this call may not be necessary for all reports
			// only those that require info on results for keywords
			// Map of test case ids to array of associated keywords
			$this->keywordData = $this->getKeywordData(array_keys($keywords_in_tplan));

      //new dBug($this->keywordData);
      //$tplan_mgr->get_keywords_tcases($this->testPlanID);
      

			// get owner id -> owner name pairs used in this test plan
			$arrOwners = getUsersForHtmlOptions($db, null, false);


			// create data object which tallies last result for each test case
			// this function now also creates mapOfLastResultByKeyword ???

			// KL - 2/01/07
			// we should NOT build executions map with cases that are just pass/failed/or blocked.
			// we should always populate the executions map with all results
			// and then programmatically figure out the last result
			// if you just query the executions table for those rows with status = $this->map_tc_status['passed']
			// that is not the way to determine last result

			$this->executionsMap = $this->buildExecutionsMap($builds_to_query, 'a', $keywordId,
			                                                 $owner, $startTime, $endTime, $executor,
			                                                 $search_notes_string, $linkExecutionBuild);
      
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
			$arrBuilds = $tplan_mgr->get_builds($this->testPlanID);
			$this->aggregateBuildResults = $this->tallyBuildResults($this->mapOfLastResultByBuild,
			                                                        $arrBuilds, $this->totalsForPlan);
		} // end if block
	} // end results constructor


  	/*
    function: get_export_file_types
    args: -
    @returns: map
             key: export file type code
             value: export file type verbose description
  	*/
	function get_export_file_types()
	{
    	return $this->export_file_types;
	}


  	/*
    function: get_import_file_types
    args: -
    returns: map
             key: import file type code
             value: import file type verbose description
	*/
	function get_import_file_types()
	{
    	return $this->import_file_types;
  	}

	/**
	* 
	* tallyKeywordResults($keywordResults, $keywordIdNamePairs)
	*
	* keywordResultsformat:
	* Array ([keyword id] => Array ( [test case id] => result ))
	*
	* example:
	* Array ( [128] => Array ( [14308] => p [14309] => n [14310] => n [14311] => f [14312] => n [14313] => n )
	*			 [11] => Array ( [14309] => n [14310] => n [14311] => f [14312] => n [14313] => n ))
	*
	* @return map indexed using Keyword ID, where each element is a map with following structure:
	*
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
  	*
  	*                  [failed] => Array
  	*                      (
  	*                          [qty] => 1
  	*                          [percentage] => 50.00
  	*                      )
  	*
  	*                  [blocked] => Array
  	*                      (
  	*                          [qty] => 0
  	*                          [percentage] => 0.00
  	*                      )
  	*
  	*                  [unknown] => Array
  	*                      (
  	*                          [qty] => 0
  	*                          [percentage] => 0.00
  	*                      )
  	*
  	*                  [not_run] => Array
  	*                      (
  	*                          [qty] => 0
  	*                          [percentage] => 0.00
  	*                      )
  	*              )
  	*      )
	*
	*      IMPORTANT:
	*                keys on details map dependends of configuration map $g_tc_status_for_ui
	*/
	private function tallyKeywordResults($keywordResults, $keywordIdNamePairs)
	{
		if ($keywordResults == null)
		{
			return null;
		}

	  // OK go ahead
		$rValue = null;
	  foreach($keywordResults as $keywordId => $results)
	  {
	    $item_name='keyword_name';
      $element=$this->tallyResults($results,sizeOf($results),$item_name);
		  $element[$item_name]=$keywordIdNamePairs[$keywordId];
			$rValue[$keywordId] = $element;
		} // foreach

    // echo "<pre>debug 20081115 - \ - " . __FUNCTION__ . " --- "; echo "</pre>";
    // new dBug($rValue); 
		return $rValue;
	} // end function



	/**
	* tallyOwnerResults($ownerResults, $ownerIdNamePairs)
	*   returns testers results
	*
	* ownerResults format:
	* Array ([owner id] => Array ( [test case id] => result ))
	*
	* output format :
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
  *
  *                  [failed] => Array
  *                      (
  *                          [qty] => 1
  *                          [percentage] => 50.00
  *                      )
  *
  *                  [blocked] => Array
  *                      (
  *                          [qty] => 0
  *                          [percentage] => 0.00
  *                      )
  *
  *                  [unknown] => Array
  *                      (
  *                          [qty] => 0
  *                          [percentage] => 0.00
  *                      )
  *
  *                  [not_run] => Array
  *                      (
  *                          [qty] => 0
  *                          [percentage] => 0.00
  *                      )
  *              )
  *      )
	*
	*      IMPORTANT:
	*                keys on details map dependends of configuration map $g_tc_status_for_ui
	*
	*/
	private function tallyOwnerResults($ownerResults, $ownerIdNamePairs)
	{
		if ($ownerResults == null) {
			return;
		}

	  // OK go ahead
	  $no_tester_assigned=lang_get('unassigned');
		$rValue = null;
    foreach($ownerResults as $ownerId => $results)
    {
	    $item_name='tester_name';
      $element=$this->tallyResults($results,sizeOf($results),$item_name);
		  $element[$item_name]= ($ownerId == -1) ? $no_tester_assigned : $ownerIdNamePairs[$ownerId];
			$rValue[$ownerId] = $element;
		}

		return $rValue;
	} // end function


	/**
	* tallyBuildResults()
	*
	* parameter1 format:
	* Array ([owner id] => Array ( [test case id] => result ))
	*
	* output format :
	* Array ( [owner id] => Array ( total, passed, failed, blocked, not run))
	*/
	private function tallyBuildResults($buildResults, $arrBuilds, $finalResults)
	{
		if ($buildResults == null)
		{
			return null;
		}

	  // OK go ahead
		$totalCases = $finalResults['total'];
		$rValue = null;
		foreach($arrBuilds as $buildId => $buildInfo)
		{
	    $item_name='build_name';
		  $results = isset($buildResults[$buildId]) ? $buildResults[$buildId] : array();
      $element=$this->tallyResults($results,$totalCases,$item_name);
		  $element[$item_name]=$buildInfo['name'];
			$rValue[$buildId] = $element;
		} // end  foreach
      
    unset($element);       
		unset($results);
		return $rValue;
	} // end function



	/**
	* returns total / pass / fail / blocked / not run results for each keyword id
	* @return array
	*/
	public function getAggregateKeywordResults() {
		return $this->aggregateKeywordResults;
	}

	/**
	* returns total / pass / fail / blocked / not run results for each owner id
	* unassigned test cases show up under owner id = -1
	* @return array
	*/
	public function getAggregateOwnerResults() {
		return $this->aggregateOwnerResults;
	}

	/**
	* returns total / pass / fail / blocked / not run results for each build id
	* @return array
	*/
	public function getAggregateBuildResults() {
		return $this->aggregateBuildResults;
	}

	/**
	* TO-DO: rename this method to getExecutionsMap()
	* (resultsTC.php is 1 file (may not be only file) that references this method)
	* @return array
	*/
	public function getSuiteList(){
		return $this->executionsMap;
	}

	/**
	* returns array which describes suite hierachy
	* @return array
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
	public function getTotalsForPlan(){
		return $this->totalsForPlan;
	}

	/**
	* single-dimension array
	* with pattern level, suite name, suite id
	* @return array
	*/
	public function getFlatArray(){
		return $this->flatArray;
	}

  /*
    function: tallyResults

    args :
          results: map: key tc_id
                        value status_code

    returns: map with following keys


  */

	private function tallyResults($results,$totalCases,$item_name=null)
	{
		if ($results == null)
		{
			return null;
		}

	  // OK go ahead
	  $code_verbose=array_flip($this->tc_status_for_statistics);
		$element = null;
		foreach($this->tc_status_for_statistics as $status_verbose => $status_code)
		{
		    $total[$status_verbose]=0;
		    $percentage[$status_verbose]=0;
		}

		$dummy=0;
		foreach($results as $tc_id => $result_code)
		{
	      $status_verbose=$code_verbose[$result_code];

        // Check if user has configured and add not_run.
        // Standard TestLink behavior:
        // 1. do not show not_run as a choice on execute pages
        // 2. do not save on DB not_run executions
        //
        if( $status_verbose !='' && $status_verbose != 'not_run')
        {
		      $total[$status_verbose]++;
          $dummy++;
        }
  	}

    // not_run is an special status
		$total['not_run'] = abs($totalCases - $dummy);
    $percentage['not_run']=number_format((($total['not_run']) / $totalCases) * 100,2);

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

		// Because I want to have this as first key on map, just for easy reading.
		// Value will be setted by caller.
		if( !is_null($item_name) )
		{
		    $element[$item_name]='';
		}
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



	/**
	* function addLastResultToMap()
	* @author kevinlevy
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
	* @return void
	*
	* rev :
	*      20070919 - franciscom - interface changes
	*      20070825 - franciscom - added node_order
	*
	*/
	private function addLastResultToMap($suiteId, $suiteName, $exec,$lastResultToTrack)
	{

    // just to avoid a lot of refactoring
    //echo "<pre>debug 20080602 - \ - " . __FUNCTION__ . " --- "; print_r($exec); echo "</pre>";
		$testcase_id=$exec['testcaseID'];
		$external_id=$exec['external_id'];
	  $buildNumber=$exec['build_id'];
		$result=$exec['status'];
		$tcversion_id=$exec['tcversion_id'];
		
		// 20080602 - franciscom
		// Maybe this check is not more needed, because $exec has been changed using same logic
		if( isset($exec['tcversion_number']) && !is_null($exec['tcversion_number']) )
		{
		    $version=$exec['tcversion_number'];
		}
		else
		{
		    $version=$exec['version'];
		}
		
		$execution_ts=$exec['execution_ts'];
    $notes=$exec['notes'];
    $executions_id=$exec['executions_id'];
	  $name=$exec['name'];
	  $tester_id=$exec['tester_id'];
	  $feature_id=$exec['feature_id'];
	  $assigner_id=$exec['assigner_id'];


		if ($buildNumber)
			$this->mapOfLastResultByBuild[$buildNumber][$testcase_id] = $result;

		$sql = "SELECT user_id FROM user_assignments WHERE feature_id = "  . $feature_id ;
		$owner_row =  $this->db->fetchFirstRow($sql,'testcase_id', 1);
		$owner_id = $owner_row['user_id'];
		if ($owner_id == '')
			$owner_id = -1;

		$associatedKeywords = null;
		if ($this->keywordData != null && array_key_exists($testcase_id, $this->keywordData))
		{
			$associatedKeywords = $this->keywordData[$testcase_id];
    }
    
		$bInsert = false;
		$bClean = false;
		// handle case where suite has already been added to mapOfLastResult
		if ($this->mapOfLastResult && array_key_exists($suiteId, $this->mapOfLastResult))
		{
			// handle case where both suite and test case have been added to elmapOfLastResult
			if (array_key_exists($testcase_id, $this->mapOfLastResult[$suiteId]))
			{
				$buildInMap = $this->mapOfCaseResults[$testcase_id]['buildNumber'];
				$execIDInMap = $this->mapOfCaseResults[$testcase_id]['execID'];
				if (($buildInMap < $buildNumber) || ($buildInMap == $buildNumber && $execIDInMap < $executions_id))
					$bInsert = true;
			}
			else // handle case where suite is in mapOfLastResult but test case has not been added
				$bInsert = true;
		}
		else // handle case where suite has not been added to mapOfLastResult
			$bInsert = true;

		if ($bInsert)
		{
			// owner assignments
			$this->mapOfLastResultByOwner[$owner_id][$testcase_id] = $result;
			// keyword assignments
			$qta_loops=sizeof($associatedKeywords);
			
			for ($i = 0;$i <$qta_loops ; $i++)
			{
				$this->mapOfLastResultByKeyword[$associatedKeywords[$i]][$testcase_id] = $result;
			}
			
			$this->mapOfCaseResults[$testcase_id]['buildNumber'] = $buildNumber;
			$this->mapOfCaseResults[$testcase_id]['execID'] = $executions_id;

			$this->mapOfLastResult[$suiteId][$testcase_id] = array("buildIdLastExecuted" => $buildNumber,
	                                                           "result" => $result,
												 		                                 "tcversion_id" => $tcversion_id,
		                                                         "external_id" => $external_id,
												 		                                 "version" => $version,
	                                                           "execution_ts" => $execution_ts,
														                                 "notes" => $notes,
	                                                           "suiteName" => $suiteName,
	                                                           "executions_id" => $executions_id,
	                                                           "name" => $name,
														                                 "tester_id" => $tester_id);
		}
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

        foreach($tcaseResults as $testcase_id => $value)
        {
					$currentResult =  $tcaseResults[$testcase_id]['result'];
  		    $status_verbose=$code_verbose[$currentResult];
	   	    $total[$status_verbose]++;
				}

				$total['total'] = count($tcaseResults);
				$this->mapOfSuiteSummary[$suiteId] =  $total;
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
  		  $tpos=$idx % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE;
  			if ($tpos ==  $this->ID_IN_SUITE_STRUCTURE)
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
			  elseif ($tpos ==  $this->ARRAY_IN_SUITE_STRUCTURE)
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
			if (($idx % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) ==  $this->ID_IN_SUITE_STRUCTURE)
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
	*
	*
	* results: map with following keys:
	*          'total','not_run', other keys depends of status configured by user
	*          If users leave untouched TestLink Factory configuration:
	*          'total','not_run','passed','failed','blocked'
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
		} // end for loop
	} // end function

	/**
	*
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
		       " FROM testcase_keywords" .
		       " WHERE keyword_id IN ($keys)";
		
		$returnMap =  $this->db->fetchColumnsIntoMap($sql,'testcase_id', 'keyword_id',$CUMULATIVE);
		return $returnMap;
	} // end function

	/**
	*
	*
	* rev :
	*/
	private function createMapOfLastResult(&$suiteStructure, &$executionsMap, $lastResult){
		$suiteName = null;
		$totalSuites=count($suiteStructure);


		for ($i = 0; $i < $totalSuites; $i++){
			if (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) == $this->NAME_IN_SUITE_STRUCTURE) {
				$suiteName = $suiteStructure[$i];
			}
			elseif (($i % $this->ITEM_PATTERN_IN_SUITE_STRUCTURE) ==  $this->ID_IN_SUITE_STRUCTURE) {
				$suiteId = $suiteStructure[$i];
				$totalCases = isset($executionsMap[$suiteId]) ? count($executionsMap[$suiteId]) : 0;
				for ($j = 0 ; $j < $totalCases; $j++) {
					$cexec = $executionsMap[$suiteId][$j];
					$this->addLastResultToMap($suiteId, $suiteName,$cexec,$lastResult);
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
	*
	* rev :
	*      20080928 - franciscom - seems that adding a control to avoid call to buildBugString()
	*                              reduces dramatically memory usage();
	*                              IMPORTANT:
	*                              we need to refactor this method, because if we had to call buildBugsString()
	*                              probably will crash again.
	*                              We need to think about function that got bug for all test cases, with one
	*                              call, may be is the solution.
	*
	*      20070916 - franciscom - removed session coupling
	*
	*      added node_order
	*/
	private function buildExecutionsMap($builds_to_query, $lastResult = 'a', $keyword = 0,
	                                    $owner = null, $startTime, $endTime, $executor,
	                                    $search_notes_string, $executeLinkBuild){
		
    // $mem=array();		
    // $mem[]=self::memory_status(__CLASS__,__FILE__,__FUNCTION__,__LINE__);
    // $xmem=current($mem);
    // echo "<pre>debug 20080928 - \ - " . __FUNCTION__ . " --- "; print_r($xmem['msg']); echo "</pre>";  
    // ob_flush();flush();
    	$searchBugs= config_get('bugInterfaceOn');
                                                    
		// first make sure we initialize the executionsMap
		// otherwise duplicate executions will be added to suites
		$executionsMap = null;

		// for execution link
		$bCanExecute = has_rights($this->db,"tp_execute");

		// ------------------------------------------------------
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
		if (!is_null($executor)) {
		    $sqlFilters .= " AND tester_id = $executor ";
		}
		if ($search_notes_string != null) {
		    $sqlFilters .= " AND notes LIKE '%" . $search_notes_string ."%' ";
		}
		// ------------------------------------------------------
		
		$prefixLink = '<a href="lib/execute/execSetResults.php?level=testcase&build_id=' . $executeLinkBuild;
    $suffixLink = htmlspecialchars($this->testCasePrefix . $this->testCaseCfg->glue_character);
    
    foreach($this->linked_tcversions as $testcaseID => $info)
		{
	
			$executionExists = true;
			$currentSuite = null;
			if (!$executionsMap || !(array_key_exists($info['testsuite_id'], $executionsMap))){
				$currentSuite = array();
			}
			else {
				$currentSuite = $executionsMap[$info['testsuite_id']];
			}
			
	    $version = $info['version'];
		  if( isset($info['tcversion_number']) && !is_null($info['tcversion_number']) )
		  {
			    $version = $info['tcversion_number'];
			}
			
			$executeLink = $prefixLink . "&id={$testcaseID}&version_id=" . $info['tcversion_id'] . '">' .
			               $suffixLink . $info['external_id'] . ":&nbsp;<b>" .  htmlspecialchars($info['name']). "</b></a>";
			
			// $this->getTCLink($bCanExecute,$testcaseID,$info['external_id'],
			//                                 $info['tcversion_id'],$info['name'],$executeLinkBuild);

      $infoToSave = array('testcaseID' => $testcaseID,
			                    'external_id' => $info['external_id'],
			                    'tcversion_id' => $info['tcversion_id'],
			                    'version' => $version,
			                    'build_id' => '',
			                    'tester_id' => '',
			                    'execution_ts' => '',
			                    'status' => $this->map_tc_status['not_run'],
			                    'executions_id' => '',
			                    'notes' => '',
			                    'bugString' => '',
			                    'name' => $info['name'],
			                    'assigner_id' => $info['assigner_id'],
			                    'feature_id' => $info['feature_id'],
			                    'execute_link' => $executeLink);
      

			if ($info['tcversion_id'] != $info['executed'])
			{
				$executionExists = false;
				if (($lastResult == 'a') || ($lastResult == $this->map_tc_status['not_run'])) 
				{
					// Initialize information on testcaseID to be "not run"
					array_push($currentSuite, $infoToSave);
				}
			}

			if ($executionExists) 
			{
				// TO-DO - this is where we can include the searching of results
				// over multiple test plans - by modifying this select statement slightly
				// to include multiple test plan ids
        //
        // 20080928 - franciscom - Be careful about memory usage problems
        //  
				$sql = "SELECT * FROM executions " .
				       "WHERE tcversion_id = " . $info['executed'] . " AND testplan_id = $this->testPlanID " ;

        $sql .= $sqlFilters;

				// mht: fix 966
				// mike_h - 20070806 - when ordering executions by the timestamp, 
				// the results are represented correctly in the report "Test Report".
				$sql .= " ORDER BY execution_ts ASC";

				$execQuery = $this->db->fetchArrayRowsIntoMap($sql,'id');
				if ($execQuery)
				{
  				if ($lastResult != $this->map_tc_status['not_run']) 
  				{
              foreach($execQuery as $executions_id => $execInfo)
					    {
					    	//$notSureA = $execInfo;
					    	//$exec_row = $notSureA[0];
					    	// $testplan_id = $exec_row['testplan_id'];
					    	$exec_row = $execInfo[0];
					    	
					    	$infoToSave['build_id'] = $exec_row['build_id'];
					    	$infoToSave['tester_id'] = $exec_row['tester_id'];
					    	$infoToSave['status'] = $exec_row['status'];
					    	$infoToSave['notes'] = $exec_row['notes'];
					    	$infoToSave['executions_id'] = $executions_id;
					    	$infoToSave['bugString'] = $searchBugs ? $this->buildBugString($this->db, $executions_id) : '';

					    	$dummy=null;
					    	$infoToSave['execution_ts'] = localize_dateOrTimeStamp(null, $dummy,'timestamp_format',
					    	                                                       $exec_row['execution_ts']);

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
		} // end foreach
		
		unset($infoToSave);
		
		return $executionsMap;
	} // end function

	/**
	* TO-DO - figure out what file to include so i don't have
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
	* return map of suite id to suite name pairs of all suites
	*/
	public function getAllSuites() {
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
	public function getTopLevelSuites(){
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
	* initializes linked_tcversions object
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
	private function generateExecTree(&$db,$keyword_id = 0, $owner = null) 
	{
	    // $mem=array();		
	    // $mem[]=self::memory_status(__CLASS__,__FILE__,__FUNCTION__,__LINE__);
	    // $xmem=current($mem);
	    // echo "<pre>debug 20080928 - \ - " . __FUNCTION__ . " --- "; print_r($xmem['msg']); echo "</pre>";  
	    // ob_flush();flush();
	
		$RECURSIVE_MODE = true;
		$tplan_mgr = $this->tplanMgr;
		$tproject_mgr = new testproject($this->db);
		$tree_manager = $tplan_mgr->tree_manager;
		$hash_descr_id = $tree_manager->get_available_node_types();
	
	    $test_spec = $tproject_mgr->get_subtree($this->tprojectID,$RECURSIVE_MODE);
		$tp_tcs = $tplan_mgr->get_linked_tcversions($this->testPlanID,null,$keyword_id, null, $owner);
		
		$this->linked_tcversions = &$tp_tcs;
		if (is_null($tp_tcs)) {
			$tp_tcs = array();
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
		
		$testcase_count = $this->removeEmptySuites($test_spec,$hash_id_descr,$tck_map,$tp_tcs,$owner);
		// $mem[]=self::memory_status(__CLASS__,__FILE__,__FUNCTION__,__LINE__);
	   	// $xmem=current($mem);
	    // echo "<pre>debug 20080928 - \ - " . __FUNCTION__ . " --- "; print_r($xmem['msg']); echo "</pre>";  
		// ob_flush();flush();
			                              
		$suiteStructure = $this->processExecTreeNode(1,$test_spec,$hash_id_descr);
		return $suiteStructure;
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

	/**
	* Function 
	* @return string Link of Test ID + Title
	*/
	function getTCLink($rights, $tcID, $tcExternalID,$tcversionID, $title, $buildID)
	{
		$title = htmlspecialchars($title);
		$suffix = htmlspecialchars($this->testCasePrefix . $this->testCaseCfg->glue_character . $tcExternalID) .
		          ":&nbsp;<b>" . $title. "</b></a>";

		$testTitle = '<a href="lib/execute/execSetResults.php?level=testcase&build_id='
				 . $buildID . '&id=' . $tcID.'&version_id='.$tcversionID.'">';
		$testTitle .= $suffix;

	  return $testTitle;
	}

	// ----------------------------------------------------------------------------------
	/**
	* Function returns prioritized test result counter
	* 
	* @param timestamp $milestoneDate - optional milestone deadline
	* @return array with three priority counters
	*/
	public function getPrioritizedResults($milestoneDate = null)
	{
		$output = array (HIGH=>0,MEDIUM=>0,LOW=>0);
		
		for($urgency=1; $urgency <= 3; $urgency++)
		{
			for($importance=1; $importance <= 3; $importance++)
			{	
				$sql = "SELECT COUNT( DISTINCT(testplan_tcversions.id ))
						FROM testplan_tcversions JOIN executions ON
						testplan_tcversions.tcversion_id = executions.tcversion_id
                        JOIN tcversions ON testplan_tcversions.tcversion_id = tcversions.id
						WHERE testplan_tcversions.testplan_id = $this->testPlanID
						AND executions.testplan_id = $this->testPlanID " .
						"AND NOT executions.status = '{$this->map_tc_status['not_run']}' " . 
			    		"AND tcversions.importance={$importance} AND testplan_tcversions.urgency={$urgency}";

				if( !is_null($milestoneDate) )
					$sql .= " AND execution_ts < '{$milestoneDate}'";

				$tmpResult = $this->db->fetchOneValue($sql);
				// parse results into three levels of priority
				if (($urgency*$importance) >= $this->priorityLevelsCfg[HIGH])
				{
					$output[HIGH] = $output[HIGH] + $tmpResult;
					tLog("getPrioritizedResults> Result-priority HIGH: $urgency, $importance = " . $output[HIGH]);
				}
				elseif (($urgency*$importance) >= $this->priorityLevelsCfg[MEDIUM])
				{
					$output[MEDIUM] = $output[MEDIUM] + $tmpResult;	
					tLog("getPrioritizedResults> Result-priority MEDIUM: $urgency, $importance = " . $output[MEDIUM]);
				}
				else
				{
					$output[LOW] = $output[LOW] + $tmpResult;
					tLog("getPrioritizedResults> Result-priority LOW: $urgency, $importance = " . $output[LOW]);
				}	
			}
		}
					
		return $output;
	}


	// ----------------------------------------------------------------------------------
	/**
	* Function returns prioritized test case counter (in Test Plan)
	* 
	* @return array with three priority counters
	*/
	public function getPrioritizedTestCases()
	{
		$output = array (HIGH=>0,MEDIUM=>0,LOW=>0);
		
		for($urgency=1; $urgency <= 3; $urgency++)
		{
			for($importance=1; $importance <= 3; $importance++)
			{	
				// get total count of related TCs
				$sql = "SELECT COUNT( testplan_tcversions.id ) FROM testplan_tcversions " .
						" JOIN tcversions ON testplan_tcversions.tcversion_id = tcversions.id " .
						" WHERE testplan_tcversions.testplan_id = " . $this->testPlanID .
			    		" AND tcversions.importance={$importance} AND testplan_tcversions.urgency={$urgency}";

				$tmpResult = $this->db->fetchOneValue($sql);

				// parse results into three levels of priority
				if (($urgency*$importance) >= $this->priorityLevelsCfg[HIGH])
				{
					$output[HIGH] = $output[HIGH] + $tmpResult;
					tLog("getPrioritizedTestCases> Result-priority HIGH: $urgency, $importance = " . $output[HIGH]);
				}
				elseif (($urgency*$importance) >= $this->priorityLevelsCfg[MEDIUM])
				{
					$output[MEDIUM] = $output[MEDIUM] + $tmpResult;	
					tLog("getPrioritizedTestCases> Result-priority MEDIUM: $urgency, $importance = " . $output[MEDIUM]);
				}
				else
				{
					$output[LOW] = $output[LOW] + $tmpResult;
					tLog("getPrioritizedTestCases> Result-priority LOW: $urgency, $importance = " . $output[LOW]);
				}	
			}
		}
					
		return $output;
	}

  /* utility method to trace memory usage
  
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

	
	function removeEmptySuites(&$node,$hash_id_descr,
	                     $tck_map = null,$tplan_tcases = null,$assignedTo = 0)
	{
		$tcase_counters = 0;
		$node_type = $hash_id_descr[$node['node_type_id']];
		if ($node_type == 'testcase')
		{
			$nodeID = $node['id'];
			$tcase_counters = 1;
			if ($tck_map && !isset($tck_map[$nodeID]))
				$tcase_counters = 0;
			else if ($tplan_tcases)
			{
				if (!isset($tplan_tcases[$nodeID]))
					$tcase_counters = 0;
				else if ($assignedTo && ($tplan_tcases[$nodeID]['user_id'] != $assignedTo))
					$tcase_counters = 0;
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
				for($i = 0;$i < $nSize;$i++)
				{
					$current = &$childNodes[$i];
					// I use set an element to null to filter out leaf menu items
					if(is_null($current))
						continue;
		  			$tcCount = $this->removeEmptySuites($current,$hash_id_descr,
					                            $tck_map,$tplan_tcases,
					                            $assignedTo);
		 			$tcase_counters += $tcCount;
		      	}
		
				if ((!is_null($tck_map) || !is_null($tplan_tcases)) && 
				     !$tcase_counters && ($node_type != 'testproject'))
				{
					$node = null;
				}
			}
	 		else if ($tplan_tcases)
				$node = null;
		}
		
		return $tcase_counters;
	}

} // ---- end class result -----
?>
