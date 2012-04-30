<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	tlTestPlanMetrics.class.php
 * @package 	TestLink
 * @author 		Kevin Levy, franciscom
 * @copyright 	2004-2012, TestLink community 
 * @link 		http://www.teamst.org/index.php
 * @uses		config.inc.php 
 * @uses		common.php 
 *
 * @internal revisions
 * @since 1.9.4
 *
 * 20120429 - franciscom -	TICKET 4989: Reports - Overall Build Status - refactoring and final business logic
 *							new method getOverallBuildStatusForRender()
 *
 * 20120419 - franciscom - new method getExecCountersByBuildStatusOnlyWithTesterAssignment()
 *
 **/

/**
 * This class is encapsulates most functionality necessary to query the database
 * for results to publish in reports.  It returns data structures to the gui layer in a
 * manner that are easy to display in smarty templates.
 * 
 * @package TestLink
 * @author kevinlevy
 */
class tlTestPlanMetrics extends testPlan
{
	/** @var resource references passed in by constructor */
	var  $db = null;

	/** @var object class references passed in by constructor */
	private $tplanMgr = null;
	private $testPlanID = -1;
	private	$tprojectID = -1;
	private	$testCasePrefix='';

	private $priorityLevelsCfg='';
	private $map_tc_status;
	private $tc_status_for_statistics;
	private $notRunStatusCode;

	/** 
	 * class constructor 
	 * @param resource &$db reference to database handler
	 **/    
	function __construct(&$db)
	{
		$this->resultsCfg = config_get('results');
		$this->testCaseCfg = config_get('testcase_cfg');

  		$this->db = $db;
  		parent::__construct($db);

  		$this->map_tc_status = $this->resultsCfg['status_code'];
    
    	// This will be used to create dynamically counters if user add new status
    	foreach( $this->resultsCfg['status_label_for_exec_ui'] as $tc_status_verbose => $label)
    	{
      		$this->tc_status_for_statistics[$tc_status_verbose] = $this->map_tc_status[$tc_status_verbose];
    	}
    	if( !isset($this->resultsCfg['status_label_for_exec_ui']['not_run']) )
    	{
      		$this->tc_status_for_statistics['not_run'] = $this->map_tc_status['not_run'];  
    	}
    	$this->notRunStatusCode = $this->tc_status_for_statistics['not_run'];

	} // end results constructor


	/**
	 * Function returns prioritized test result counter
	 * 
	 * @param timestamp $milestoneTargetDate - (optional) milestone deadline
	 * @param timestamp $milestoneStartDate - (optional) milestone start date
	 * @return array with three priority counters
	 */
	public function getPrioritizedResults($tplanID,$milestoneTargetDate = null, $milestoneStartDate = null)
	{
		$output = array (HIGH=>0,MEDIUM=>0,LOW=>0);
		
		for($urgency=1; $urgency <= 3; $urgency++)
		{
			for($importance=1; $importance <= 3; $importance++)
			{	
				$sql = "SELECT COUNT(DISTINCT(TPTCV.id )) " .
					" FROM {$this->tables['testplan_tcversions']} TPTCV " .
					" JOIN {$this->tables['executions']} E ON " .
					" TPTCV.tcversion_id = E.tcversion_id " .
					" JOIN {$this->tables['tcversions']} TCV ON " .
					" TPTCV.tcversion_id = TCV.id " .
					" WHERE TPTCV.testplan_id = {$tplanID} " .
					" AND TPTCV.platform_id = E.platform_id " .
					" AND E.testplan_id = {$tplanID} " .
					" AND NOT E.status = '{$this->map_tc_status['not_run']}' " . 
					" AND TCV.importance={$importance} AND TPTCV.urgency={$urgency}";
				
				// BUGID 4511 - Milestones did not handle start and target date properly
				$end_of_the_day = " 23:59:59";
				$beginning_of_the_day = " 00:00:00";
				
				if( !is_null($milestoneTargetDate) )
				{
					$sql .= " AND execution_ts < '" . $milestoneTargetDate . $end_of_the_day ."'";
				}
				
				if( !is_null($milestoneStartDate) )
				{
					$sql .= " AND execution_ts > '" . $milestoneStartDate . $beginning_of_the_day ."'";
				}
				
				$tmpResult = $this->db->fetchOneValue($sql);
				// parse results into three levels of priority
				
				//BUGID 4418 - clean up priority usage
				$priority = priority_to_level($urgency*$importance);
				$output[$priority] = $output[$priority] + $tmpResult;
			}
		}
		
		return $output;
	}

