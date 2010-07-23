<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Manager for assignment activities
 *
 * @package 	TestLink
 * @author 		Francisco Mancardi
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: assignment_mgr.class.php,v 1.12 2010/07/23 11:39:03 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal revisions:
 * 
 * 20100722 - asimon - BUGID 3406 - added copy_assignments(), delete_by_build_id(),
 *                                  get_not_run_tc_count_per_build() and
 *                                  get_count_of_assignments_per_build_id(),
 *                                  modified assign() and update() to include build_id
 */
 
/**
 * class manage assignment users for testing
 * @package 	TestLink
 */ 
class assignment_mgr extends tlObjectWithDB
{

	function assignment_mgr(&$db) 
	{
	    parent::__construct($db);
	}

	/*
	 $key_field: contains the filename that has to be used as the key of
	             the returned hash.    
	*/
	function get_available_types($key_field='description') 
	{
		static $hash_types;
		if (!$hash_types)
		{
	    	$sql = "SELECT * FROM {$this->tables['assignment_types']}";
			$hash_types = $this->db->fetchRowsIntoMap($sql,$key_field);
		}
		return $hash_types;
	}

  /*
   $key_field: contains the name column that has to be used as the key of
               the returned hash.    
  */
	function get_available_status($key_field='description') 
	{
		static $hash_types;
		if (!$hash_types)
		{
			$sql = " SELECT * FROM {$this->tables['assignment_status']} "; 
			$hash_types = $this->db->fetchRowsIntoMap($sql,$key_field);
		}
		
		return $hash_types;
	}

	// $feature_id can be an scalar or an array
	 
	function delete_by_feature_id($feature_id) 
	{
	    if( is_array($feature_id) )
	    {
			$feature_id_list = implode(",",$feature_id);
			$where_clause = " WHERE feature_id IN ($feature_id_list) ";
	    }
	    else
	    {
			$where_clause = " WHERE feature_id={$feature_id}";
	    }
		$sql = " DELETE FROM {$this->tables['user_assignments']}  {$where_clause}"; 
		$result = $this->db->exec_query($sql);
	}


  	/**
  	 * 
  	 * @param $feature_map
  	 * $feature_map['feature_id']['user_id']
	 * $feature_map['feature_id']['type']
  	 * $feature_map['feature_id']['status']
  	 * $feature_map['feature_id']['assigner_id']
  	 * 
  	 * @internal revisions:
  	 *   20100714 - asimon - BUGID 3406: modified to include build ID
  	 */
	function assign($feature_map) 
	{
		foreach($feature_map as $feature_id => $elem)
		{
			$sql = "INSERT INTO {$this->tables['user_assignments']} " .
					"(feature_id,user_id,assigner_id," .
					"type,status,creation_ts";
									
			$values = "VALUES({$feature_id},{$elem['user_id']},{$elem['assigner_id']}," .
					"{$elem['type']},{$elem['status']}," . $elem['creation_ts'];
			
			if(isset($elem['deadline_ts']) )
			{
				$sql .=",deadline_ts";
				$values .="," . $elem['deadline_ts']; 
			}     
			
			// BUGID 3406
			if (isset($elem['build_id'])) {
				$sql .= ",build_id";
				$values .= "," . $elem['build_id'];
			}
			
			$sql .= ") " . $values . ")";
			$this->db->exec_query($sql);
		}
	}
	

	/**
	 * 
	 * @param $feature_map
	 * $feature_map: key   => feature_id
	 *               value => hash with optional keys 
	 *                        that have the same name of user_assignment fields
	 * 
	 * @internal revisions:
	 *   20100714 - asimon - BUGID 3406: modified to include build ID
	 */
	function update($feature_map) 
	{
	  
		foreach($feature_map as $feature_id => $elem)
		{
			$sepa = "";
			$sql = "UPDATE {$this->tables['user_assignments']} SET ";
			// BUGID 3406 - added build_id
			$simple_fields = array('user_id','assigner_id','type','status','build_id');
			$date_fields = array('deadline_ts','creation_ts');  
		
			foreach($simple_fields as $idx => $field)
			{
				if(isset($elem[$field]))
				{
					$sql .= $sepa . "$field={$elem[$field]} ";
					$sepa=",";
				}
			}
			
			foreach($date_fields as $idx => $field)
			{
				if(isset($elem[$field]))
				{
					$sql .= $sepa . "$field=" . $elem[$field] . " ";
					$sepa = ",";
				}
			}
			
			$sql .= "WHERE feature_id={$feature_id}";
			
			$this->db->exec_query($sql);
		}
	}
	
	/**
	 * Get the number of assigned users for a given build ID.
	 * @param int $build_id ID of the build to check
	 * @param int $count_all_types if true, all assignments will be counted, otherwise
	 *                             only tester assignments
	 * @return int $count Number of assignments
	 */
	function get_count_of_assignments_for_build_id($build_id, $count_all_types = false) {
		$count = 0;
		
		$types = $this->get_available_types();
	    $tc_execution_type = $types['testcase_execution']['id'];
		$type_sql = ($count_all_types) ? "" : " AND type = {$tc_execution_type} ";
	    
	    $sql = " SELECT COUNT(id) AS count FROM {$this->tables['user_assignments']} " .
		       " WHERE build_id = {$build_id} {$type_sql} ";
	    
	    $count = $this->db->fetchOneValue($sql);
	    
		return $count;
	}
		
