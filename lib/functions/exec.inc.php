<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Functions for execution feature (add test results) 
 * Legacy code (party covered by classes now)
 *
 * @package     TestLink
 * @author      Martin Havlat
 * @copyright   2005-2012, TestLink community 
 * @filesource  exec.inc.php
 * @link        http://www.teamst.org/index.php
 *
 * @internal revisions
 * @since 1.9.6
 * 
 *
 **/

require_once('common.php');

/** 
 * Building the dropdown box of results filter
 * 
 * @return array map of 'status_code' => localized string
 **/
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
 * @internal revisions
 * 
 */
function write_execution(&$db,&$exec_signature,&$exec_data)
{
  $executions_table = DB_TABLE_PREFIX . 'executions';
  $resultsCfg = config_get('results');
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
             " execution_ts,notes,tcversion_number,platform_id,execution_duration)".
             " VALUES ( {$exec_signature->build_id}, {$exec_signature->user_id}, '{$exec_data['status'][$tcversion_id]}',".
             "{$exec_signature->tplan_id}, {$tcversion_id},{$db_now},'{$my_notes}'," .
             "{$version_number},{$exec_signature->platform_id}";

      if(trim($exec_data['execution_duration']) == '')
      {
        $dura = 'NULL ';  
      } 
      else
      {
        $dura = floatval($exec_data['execution_duration']);
      }  

      $sql .= ',' .$dura . ")";


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
  
  $sql = "DELETE FROM {$execution_bugs} WHERE execution_id={$exec_id} " .
         "AND bug_id='" . $prep_bug_id ."'";
  $result = $db->exec_query($sql);
  
  
  if(!$just_delete)
  {
    $sql = "INSERT INTO {$execution_bugs} (execution_id,bug_id) " .
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
 * @return array list of 'bug_id' with values: build_name,link_to_bts,isResolved
 */
function get_bugs_for_exec(&$db,&$bug_interface,$execution_id,$raw = null)
{
  $tables = tlObjectWithDB::getDBTables(array('executions','execution_bugs','builds'));
  $bug_list=array();

  $debugMsg = 'FILE:: ' . __FILE__ . ' :: FUNCTION:: ' . __FUNCTION__;
  if( is_object($bug_interface) )
  {
    
    $sql =  "/* $debugMsg */ SELECT execution_id,bug_id,builds.name AS build_name " .
            "FROM {$tables['execution_bugs']}, {$tables['executions']} executions, " .
            " {$tables['builds']} builds ".
            " WHERE execution_id = {$execution_id} " .
            " AND   execution_id = executions.id " .
            " AND   executions.build_id = builds.id " .
            " ORDER BY builds.name,bug_id";

    $map = $db->get_recordset($sql);
    if( !is_null($map) )
    {   
      $opt['raw'] = $raw;
      $addAttr = !is_null($raw);
      foreach($map as $elem)
      {
        $dummy = $bug_interface->buildViewBugLink($elem['bug_id'],$opt);
        $bug_list[$elem['bug_id']]['link_to_bts'] = $dummy->link;
        $bug_list[$elem['bug_id']]['build_name'] = $elem['build_name'];
        $bug_list[$elem['bug_id']]['isResolved'] = $dummy->isResolved;
        if($addAttr)
        {
          foreach($raw as $kj)
          {
          	if( property_exists($dummy,$kj) )
          	{
              $bug_list[$elem['bug_id']][$kj] = $dummy->$kj;
          	}
          } 
        }       
        unset($dummy);
      }
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
function get_execution(&$dbHandler,$execution_id,$opt=null)
{
  $my = array('options' => array('output' => 'raw'));
  $my['options'] = array_merge($my['options'], (array)$opt);
  $tables = tlObjectWithDB::getDBTables(array('executions','nodes_hierarchy','builds','platforms'));
  
  $safe_id = intval($execution_id); 
  switch($my['options']['output'])
  {
    case 'audit':
      $sql = " SELECT B.name AS build_name,PLAT.name AS platform_name, " .
             " NH_TPLAN.name AS testplan_name, NH_TC.name AS testcase_name, " .
             " E.id AS exec_id, NH_TPROJ.name AS testproject_name " . 
           " FROM {$tables['executions']} E " .
           " JOIN {$tables['builds']} B ON B.id = E.build_id " . 
           " JOIN {$tables['platforms']} PLAT ON PLAT.id = E.platform_id " . 
           " JOIN {$tables['nodes_hierarchy']} NH_TPLAN ON NH_TPLAN.id = E.testplan_id " . 
           " JOIN {$tables['nodes_hierarchy']} NH_TCV ON NH_TCV.id = E.tcversion_id " . 
           " JOIN {$tables['nodes_hierarchy']} NH_TC ON NH_TC.id = NH_TCV.parent_id " . 
           " JOIN {$tables['nodes_hierarchy']} NH_TPROJ ON NH_TPROJ.id = NH_TPLAN.parent_id " . 
           " WHERE E.id = " . $safe_id;
    break;    
    
    case 'raw':
    default:
      $sql = " SELECT * FROM {$tables['executions']} E ".
           " WHERE E.id = " . $safe_id;
    break;    
  } 
  tLog(__FUNCTION__ . ':' . $sql,"DEBUG");
  $rs = $dbHandler->get_recordset($sql);
  return($rs);
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
  $tables = tlObjectWithDB::getDBTables(array('executions','execution_bugs','cfield_execution_values'));
  
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