	/**
	 * Function returns prioritized test case counter (in Test Plan)
	 * 
	 * @return array with three priority counters
	 */
	public function getPrioritizedTestCaseCounters($tplanID)
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
						" WHERE TPTCV.testplan_id = " . $tplanID .
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
	 * 
	 */
	function getMilestonesMetrics($tplanID, $milestoneSet=null)
	{        
		$results = array();
		// get amount of test cases for each execution result + total amount of test cases
        $planMetrics = $this->getStatusTotals($tplanID);
		$milestones =  is_null($milestoneSet) ? $this->get_milestones($tplanID) : $milestoneSet;
		// get amount of test cases for each priority for test plan			
		$priorityCounters = $this->getPrioritizedTestCaseCounters($tplanID);
        $pc = array(LOW => 'result_low_percentage', MEDIUM => 'result_medium_percentage',
                    HIGH => 'result_high_percentage' );
        
        $checks = array(LOW => 'low_percentage', MEDIUM => 'medium_percentage',
                        HIGH => 'high_percentage' );

        $on_off = array(LOW => 'low_incomplete', MEDIUM => 'medium_incomplete',
                        HIGH => 'high_incomplete' );
        
        // Important:
        // key already defined on item: high_percentage,medium_percentage,low_percentage
		foreach($milestones as $item)
		{
            $item['tcs_priority'] = $priorityCounters;
		    $item['tc_total'] = $planMetrics['total'];
		    // get amount of executed test cases for each priority before target_date
		    $item['results'] = $this->getPrioritizedResults($tplanID, $item['target_date'], $item['start_date']);
            $item['tc_completed'] = 0;
            
            // calculate percentage of executed test cases for each priority
            foreach( $pc as $key => $item_key)
            {
            	$item[$item_key] = $this->get_percentage($priorityCounters[$key], $item['results'][$key]);
            	$item['tc_completed'] += $item['results'][$key];
            }
            
            // amount of all executed tc with any priority before target_date / all test cases
            $item['percentage_completed'] = $this->get_percentage($item['tc_total'], $item['tc_completed']);
            
            foreach( $checks as $key => $item_key)
            {
            	// add 1 decimal places to expected percentages
            	$item[$checks[$key]] = number_format($item[$checks[$key]], 1);
            	
            	// check if target for each priority is reached
            	// show target as reached if expected percentage is greater than executed percentage
            	$item[$on_off[$key]] = ($item[$checks[$key]] > $item[$pc[$key]]) ? ON : OFF;
            }
            // BUGID 3820
		    $results[$item['id']] = $item;
	  	}
		return $results;
	}
	
	
	/**
	 * calculate percentage and format
	 * 
	 * @param int $total Total count
	 * @param int $parameter a parameter count
	 * @return string formatted percentage
	 */
	function get_percentage($total, $parameter)
	{
		$percentCompleted = $total > 0 ? (($parameter / $total) * 100) : 100;
		return number_format($percentCompleted,1);
	}


