<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Andreas Simon
 * @copyright 2010, TestLink community
 * @version CVS: $Id: build_progress.class.php,v 1.9.2.1 2011/02/11 14:51:10 asimon83 Exp $
 * 
 * @internal revisions:
 * 20100929 - asimon - corrected values for multiple platforms
 * 20100927 - asimon - corrected count of not run test cases
 * 20100821 - asimon - BUGID 3682
 * 20100820 - asimon - added last missing comments, little refactorization for table prefix
 * 20100731 - asimon - initial commit		
 */

/**
 * This class extends tlObjectWithDB to load and compute the data needed
 * to generate the build based results table in reports. 
 * It generates information about the progress by tester per build.
 * First, the necessary data from execution and assignment tables is loaded to
 * generate a map of those executions which have been done on builds on which
 * a tester has been assigned to the respective testcases. 
 * Then, those executions will get loaded and written to a second map, which
 * have been done on testcases that have no tester assigned on the build
 * which has been executed. This information is needed to have the possibility
 * to generate also data for old builds (before database migration from 1.9)
 * without user assignments so that they can also be shown in statistics.
 * Together, these maps will be computed to generate all the data
 * and numbers, percentages and progress which will be displayed on resulting table.
 * 
 * @author Andreas Simon
 * @package TestLink
 * 
 * @internal revisions
 * 20110211 - asimon - BUGID 4192: show only open builds by default
 */
class build_progress extends tlObjectWithDB {
	
	public $tplan_mgr = null;
	public $tproject_mgr = null;
	
	private $labels = null;
	private $tplan_id = 0;
	private $tplan_info = null;
	private $build_set = null;
	private $build_list = null;
	private $status_map = array();
	private $assigned_execution_map = array();
	private $unassigned_execution_map = array();
	private $results_matrix = array();
	
	/**
	 * Getter for the map of assigned executions.
	 * 
	 * @author Andreas Simon
	 * @return array
	 */
	public function get_assigned_execution_map() {
		return $this->assigned_execution_map;
	}
	
	/**
	 * Getter for the map of unassigned executions (results on testcases which have no tester assigned).
	 * Useful to consider executions by users with the given rights to execute testcases which
	 * are not assigned to them. 
	 * Also the only way to consider old builds from migrated TestLink version 1.8
	 * which have no user assignments per build yet, they could otherwise not be used
	 * for statistics.
	 * 
	 * @author Andreas Simon
	 * @return array
	 */
	public function get_unassigned_execution_map() {
		return $this->unassigned_execution_map;
	}
	
	/**
	 * Getter for the status map. Contains the configured testcase statuses
	 * in an array of the form statuslabel (key) => statuscode (value) for usage
	 * of generating column headers for each status in the table.
	 * 
	 * @author Andreas Simon
	 * @return array
	 */
	public function get_status_map() {
		return $this->status_map;
	}
	
	/**
	 * Getter for the complete, computed result matrix.
	 * 
	 * @author Andreas Simon
	 * @return array
	 */
	public function get_results_matrix() {
		return $this->results_matrix;
	}
	
	/**
	 * Getter for the build set of this testplan.
	 * 
	 * @author Andreas Simon
	 * @return array
	 */
	public function get_build_set() {
		return $this->build_set;
	}
	
	
	/**
	 * Load the necessary data from execution and assignment tables and generate the
	 * build result matrix by computing this data.
	 * 
	 * @author Andreas Simon
	 * @param Database $db reference to database object
	 * @param int $tplan_id Testplan ID for which the table shall be generated
	 */
	public function __construct(&$db, $tplan_id, $show_closed_builds) {
		
		parent::__construct($db);
		
		$this->labels = array('na' => lang_get('not_aplicable'));
		
		// BUGID 4192
		$this->show_closed_builds = $show_closed_builds;
		
		$res_cfg = config_get('results');
		foreach ($res_cfg['status_label_for_exec_ui'] as $status => $label) {
			$this->status_map[$status] = $res_cfg['status_code'][$status];
		}
		
		$this->tplan_mgr = new testplan($this->db);
		$this->tproject_mgr = new testproject($this->db);
				
		$this->tplan_id = is_numeric($tplan_id) ? $tplan_id : 0;
		
		if ($this->tplan_id) {
			$this->tplan_info = $this->tplan_mgr->get_by_id($this->tplan_id);
			
			$this->load_builds();
			$this->load_assigned_execution_map();
			$this->load_unassigned_execution_map();
			$this->build_results_matrix();
		}
	} // end of method
	
