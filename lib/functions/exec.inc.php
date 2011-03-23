<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Functions for execution feature (add test results) 
 * Legacy code (party covered by classes now)
 *
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2005-2009, TestLink community 
 * @version    	CVS: $Id: exec.inc.php,v 1.60 2010/06/24 17:25:53 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 *
 * 20100522 - franciscom - BUGID 3479 - Bulk Execution - Custom Fields Bulk Assignment (write_execution())
 * 20100522 - franciscom - BUGID 3440 - get_bugs_for_exec() - added is_object() check 
 * 20090815 - franciscom - write_execution() - interface changes 
 * 20081231 - franciscom - write_execution() changes to manage bulks exec notes
 * 20080528 - franciscom - BUGID 1504 - changes in write_execution
 *                                      using version_number
 * 20080504 - franciscom - removed deprecated functions
 * 20051119  - scs - added fix for 227
 * 20060311 - kl - some modifications to SQL queries dealing with 1.7
 *                 builds table in order to comply with new 1.7 schema
 * 20060528 - franciscom - adding management of bulk update
 * 20060916 - franciscom - added write_execution_bug()
 *                               get_bugs_for_exec()
 * 20070105 - franciscom - interface changes write_execution()
 * 20070222 - franciscom - BUGID 645 createResultsMenu()
 * 20070617 - franciscom - BUGID     insert_id() problems for Postgres and Oracle?
 **/
/** 
 * @uses  common.php required basic environment (configuration and core libraries) 
 **/
require_once('common.php');


/** 
 * Building the dropdown box of results filter
 * 
 * @return array map of 'status_code' => localized string
 **/
// BUGID 645 
function createResultsMenu()
{
	$resultsCfg = config_get('results');
	
	// Fixed values, that has to be added always
	$my_all = isset($resultsCfg['status_label']['all'])?$resultsCfg['status_label']['all']:'';
	$menu_data[$resultsCfg['status_code']['all']] = $my_all;
	$menu_data[$resultsCfg['status_code']['not_run']] = lang_get($resultsCfg['status_label']['not_run']);
	
	// loop over status for user interface, because these are the statuses
	// user can assign while executing test cases
	foreach($resultsCfg['status_label_for_exec_ui'] as $verbose_status => $status_label)
	{
		$code = $resultsCfg['status_code'][$verbose_status];
		$menu_data[$code] = lang_get($status_label); 
	}
	
	return $menu_data;
}
	
	
/**
 * write execution result to DB
 * 
 * @param resource &$db reference to database handler
 * @param obj &$exec_signature object with tproject_id,tplan_id,build_id,platform_id,user_id
 * 
 * @internal Revisions:
 * 
 *
 * 20110323 - Parameter map_last_exec not used within function -> removed
 * 20100522 - BUGID 3479 - Bulk Execution - Custom Fields Bulk Assignment
 */
function write_execution(&$db,&$exec_signature,&$exec_data)
{
	$executions_table = DB_TABLE_PREFIX . 'executions';
	$resultsCfg = config_get('results');
	// $bugInterfaceOn = config_get('bugInterfaceOn');
	$db_now = $db->db_now();
	$cfield_mgr = New cfield_mgr($db);
	$cf_prefix = $cfield_mgr->get_name_prefix();
	$len_cfp = tlStringLen($cf_prefix);
	$cf_nodeid_pos = 4;
	$bulk_notes = '';
	
	$ENABLED = 1;
	$cf_map = $cfield_mgr->get_linked_cfields_at_execution($exec_signature->tproject_id,$ENABLED,'testcase');
	$has_custom_fields = is_null($cf_map) ? 0 : 1;
	
	// extract custom fields id.
	$map_nodeid_array_cfnames=null;
	foreach($exec_data as $input_name => $value)
	{
		if( strncmp($input_name,$cf_prefix,$len_cfp) == 0 )
		{
			$dummy=explode('_',$input_name);
			$map_nodeid_array_cfnames[$dummy[$cf_nodeid_pos]][]=$input_name;
		} 
	}
	
	if( isset($exec_data['do_bulk_save']) )
	{
		// create structure to use common algoritm
		$item2loop= $exec_data['status'];
		$is_bulk_save=1;
		$bulk_notes = $db->prepare_string(trim($exec_data['bulk_exec_notes']));		
	}	
	else
	{
		$item2loop= $exec_data['save_results'];
		$is_bulk_save=0;
	}
	
	foreach ( $item2loop as $tcversion_id => $val)
	{
		$tcase_id=$exec_data['tc_version'][$tcversion_id];
		$current_status = $exec_data['status'][$tcversion_id];
		$version_number=$exec_data['version_number'][$tcversion_id];;
		$has_been_executed = ($current_status != $resultsCfg['status_code']['not_run'] ? TRUE : FALSE);
		if($has_been_executed)
		{ 
			
			$my_notes = $is_bulk_save ? $bulk_notes : $db->prepare_string(trim($exec_data['notes'][$tcversion_id]));		
			$sql = "INSERT INTO {$executions_table} ".
				"(build_id,tester_id,status,testplan_id,tcversion_id," .
				" execution_ts,notes,tcversion_number,platform_id)".
				" VALUES ( {$exec_signature->build_id}, {$exec_signature->user_id}, '{$exec_data['status'][$tcversion_id]}',".
				"{$exec_signature->tplan_id}, {$tcversion_id},{$db_now},'{$my_notes}'," .
				"{$version_number},{$exec_signature->platform_id}" . 
				")";
			$db->exec_query($sql);  	
			
			// at least for Postgres DBMS table name is needed. 
			$execution_id = $db->insert_id($executions_table);
			
			if( $has_custom_fields )
			{
				// test useful when doing bulk update, because some type of custom fields
				// like checkbox can not exist on exec_data. => why ??
				//
				$hash_cf = null;
				$access_key = $is_bulk_save ? 0 : $tcase_id;
				if( isset($map_nodeid_array_cfnames[$access_key]) )
				{ 
					foreach($map_nodeid_array_cfnames[$access_key] as $cf_v)
					{
						$hash_cf[$cf_v]=$exec_data[$cf_v];
					}  
				}                          
				$cfield_mgr->execution_values_to_db($hash_cf,$tcversion_id, $execution_id, $exec_signature->tplan_id,$cf_map);
			}                                     
		}
	}
}