	// Work on ALL ACTIVE BUILDS
	function getExecCountersByBuildStatusOnlyWithTesterAssignment($id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;


		$statusCode =array_flip(array_keys($this->resultsCfg['status_label_for_exec_ui']));
		foreach($statusCode as $key => &$dummy)
		{
			$dummy = $this->resultsCfg['status_code'][$key];	
		}

		$activeBuilds = array_keys($this->get_builds($id,testplan::ACTIVE_BUILDS));
		$activeBuildsInClause = implode(",",$activeBuilds);
		$getOpt = array('outputFormat' => 'mapAccessByID' , 'addIfNull' => true);
		$platformSet = array_keys($this->getPlatforms($id,$getOpt));

		new dBug($platformSet);
		new dBug($activeBuilds); 
		$execCode = intval($this->assignment_types['testcase_execution']['id']);
		
		// Common sentece - reusable
		$sqlExec = 	"/* {$debugMsg} */" . 
					" SELECT  E.build_id,E.platform_id,E.status, count(0) AS exec_qty " .
					" FROM last_executions LE " .
					" /* Get execution status */ " .
					" JOIN {$this->tables['executions']} E ON E.id=LE.id " .
					" AND LE.build_id IN ({$activeBuildsInClause}) " .
					" /* Get feature id */ " .
					" JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.testplan_id = LE.testplan_id " .
					" AND TPTCV.platform_id = LE.platform_id AND TPTCV.tcversion_id = LE.tcversion_id " .
					" AND TPTCV.testplan_id = " . intval($id) .
					" /* Get only assigned items for executions on build set */ " .
					" /*LEFTPLACEHOLDER*/ JOIN {$this->tables['user_assignments']} UA ON UA.feature_id = TPTCV.id " .
					" AND UA.build_id = LE.build_id  AND UA.type = {$execCode} ";
				
		// get all execution status from DB Only for test cases with tester assigned			
		$sql = 	$sqlExec .		
				" /* FILTER ONLY ACTIVE BUILDS on target test plan */ " .
				" WHERE LE.testplan_id=" . intval($id) . 
				" AND LE.build_id IN ({$activeBuildsInClause}) " .
				" GROUP BY E.build_id,E.platform_id,E.status ";
	
		$keyCols = array('build_id','platform_id','status');
		$exec_with_tester = (array)$this->db->fetchRowsIntoMap3l($sqlExec,$keyCols);           
           
           
		echo $sql;

		// get total assignments
		
		$sql = 	"/* $debugMsg */ ".
				" SELECT COUNT(id) AS qty, build_id " . 
				" FROM {$this->tables['user_assignments']} " .
				" WHERE build_id IN ( " . $activeBuildsInClause . " ) " .
				" AND type = {$execCode} " . 
				" GROUP BY build_id ";

		$totalAssignedByBuild = (array)$this->db->fetchRowsIntoMap($sql,'build_id');

		// get all execution status from DB Only for test cases WITHOUT tester assigned			
		$sql = 	$sqlExec .		
				" WHERE LE.testplan_id=" . intval($id) . ' AND UA.feature_id IS NULL ' .  
				" GROUP BY E.build_id,E.platform_id,E.status ";
	
		echo $sql;
		// $exec_wo_tester = (array)$this->db->fetchMapRowsIntoMap($sql,'build_id','platform_id', 1);
	    $exec_wo_tester = (array)$this->db->fetchRowsIntoMap3l($sql,$keyCols);           


		new dBug($exec_with_tester);
		new dBug($totalAssignedByBuild);

		new dBug($exec_wo_tester);

		$info = array();
		foreach($activeBuilds as $buildID)
		{
			if( !isset($exec_with_tester[$buildID]) )
			{
				$exec_with_tester[$buildID] = array();
		    }
			foreach($platformSet as $platformID)
			{
				foreach($statusCode as $verbose => $code )
				{
					$exec_with_tester[$buildID][$platformID][] = array('build_id' => $buildID,
																	   'platform_id' => $platformID,
																	   'status' => $code, 'exec_qty' => 0);			
				}	
			}
		    
		    
		}
		new dBug($exec_with_tester);
		die();		
		
		/*		
		$info = array();
		foreach($activeBuilds as $buildID)
		{
			if( !isset($rs[$buildID]) )
			{
				$rs[$buildID] = array();
				foreach($platformSet as $platformID)
				{
					
				}
		    }
			
			if( isset($rs[$buildID]) )
			{
				// loop over all defined platforms
				foreach($platformSet as $platformID)
				{
					if( isset($rs[$buildID][$platformID] )
					{
						
					}
					else
					{
						// all counters with 0
							
					}	
				}
				

				foreach($rs[$buildID] as $platformExec)
				{
					// loop over results
					foreach($platformExec as $resultByStatus)
					{
												
					}
				}						
			}	
			
		}

		// foreach($this->resultsCfg['status_label_for_exec_ui' as $)
		if(!is_null($rsnr))
		{
			
		}
		*/
		
		return array($rs,$rsAll);
		
	}