	/**
	 * Load build IDs for this testplan.
	 * 
	 * @author Andreas Simon
	 */
	private function load_builds() {
		// BUGID 3682
		$option = $this->show_closed_builds ? null : testplan::GET_OPEN_BUILD;
		$this->build_set = $this->tplan_mgr->get_builds($this->tplan_id, testplan::GET_ACTIVE_BUILD, $option);
		
		if (is_array($this->build_set)) {
			$keys = array_keys($this->build_set);
			$this->build_list =  implode(",", $keys);
		}
	} // end of method

	/**
	 * Load all assigned executions from database and build a map from it for each build. 
	 * The user assignments will be used as the base, that means we get only
	 * execution results for testcases which have a tester assigned for this build,
	 * no results for testcases that have no tester assigned or other builds.
	 * 
	 * @author Andreas Simon
	 */
	private function load_assigned_execution_map() {
		foreach ($this->build_set as $build_id => $build_info) {
		$sql = " SELECT UA.build_id AS build_id, UA.feature_id AS feature_id, " .
		       "        UA.user_id as user_id, " .
		       "        TPTCV.testplan_id AS testplan_id, TPTCV.tcversion_id AS tcversion_id, " .
		       "        TPTCV.platform_id AS platform_id, " .
		       "        E.status AS status, E.id as execution_id, E.tester_id as tester_id " .
		       " FROM {$this->tables['user_assignments']} UA " .
		       " LEFT OUTER JOIN {$this->tables['testplan_tcversions']} TPTCV " .
		       "                 ON UA.feature_id = TPTCV.id " .
		       " LEFT OUTER JOIN {$this->tables['executions']} E " . 
		       "                 ON TPTCV.tcversion_id = E.tcversion_id " . 
		       "                 AND UA.build_id = E.build_id " .
		       "                 AND TPTCV.platform_id = E.platform_id " .
		       " WHERE UA.type = 1 " .
		       "       AND UA.build_id = {$build_id} " .
		       " ORDER BY E.id DESC ";
		
		$this->assigned_execution_map[$build_id] = $this->db->fetchMapRowsIntoMap($sql, 'user_id', 'tcversion_id', 
		                                                               database::CUMULATIVE);
		}
	} // end of method
	
	/**
	 * This is the counterpart to load_assigned_executions. It loads those results from the
	 * executions table which were stored for testcases that have no tester assigned on
	 * that build. These will later appear on the results matrix as "unassigned executions".
	 * It happens when e.g. Admin or other user with given rights executes an unassigned testcase.
	 * It is also the only form in which build based results for old builds from TestLink version 1.8
	 * without migrated user assignments will be displayed, since there were no user assignments 
	 * on build level before version 1.9.
	 * 
	 * @author Andreas Simon
	 */
	private function load_unassigned_execution_map() {
		foreach ($this->build_set as $build_id => $build_info) {
		$sql = " SELECT E.id as execution_id, E.status AS status, " . 
		       "        E.build_id as build_id, E.tester_id as tester_id, " .
		       "        TPTCV.id AS feature_id, TPTCV.testplan_id AS testplan_id, " . 
		       "        TPTCV.tcversion_id AS tcversion_id, TPTCV.platform_id AS platform_id, " .
		       "        UA.user_id as user_id " .
		       " FROM {$this->tables['executions']} E " .
		       " LEFT OUTER JOIN {$this->tables['testplan_tcversions']} TPTCV " .
		       "                 ON TPTCV.tcversion_id = E.tcversion_id " . 
		       "                    AND TPTCV.platform_id = E.platform_id " . 
		       "                    AND TPTCV.testplan_id = E.testplan_id " .
		       " LEFT OUTER JOIN {$this->tables['user_assignments']} UA " .
		       "                 ON UA.feature_id = feature_id " .
		       "                    AND feature_id = TPTCV.id " .
		       "                    AND UA.build_id = E.build_id " .
		       " WHERE E.build_id = {$build_id} AND UA.user_id IS NULL " .
		       " ORDER BY E.id DESC ";
		
		$this->unassigned_execution_map[$build_id] = $this->db->fetchRowsIntoMap($sql, 'tcversion_id', 
		                                                                 database::CUMULATIVE);
		}
	} // end of method
	
