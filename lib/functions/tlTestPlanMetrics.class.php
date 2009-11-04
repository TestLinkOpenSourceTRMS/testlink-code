<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package 	TestLink
 * @author 		Kevin Levy, franciscom
 * @copyright 	2004-2009, TestLink community 
 * @version    	CVS: $Id: tlTestPlanMetrics.class.php,v 1.1 2009/11/04 08:10:14 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 * @uses		config.inc.php 
 * @uses		common.php 
 *
 * @internal Revisions:
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
    $this->priorityLevelsCfg = config_get('priority_levels');
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
	 * @param timestamp $milestoneDate - (optional) milestone deadline
	 * @return array with three priority counters
	 */
	public function getPrioritizedResults($tplanID,$milestoneDate = null)
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
					" WHERE TPTCV.testplan_id = {$this->testPlanID} " .
					" AND TPTCV.platform_id = E.platform_id " .
					" AND E.testplan_id = {$tplanID} " .
					" AND NOT E.status = '{$this->map_tc_status['not_run']}' " . 
					" AND TCV.importance={$importance} AND TPTCV.urgency={$urgency}";
				
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


	/**
	 * 
	 */
	function getMilestonesMetrics($tplanID, $milestoneSet=null)
	{
        // new dBug($tplanID);
        
		$results = null;
        $planMetrics = $this->getStatusTotals($tplanID);
		$milestones =  is_null($milestoneSet) ? $this->get_milestones($tplanID) : $milestoneSet;			
		$priorityCounters = $this->getPrioritizedTestCaseCounters($tplanID);

        $pc = array(LOW => 'result_low_percentage', MEDIUM => 'result_high_percentage',
                    HIGH => 'result_high_percentage' );
        
        $checks = array(LOW => 'low_percentage', MEDIUM => 'medium_percentage',
                        HIGH => 'high_percentage' );

        $on_off = array(LOW => 'low_incomplete', MEDIUM => 'medium_incomplete',
                        HIGH => 'high_incomplete' );
        
        // Important:
        // key already defined on item: high_percentage,medium_percentage,low_percentage
		foreach($milestones as $item)
		{
            // new dBug($item);
		    $item['tc_total'] = $planMetrics['total'];
		    $item['results'] = $this->getPrioritizedResults($item['target_date']);
            $item['tc_completed'] = 0;
            foreach( $pc as $key => $item_key)
            {
            	$pc[$key] = $pc[$key] > 0 ?  ($item['results'][$key] / $pc[$key]) * 100 : 0;
            	$item[$item_key] = number_format($pc[$key],1);
            	$item['tc_completed'] = $item['results'][$key];
            }
		    		    
       		// $item['low_incomplete'] = OFF;
        	// $item['medium_incomplete'] = OFF;
	    	// $item['high_incomplete'] = OFF;
	    	$item['all_incomplete'] = OFF;
            
            foreach( $checks as $key => $item_key)
            {
            	// echo $key . '<br>';
            	// echo $on_off[$key] . '<br>';
            	// echo $pc[$key] . '<br>';
            	// echo $checks[$item_key] . '<br>';
            	
            	$item[$on_off[$key]] = ($pc[$key] < $item[$checks[$key]]) ? ON : OFF; 
            }

		    if ($item['percentage_completed'] < $item['low_percentage'])
		    {
		    	$item['all_incomplete'] = ON;
        	}
        
		    foreach( $checks as $key => $item_key)
            {
            	$item[$checks[$key]] = number_format($item[$checks[$key]], 2);
            }	
		    $results[$item['target_date']] = $item;
	  	}
		return $results;
	}
}
?>