	// Work on ALL ACTIVE BUILDS IGNORING Platforms
	function getExecCountersByBuildExecStatus($id, $opt=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$my['opt'] = array('getUnassigned' => false);
		$my['opt'] = array_merge($my['opt'], (array)$opt);
		
		$statusCode =array_flip(array_keys($this->resultsCfg['status_label_for_exec_ui']));
		foreach($statusCode as $key => &$dummy)
		{
			$dummy = $this->resultsCfg['status_code'][$key];	
		}

		$activeBuilds = array_keys($ab=$this->get_builds($id,testplan::ACTIVE_BUILDS));
		$buildsInClause = implode(",",$activeBuilds);
		$execCode = intval($this->assignment_types['testcase_execution']['id']);
		
		new dBug($ab);
		
		
		
		// This subquery is BETTER than the VIEW, need to understand why
		// Last Executions By Build (LEBB)
		$sqlLEBB = 	" SELECT EE.tcversion_id,EE.testplan_id,EE.build_id,MAX(EE.id) AS id " .
				  	" FROM {$this->tables['executions']} EE " . 
				   	" WHERE EE.testplan_id=" . intval($id) . 
					" AND EE.build_id IN ({$buildsInClause}) " .
				   	" GROUP BY EE.tcversion_id,EE.testplan_id,EE.build_id ";
		
		
		// Common sentece - reusable
		$sqlExec = 	"/* {$debugMsg} */" . 
					// " SELECT E.build_id AS build_id,COALESCE(E.status,'{$this->notRunStatusCode}') AS status, count(0) AS exec_qty " .
					" SELECT UA.build_id,COALESCE(E.status,'{$this->notRunStatusCode}') AS status, count(0) AS exec_qty " .

					" /* Get feature id with Tester Assignment */ " .
					" FROM {$this->tables['testplan_tcversions']} TPTCV " .

					" /*LEFTPLACEHOLDER*/ JOIN {$this->tables['user_assignments']} UA " .
					" ON UA.feature_id = TPTCV.id " .
					" AND UA.build_id IN ({$buildsInClause}) AND UA.type = {$execCode} " .

					" /* GO FOR Absolute LATEST exec ID by BUILD IGNORE  Platform */ " .
					" LEFT OUTER JOIN ({$sqlLEBB}) AS LEBB " .
					" ON  LEBB.testplan_id = TPTCV.testplan_id " .
					" AND LEBB.tcversion_id = TPTCV.tcversion_id " .
					" AND LEBB.testplan_id = " . intval($id) .

					" /* Get execution status INCLUDING NOT RUN */ " .
					" LEFT OUTER JOIN {$this->tables['executions']} E " .
					" ON  E.id = LEBB.id " .
					" AND E.build_id = LEBB.build_id " .
					" AND E.build_id IN ({$buildsInClause}) ";
				
				
		// get all execution status from DB Only for test cases with tester assigned			
		$sql = 	$sqlExec .		
				" /* FILTER ONLY ACTIVE BUILDS on target test plan */ " .
				" WHERE TPTCV.testplan_id=" . intval($id) . 
				" AND UA.build_id IN ({$buildsInClause}) " .
				" GROUP BY build_id,status ";
	
		// 
		echo '<br><b>' . __FUNCTION__ . '</b><br>'; 
		echo $sql . '<br>';
		// die();
		
        $exec['with_tester'] = (array)$this->db->fetchMapRowsIntoMap($sql,'build_id','status');              

		if( $my['opt']['getUnassigned'] )
		{
			// NEED TO CHECK 
			// get all execution status from DB Only for test cases WITHOUT tester assigned			
			$sqlExecLOJ = str_replace('/*LEFTPLACEHOLDER*/',' LEFT OUTER ',$sqlExec);
			$sql = $sqlExecLOJ .
					" WHERE LEBB.testplan_id=" . intval($id) . ' AND UA.feature_id IS NULL ' .  
					" GROUP BY E.build_id,E.status ";
		
	        $exec['wo_tester'] = (array)$this->db->fetchMapRowsIntoMap($sql,'build_id','status');              
		}


		// Need to Add info regarding:
		// - Add info for ACTIVE BUILD WITHOUT any execution. ???
		//   Hmm, think about Need to check is this way is better that request DBMS to do it.
		// - Execution status that have not happened
		
		foreach($exec as &$elem)
		{
			foreach($activeBuilds as $buildID)
			{
				if( !isset($elem[$buildID]) )
				{
					$elem[$buildID] = array();
			    }
				foreach($statusCode as $verbose => $code)
				{
						if(!isset($elem[$buildID][$code]))
						{
							$elem[$buildID][$code] = array('build_id' => $buildID,'status' => $code, 'exec_qty' => 0);			
						}												   
				}
			}
		}


		
		// get total assignments by BUILD ID
		$sql = 	"/* $debugMsg */ ".
				" SELECT COUNT(0) AS qty, UA.build_id " . 
				" FROM {$this->tables['user_assignments']} UA " .
				" JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.id = UA.feature_id " .
				" WHERE UA. build_id IN ( " . $buildsInClause . " ) " .
				" AND UA.type = {$execCode} " . 
				" GROUP BY build_id";

		$exec['total_assigned'] = (array)$this->db->fetchRowsIntoMap($sql,'build_id');
		$exec['active_builds'] = $ab;

		return $exec;
	}
	
                                      
	/**
	 *
	 * @internal revisions
	 *
	 * @since 1.9.4
	 * 20120429 - franciscom - TICKET 4989: Reports - Overall Build Status - refactoring and final business logic
	 **/
	function getOverallBuildStatusForRender($id)
	{
	   	$renderObj = null;
		$code_verbose = $this->getStatusForReports();
	    $labels = $this->resultsCfg['status_label'];
	    
		$metrics = $this->getExecCountersByBuildExecStatus($id);
	   	if( !is_null($metrics) )
	   	{
	   		$renderObj = new stdClass();

			$buildList = array_keys($metrics['active_builds']);
			$renderObj->info = array();	
		    foreach($buildList as $buildID)
		    {
				$totalRun = 0;
		    	$renderObj->info[$buildID]['build_name'] = $metrics['active_builds'][$buildID]['name']; 	
		    	$renderObj->info[$buildID]['total_assigned'] = $metrics['total_assigned'][$buildID]['qty']; 	

				$renderObj->info[$buildID]['details'] = array();
				
				$rf = &$renderObj->info[$buildID]['details'];
				foreach($code_verbose as $statusCode => $statusVerbose)
				{
					$rf[$statusVerbose] = array('qty' => 0, 'percentage' => 0);
					$rf[$statusVerbose]['qty'] = $metrics['with_tester'][$buildID][$statusCode]['exec_qty']; 	
					
					if( $renderObj->info[$buildID]['total_assigned'] > 0 ) 
					{
						$rf[$statusVerbose]['percentage'] = number_format(100 * 
																		  ($rf[$statusVerbose]['qty'] / 
															 			   $renderObj->info[$buildID]['total_assigned']),1);
					}
					
					$totalRun += $statusVerbose == 'not_run' ? 0 : $rf[$statusVerbose]['qty'];
				}
				$renderObj->info[$buildID]['percentage_completed'] =  number_format(100 * 
																					($totalRun / 
																					 $renderObj->info[$buildID]['total_assigned']),1);
		    }
		   	
		    foreach($code_verbose as $status_verbose)
		    {
		    	$l18n_label = isset($labels[$status_verbose]) ? lang_get($labels[$status_verbose]) : 
		                      lang_get($status_verbose); 
		    
		    	$renderObj->colDefinition[$status_verbose]['qty'] = $l18n_label;
		    	$renderObj->colDefinition[$status_verbose]['percentage'] = '[%]';
		    }
	
		}
		return $renderObj;
	}