	/**
	 * Build the result matrix for the builds and testers. After assigned and unassigned executions
	 * have been retrieved from database, this method combines both maps to one resulting map
	 * with computed statistics which can directly be used to build an ExtJS table from it.
	 * 
	 * @author Andreas Simon
	 */
	private function build_results_matrix() {
		
		// count assigned executions
		foreach ($this->assigned_execution_map as $build_id => $build_executions) {
			if (!is_null($build_executions)) {
				foreach ($build_executions as $user_id => $tcversion_executions) {
					$counters = $this->compute_results($build_id, $user_id, $tcversion_executions);
					$this->results_matrix[$build_id][$user_id] = $counters;
				}
			}
		}
		
		// count unassigned executions
		foreach ($this->unassigned_execution_map as $build_id => $build_executions) {
			if (!is_null($build_executions)) {
				$counters = $this->compute_results($build_id, TL_NO_USER, $build_executions);
				$this->results_matrix[$build_id][TL_NO_USER] = $counters;
			}
		}
	} // end of method
	
	/**
	 * Get the computed statistical values about testcase statuses and their percentage, 
	 * including complete progress percentage, 
	 * when building the result matrix table for each build and tester. 
	 * 
	 * @author Andreas Simon
	 * @param int $build_id
	 * @param int $user_id
	 * @param array $map
	 * @uses assignment_mgr
	 *
	 * @internal revisions:
	 * 20100929 - asimon - corrected values for multiple platforms
	 */
	private function compute_results($build_id, $user_id, $map) {

		$counters = array();
		
		if ($user_id == TL_NO_USER) {
			$counters['total'] = $this->labels['na'];
			$nr_count = $this->labels['na'];
		} else {
			$counters['total'] = $this->tplan_mgr->assignment_mgr->get_count_of_assignments_for_build_id(
			                                                       $build_id, false, $user_id);
			// workaround for incorrect not run value
			//$nr_count = $this->tplan_mgr->assignment_mgr->get_not_run_tc_count_per_build($build_id,
			//                                                                             false,
			//                                                                             $user_id);
			$nr_count = $counters['total'];
		}
		
		$temp = array();
		
		foreach ($map as $tcversion_id => $execution_info) {
			// latest execution is always at index 0 because of ordered SQL statement

			// 20100929 - asimon - corrected values for multiple platforms
			$platforms = array();
			foreach($execution_info as $key => $info) {
				if (!in_array($info['platform_id'], $platforms)) {
					$code = $info['status'];
					$temp[$code] = isset($temp[$code]) ? $temp[$code] + 1 : 1;
					$platforms[] = $info['platform_id'];
				}
			}
		}
		
		foreach ($this->status_map as $status => $code) {
			$counters[$status] = array('count' => 0, 'percentage' => 0);

			if (!is_numeric($counters['total']) || $counters['total'] == 0) {
				$counters[$status]['percentage'] = $this->labels['na'];
			}
			
			if (isset($temp[$code])) {
				$counters[$status]['count'] = $temp[$code];

				if ($user_id != TL_NO_USER) {
					$nr_count = $nr_count - $temp[$code];
				}

				if ($counters[$status]['count'] != 0
				&& is_numeric($counters['total']) && $counters['total'] != 0) {
					$percent = $counters[$status]['count'] / $counters['total'] * 100;
					$percent = number_format($percent, 2);
					$counters[$status]['percentage'] = $percent;
				}
			}			
		}
		
		// add not run and total progress
		if (is_numeric($counters['total']) && $counters['total'] != 0) {
		//&& is_numeric($nr_count) && $nr_count != 0) {
			$percent = $nr_count / $counters['total'] * 100;
			$percent = number_format($percent, 2);
			$progress = number_format(100 - $percent, 2);
		} else {
			$percent = $this->labels['na'];
			$progress = $this->labels['na'];
		}
		
		$counters['not_run']['count'] = $nr_count;
		$counters['not_run']['percentage'] = $percent;		
		$counters['progress'] = $progress;
		
		return $counters;
	} // end of method
}// end of class
?>