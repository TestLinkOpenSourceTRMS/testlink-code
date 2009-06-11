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
 * @version    	CVS: $Id: assignment_mgr.class.php,v 1.8 2009/06/11 15:42:53 schlundus Exp $
 * @link 		http://www.teamst.org/index.php
 *
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
		static $s_hash_types;
		if (!$s_hash_types)
		{
			$sql = "SELECT * FROM {$this->tables['assignment_types']}";
			$s_hash_types = $this->db->fetchRowsIntoMap($sql,$key_field);
		}
		return $s_hash_types;
	}

  /*
   $key_field: contains the name column that has to be used as the key of
               the returned hash.    
  */
	function get_available_status($key_field='description') 
	{
		static $s_hash_types;
		if (!$s_hash_types)
		{
			$sql = " SELECT * FROM {$this->tables['assignment_status']} "; 
			$s_hash_types = $this->db->fetchRowsIntoMap($sql,$key_field);
		}
		
		return $s_hash_types;
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

  // $feature_map['feature_id']['user_id']
  // $feature_map['feature_id']['type']
  // $feature_map['feature_id']['status']
  // $feature_map['feature_id']['assigner_id']
  //
  //
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
			
			$sql .= ") " . $values . ")";
			$this->db->exec_query($sql);
		}
	}
	

  // 
  // $feature_map: key   => feature_id
  //               value => hash with optional keys 
  //                        that have the same name of user_assignment fields
  //
  //
	function update($feature_map) 
	{
	  
		foreach($feature_map as $feature_id => $elem)
		{
			$sepa = "";
			$sql = "UPDATE {$this->tables['user_assignments']} SET ";
			$simple_fields = array('user_id','assigner_id','type','status');
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
}
?>