	// Consider ONLY ACTIVE BUILDS
	function getExecCountersByKeywordExecStatus($id, $opt=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$my['opt'] = array('getUnassigned' => false, 'tprojectID' => 0);
		$my['opt'] = array_merge($my['opt'], (array)$opt);
		
		$statusCode = array_flip(array_keys($this->resultsCfg['status_label_for_exec_ui']));
		foreach($statusCode as $key => $dummy)
		{
			$statusCode[$key] = $this->resultsCfg['status_code'][$key];	
		}

		$activeBuilds = array_keys($ab=$this->get_builds($id,testplan::ACTIVE_BUILDS));
		$buildsInClause = implode(",",$activeBuilds);
		$execCode = intval($this->assignment_types['testcase_execution']['id']);
		
		
		// may be too brute force but ...
		if( ($tprojectID = $my['opt']['tprojectID']) == 0 )
		{
			$info = $this->tree_manager->get_node_hierarchy_info($id);
			$tprojectID = $info['parent_id'];
		} 
		$tproject_mgr = new testproject($this->db);
		$keywordSet = $tproject_mgr->get_keywords_map($tprojectID);
		$tproject_mgr = null;
		
		
		
		// This subquery is BETTER than the VIEW, need to understand why
		// Last Executions By Build (LEBB)
		$sqlLE = 	" SELECT EE.tcversion_id,EE.testplan_id,MAX(EE.id) AS id " .
				  	" FROM {$this->tables['executions']} EE " . 
				   	" WHERE EE.testplan_id=" . intval($id) . 
					" AND EE.build_id IN ({$buildsInClause}) " .
				   	" GROUP BY EE.tcversion_id,EE.testplan_id ";
		
		
		// Common sentece - reusable
		$sqlExec = 	"/* {$debugMsg} */" . 
					" SELECT TCK.keyword_id,COALESCE(E.status,'{$this->notRunStatusCode}') AS status, count(0) AS exec_qty " .

					" /* Get feature id with Tester Assignment */ " .
					" FROM {$this->tables['testplan_tcversions']} TPTCV " .

					" /*LEFTPLACEHOLDER*/ JOIN {$this->tables['user_assignments']} UA " .
					" ON UA.feature_id = TPTCV.id " .
					" AND UA.build_id IN ({$buildsInClause}) AND UA.type = {$execCode} " .

					" /* Get ONLY Test case versions that has AT LEAST one Keyword assigned */ ".
					" JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
					" ON NHTCV.id = TPTCV.tcversion_id " .
					" JOIN {$this->tables['testcase_keywords']} TCK " .
					" ON TCK.testcase_id = NHTCV.parent_id " .

					" /* GO FOR Absolute LATEST exec ID IGNORE BUILD AND Platform */ " .
					" LEFT OUTER JOIN ({$sqlLE}) AS LE " .
					" ON  LE.testplan_id = TPTCV.testplan_id " .
					" AND LE.tcversion_id = TPTCV.tcversion_id " .
					" AND LE.testplan_id = " . intval($id) .

					" /* Get execution status INCLUDING NOT RUN */ " .
					" LEFT OUTER JOIN {$this->tables['executions']} E " .
					" ON  E.id = LE.id " .
					" AND E.build_id IN ({$buildsInClause}) ";
				
				
		// get all execution status from DB Only for test cases with tester assigned			
		$sql = 	$sqlExec .		
				" /* FILTER ONLY ACTIVE BUILDS on target test plan */ " .
				" WHERE TPTCV.testplan_id=" . intval($id) . 
				" AND UA.build_id IN ({$buildsInClause}) " .
				" GROUP BY keyword_id,status ";
	
		// 
		echo '<br><b>' . __FUNCTION__ . '</b><br>'; 
		echo $sql . '<br>';
		// die();

		// $sql = "SELECT keyword, keyword_id,status
		
        $exec['with_tester'] = (array)$this->db->fetchMapRowsIntoMap($sql,'keyword_id','status');              

		// NEED TO UNDERSTAND if I'm goin to use this
		if( $my['opt']['getUnassigned'] )
		{
			// NEED TO CHECK 
			// get all execution status from DB Only for test cases WITHOUT tester assigned			
			$sqlExecLOJ = str_replace('/*LEFTPLACEHOLDER*/',' LEFT OUTER ',$sqlExec);
			$sql = $sqlExecLOJ .
					" WHERE LE.testplan_id=" . intval($id) . ' AND UA.feature_id IS NULL ' .  
					" GROUP BY E.build_id,E.status ";
		
	        $exec['wo_tester'] = (array)$this->db->fetchMapRowsIntoMap($sql,'build_id','status');              
		}

		
		foreach($exec as &$elem)
		{
			foreach($elem as $keywordID => $dummy)
			{
				foreach($statusCode as $verbose => $code)
				{
					if(!isset($elem[$keywordID][$code]))
					{
						$elem[$keywordID][$code] = array('keyword_id' => $keywordID,'status' => $code, 'exec_qty' => 0);			
					}						
				}
			}
		}

		// get total assignments by Keyword id
		$sql = 	"/* $debugMsg */ ".
				" SELECT COUNT(0) AS qty, TCK.keyword_id " . 
				" FROM {$this->tables['user_assignments']} UA " .
				" JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.id = UA.feature_id " .

				" /* Get ONLY Test case versions that has AT LEAST one Keyword assigned */ ".
				" JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
				" ON NHTCV.id = TPTCV.tcversion_id " .
				" JOIN {$this->tables['testcase_keywords']} TCK " .
				" ON TCK.testcase_id = NHTCV.parent_id " .

				" WHERE UA. build_id IN ( " . $buildsInClause . " ) " .
				" AND UA.type = {$execCode} " . 
				" GROUP BY keyword_id";

		$exec['total_assigned'] = (array)$this->db->fetchRowsIntoMap($sql,'keyword_id');
		$exec['keywords'] = $keywordSet;

	
		return $exec;
	}