/**
 * 
 *
 */
function write_execution_bug(&$db,$exec_id, $bug_id,$just_delete=false)
{
	$execution_bugs = DB_TABLE_PREFIX . 'execution_bugs';
	
	// Instead of Check if record exists before inserting, do delete + insert
	$prep_bug_id = $db->prepare_string($bug_id);
	
	$sql = "DELETE FROM {$execution_bugs} " .
		"WHERE execution_id={$exec_id} " .
		"AND bug_id='" . $prep_bug_id ."'";
	$result = $db->exec_query($sql);
	
	if(!$just_delete)
	{
		$sql = "INSERT INTO {$execution_bugs} " .
			"(execution_id,bug_id) " .
			"VALUES({$exec_id},'" . $prep_bug_id . "')";
		$result = $db->exec_query($sql);  	     
	}
	
	return $result ? 1 : 0;
}


/**
 * get data about bug from external tool
 * 
 * @param resource &$db reference to database handler
 * @param object &$bug_interface reference to instance of bugTracker class
 * @param integer $execution_id Identifier of execution record
 * 
 * @return array list of 'bug_id' with values: 'build_name' and 'link_to_bts'
 */
function get_bugs_for_exec(&$db,&$bug_interface,$execution_id)
{
	$tables['execution_bugs'] = DB_TABLE_PREFIX . 'execution_bugs';
	$tables['executions'] = DB_TABLE_PREFIX . 'executions';
	$tables['builds'] = DB_TABLE_PREFIX . 'builds';
	
	$bug_list=array();
	$sql = "SELECT execution_id,bug_id,builds.name AS build_name " .
		"FROM {$tables['execution_bugs']}, {$tables['executions']} executions, " .
		" {$tables['builds']} builds ".
		"WHERE execution_id={$execution_id} " .
		"AND   execution_id=executions.id " .
		"AND   executions.build_id=builds.id " .
		"ORDER BY builds.name,bug_id";
	$map = $db->get_recordset($sql);
	
	// BUGID 3440 - added is_object() check
	if( !is_null($map) && is_object($bug_interface))
	{  	
		foreach($map as $elem)
		{
			$bug_list[$elem['bug_id']]['link_to_bts'] = $bug_interface->buildViewBugLink($elem['bug_id'],GET_BUG_SUMMARY);
			$bug_list[$elem['bug_id']]['build_name'] = $elem['build_name'];
		}
	}
	
	return($bug_list);
}


/**
 * get data about one test execution
 * 
 * @param resource &$db reference to database handler
 * @param datatype $execution_id
 * 
 * @return array all values of executions DB table in format field=>value
 */
function get_execution(&$db,$execution_id)
{
	$tables['executions'] = DB_TABLE_PREFIX . 'executions';
	
	$sql = "SELECT * " .
		"FROM {$tables['executions']} ".
		"WHERE id={$execution_id} ";
	
	$map = $db->get_recordset($sql);
	return($map);
}

/** 
 * delete one test execution from database (include child data and relations)
 * 
 * @param resource &$db reference to database handler
 * @param datatype $execution_id
 * 
 * @return boolean result of delete
 * 
 * @TODO delete attachment, userassignment
 * @TODO run SQL as transaction if database engine allows 
 **/
function delete_execution(&$db,$exec_id)
{
	$tables['execution_bugs'] = DB_TABLE_PREFIX . 'execution_bugs';
	$tables['executions'] = DB_TABLE_PREFIX . 'executions';
	$tables['cfield_execution_values'] = DB_TABLE_PREFIX . 'cfield_execution_values';
	
	$sql = array(
		"DELETE FROM {$tables['execution_bugs']} WHERE execution_id = {$exec_id}", // delete bugs
		"DELETE FROM {$tables['cfield_execution_values']} WHERE execution_id = {$exec_id}", // delete CF
		"DELETE FROM {$tables['executions']} WHERE id = {$exec_id}" // delete execution
	);
	
	foreach ($sql as $the_stm)
	{
		$result = $db->exec_query($the_stm);
		if (!$result)
		{
			break;
		}
	}
	
	return $result;
}

/**
 * @param $db resource the database connecton
 * @param $execID integer the execution id whose notes should be set
 * @param $notes string the execution notes to set
 * @return unknown_type
 */
function updateExecutionNotes(&$db,$execID,$notes)
{
    $table = tlObjectWithDB::getDBTables('executions');
    $sql = "UPDATE {$table['executions']} " .
           "SET notes = '" . $db->prepare_string($notes) . "' " .
           "WHERE id = {$execID}";
    
    return $db->exec_query($sql) ? tl::OK : tl::ERROR;     
}

?>