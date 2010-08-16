<?php

/**
 * 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Andreas Simon
 * @copyright 2010, TestLink community
 * @version CVS: $Id: build_progress.class.php,v 1.2 2010/08/16 12:35:20 asimon83 Exp $
 *
 * Generate information about the progress by tester per build.
 * 
 * @internal revisions:
 * 20100731 - asimon - initial commit
 * 		
 */

class build_progress extends tlObjectWithDB {
	
	public $tplan_mgr = null;
	public $tproject_mgr = null;
	
	private $na_string = null;
	private $tplan_id = 0;
	private $tplan_info = null;
	private $build_set = null;
	private $build_list = null;
	private $status_map = array();
	private $assigned_execution_map = array();
	private $unassigned_execution_map = array();
	private $results_matrix = array();
	
	public function get_assigned_execution_map() {
		return $this->assigned_execution_map;
	}
	
	public function get_unassigned_execution_map() {
		return $this->unassigned_execution_map;
	}
	
	public function get_status_map() {
		return $this->status_map;
	}
	
	public function get_results_matrix() {
		return $this->results_matrix;
	}
	
	public function get_build_set() {
		return $this->build_set;
	}
	
	public function __construct(&$db, $tplan_id) {
		
		parent::__construct($db);
		
		$this->na_string = lang_get('not_aplicable');
		
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
	}
	
	private function load_builds() {
		$this->build_set = $this->tplan_mgr->get_builds($this->tplan_id);
		
		if (is_array($this->build_set)) {
			$keys = array_keys($this->build_set);
			$this->build_list =  implode(",", $keys);
		}
	}

	private function load_assigned_execution_map() {
		foreach ($this->build_set as $build_id => $build_info) {
		$sql = " SELECT UA.build_id AS build_id, UA.feature_id AS feature_id, " .
		       "        UA.user_id as user_id, " .
		       "        TPTCV.testplan_id AS testplan_id, TPTCV.tcversion_id AS tcversion_id, " .
		       "        TPTCV.platform_id AS platform_id, " .
		       "        E.status AS status, E.id as execution_id, E.tester_id as tester_id " .
		       " FROM user_assignments UA " .
		       " LEFT OUTER JOIN testplan_tcversions TPTCV " . 
		       "                 ON UA.feature_id = TPTCV.id " .
		       " LEFT OUTER JOIN executions E " . 
		       "                 ON TPTCV.tcversion_id = E.tcversion_id " . 
		       "                 AND UA.build_id = E.build_id " .
		       "                 AND TPTCV.platform_id = E.platform_id " .
		       //" WHERE E.status IS NOT NULL AND UA.type = 1 " .
		       " WHERE UA.type = 1 " .
		       "       AND UA.build_id = {$build_id} " .
		       " ORDER BY E.id DESC ";
		
		$this->assigned_execution_map[$build_id] = $this->db->fetchMapRowsIntoMap($sql, 'user_id', 'tcversion_id', 
		                                                               database::CUMULATIVE);
		}
	}
	
	private function load_unassigned_execution_map() {
		foreach ($this->build_set as $build_id => $build_info) {
		$sql = " SELECT E.id as execution_id, E.status AS status, " . 
		       "        E.build_id as build_id, E.tester_id as tester_id, " .
		       "        TPTCV.id AS feature_id, TPTCV.testplan_id AS testplan_id, " . 
		       "        TPTCV.tcversion_id AS tcversion_id, TPTCV.platform_id AS platform_id, " .
		       "        UA.user_id as user_id " .
		       " FROM executions E " .
		       " LEFT OUTER JOIN testplan_tcversions TPTCV " .
		       "                 ON TPTCV.tcversion_id = E.tcversion_id " . 
		       "                    AND TPTCV.platform_id = E.platform_id " . 
		       "                    AND TPTCV.testplan_id = E.testplan_id " .
		       " LEFT OUTER JOIN user_assignments UA " .
		       "                 ON UA.feature_id = feature_id " .
		       "                    AND feature_id = TPTCV.id " .
		       "                    AND UA.build_id = E.build_id " .
		       " WHERE E.build_id = {$build_id} AND UA.user_id IS NULL " .
		       " ORDER BY E.id DESC ";
		
		$this->unassigned_execution_map[$build_id] = $this->db->fetchRowsIntoMap($sql, 'tcversion_id', 
		                                                                 database::CUMULATIVE);
		}
	}
	
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
	}
	
	private function compute_results($build_id, $user_id, $map) {
		
		$counters = array();
		
		if ($user_id == TL_NO_USER) {
			$counters['total'] = $this->na_string;
			$nr_count = $this->na_string;
		} else {
			$counters['total'] = $this->tplan_mgr->assignment_mgr->get_count_of_assignments_for_build_id(
			                                                       $build_id, false, $user_id);
			$nr_count = $this->tplan_mgr->assignment_mgr->get_not_run_tc_count_per_build($build_id, 
			                                                                             false, 
			                                                                             $user_id);
		}
		
		$temp = array();
		
		foreach ($map as $tcversion_id => $execution_info) {
			// latest execution is always at index 0 because of ordered SQL statement
			$code = $execution_info[0]['status'];
			$temp[$code] = isset($temp[$code]) ? $temp[$code] + 1 : 1;
		}
		
		foreach ($this->status_map as $status => $code) {
			$counters[$status] = array('count' => 0, 'percentage' => 0);

			if (!is_numeric($counters['total']) || $counters['total'] == 0) {
				$counters[$status]['percentage'] = $this->na_string;
			}
			
			if (isset($temp[$code])) {
				$counters[$status]['count'] = $temp[$code];
				
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
			$percent = $this->na_string;
			$progress = $this->na_string;
		}
		
		$counters['not_run']['count'] = $nr_count;
		$counters['not_run']['percentage'] = $percent;		
		$counters['progress'] = $progress;
		
		return $counters;
	}
}

?>