	/**
	 *
	 * @internal revisions
	 *
	 * @since 1.9.4
	 * 20120429 - franciscom - 
	 **/
	function getStatusTotalsByKeywordForRender($id)
	{
	   	$renderObj = null;
		$code_verbose = $this->getStatusForReports();
	    $labels = $this->resultsCfg['status_label'];
	    
		$metrics = $this->getExecCountersByKeywordExecStatus($id);
	   	if( !is_null($metrics) )
	   	{
	   		$renderObj = new stdClass();

			// now we are going to loop over keyword set, that we got previously
			// ORDERED BY keyword ASC
			$itemList = array_keys($metrics['keywords']);
			$renderObj->info = array();	
		    foreach($itemList as $itemID)
		    {
		    	if( isset($metrics['with_tester'][$itemID]) )
		    	{
					$totalRun = 0;
		    		$renderObj->info[$itemID]['type'] = 'keyword';
		    		$renderObj->info[$itemID]['name'] = $metrics['keywords'][$itemID]; 	
		    		$renderObj->info[$itemID]['total_tc'] = $metrics['total_assigned'][$itemID]['qty']; 	
					$renderObj->info[$itemID]['details'] = array();
					
					$rf = &$renderObj->info[$itemID]['details'];
					foreach($code_verbose as $statusCode => $statusVerbose)
					{
						$rf[$statusVerbose] = array('qty' => 0, 'percentage' => 0);
						$rf[$statusVerbose]['qty'] = $metrics['with_tester'][$itemID][$statusCode]['exec_qty']; 	
						
						if( $renderObj->info[$itemID]['total_tc'] > 0 ) 
						{
							$rf[$statusVerbose]['percentage'] = number_format(100 * 
																			  ($rf[$statusVerbose]['qty'] / 
																 			   $renderObj->info[$itemID]['total_tc']),1);
						}
						$totalRun += $statusVerbose == 'not_run' ? 0 : $rf[$statusVerbose]['qty'];
					}
					$renderObj->info[$itemID]['percentage_completed'] =  number_format(100 * 
																						($totalRun / 
																						 $renderObj->info[$itemID]['total_tc']),1);
		    	}
		    }
		   	
		    foreach($code_verbose as $status_verbose)
		    {
		    	$l18n_label = isset($labels[$status_verbose]) ? lang_get($labels[$status_verbose]) : 
		                      lang_get($status_verbose); 
		    
		    	$renderObj->colDefinition[$status_verbose]['qty'] = $l18n_label;
		    	$renderObj->colDefinition[$status_verbose]['percentage'] = '[%]';
		    }
	
		}
		return $renderObj;
	}



