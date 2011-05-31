<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package 	TestLink
 * @author 		Kevin Levy, franciscom
 * @copyright 	2004-2009, TestLink community 
 * @version    	CVS: $Id: tlTestPlanMetrics.class.php,v 1.7 2010/10/18 14:55:36 mx-julian Exp $
 * @link 		http://www.teamst.org/index.php
 * @uses		config.inc.php 
 * @uses		common.php 
 *
 * @internal Revisions:
 * 20110415 - Julian - BUGID 4418 - Clean up priority usage within Testlink
 * 20101018 - Julian - BUGID 2236 - Milestones Report broken
 *                     BUGID 3830 - Milestone is not shown on Report more than one milestone
                                    have the same target date
                       BUGID 2770 - Start date for milestones
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
	private $resultsCfg;
	private $testCaseCfg='';
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

	
}
?>