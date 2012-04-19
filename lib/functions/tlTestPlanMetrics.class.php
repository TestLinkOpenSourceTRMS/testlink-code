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
	//private $resultsCfg;
	//private $testCaseCfg;
	private $map_tc_status;
	private $tc_status_for_statistics;

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


	
}
?>