	function getExecCountersByPlatformExecStatus($id, $opt=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$my['opt'] = array('getUnassigned' => false, 'tprojectID' => 0);
		$my['opt'] = array_merge($my['opt'], (array)$opt);
		
		$statusCode = array_flip(array_keys($this->resultsCfg['status_label_for_exec_ui']));
		foreach($statusCode as $key => $dummy)
		{
			$statusCode[$key] = $this->resultsCfg['status_code'][$key];	
		}

		$activeBuilds = array_keys($ab=$this->get_builds($id,testplan::ACTIVE_BUILDS));
		$buildsInClause = implode(",",$activeBuilds);
		$execCode = intval($this->assignment_types['testcase_execution']['id']);

		$getOpt = array('outputFormat' => 'mapAccessByID', 'outputDetails' => 'name', 'addIfNull' => true);
		$platformSet = $this->getPlatforms($id,$getOpt);
		
		
	
		// This subquery is BETTER than the VIEW, need to understand why
		// Latest Executions by Platform
		// 
		$sqlLEBP = 	" SELECT EE.tcversion_id,EE.testplan_id,EE.platform_id,MAX(EE.id) AS id " .
				  	" FROM {$this->tables['executions']} EE " . 
				   	" WHERE EE.testplan_id=" . intval($id) . 
					" AND EE.build_id IN ({$buildsInClause}) " .
				   	" GROUP BY EE.tcversion_id,EE.testplan_id,EE.platform_id ";
		
		
		$sqlExec = 	"/* {$debugMsg} */" . 
					" SELECT TPTCV.platform_id,COALESCE(E.status,'{$this->notRunStatusCode}') AS status, count(0) AS exec_qty " .

					" /* Get feature id with Tester Assignment */ " .
					" FROM {$this->tables['testplan_tcversions']} TPTCV " .

					" /*LEFTPLACEHOLDER*/ JOIN {$this->tables['user_assignments']} UA " .
					" ON UA.feature_id = TPTCV.id " .
					" AND UA.build_id IN ({$buildsInClause}) AND UA.type = {$execCode} " .

					" /* GO FOR Absolute LATEST exec ID (is exists), IGNORE BUILD */ " .
					" LEFT OUTER JOIN ({$sqlLEBP}) AS LEBP " .
					" ON  LEBP.tcversion_id = TPTCV.tcversion_id " .
					" AND LEBP.platform_id = TPTCV.platform_id " .
					" AND LEBP.testplan_id = TPTCV.testplan_id " .
					" AND LEBP.testplan_id = " . intval($id) .

					" /* Get execution status INCLUDING NOT RUN */ " .
					" LEFT OUTER JOIN {$this->tables['executions']} E " .
					" ON  E.id = LEBP.id " .
					" AND E.build_id IN ({$buildsInClause}) ";
				
				
		// get all execution status from DB AND NOT RUN, Only for test cases with tester assigned			
		$sql = 	$sqlExec .		
				" /* FILTER ONLY ACTIVE BUILDS on target test plan */ " .
				" WHERE TPTCV.testplan_id=" . intval($id) . 
				" AND UA.build_id IN ({$buildsInClause}) " .
				" GROUP BY platform_id,status ";
	
		// 
		echo '<br><b>' . __FUNCTION__ . '</b><br>'; 
		echo $sql . '<br>';
		// die();

		// $sql = "SELECT keyword, keyword_id,status
		
        $exec['with_tester'] = (array)$this->db->fetchMapRowsIntoMap($sql,'platform_id','status');              


		// add basic data for exec status not found on DB.
		foreach($exec as &$elem)
		{
			foreach($elem as $itemID => $dummy)
			{
		 		foreach($statusCode as $verbose => $code)
		 		{
		 			if(!isset($elem[$itemID][$code]))
		 			{
		 				$elem[$itemID][$code] = array('keyword_id' => $itemID,'status' => $code, 'exec_qty' => 0);			
		 			}						
		 		}
		 	}
		}

		// get total assignments by Platform id
		$sql = 	"/* $debugMsg */ ".
				" SELECT COUNT(0) AS qty, TPTCV.platform_id " . 
				" FROM {$this->tables['user_assignments']} UA " .
				" JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.id = UA.feature_id " .

				" WHERE UA. build_id IN ( " . $buildsInClause . " ) " .
				" AND UA.type = {$execCode} " . 
				" GROUP BY platform_id";

		$exec['total_assigned'] = (array)$this->db->fetchRowsIntoMap($sql,'platform_id');
		$exec['platforms'] = $platformSet;

	
		return $exec;
	}