	/**
	 * Get assigned testcases
	 * @param $build_id
	 * @param $all_types
	 */
	function get_not_run_tc_count_per_build($build_id, $all_types = false) {
		$count = 0;
		
		$types = $this->get_available_types();
	    $tc_execution_type = $types['testcase_execution']['id'];
		$type_sql = ($all_types) ? "" : " AND UA.type = {$tc_execution_type} ";
		
		/*
		 * Statement magic explanation:
		 * 
		 * This gets all assigned tcversions with NULL as status when they were not run per build:
		 * 
		 * SELECT UA.id AS id, UA.build_id AS build_id, UA.feature_id AS feature_id,
		 *        TPTCV.testplan_id AS testplan_id, TPTCV.tcversion_id AS tcversion_id,
		 *        TPTCV.platform_id AS platform_id, E.status AS status
		 * FROM user_assignments UA
		 * LEFT OUTER JOIN testplan_tcversions TPTCV 
		 *     ON UA.feature_id = TPTCV.id
		 * LEFT OUTER JOIN executions E 
		 *     ON TPTCV.tcversion_id = E.tcversion_id 
		 *     AND UA.build_id = E.build_id
		 *     AND TPTCV.platform_id = E.platform_id
		 * WHERE UA.type = 1 AND UA.build_id = 91
		 * GROUP BY id
		 * 
		 * Without the GROUP BY, there may be multiple executions for each ID.
		 * With the GROUP BY you get only one row per ID,
		 * but the result does not have to be the last one, so
		 * you can only rely on the count of "not run" here, not any other status.
		 * So, to count only those which have not been run we use a statement like:
		 * 
		 * SELECT COUNT(UA.id)
		 * FROM user_assignments UA
		 * LEFT OUTER JOIN testplan_tcversions TPTCV 
		 *     ON UA.feature_id = TPTCV.id
		 * LEFT OUTER JOIN executions E 
		 *     ON TPTCV.tcversion_id = E.tcversion_id 
		 *     AND UA.build_id = E.build_id
		 *     AND TPTCV.platform_id = E.platform_id
		 * WHERE UA.build_id = 91 AND E.status IS NULL AND UA.type = 1
		 */
		
		$sql = " SELECT COUNT(UA.id) " .
		       " FROM {$this->tables['user_assignments']} UA " .
		       " LEFT OUTER JOIN {$this->tables['testplan_tcversions']} TPTCV " .
		       "     ON UA.feature_id = TPTCV.id " .
		       " LEFT OUTER JOIN {$this->tables['executions']} E " .
		       "     ON TPTCV.tcversion_id = E.tcversion_id " .
		       "     AND UA.build_id = E.build_id " .
		       "     AND TPTCV.platform_id = E.platform_id " .
		       " WHERE UA.build_id = {$build_id} AND E.status IS NULL {$type_sql} ";
		
		if (isset($build_id) && is_numeric($build_id)) {
			$count = $this->db->fetchOneValue($sql);
		}
		
		return $count;
	}
	
	/**
	 * Copy the test case execution assignments for a test plan
	 * from one build to another.
	 * During copying of assignments, the assigner id can be updated if an ID is passed
	 * and the timestamp will be updated.
	 * 
	 * @author Andreas Simon
	 * @param int $source_build_id ID of the build to copy the assignments from
	 * @param int $target_build_id ID of the target build to which the assignments will be copied
	 * @param int $assigner_id will be set as assigner ID of the new assignments if != 0,
	 *                         otherwise old assigner ID will be copied 
	 * @param bool $keep_old_assignments if true, existing assignments in target build will be kept,
	 *                            otherwise (default) every existing tester assignment will be deleted
	 * @param int $copy_all_types If true, all assignments will be copied regardless of type, 
	 *                            else only tester assignments will be copied (default).
	 */
	function copy_assignments($source_build_id, $target_build_id, $assigner_id = 0,
	                          $keep_old_assignments = false, $copy_all_types = false) {
		$ua = $this->tables['user_assignments'];
		$creation_ts = $this->db->db_now();
		$types = $this->get_available_types();
	    $tc_execution_type = $types['testcase_execution']['id'];
	    $delete_all_types = $copy_all_types;
	    
		$type_sql = ($copy_all_types) ? "" : " AND type = {$tc_execution_type} ";
		$user_sql = (is_numeric($assigner_id) && $assigner_id != 0) ? $assigner_id : "assigner_id";

		if ($keep_old_assignments == false) {
			// delete the old tester assignments in target builds if there are any
			$this->delete_by_build_id($target_build_id, $delete_all_types);
		}
		
		$sql = " INSERT INTO {$ua} " .
		       " (type, feature_id, user_id, deadline_ts, " .
		       " assigner_id, creation_ts, status, build_id) " .
		       " SELECT type, feature_id, user_id, deadline_ts, " . 
		       " {$user_sql}, {$creation_ts}, status, {$target_build_id} " .
		       " FROM {$ua} " .
		       " WHERE build_id = {$source_build_id} {$type_sql} ";
		
		$this->db->exec_query($sql);
	} // end of method
	
	/**
	 * Delete the user assignments for a given build.
	 * 
	 * @author Andreas Simon
	 * @param int $build_id The ID of the build for which the user assignments shall be deleted.
	 * @param int $delete_all_types If true, all assignments regardless of type will be deleted,
	 *                              else (default) only tester assignments.
	 */
	function delete_by_build_id($build_id, $delete_all_types = false) {
		$type_sql = "";
		
		if (!$delete_all_types) {
			$types = $this->get_available_types();
		    $tc_execution_type = $types['testcase_execution']['id'];
		    $type_sql = " AND type = {$tc_execution_type} ";
		}
		
		$sql = " DELETE FROM {$this->tables['user_assignments']} " .
		       " WHERE build_id = {$build_id} {$type_sql} ";
		
		$this->db->exec_query($sql);
	} // end of method
}
?>