	/**
	 *
	 * @internal revisions
	 *
	 * @since 1.9.4
	 * 20120429 - franciscom - 
	 **/
	function getStatusTotalsByPlatformForRender($id)
	{
	   	$renderObj = null;
		$code_verbose = $this->getStatusForReports();
	    $labels = $this->resultsCfg['status_label'];
	    
		$metrics = $this->getExecCountersByPlatformExecStatus($id);
		echo __FUNCTION__ .'<br>';
		new dBug($metrics);
		
	   	if( !is_null($metrics) )
	   	{
	   		$renderObj = new stdClass();

			// now we are going to loop over keyword set, that we got previously
			// ORDERED BY keyword ASC
			$itemList = array_keys($metrics['platforms']);
			$renderObj->info = array();	
		    foreach($itemList as $itemID)
		    {
		    	if( isset($metrics['with_tester'][$itemID]) )
		    	{
					$totalRun = 0;
		    		$renderObj->info[$itemID]['type'] = 'platform';
		    		$renderObj->info[$itemID]['name'] = $metrics['platforms'][$itemID]; 	
		    		$renderObj->info[$itemID]['total_tc'] = $metrics['total_assigned'][$itemID]['qty']; 	
					$renderObj->info[$itemID]['details'] = array();
					
					$rf = &$renderObj->info[$itemID]['details'];
					foreach($code_verbose as $statusCode => $statusVerbose)
					{
						$rf[$statusVerbose] = array('qty' => 0, 'percentage' => 0);
						$rf[$statusVerbose]['qty'] = $metrics['with_tester'][$itemID][$statusCode]['exec_qty']; 	
						
						if( $renderObj->info[$itemID]['total_tc'] > 0 ) 
						{
							$rf[$statusVerbose]['percentage'] = number_format(100 * 
																			  ($rf[$statusVerbose]['qty'] / 
																 			   $renderObj->info[$itemID]['total_tc']),1);
						}
						$totalRun += $statusVerbose == 'not_run' ? 0 : $rf[$statusVerbose]['qty'];
					}
					$renderObj->info[$itemID]['percentage_completed'] =  number_format(100 * 
																						($totalRun / 
																						 $renderObj->info[$itemID]['total_tc']),1);
		    	}
		    }
		   	
		    foreach($code_verbose as $status_verbose)
		    {
		    	$l18n_label = isset($labels[$status_verbose]) ? lang_get($labels[$status_verbose]) : 
		                      lang_get($status_verbose); 
		    
		    	$renderObj->colDefinition[$status_verbose]['qty'] = $l18n_label;
		    	$renderObj->colDefinition[$status_verbose]['percentage'] = '[%]';
		    }
	
		}
		return $renderObj;
	}

	
}
?>