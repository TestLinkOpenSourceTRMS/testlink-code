<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource $RCSfile: testplan.class.php,v $
 * @version $Revision: 1.110 $
 * @modified $Date: 2009/04/30 18:46:36 $ by $Author: schlundus $
 * 
 * @copyright Copyright (c) 2008, TestLink community
 * @author franciscom
 *
 *
 * Manages test plan operations and related items like Custom fields, 
 * Builds, Custom fields, etc.
 *
 * --------------------------------------------------------------------------------------
 * @todo class for builds and milestones should extend testPlan class
 * @todo create class testplanEdit (as extension of testplan class) and 
 *		move here create,edit,delete,copy related stuff
 * @todo remove dependency to tree.class.php, assignment_mgr.class.php, attachments.inc.php
 * 		add object.class.php
 *
 * --------------------------------------------------------------------------------------
 * Revisions:
 *
 *  20090411 - franciscom - BUGID 2369 - link_tcversions() - interface changes
 *  20090214 - franciscom - BUGID 2099 - get_linked_tcversions() - added new columns in output recordset
 *  20090208 - franciscom - testplan class - new method get_build_by_id()
 *  20090201 - franciscom - copy_milestones() - wrong SQL sentece 
 *                          A,B,C fields renamed to lower case a,b,c to avoid problems
 *                          between differnt database (case and no case sensitive)
 *  20081227 - franciscom - BUGID 1913 - filter by same results on ALL previous builds
 *                          get_same_status_for_build_set(), get_prev_builds()
 *
 *  20081214 - franciscom - Thanks to postgres found missing CAST() on SUM() 
 *  20081206 - franciscom - BUGID 1910 - get_estimated_execution_time() - added new filter
 *                                       get_linked_tcversions() - added test suites filter 
 *  20080820 - franciscom - added get_estimated_execution_time() as result of contributed idea.
 *
 *  20080811 - franciscom - BUGID 1650 (REQ)
 *                          html_table_of_custom_field_values() interface changes
 *                          {$this->builds_table} instead of 'builds'
 *
 * 	20080717 - havlatm - added get_node_name
 *  20080614 - franciscom - get_linked_and_newest_tcversions() - fixed bug  (thanks to PostGres)
 *  20080428 - franciscom - supporting multiple keywords in get_linked_tcversions()
 *                          (based on contribution by Eugenia Drosdezki)
 *  20080310 - sbouffard - contribution added NHB.name to recordset (useful for API methods).  
 *  20071010 - franciscom - BUGID     MSSQL reserved word problem - open
 *  20070927 - franciscom - BUGID 1069
 *                          added _natsort_builds() (see natsort info on PHP manual).
 *                          get_builds() add call to _natsort_builds()
 *                          get_builds_for_html_options() add call to natsort()
 *  20070310 - franciscom - BUGID 731
 *  20070306 - franciscom - BUGID 705 - changes in get_linked_tcversions()
 * ----------------------------------------------------------------------------------- */

require_once( dirname(__FILE__) . '/tree.class.php' );
require_once( dirname(__FILE__) . '/assignment_mgr.class.php' );
require_once( dirname(__FILE__) . '/attachments.inc.php' );


class testplan extends tlObjectWithAttachments
{
	const GET_ALL=null;
	const GET_ACTIVE_BUILD=1;
	const GET_INACTIVE_BUILD=0;
	const GET_OPEN_BUILD=1;
	const GET_CLOSED_BUILD=0;
	const ACTIVE_BUILDS=1;
	const ENABLED=1;

	var $db;
	var $tree_manager;
	var $assignment_mgr;
	var $cfield_mgr;
	var $tcase_mgr;

  	var $users_table="users";
  	var $builds_table="builds";
 	var $custom_fields_table="custom_fields";
 	var $cfield_design_values_table="cfield_design_values";
  	var $cfield_execution_values_table="cfield_execution_values";
  	var $cfield_testplan_design_values_table="cfield_testplan_design_values";  
  	var $cfield_node_types_table="cfield_node_types";
  	var $execution_bugs_table="execution_bugs";
  	var $executions_table='executions';
    var $nodes_hierarchy_table='nodes_hierarchy';
	var $milestones_table='milestones';
  	var $tcversions_table='tcversions';
  	var $testplans_table="testplans";
	var $testplan_tcversions_table="testplan_tcversions";


	var $assignment_types;
	var $assignment_status;
	var $user_feedback_message = '';
	
	/**
	 * testplan class constructor
	 * 
	 * args: db [reference] db object
	 * returns: N/A
	 */
	function testplan(&$db)
	{
	    $this->db = &$db;
	    $this->tree_manager = New tree($this->db);
      
	    $this->assignment_mgr = New assignment_mgr($this->db);
	    $this->assignment_types = $this->assignment_mgr->get_available_types();
	    $this->assignment_status = $this->assignment_mgr->get_available_status();
      
    	$this->cfield_mgr = new cfield_mgr($this->db);
    	$this->tcase_mgr = New testcase($this->db);
    	
	    tlObjectWithAttachments::__construct($this->db,'testplans');
	}


// --------------------------------------------------------------------------------------
/*
  function: create
            creates a tesplan on Database, for a testproject.

  args: name: testplan name
        notes: testplan notes
        testproject_id: testplan parent.

  returns: id: if everything ok -> id of new testplan (node id).
               if problems -> 0.

*/
function create($name,$notes,$testproject_id)
{
	$node_types=$this->tree_manager->get_available_node_types();
	$tplan_id = $this->tree_manager->new_node($testproject_id,$node_types['testplan'],$name);

	$sql = "INSERT INTO {$this->testplans_table} (id,notes,testproject_id)
	        VALUES ( {$tplan_id} " . ", '" .
	                 $this->db->prepare_string($notes) . "'," .
	                 $testproject_id .")";
	$result = $this->db->exec_query($sql);
	$id = 0;
	if ($result)
	{
		$id = $tplan_id;
	}
	return $id;
}


// --------------------------------------------------------------------------------------
/*
  function: update testplan information

  args: id: testplan id
        name:
        notes:
        is_active: active status

  returns: 1 -> ok
           0 -> ko
*/
function update($id,$name,$notes,$is_active)
{
	$do_update = 1;
	$result = null;
	$active = to_boolean($is_active);
	$name = trim($name);

	// two tables to update and we have no transaction yet.
	$rsa = $this->get_by_id($id);
	$duplicate_check = (strcmp($rsa['name'],$name) != 0 );

	if($duplicate_check)
	{
		$rs = $this->get_by_name($name,$rsa['parent_id']);
		$do_update = is_null($rs);
	}

	if($do_update)
	{
		// Update name
		$sql = "UPDATE {$this->nodes_hierarchy_table} " .
				"SET name='" . $this->db->prepare_string($name) . "'" .
				"WHERE id={$id}";
		$result = $this->db->exec_query($sql);

		if($result)
		{
			$sql = "UPDATE {$this->testplans_table} " .
					"SET active={$active}," .
					"notes='" . $this->db->prepare_string($notes). "' " .
					"WHERE id=" . $id;
			$result = $this->db->exec_query($sql);
		}
	}
	return ($result ? 1 : 0);
}


// --------------------------------------------------------------------------------------
/*
  function: get_by_name
            get information about a testplan using name as access key.
            Search can be narrowed, givin a testproject id as filter criteria.

  args: name: testplan name
        [tproject_id]: default:0 -> system wide search i.e. inside all testprojects

  returns: if nothing found -> null
           if found -> array where every element is a map with following keys:
                       id: testplan id
                       notes:
                       active: active status
                       is_open: open status
                       name: testplan name
                       testproject_id
*/
function get_by_name($name,$tproject_id = 0)
{
	$sql = " SELECT testplans.*, NH.name " .
	       " FROM {$this->testplans_table} testplans, {$this->nodes_hierarchy_table} NH" .
	       " WHERE testplans.id=NH.id " .
	       " AND NH.name = '" . $this->db->prepare_string($name) . "'";

	if($tproject_id > 0 )
	{
		$sql .= " AND NH.parent_id={$tproject_id}";
	}
    
    $recordset = $this->db->get_recordset($sql);
	return($recordset);
}

// --------------------------------------------------------------------------------------
/*
  function: get_by_id

  args : id: testplan id

  returns: map with following keys:
           id: testplan id
           name: testplan name
           notes: testplan notes
           testproject_id
           active
           is_open
           parent_id
*/
function get_by_id($id)
{
	$sql = " SELECT testplans.*,NH.name,NH.parent_id
	         FROM {$this->testplans_table} testplans, {$this->nodes_hierarchy_table} NH
	         WHERE testplans.id = NH.id
	         AND   testplans.id = {$id}";
	$recordset = $this->db->get_recordset($sql);
	return($recordset ? $recordset[0] : null);
}


// --------------------------------------------------------------------------------------
/*
  function: get_all
            get array of info for every test plan,
            without considering Test Project and any other kind of filter.
            Every array element contains an assoc array

  args : -

  returns: array, every element is a  map with following keys:
           id: testplan id
           name: testplan name
           notes: testplan notes
           testproject_id
           active
           is_open
           parent_id
*/
function get_all()
{
	$sql = " SELECT testplans.*, nodes_hierarchy.name
	         FROM {$this->testplans_table} testplans, {$this->nodes_hierarchy_table} nodes_hierarchy
	         WHERE testplans.id=nodes_hierarchy.id";
	$recordset = $this->db->get_recordset($sql);
	return $recordset;
}


// --------------------------------------------------------------------------------------
/*
  function: count_testcases
            get number of testcases linked to a testplan

  args: id: testplan id

  returns: number

*/
public function count_testcases($id)
{
	$sql = "SELECT COUNT(testplan_id) AS qty FROM {$this->testplan_tcversions_table}
	        WHERE testplan_id={$id}";
	$recordset = $this->db->get_recordset($sql);
	$qty = 0;
	if(!is_null($recordset))
	{
		$qty = $recordset[0]['qty'];
	}
	return $qty;
}


// --------------------------------------------------------------------------------------
/*
  function: tcversionInfoForAudit
            get info regarding tcversions, to generate useful audit messages
            

  args :
        $tplan_id: test plan id
        $items: assoc array key=tc_id value=tcversion_id
                passed by reference for speed

  returns: -

  rev: 20080629 - franciscom - audit message improvements
*/
function tcversionInfoForAudit($tplan_id,&$items)
{
  // Get human readeable info for audit
  $ret=array();
  $tcase_cfg = config_get('testcase_cfg');
  
	$dummy=reset($items);
  $ret['tcasePrefix']=$this->tcase_mgr->getPrefix($dummy) . $tcase_cfg->glue_character;
  
  $sql=" SELECT TCV.id, tc_external_id, version, NHB.name " .
       " FROM {$this->tcversions_table} TCV,{$this->nodes_hierarchy_table} NHA, " .
       " {$this->nodes_hierarchy_table} NHB " .
       " WHERE NHA.id=TCV.id " .
       " AND NHB.id=NHA.parent_id  " .
       " AND TCV.id IN (" . implode(',',$items) . ")";

  $ret['info']=$this->db->fetchRowsIntoMap($sql,'id');  
  $ret['tplanInfo']=$this->get_by_id($tplan_id);                                                          
                                                        
  return $ret;
}


// --------------------------------------------------------------------------------------
/*
  function: link_tcversions
            associates version of different test cases to a test plan.
            this is the way to populate a test plan

  args :
        $id: test plan id
        $items_to_link: assoc array key=tc_id value=tcversion_id
                        passed by reference for speed

  returns: N/A

  rev: 20080629 - franciscom - audit message improvements
*/
/**
 * link_tcversions
 * associates version of different test cases to a test plan.
 * this is the way to populate a test plan
 *
 *
 *
 */
function link_tcversions($id,&$items_to_link,$userId)
{
    // Get human readeable info for audit
    $title_separator = config_get('gui_title_separator_1');
    $auditInfo=$this->tcversionInfoForAudit($id,$items_to_link);
    $info=$auditInfo['info'];
    $tcasePrefix=$auditInfo['tcasePrefix'];
    $tplanInfo=$auditInfo['tplanInfo'];
   
    // Important: MySQL do not support default values on datetime columns that are functions
    // that's why we are using db_now().
	$sql = "INSERT INTO {$this->testplan_tcversions_table} " .
	       "(testplan_id,author_id,creation_ts,tcversion_id) VALUES ({$id},{$userId},{$this->db->db_now()},";
	foreach($items_to_link as $tc => $tcversion)
	{
		$result = $this->db->exec_query($sql . "{$tcversion})");
		if ($result)
		{
			$auditMsg=TLS("audit_tc_added_to_testplan",
			              $tcasePrefix . $info[$tcversion]['tc_external_id'] . 
			              $title_separator . $info[$tcversion]['name'],
			              $info[$tcversion]['version'],$tplanInfo['name']);
			              
			logAuditEvent($auditMsg,"ASSIGN",$id,"testplans");
		}	
	}
}


// --------------------------------------------------------------------------------------
/*
  function: setExecutionOrder

  args :
        $id: test plan id
        $executionOrder: assoc array key=tcversion_id value=order
                         passed by reference for speed

  returns: N/A
*/
function setExecutionOrder($id,&$executionOrder)
{
	foreach($executionOrder as $tcVersionID => $execOrder)
	{
    	$execOrder=intval($execOrder);
    	$sql="UPDATE {$this->testplan_tcversions_table} " .
           "SET node_order={$execOrder} " .
           "WHERE testplan_id={$id} " .
           "AND tcversion_id={$tcVersionID}";
		$result = $this->db->exec_query($sql);
	}
}



// --------------------------------------------------------------------------------------
/*
  function: get_linked_tcversions
            get information about testcases linked to a testplan.

  args :
         id: testplan id
         [tcase_id]: default null => get any testcase
                     numeric      => just get info for this testcase

         [keyword_id]: default 0 => do not filter by keyword id
                       numeric   => filter by keyword id

         [executed]: default NULL => get executed and NOT executed
                                     get only executed tcversions

         [assigned_to]: default NULL => do not filter by user assign.
                        array() with user id to be used on filter

         [exec_status]: default NULL => do not filter by execution status
                        character    => filter by execution status=character

         [build_id]: default 0 => do not filter by build id
                     numeric   => filter by build id

         [cf_hash]: default null => do not filter by Custom Fields values

         [include_unassigned]: has effects only if [assigned_to] <> null.
                               default: false
                               true: also testcase not assigned will be retreived
		     
		     [urgencyImportance] : filter only Tc's with certain (urgency*importance)-value 
		     
		     [tsuites_id]: default null.
		                   If present only tcversions that are children of this testsuites
		                   will be included
		     [exec_type] default null -> all types              
         [details]: default 'simple'
                    'full': add summary, steps and expected_results
		     
  returns: map
           key: testcase id
           value: map with following keys:

           Notice:
           executed field: will take the following values
                           NULL if the tc version has not been executed in THIS test plan
                           tcversion_id if has executions

	rev :
	    20090214 - franciscom - added tcversions.execution_type and 
	                            executions.execution_type AS execution_run_type in result
		  20081220 - franciscom - exec_status can be an array to allow OR filtering 
		  20080714 - havlatm - added urgency
    	20080602 - franciscom - tcversion_number in output
    	20080309 - sbouffard - added NHB.name to recordset
    	20080114 - franciscom - added external_id in output
     	20070825 - franciscom - added NHB.node_order on ORDER BY
    	20070630 - franciscom - added active tcversion status in output recorset
    	20070306 - franciscom - BUGID 705
*/
public function get_linked_tcversions($id,$tcase_id=null,$keyword_id=0,$executed=null,
                                          $assigned_to=null,$exec_status=null,$build_id=0,
                                          $cf_hash = null, $include_unassigned=false,
                                          $urgencyImportance = null, $tsuites_id=null, 
                                          $exec_type=null,$details='simple')
{
	$resultsCfg = config_get('results');
	$status_not_run=$resultsCfg['status_code']['not_run'];

	$tcversion_exec_type_filter=" ";
	$keywords_join = " ";
	$keywords_filter = " ";
	$tc_id_filter = " ";
	$executions_join = " ";
	$executions_filter=" ";
	$sql_subquery='';
	$build_filter = " ";

  if( !is_null($exec_type) )
  {
      $tcversion_exec_type_filter = "AND TCV.execution_type IN (" .implode(",",(array)$exec_type) . " ) ";     
  }

	// Based on work by Eugenia Drosdezki
	if( is_array($keyword_id) )
	{
    	// 0 -> no keyword, remove 
    	if( $keyword_id[0] == 0 )
    	{
    	   array_shift($keyword_id);
    	}
 
    	if(count($keyword_id))
    	{
          $keywords_filter = " AND TK.keyword_id IN (" . implode(',',$keyword_id) . ")";          	
    	}  
	}
	else if($keyword_id > 0)
	{
	    $keywords_filter = " AND TK.keyword_id = {$keyword_id} ";
	}
	
	if(trim($keywords_filter) != "")
	{
	    $keywords_join = " JOIN testcase_keywords TK ON NHA.parent_id = TK.testcase_id ";
	}
	
	if (!is_null($tcase_id) )
	{
	   if( is_array($tcase_id) )
	   {
        $tc_id_filter = " AND NHA.parent_id IN (" . implode(',',$tcase_id) . ")";          	
	   }
	   else if ($tcase_id > 0 )
	   {
	      $tc_id_filter = " AND NHA.parent_id = {$tcase_id} ";
	   }
	}

	// --------------------------------------------------------------
	if(!is_null($exec_status) )
	{
	    // if( $exec_status == $status_not_run)
	    // {
	    //   $executions_filter=" AND E.status IS NULL ";
	    // }
	    // else
	    // {
	    //   // 20081220 - franciscom
	    //   // $executions_filter=" AND E.status='" . $exec_status . "' ";
	    //   // Remember status code are characters non numbers, then we need to use
	    //   // single quotes on IN clause elements
	    //   $executions_filter=" AND E.status IN ('" . implode("','",$exec_status) . "') ";
	    //   $sql_subquery=" AND E.id IN ( SELECT MAX(id) " .
      //                 "               FROM  executions " .
      //                 "               WHERE testplan_id={$id} " .
      //                 "               GROUP BY tcversion_id,testplan_id )";
	    // }
	    $executions_filter='';
	    $notrun_filter=null;
	    $otherexec_filter=null;
	    
	    $notRunPresent = array_search($status_not_run,$exec_status); 
	    if($notRunPresent !== false)
	    {
	        $notrun_filter = " E.status IS NULL ";
	        unset($exec_status[$notRunPresent]);  
	    }
	    
	    if(count($exec_status) > 0)
	    {
          $otherexec_filter=" E.status IN ('" . implode("','",$exec_status) . "') ";
	        $sql_subquery=" AND E.id IN ( SELECT MAX(id) " .
                        "               FROM  executions " .
                        "               WHERE testplan_id={$id} " .
                        "               GROUP BY tcversion_id,testplan_id )";
                        
                        
	    }
      if( !is_null($otherexec_filter) )
      {
          $executions_filter = " ( {$otherexec_filter} {$sql_subquery} ) ";  
      }
      if( !is_null($notrun_filter) )
      {
        if($executions_filter != "")
        {
            $executions_filter .= " OR ";
        }
        $executions_filter .= $notrun_filter;
      }
      
      if($executions_filter != "")
      {
          $executions_filter = " AND ({$executions_filter} )";     
      }
	}

	// --------------------------------------------------------------
	if( $build_id > 0 )
	{
      $build_filter = " AND E.build_id={$build_id} ";
	}

	if(is_null($executed))
	{
     $executions_join = " LEFT OUTER ";
	}
	$executions_join .= " JOIN executions E ON " .
	                    " (NHA.id = E.tcversion_id AND " .
	                    "  E.testplan_id=T.testplan_id {$build_filter}) ";

	// --------------------------------------------------------------
	// missing condition on testplan_id between execution and testplan_tcversions
	// added tc_id in order clause to maintain same order that navigation tree

	// 20080602 - franciscom - added tcversion_number
	// 20080114 - franciscom - added tc_external_id
	// 20070106 - franciscom - Postgres does not like Column alias without AS, 
	//							and (IMHO) he is right
	// 20070917 - added version
	// 20080331 - added T.node_order
  //	
  $more_tcase_fields = '';
  $join_for_parent = '';
  $more_parent_fields = '';
	if($details == 'full')
	{
	    $more_tcase_fields = 'TCV.summary,TCV.steps,TCV.expected_results,';
	    $join_for_parent = " JOIN {$this->nodes_hierarchy_table} NHC ON NHB.parent_id = NHC.id ";
	    $more_parent_fields = 'NHC.name as tsuite_name,';
	}
	
	$sql = " SELECT NHB.parent_id AS testsuite_id, {$more_tcase_fields} {$more_parent_fields}" .
	       " NHA.parent_id AS tc_id, NHB.node_order AS z, NHB.name," .
	       " T.tcversion_id AS tcversion_id, T.id AS feature_id, " .
	       " T.node_order AS execution_order, TCV.version AS version, TCV.active," .
	       " TCV.tc_external_id AS external_id, TCV.execution_type," .
	       " E.id AS exec_id, E.tcversion_number," .
	       " E.tcversion_id AS executed, E.testplan_id AS exec_on_tplan, " .
	       " E.execution_type AS execution_run_type, E.testplan_id AS exec_on_tplan, " .
	       " UA.user_id,UA.type,UA.status,UA.assigner_id,T.urgency, " .
	       " COALESCE(E.status,'" . $status_not_run . "') AS exec_status ".
	       " FROM {$this->nodes_hierarchy_table} NHA " .
	       " JOIN {$this->nodes_hierarchy_table} NHB ON NHA.parent_id = NHB.id " .
	       $join_for_parent .
	       " JOIN {$this->testplan_tcversions_table} T ON NHA.id = T.tcversion_id " .
	       " JOIN  {$this->tcversions_table} TCV ON NHA.id = TCV.id {$tcversion_exec_type_filter} " .
	       " {$executions_join} " .
	       " {$keywords_join} " .
	       " LEFT OUTER JOIN user_assignments UA ON UA.feature_id = T.id " .
	       " WHERE T.testplan_id={$id} {$keywords_filter} {$tc_id_filter} " .
	       " AND (UA.type={$this->assignment_types['testcase_execution']['id']} OR UA.type IS NULL) " . 
	       $executions_filter;

  // 20081220 - franciscom
	// if (!is_null($assigned_to) && $assigned_to > 0)
	// {
	//
	// If special user id TL_USER_ANYBODY is present in set of user id,
	// we will DO NOT FILTER by user ID
	if( !is_null($assigned_to) && !in_array(TL_USER_ANYBODY,(array)$assigned_to) )
	{  
    $sql .= " AND ";

	  // Warning!!!:
	  // If special user id TL_USER_NOBODY is present in set of user id
	  // we will ignore any other user id present on set.
	  if( in_array(TL_USER_NOBODY,(array)$assigned_to) )
	  {
	      $sql .= " UA.user_id IS NULL "; 
	  } 
	  else
	  {
		    $sql_unassigned="";
		    if( $include_unassigned )
		    {
		        $sql .= "(";
		        $sql_unassigned=" OR UA.user_id IS NULL)";
		    }
		    $sql .= " UA.user_id IN (" . implode(",",$assigned_to) . ") " . $sql_unassigned;
		}
	}
	
	if (!is_null($urgencyImportance))
	{
		$urgencyImportanceCfg = config_get("urgencyImportance");
		if ($urgencyImportance == HIGH)
			$sql .= " AND (urgency * importance) >= ".$urgencyImportanceCfg->threshold['high'];
		else if($urgencyImportance == LOW)
			$sql .= " AND (urgency * importance) < ".$urgencyImportanceCfg->threshold['low'];
		else
			$sql .= " AND ( ((urgency * importance) >= ".$urgencyImportanceCfg->threshold['low']." AND  ((urgency * importance) < ".$urgencyImportanceCfg->threshold['high']."))) ";
	}
	
	  // test suites filter
	  if (!is_null($tsuites_id))
	  {
	     $tsuiteSet = is_array($tsuites_id) ? $tsuites_id : array($tsuites_id);
	     $sql .= " AND NHB.parent_id IN (" . implode(',',$tsuiteSet) . ")";
	  }
	
	  // BUGID 989 - added NHB.node_order
	  $sql .= " ORDER BY testsuite_id,NHB.node_order,tc_id,E.id ASC";
	  $recordset = $this->db->fetchRowsIntoMap($sql,'tc_id');

	  // 20070913 - jbarchibald
	  // here we add functionality to filter out the custom field selections
    if (!is_null($cf_hash)) {
        $recordset = $this->filter_cf_selection($recordset, $cf_hash);
    }
	  return $recordset;
}


// --------------------------------------------------------------------------------------
/*
  function: get_linked_and_newest_tcversions
            returns for every test case in a test plan
            the tc version linked and the newest available version

  args: id: testplan id
        [tcase_id]: default null => all testcases linked to testplan

  returns: map key: testcase internal id
           values: map with following keys:

            [name]
            [tc_id] (internal id)
            [tcversion_id]
            [newest_tcversion_id]
            [tc_external_id]
            [version] (for humans)
            [newest_version] (for humans)

  rev:
      20080614 - franciscom - fixed bug on SQL generated while
                              adding tc_external_id on results.
      20080126 - franciscom - added tc_external_id on results
*/
function get_linked_and_newest_tcversions($id,$tcase_id=null)
{
  $tc_id_filter = " ";
	if (!is_null($tcase_id) )
	{
	   if( is_array($tcase_id) )
	   {
        // ??? implement as in ?
	   }
	   else if ($tcase_id > 0 )
	   {
	      $tc_id_filter = " AND NHA.parent_id = {$tcase_id} ";
	   }
	}
	
	// 20080614 - franciscom
	// Peter Rooms found bug due to wrong SQL, accepted by MySQL but not by PostGres
	// Missing column in GROUP BY Clause

	$sql = " SELECT MAX(NHB.id) AS newest_tcversion_id, " .
	       " NHA.parent_id AS tc_id, NHC.name, T.tcversion_id AS tcversion_id," .
	       " TCVA.tc_external_id AS tc_external_id, TCVA.version AS version " .
	       " FROM {$this->nodes_hierarchy_table} NHA " .
	       
	       // NHA - will contain ONLY nodes of type testcase_version that are LINKED to test plan
	       " JOIN {$this->testplan_tcversions_table} T ON NHA.id = T.tcversion_id " . 

	       // Get testcase_version data for LINKED VERSIONS
	       " JOIN {$this->tcversions_table} TCVA ON TCVA.id = T.tcversion_id" .

	       // Work on Sibblings - Start
	       // NHB - Needed to get ALL testcase_version sibblings nodes
	       " JOIN {$this->nodes_hierarchy_table} NHB ON NHB.parent_id = NHA.parent_id " .
	       
	       // Want only ACTIVE Sibblings
	       " JOIN {$this->tcversions_table} TCVB ON TCVB.id = NHB.id AND TCVB.active=1 " . 
	       // Work on Sibblings - STOP 

	       // NHC will contain - nodes of type TESTCASE (parent of testcase versions we are working on)
	       // we use NHC to get testcase NAME ( testcase version nodes have EMPTY NAME)
	       " JOIN {$this->nodes_hierarchy_table} NHC ON NHC.id = NHA.parent_id " .
         
         // Want to get only testcase version with id (NHB.id) greater than linked one (NHA.id)
	       " WHERE T.testplan_id={$id} AND NHB.id > NHA.id" . $tc_id_filter .
	       " GROUP BY NHA.parent_id, NHC.name, T.tcversion_id, TCVA.tc_external_id, TCVA.version  ";

	$sql2 = " SELECT SUBQ.name, SUBQ.newest_tcversion_id, SUBQ.tc_id, " .
	        " SUBQ.tcversion_id, SUBQ.version, SUBQ.tc_external_id, " .
	        " TCV.version AS newest_version " .
	        " FROM {$this->tcversions_table} TCV, ( $sql ) AS SUBQ " .
	        " WHERE SUBQ.newest_tcversion_id = TCV.id " .
	        " ORDER BY SUBQ.tc_id ";

	return $this->db->fetchRowsIntoMap($sql2,'tc_id');
}


// --------------------------------------------------------------------------------------
/**
 * Remove of records from user_assignments table
 * 
 * @author franciscom
 * @param $id   : test plan id
 * @param $items: assoc array key=tc_id value=tcversion_id
 * @return N/A 
 */
function unlink_tcversions($id,&$items)
{
	if(!is_null($items))
	{
	    // Get human readeable info for audit
      $gui_cfg = config_get('gui');
      $auditInfo=$this->tcversionInfoForAudit($id,$items);
      $info=$auditInfo['info'];
      $tcasePrefix=$auditInfo['tcasePrefix'];
      $tplanInfo=$auditInfo['tplanInfo'];
	    
		  $idList = implode(",",$items);
	    $in_clause = " AND tcversion_id IN (" . $idList . ")";

      // Need to remove all related info:
      // execution_bugs - to be done
      // executions

      // First get the executions id if any exist
      $sql=" SELECT id AS execution_id
             FROM executions
             WHERE testplan_id = {$id} ${in_clause}";
      $exec_ids = $this->db->fetchRowsIntoMap($sql,'execution_id');

      if( !is_null($exec_ids) and count($exec_ids) > 0 )
      {
          // has executions
          $exec_ids = array_keys($exec_ids);
          $exec_id_where= " WHERE execution_id IN (" . implode(",",$exec_ids) . ")";

          // Remove bugs if any exist
          $sql=" DELETE FROM execution_bugs {$exec_id_where} ";
          $result = $this->db->exec_query($sql);

          // now remove executions
          $sql=" DELETE FROM executions
                 WHERE testplan_id = {$id} ${in_clause}";
          $result = $this->db->exec_query($sql);
      }

      // ----------------------------------------------------------------
      // 20060910 - franciscom
      // to remove the assignment to users (if any exists)
      // we need the list of id
      $sql=" SELECT id AS link_id FROM {$this->testplan_tcversions_table}
             WHERE testplan_id={$id} {$in_clause} ";
	    // $link_id = $this->db->get_recordset($sql);
	    $link_ids = $this->db->fetchRowsIntoMap($sql,'link_id');
	    $features = array_keys($link_ids);
	    if( count($features) == 1)
	    {
	      $features=$features[0];
	    }
	    $this->assignment_mgr->delete_by_feature_id($features);
	    // ----------------------------------------------------------------

      // Delete from link table
      $sql=" DELETE FROM {$this->testplan_tcversions_table}
             WHERE testplan_id={$id} {$in_clause} ";
	    $result = $this->db->exec_query($sql);


	    foreach($items as $tc => $tcversion)
	    {
	    		$auditMsg=TLS("audit_tc_removed_from_testplan",
	    		              $tcasePrefix . $info[$tcversion]['tc_external_id'] . 
	    		              $gui_cfg->title_separator_1 . $info[$tcversion]['name'],
	    		              $info[$tcversion]['version'],$tplanInfo['name']);
	    		              
	    		logAuditEvent($auditMsg,"UNASSIGN",$id,"testplans");
	    }
	}
} // end function unlink_tcversions


// --------------------------------------------------------------------------------------
// 20060430 - franciscom
function get_keywords_map($id,$order_by_clause='')
{
  $map_keywords=null;

  // keywords are associated to testcase id, then first
  // we need to get the list of testcases linked to the testplan
  $linked_items = $this->get_linked_tcversions($id);
  if( !is_null($linked_items) )
  {
     $tc_id_list = implode(",",array_keys($linked_items));

  	 $sql = "SELECT DISTINCT keyword_id,keywords.keyword
	           FROM testcase_keywords,keywords
	           WHERE keyword_id = keywords.id
	           AND testcase_id IN ( {$tc_id_list} )
	           {$order_by_clause}";
	   $map_keywords = $this->db->fetchColumnsIntoMap($sql,'keyword_id','keyword');
  }
  return ($map_keywords);
} // end function


// --------------------------------------------------------------------------------------
/*
  function: get_keywords_tcases 

  args :
        [$keyword_id]: can be an array
        
  returns: TBD

*/
function get_keywords_tcases($id,$keyword_id=0)
{
  $CUMULATIVE=1;
  $map_keywords=null;

  // keywords are associated to testcase id, then first
  // we need to get the list of testcases linked to the testplan
  $linked_items = $this->get_linked_tcversions($id);
  if( !is_null($linked_items) )
  {
     $keyword_filter= '' ;
     
     if( is_array($keyword_id) )
     {
         $keyword_filter = " AND keyword_id IN (" . implode(',',$keyword_id) . ")"; 
     }
     else if( $keyword_id > 0 )
     {
         $keyword_filter = " AND keyword_id = {$keyword_id} ";
     }
     
     
     $tc_id_list = implode(",",array_keys($linked_items));

     // 20081116 - franciscom -
     // Does DISTINCT is needed ? Humm now I think no.
  	 $sql = "SELECT DISTINCT testcase_id,keyword_id,keyword
	           FROM testcase_keywords,keywords
	           WHERE keyword_id = keywords.id
	           AND testcase_id IN ( {$tc_id_list} )
 		         {$keyword_filter}
			       ORDER BY keyword ASC ";
			       
		// 20081116 - franciscom
		// CUMULATIVE is needed to get all keywords assigned to each testcase linked to testplan	       
		$map_keywords = $this->db->fetchRowsIntoMap($sql,'testcase_id',$CUMULATIVE);
  }
  
  return ($map_keywords);
} // end function


// --------------------------------------------------------------------------------------
/*
  function: copy_as
            creates a new test plan using an existent one as source.
	Note:	copy_test_urgency is not appropriate to copy


  args: id: source testplan id
        new_tplan_id: destination
        [tplan_name]: default null.
                      != null => set this as the new name

        [tproject_id]: default null.
                       != null => set this as the new testproject for the testplan
                              this allow us to copy testplans to differents test projects.
        [copy_options]: default null
                        null: do a deep copy => copy following test plan child elements:
                              builds,linked tcversions,milestones,
                              user_roles,priorities.
                        != null, a map with keys that controls what child elements to copy

        [tcversion_type]:  default null -> use same version present on source testplan
                          'lastest' -> for every testcase linked to source testplan
                                      use lastest available version

  returns: N/A
*/
function copy_as($id,$new_tplan_id,$tplan_name=null,
                 $tproject_id=null,$copy_options=null,$tcversion_type=null)
{
  // CoPy configuration
  // Configure here only elements that has his own table.
  // Exception example:
  //                   test urgency information is not stored in a special table
  //                   then key can set to 0, or better REMOVED.
  //
  $cp_options = array('copy_tcases' => 1,'copy_milestones' => 1, 'copy_user_roles' => 1, 'copy_builds' => 1);
  $cp_methods = array('copy_tcases' => 'copy_linked_tcversions',
	                    'copy_milestones' => 'copy_milestones',
	                    'copy_user_roles' => 'copy_user_roles',
	                    'copy_builds' => 'copy_builds');


  if( !is_null($copy_options) )
  {
    $cp_options=$copy_options;
  }

  // get source testplan general info
  $rs_source=$this->get_by_id($id);

  if(!is_null($tplan_name))
  {
    $sql="UPDATE {$this->nodes_hierarchy_table} " .
         "SET name='" . $this->db->prepare_string(trim($tplan_name)) . "' " .
         "WHERE id={$new_tplan_id}";
    $this->db->exec_query($sql);
  }

  if(!is_null($tproject_id))
  {
    $sql="UPDATE {$this->testplans_table} SET testproject_id={$tproject_id} " .
         "WHERE id={$new_tplan_id}";
    $this->db->exec_query($sql);
  }


  foreach( $cp_options as $key => $do_copy )
  {
    if( $do_copy )
    {
      if( isset($cp_methods[$key]) )
      {
          $copy_method=$cp_methods[$key];
          $this->$copy_method($id,$new_tplan_id,$tcversion_type);
      }
    }
  }

} // end function copy_as


// --------------------------------------------------------------------------------------
// $id: source testplan id
// $new_tplan_id: destination
//
private function copy_builds($id,$new_tplan_id)
{
  $rs=$this->get_builds($id);

  if(!is_null($rs))
  {
    foreach($rs as $build)
    {
      $sql="INSERT INTO {$this->builds_table} (name,notes,testplan_id) " .
           "VALUES ('" . $this->db->prepare_string($build['name']) ."'," .
           "'" . $this->db->prepare_string($build['notes']) ."',{$new_tplan_id})";

      $this->db->exec_query($sql);
    }
  }
}


// --------------------------------------------------------------------------------------
/*
  function: copy_linked_tcversions

  args: id: source testplan id
        new_tplan_id: destination
        [tcversion_type]: default null -> use same version present on source testplan
                          'lastest' -> for every testcase linked to source testplan
                                      use lastest available version

  returns:
  
  Note: test urgency is set to default in the new Test plan (not copied)
*/
private function copy_linked_tcversions($id,$new_tplan_id,$tcversion_type=null)
{
  $sql="SELECT * FROM {$this->testplan_tcversions_table} WHERE testplan_id={$id} ";

  $rs=$this->db->get_recordset($sql);

  if(!is_null($rs))
  {
   	$tcase_mgr = new testcase($this->db);

    foreach($rs as $elem)
    {
      $tcversion_id = $elem['tcversion_id'];

  		if( !is_null($tcversion_type) )
		  {
			  $sql="SELECT * FROM {$this->nodes_hierarchy_table} WHERE id={$tcversion_id} ";
			  $rs2=$this->db->get_recordset($sql);
			  $last_version_info = $tcase_mgr->get_last_version_info($rs2[0]['parent_id']);
			  $tcversion_id = $last_version_info ? $last_version_info['id'] : $tcversion_id ;
		  }

      $sql="INSERT INTO {$this->testplan_tcversions_table} " .
           "(testplan_id,tcversion_id) " .
           "VALUES({$new_tplan_id},{$tcversion_id})";
      $this->db->exec_query($sql);
    }
  }
}


// --------------------------------------------------------------------------------------
/*
  function: copy_milestones

  args: id: source testplan id
        new_tplan_id: destination

  returns:

  rev : 20070519 - franciscom
        changed date to target_date, because date is an Oracle reverved word.

*/
private function copy_milestones($tplan_id,$new_tplan_id)
{
  $rs=$this->get_milestones($tplan_id);
  if(!is_null($rs))
  {
    foreach($rs as $mstone)
    {
      $sql="INSERT INTO {$this->milestones_table} (name,a,b,c,target_date,testplan_id) " .
           "VALUES ('" . $this->db->prepare_string($mstone['name']) ."'," .
           $mstone['high_percentage'] . "," . $mstone['medium_percentage'] . "," . 
           $mstone['low_percentage'] . ",'" . $mstone['target_date'] . "',{$new_tplan_id})";
      $this->db->exec_query($sql);
    }
  }
}


// --------------------------------------------------------------------------------------
/**
 * Get all milestones for a Test Plan
 * @param int $tplan_id Test Plan identificator
 * @return array of arrays TBD fields description 
 */
function get_milestones($tplan_id)
{
	$sql="SELECT id, name, a AS high_percentage, b AS medium_percentage, c AS low_percentage, " .
	     "target_date, testplan_id " .       
	     "FROM {$this->milestones_table} " .
	     "WHERE testplan_id={$tplan_id} ORDER BY target_date,name";
	return $this->db->get_recordset($sql);
}


// --------------------------------------------------------------------------------------
/**
 * Copy user roles to a new Test Plan
 * @param int $original_tplan_id original Test Plan identificator
 * @param int $new_tplan_id new Test Plan identificator
 * @return N/A 
 */
private function copy_user_roles($original_tplan_id, $new_tplan_id)
{
	$sql = "SELECT * FROM user_testplan_roles WHERE testplan_id={$original_tplan_id} ";
	$rs=$this->db->get_recordset($sql);

	if(!is_null($rs))
	{
    	foreach($rs as $elem)
    	{
      		$sql="INSERT INTO user_testplan_roles " .
           		"(testplan_id,user_id,role_id) " .
           		"VALUES({$new_tplan_id}," . $elem['user_id'] ."," . $elem['role_id'] . ")";
      		$this->db->exec_query($sql);
		}
	}
}


// --------------------------------------------------------------------------------------
/**
 * Gets all testplan related user assignments
 *
 * @param int $testPlanID the testplan id
 * @return array assoc map with keys taken from the user_id column
 **/
	function getUserRoleIDs($testPlanID)
	{
		$query = "SELECT user_id,role_id FROM user_testplan_roles WHERE testplan_id = {$testPlanID}";
		$roles = $this->db->fetchRowsIntoMap($query,'user_id');
		return $roles;
	}


// --------------------------------------------------------------------------------------
/**
 * Inserts a testplan related role for a given user
 *
 * @param int $userID the id of the user
 * @param int $testPlanID the testplan id
 * @param int $roleID the role id
 * @return returns tl::OK on success, tl::ERROR else
 **/

function addUserRole($userID,$testPlanID,$roleID)
{
	$query = "INSERT INTO user_testplan_roles (user_id,testplan_id,role_id) VALUES " .
			" ({$userID},{$testPlanID},{$roleID})";
	if ($this->db->exec_query($query))
	{
		$testPlan = $this->get_by_id($testPlanID);
		$role = tlRole::getByID($this->db,$roleID,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
		$user = tlUser::getByID($this->db,$userID,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
		if ($user && $testPlan && $role)
			logAuditEvent(TLS("audit_users_roles_added_testplan",$user->getDisplayName(),
			$testPlan['name'],$role->name),"ASSIGN",$testPlanID,"testplans");
		return tl::OK;
	}
	return tl::ERROR;
}


// --------------------------------------------------------------------------------------
/**
 * Deletes all testplan related role assignments for a given testplan
 *
 * @param int $testPlanID the testplan id
 * @return tl::OK  on success, tl::FALSE else
 **/
function deleteUserRoles($testPlanID)
{
	$query = "DELETE FROM user_testplan_roles WHERE testplan_id = {$testPlanID}";
	if ($this->db->exec_query($query))
	{
		$testPlan = $this->get_by_id($testPlanID);
		if ($testPlan)
			logAuditEvent(TLS("audit_all_user_roles_removed_testplan",$testPlan['name']),"ASSIGN",$testPlanID,"testplans");
		return tl::OK;
	}
	return tl::ERROR;
}


// --------------------------------------------------------------------------------------
/*
  function:

  args :

  returns:

  rev :
        20070129 - franciscom - added custom field management
*/
function delete($id)
{
  $the_sql=array();
  $main_sql=array();

  $this->deleteUserRoles($id);
  $the_sql[]="DELETE FROM {$this->milestones_table} WHERE testplan_id={$id}";
  
  // 20080815 - franciscom
  // CF used on testplan_design are linked by testplan_tcversions.id
  $the_sql[]="DELETE FROM {$this->cfield_testplan_design_values_table} WHERE link_id ".
             "IN (SELECT id FROM {$this->testplan_tcversions_table} WHERE testplan_id={$id})";
  
  $the_sql[]="DELETE FROM {$this->testplan_tcversions_table} WHERE testplan_id={$id}";
  
  $the_sql[]="DELETE FROM {$this->builds_table} WHERE testplan_id={$id}";
  $the_sql[]="DELETE FROM {$this->cfield_execution_values_table} WHERE testplan_id={$id}";

  // When deleting from executions, we need to clean related tables
  $the_sql[]="DELETE FROM {$this->execution_bugs_table} WHERE execution_id ".
             "IN (SELECT id FROM {$this->executions_table} WHERE testplan_id={$id})";
  $the_sql[]="DELETE FROM {$this->executions_table} WHERE testplan_id={$id}";


  foreach($the_sql as $sql)
  {
    $this->db->exec_query($sql);
  }

  $this->deleteAttachments($id);

  $this->cfield_mgr->remove_all_design_values_from_node($id);
  // ------------------------------------------------------------------------

  // Finally delete from main table
  $main_sql[]="DELETE FROM {$this->testplans_table} WHERE id={$id}";
  $main_sql[]="DELETE FROM {$this->nodes_hierarchy_table} WHERE id={$id}";

  foreach($main_sql as $sql)
  {
    $this->db->exec_query($sql);
  }
} // end delete()



// --------------------------------------------------------------------------------------
// Build related methods
// --------------------------------------------------------------------------------------

/*
  function: get_builds_for_html_options()


  args :
        $id     : test plan id.
        [active]: default:null -> all, 1 -> active, 0 -> inactive BUILDS
        [open]  : default:null -> all, 1 -> open  , 0 -> closed/completed BUILDS

  returns:

  rev :
        20070129 - franciscom - order to ASC
        20070120 - franciscom
        added active, open
*/
function get_builds_for_html_options($id,$active=null,$open=null)
{
	$sql = " SELECT id, name " .
	       " FROM {$this->builds_table} WHERE testplan_id = {$id} ";

	// 20070120 - franciscom
 	if( !is_null($active) )
 	{
 	   $sql .= " AND active=" . intval($active) . " ";
 	}
 	if( !is_null($open) )
 	{
 	   $sql .= " AND is_open=" . intval($open) . " ";
 	}

  $sql .= " ORDER BY name ASC";


	// BUGID
	$recordset=$this->db->fetchColumnsIntoMap($sql,'id','name');
	if( !is_null($recordset) )
	{
	  natsort($recordset);
	}

	return $recordset;

}


// --------------------------------------------------------------------------------------
/*
  function: get_max_build_id

  args :
        $id     : test plan id.

  returns:
*/
function get_max_build_id($id,$active = null,$open = null)
{
	$sql = " SELECT MAX(id) AS maxbuildid " .
	       " FROM {$this->builds_table} " .
	       " WHERE testplan_id = {$id}";

 	if(!is_null($active))
 	{
 	   $sql .= " AND active = " . intval($active) . " ";
 	}
 	if( !is_null($open) )
 	{
 	   $sql .= " AND is_open = " . intval($open) . " ";
 	}

	$recordset = $this->db->get_recordset($sql);
	$maxBuildID = 0;
	if ($recordset)
	{
		$maxBuildID = intval($recordset[0]['maxbuildid']);
  }
	return $maxBuildID;
}

/*
  function: get_testsuites

  args :
	$id     : test plan id.
	
  
  returns: returns flat list of names of test suites (including nest test suites)  No particular Order.
  
*/
function get_testsuites($id)
{
    $sql = "SELECT nhgrandparent.name, nhgrandparent.id " . 
    "FROM testplan_tcversions tptcv, nodes_hierarchy nh, nodes_hierarchy nhparent, nodes_hierarchy nhgrandparent " . 
    "WHERE tptcv.tcversion_id = nh.id " .
    "AND nh.parent_id = nhparent.id " .
    "AND nhparent.parent_id = nhgrandparent.id " .
    "AND tptcv.testplan_id = " . $id . " " .
    "GROUP BY nhgrandparent.id " .
    "ORDER BY nhgrandparent.name" ;
    
    $recordset = $this->db->get_recordset($sql);
    
    //Now the recordset contains testsuites that have child test cases... 
    //However there could potentially be testsuites that only have grandchildren/greatgrandchildren
    //this will iterate through found test suites and check for 
    $superset = $recordset;
    foreach($recordset as $value)
    {
	$superset = array_merge($superset, $this->get_parenttestsuites($value['id']));
    }    
    
    //At this point there may be duplicates
    $dup_track = array();
    foreach($superset as $value)
    {
	if (!array_key_exists($value['id'],$dup_track))
	{
	    $dup_track[$value['id']] = true;
	    $finalset[] = $value;
	}        
    }    
    
    //Needs to be alphabetical based upon name attribute 
    usort($finalset, array("testplan", "compare_name"));
    return $finalset;
}

/*
 function: compare_name
Used for sorting a list by nest name attribute

  args :
	$a     : first array to compare
	$b       : second array to compare
  
  returns: an integer indicating the result of the comparison
 */
private function compare_name($a, $b)
{
    return strcasecmp($a['name'], $b['name']);
}

/*
 function: get_parenttestsuites

Used by get_testsuites
 
Recursive function used to get all the parent test suites of potentially testcase free testsuites.
If passed node id isn't the product then it's merged into result set.

  args :
	$id     : $id of potential testsuite
  
  returns: an array of all testsuite ancestors of $id
 */
private function get_parenttestsuites($id)
{
    $sql = "SELECT name, id, parent_id " .
	    "FROM nodes_hierarchy nh " .
	    "WHERE nh.node_type_id <> 1 " .
	    "AND nh.id = " . $id;
	    
    $recordset = $this->db->get_recordset($sql);
    
    $myarray = array();
    if (count($recordset) > 0)
    {        
	//Don't want parentid in final result so just adding in attributes we want.
	$myarray = array(array('name'=>$recordset[0]['name'], 'id'=>$recordset[0]['id']));
	
	$myarray = array_merge($myarray, $this->get_parenttestsuites($recordset[0]['parent_id'])); 
    }
    
    return $myarray;            
}


// --------------------------------------------------------------------------------------
/*
  function: get_builds
            get info about builds defined for a testlan.
            Build can be filtered by active and open status.

  args :
        id: test plan id.
        [active]: default:null -> all, 1 -> active, 0 -> inactive BUILDS
        [open]: default:null -> all, 1 -> open  , 0 -> closed/completed BUILDS

  returns: map, where elements are ordered by build name, using variant of nasort php function.
           key: build id
           value: map with following keys
                  id: build id
                  name: build name
                  notes: build notes
                  active: build active status
                  is_open: build open status
                  testplan_id

  rev :
        20070120 - franciscom
        added active, open
*/
function get_builds($id,$active=null,$open=null)
{
	$sql = " SELECT id,testplan_id, name, notes, active, is_open " .
	       " FROM {$this->builds_table} WHERE testplan_id = {$id} " ;

 	if( !is_null($active) )
 	{
 	   $sql .= " AND active=" . intval($active) . " ";
 	}
 	if( !is_null($open) )
 	{
 	   $sql .= " AND is_open=" . intval($open) . " ";
 	}

	$sql .= "  ORDER BY name ASC";

 	$recordset = $this->db->fetchRowsIntoMap($sql,'id');

  if( !is_null($recordset) )
  {
    $recordset = $this->_natsort_builds($recordset);
  }

	return $recordset;
}


// --------------------------------------------------------------------------------------
/**
 * get_build_by_name
 * Get a build belonging to a test plan, using build name as access key
 *
 * @param int id: test plan id
 * @param string build_name: 
 *
 */
function get_build_by_name($id,$build_name)
{
  $safe_build_name=$this->db->prepare_string(trim($build_name));

	$sql = " SELECT id,testplan_id, name, notes, active, is_open " .
	       " FROM {$this->builds_table} " .
	       " WHERE testplan_id = {$id} AND name='{$safe_build_name}'";


 	$recordset = $this->db->get_recordset($sql);
  $rs=null;
  if( !is_null($recordset) )
  {
     $rs=$recordset[0];
  }
  return $rs;
}

/**
 * get_build_by_id
 * Get a build belonging to a test plan, using build id as access key
 *
 * @param int id: test plan id
 * @param int build_id: 
 *
 */
function get_build_by_id($id,$build_id)
{
	$sql = " SELECT id,testplan_id, name, notes, active, is_open " .
	       " FROM {$this->builds_table} BUILDS " .
	       " WHERE testplan_id = {$id} AND BUILDS.id={$build_id}";

 	$recordset = $this->db->get_recordset($sql);
  $rs=null;
  if( !is_null($recordset) )
  {
     $rs=$recordset[0];
  }
  return $rs;
}


/**
 * Get the number of builds of a given TestPlan
 *
 * @param int tplanID test plan id
 *
 * @return int number of builds
 */
function getNumberOfBuilds($tplanID)
{
	$sql = "SELECT count(id) AS num_builds FROM builds WHERE builds.testplan_id = " . $tplanID;
	return $this->db->fetchOneValue($sql);
}

// --------------------------------------------------------------------------------------
/*
  function:
  args :
  returns:
*/
function _natsort_builds($builds_map)
{
  // BUGID - sort in natural order (see natsort in PHP manual)
  foreach($builds_map as $key => $value)
  {
    $vk[$value['name']]=$key;
    $build_names[$key]=$value['name'];
  }

  natsort($build_names);
  $build_num=count($builds_map);
  foreach($build_names as $key => $value)
  {
    $dummy[$key]=$builds_map[$key];
  }
  return $dummy;
}


// --------------------------------------------------------------------------------------
/*
  function: check_build_name_existence

  args:
       tplan_id: test plan id.
       build_name
      [build_id}: default: null
                  when is not null we add build_id as filter, this is useful
                  to understand if is really a duplicate when using this method
                  while managing update operations via GUI

  returns: 1 => name exists

  rev: 20080217 - franciscom - added build_id argument

*/
//@TODO: schlundus, this is only a special case of get_build_id_by_name, so it should be refactored
function check_build_name_existence($tplan_id,$build_name,$build_id=null,$case_sensitive=0)
{
 	$sql = " SELECT id, name, notes " .
	       " FROM {$this->builds_table} " .
	       " WHERE testplan_id = {$tplan_id} ";


	if($case_sensitive)
	{
	    $sql .= " AND name=";
	}
	else
	{
      $build_name=strtoupper($build_name);
	    $sql .= " AND UPPER(name)=";
	}
	$sql .= "'" . $this->db->prepare_string($build_name) . "'";

	if( !is_null($build_id) )
	{
	    $sql .= " AND id <> " . $this->db->prepare_int($build_id);
	}


  $result = $this->db->exec_query($sql);
  $status= $this->db->num_rows($result) ? 1 : 0;

	return $status;
}

/*
  function: get_build_id_by_name

Ignores case

  args :
	$tplan_id     : test plan id. 
	$build_name   : build name. 
  
  returns: 
  The ID of the build name specified regardless of case.

  rev :
*/
//@TODO: schlundus, this is only a special case of get_build_by_name, so it should be refactored
function get_build_id_by_name($tplan_id,$build_name)
{
     $sql = " SELECT builds.id, builds.name, builds.notes " .
	   " FROM builds " .
	   " WHERE builds.testplan_id = {$tplan_id} ";

      $build_name=strtoupper($build_name);        
	$sql .= " AND UPPER(builds.name)=";
    $sql .= "'" . $this->db->prepare_string($build_name) . "'";    
    
  //$result = $this->db->exec_query($sql);
  
    $recordset = $this->db->get_recordset($sql);
    $BuildID = 0;
    if ($recordset)
	$BuildID = intval($recordset[0]['id']);
	    
    return $BuildID;  
}

// --------------------------------------------------------------------------------------
/*
  function: create_build

  args :
        $tplan_id
        $name
        $notes
        [$active]: default: 1
        [$open]: default: 1



  returns:

  rev :
*/
function create_build($tplan_id,$name,$notes = '',$active=1,$open=1)
{
	$sql = " INSERT INTO {$this->builds_table} (testplan_id,name,notes,active,is_open) " .
	       " VALUES ('". $tplan_id . "','" .
	                     $this->db->prepare_string($name) . "','" .
	                     $this->db->prepare_string($notes) . "'," .
	                     "{$active},{$open})";

	$new_build_id = 0;
	$result = $this->db->exec_query($sql);
	if ($result)
	{
		$new_build_id = $this->db->insert_id($this->builds_table);
	}

	return $new_build_id;
}


// --------------------------------------------------------------------------------------
// Custom field related methods
// --------------------------------------------------------------------------------------
/*
  function: get_linked_cfields_at_design

  args: $id
        [$parent_id]: testproject id
        [$show_on_execution]: default: null
                              1 -> filter on field show_on_execution=1
                              0 or null -> don't filter

  returns: hash

  rev :
        20061231 - franciscom - added $parent_id
*/
function get_linked_cfields_at_design($id,$parent_id=null,$show_on_execution=null)
{
  $path_len=0;
  if( is_null($parent_id) )
  {
      // Need to get testplan parent (testproject id) in order to get custom fields
      // 20081122 - franciscom - need to check when we can call this with ID=NULL
      $the_path = $this->tree_manager->get_path(!is_null($id) ? $id : $parent_id);
      $path_len = count($the_path);
  }
  $tproject_id = ($path_len > 0)? $the_path[$path_len-1]['parent_id'] : $parent_id; 

  $cf_map = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,self::ENABLED,
                                                            $show_on_execution,'testplan',$id);

  return $cf_map;
}


// --------------------------------------------------------------------------------------
/*
  function: get_linked_cfields_at_execution

  args: $id
        [$parent_id]: if present is testproject id
        [$show_on_execution]: default: null
                              1 -> filter on field show_on_execution=1
                              0 or null -> don't filter

  returns: hash

  rev :
        20061231 - franciscom - added $parent_id
*/
function get_linked_cfields_at_execution($id,$parent_id=null,$show_on_execution=null)
{
  $path_len=0;
  if( is_null($parent_id) )
  {
      // Need to get testplan parent (testproject id) in order to get custom fields
      // 20081122 - franciscom - need to check when we can call this with ID=NULL
      $the_path = $this->tree_manager->get_path(!is_null($id) ? $id : $parent_id);
      $path_len = count($the_path);
  }
  $tproject_id = ($path_len > 0)? $the_path[$path_len-1]['parent_id'] : $parent_id; 

  // 20081122 - franciscom - humm!! need to look better IMHO this call is done to wrong function
  $cf_map=$this->cfield_mgr->get_linked_cfields_at_execution($tproject_id,self::ENABLED,
                                                             $show_on_execution,'testplan',$id);
  return($cf_map);
}

// --------------------------------------------------------------------------------------
/* Get Custom Fields  Detail which are enabled on Execution of a TestCase/TestProject.
  function: get_linked_cfields_id

  args: $testproject_id 

  returns: hash map of id : label

  rev :

*/

function get_linked_cfields_id($tproject_id)
{
	$field_map = new stdClass();
	
	$sql = "SELECT field_id,label
			FROM cfield_testprojects, custom_fields
			WHERE
			custom_fields.id = cfield_testprojects.field_id 
			and cfield_testprojects.active = 1 
			and custom_fields.enable_on_execution = 1 
			and custom_fields.show_on_execution = 1 
			and cfield_testprojects.testproject_id = {$tproject_id}
			order by field_id";
	
	$field_map = $this->db->fetchColumnsIntoMap($sql,'field_id','label');
	return($field_map);
}

// --------------------------------------------------------------------------------------
/* 

// --------------------------------------------------------------------------------------
/*
  function: html_table_of_custom_field_inputs

  args: $id
        [$parent_id]: need when you call this method during the creation
                      of a test suite, because the $id will be 0 or null.

        [$scope]: 'design','execution'

  returns: html string
*/
function html_table_of_custom_field_inputs($id,$parent_id=null,$scope='design')
{
  $cf_smarty='';

  if( $scope=='design' )
  {
	$cf_map = $this->get_linked_cfields_at_design($id,$parent_id);
  }
  else
  {
    $cf_map=$this->get_linked_cfields_at_execution($id,$parent_id);
  }

  if( !is_null($cf_map) )
  {
    foreach($cf_map as $cf_id => $cf_info)
    {
        $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label'],null,true));
        $cf_smarty .= '<tr><td class="labelHolder">' . htmlspecialchars($label) . "</td><td>" .
                      $this->cfield_mgr->string_custom_field_input($cf_info) . "</td></tr>\n";
    } //foreach($cf_map
  }


  if($cf_smarty != '')
  {
    $cf_smarty = "<table>" . $cf_smarty . "</table>";
  }
  return($cf_smarty);
}


// --------------------------------------------------------------------------------------
/*
  function: html_table_of_custom_field_values

  args: $id
        [$scope]: 'design','execution'
        
        [$filters]:default: null
                            
                           map with keys:
        
                           [show_on_execution]: default: null
                                                1 -> filter on field show_on_execution=1
                                                     include ONLY custom fields that can be viewed
                                                     while user is execution testcases.
                           
                                                0 or null -> don't filter

  returns: html string

  rev :
       20080811 - franciscom - BUGID 1650 (REQ)
       20070701 - franciscom - fixed return string when there are no custom fields.
*/
function html_table_of_custom_field_values($id,$scope='design',$filters=null,$formatOptions=null)
{
    $cf_smarty='';
    $parent_id=null;
    $td_style='class="labelHolder"' ;
    $add_table=true;
    $table_style='';
    if( !is_null($formatOptions) )
    {
        $td_style=isset($formatOptions['td_css_style']) ? $formatOptions['td_css_style'] : $td_style;
        $add_table=isset($formatOptions['add_table']) ? $formatOptions['add_table'] : true;
        $table_style=isset($formatOptions['table_css_style']) ? $formatOptions['table_css_style'] : $table_style;
    } 


    if( $scope=='design' )
    {
    	//@TODO: schlundus, can this be speed up with tprojectID?
      $cf_map=$this->get_linked_cfields_at_design($id,$parent_id,$filters);
    }
    else
    {
    	//@TODO: schlundus, can this be speed up with tprojectID?
      $cf_map=$this->get_linked_cfields_at_execution($id);
    }
    
    if( !is_null($cf_map) )
    {
      foreach($cf_map as $cf_id => $cf_info)
      {
        // if user has assigned a value, then node_id is not null
        if(isset($cf_info['node_id']) && $cf_info['node_id'])
        {
          // true => do not create input in audit log
          $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label'],null,true));
          $cf_smarty .= "<tr><td {$td_style}>" . htmlspecialchars($label) . "</td><td>" .
                        $this->cfield_mgr->string_custom_field_value($cf_info,$id) . "</td></tr>\n";
        }
      }
    }
    
    if($cf_smarty != '' && $add_table)
    {
      $cf_smarty = "<table {$table_style}>" . $cf_smarty . "</table>";
    }
    return($cf_smarty);
} // function end


// --------------------------------------------------------------------------------------
/*
  function: filter_cf_selection

  args :
        $tp_tcs - this comes from get_linked_tcversion
        $cf_hash [cf_id] = value of cfields to filter by.

  returns: array filtered by selected custom fields.

  rev :
*/
function filter_cf_selection ($tp_tcs, $cf_hash)
{
    $new_tp_tcs = null;

    foreach ($tp_tcs as $tc_id => $tc_value)
    {

        foreach ($cf_hash as $cf_id => $cf_value)
        {
            $passed = 0;
            // there will never be more than one record that has a field_id / node_id combination
            $sql = "SELECT value FROM {$this->cfield_design_values_table} " .
                    "WHERE field_id = $cf_id " .
                    "AND node_id = $tc_id ";

            $result = $this->db->exec_query($sql);
			      $myrow = $this->db->fetch_array($result);

            // push both to arrays so we can compare
            $possibleValues = explode ('|', $myrow['value']);
            $valuesSelected = explode ('|', $cf_value);

            // we want to match any selected item from list and checkboxes.
            if ( count($valuesSelected) ) {
                foreach ($valuesSelected as $vs_id => $vs_value) {
                    $found = array_search($vs_value, $possibleValues);
                    if (is_int($found)) {
                        $passed = 1;
                    } else {
                        $passed = 0;
                        break;
                    }
                }
            }
            // if we don't match, fall out of the foreach.
            // this gives a "and" search for all cf's, if this is removed then it responds
            // as an "or" search
            // perhaps this could be parameterized.
            if ($passed == 0) {
                break;
            }
        }
        if ($passed) {
            $new_tp_tcs[$tc_id] = $tp_tcs[$tc_id];
        }
    }
    return ($new_tp_tcs);
}


/*
  function: get_estimated_execution_time
            Created after a contributed code (BUGID 1670)

            Takes all testcases linked to testplan and computes
            SUM of values assigned AT DESIGN TIME to customa field
            named CF_ESTIMATED_EXEC_TIME

            IMPORTANT:
            1. at time of this writting (20080820) this CF can be of type: string,numeric or float.
            2. YOU NEED TO USE . (dot) as decimal separator (US decimal separator?) or
               sum will be wrong. 
         
            
            
  args:id testplan id
       tcase_set: default null

  returns: sum of CF values for all testcases linked to testplan

  rev: 20080820 - franciscom
*/
function get_estimated_execution_time($id,$tcase_set=null)
{
    // Get list of test cases on test plan
    $estimated=0;
    $cf_info = $this->cfield_mgr->get_by_name('CF_ESTIMATED_EXEC_TIME');

    // CF exists ?
    if( ($status_ok=!is_null($cf_info)) )
    {
      $cfield_id=key($cf_info);
    }

    if( $status_ok)
    {
        if( is_null($tcase_set) )
        {
            // we will compute time for ALL linked test cases
            $linked_testcases=$this->get_linked_tcversions($id);  
            if( ($status_ok=!is_null($linked_testcases)) )
            {
                $tcase_ids=array_keys($linked_testcases);
            }    
        }
        else
        {
            $tcase_ids=$tcase_set;  
        }
    }  
    
    if($status_ok)
    {
        $sql="SELECT SUM(CAST(value AS NUMERIC)) ";
        if( DB_TYPE == 'mysql')
        {
            $sql="SELECT SUM(value) ";
        } 
        else if ( DB_TYPE == 'postgres')
        {
            $sql="SELECT SUM(CAST(value AS NUMERIC)) ";
        }        
        $sql .= " AS SUM_VALUE FROM {$this->cfield_design_values_table} CFDV " .
                " WHERE CFDV.field_id={$cfield_id} " .
                " AND node_id IN (" . implode(',',$tcase_ids) . ")";
        $estimated=$this->db->fetchOneValue($sql);
        $estimated=is_null($estimated) ? 0 :$estimated;
    }
    
    return $estimated;
}    


/*
  function: get_execution_time
            Takes all testcases (or a subset of executions) linked to testplan 
            that has been executed and computes SUM of values assigned AT EXECUTION TIME 
            to customa field named CF_EXEC_TIME

            IMPORTANT:
            1. at time of this writting (20081207) this CF can be of type: string,numeric or float.
            2. YOU NEED TO USE . (dot) as decimal separator (US decimal separator?) or
               sum will be wrong. 
            
  args:id testplan id
       $execution_set: default null

  returns: sum of CF values for all testcases linked to testplan

  rev: 20081207 - franciscom
*/
function get_execution_time($id,$execution_set=null)
{
    $total_time=0;
    $cf_info = $this->cfield_mgr->get_by_name('CF_EXEC_TIME');

    // CF exists ?
    if( ($status_ok=!is_null($cf_info)) )
    {
      $cfield_id=key($cf_info);
    }

    if( $status_ok)
    {
        if( is_null($execution_set) )
        {
            // we will compute time for ALL linked and executed test cases,
            // just for LAST executed TCVERSION
            $linked_executed=$this->get_linked_tcversions($id,null,0,'just_executed'); 
            if( ($status_ok=!is_null($linked_executed)) )
            {
                foreach($linked_executed as $tcase_id => $info)
                {
                    $execution_ids[]=$info['exec_id'];
                }    
            }    
        }
        else
        {
            $execution_ids=$execution_set;  
        }
    }  
    
    if($status_ok)
    {
        $sql="SELECT SUM(CAST(value AS NUMERIC)) ";
        if( DB_TYPE == 'mysql')
        {
            $sql="SELECT SUM(value) ";
        } 
        else if ( DB_TYPE == 'postgres')
        {
            $sql="SELECT SUM(CAST(value AS NUMERIC)) ";
        }        

        $sql .= " AS SUM_VALUE FROM {$this->cfield_execution_values_table} CFEV " .
                " WHERE CFEV.field_id={$cfield_id} " .
                " AND testplan_id={$id} " .
                " AND execution_id IN (" . implode(',',$execution_ids) . ")";
     
        $total_time=$this->db->fetchOneValue($sql);
        $total_time=is_null($total_time) ? 0 :$total_time;
    }
    return $total_time;
}    


/*
  function: get_prev_builds() 

  args: id: testplan id
        build_id: all builds belonging to choosen testplan,
                  with id < build_id will be retreived.
        [active]: default null  -> do not filter on active status
  
  returns: 

*/
function get_prev_builds($id,$build_id,$active=null)
{
	$sql = " SELECT id,testplan_id, name, notes, active, is_open " .
	       " FROM {$this->builds_table} " . 
	       " WHERE testplan_id = {$id} AND id < {$build_id}" ;

 	if( !is_null($active) )
 	{
 	   $sql .= " AND active=" . intval($active) . " ";
 	}

 	$recordset = $this->db->fetchRowsIntoMap($sql,'id');
  return $recordset;
}


/*
  function: get_same_status_for_build_set() 
            returns set of tcversions that has same execution status
            in every build present on buildSet.

            ATTENTION!!!: this does not work for not_run status
            
  args: id: testplan id
        buildSet: builds to analise.
        status: status code
          
  returns: 

*/
function get_same_status_for_build_set($id,$buildSet,$status)
{
    $node_types=$this->tree_manager->get_available_node_types();
    $resultsCfg = config_get('results');

    $num_exec = count($buildSet);
    $build_in = implode(",", $buildSet);
    $status_in = implode("',", (array)$status);
    
    if( in_array($resultsCfg['status_code']['not_run'], (array)$status) )
    {
      
        $sql = " SELECT distinct T.tcversion_id,E.build_id,NH.parent_id AS tcase_id " .
               " FROM testplan_tcversions T " .
               " JOIN nodes_hierarchy NH ON T.tcversion_id=NH.id " .
               " AND NH.node_type_id={$node_types['testcase_version']} " .
               " LEFT OUTER JOIN executions E ON T.tcversion_id = E.tcversion_id " .
               " AND T.testplan_id=E.testplan_id AND E.build_id IN ({$build_in}) " .
               " WHERE T.testplan_id={$id} AND E.build_id IS NULL ";
    }
    else
    {
        $sql = " SELECT EE.status,SQ1.tcversion_id, NH.parent_id AS tcase_id, COUNT(EE.status) AS exec_qty " .
               " FROM executions EE, nodes_hierarchy NH," .
               " (SELECT E.tcversion_id,E.build_id,MAX(E.id) AS last_exec_id " .
               " FROM executions E " .
               " WHERE E.build_id IN ({$build_in}) " .
               " GROUP BY E.tcversion_id,E.build_id) AS SQ1 " .
               " WHERE EE.build_id IN ({$build_in}) " .
               " AND EE.status IN ('" . $status . "') AND NH.node_type_id={$node_types['testcase_version']} " .
               " AND SQ1.last_exec_id=EE.id AND SQ1.tcversion_id=NH.id " .
               " GROUP BY status,SQ1.tcversion_id,NH.parent_id" .
               " HAVING count(EE.status)= {$num_exec} " ;
    }
   
    // echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";
    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return $recordset;
}

} // end class testplan
// ######################################################################################







// ######################################################################################

// Build Manager Class

// ##################################################################################
class build_mgr
{
	var $db;
	var $builds_table="builds";

  /*
   function:

   args :

   returns:

  */
	function build_mgr(&$db)
	{
		$this->db = &$db;
	}


  /*
    function: create

    args :
          $tplan_id
          $name
          $notes
          [$active]: default: 1
          [$open]: default: 1



    returns:

    rev :
  */
  function create($tplan_id,$name,$notes = '',$active=1,$open=1)
  {
  	$sql = " INSERT INTO {$this->builds_table} (testplan_id,name,notes,active,is_open) " .
  	       " VALUES ('". $tplan_id . "','" .
  	                     $this->db->prepare_string($name) . "','" .
  	                     $this->db->prepare_string($notes) . "'," .
  	                     "{$active},{$open})";

  	$new_build_id = 0;
  	$result = $this->db->exec_query($sql);
  	if ($result)
  	{
  		$new_build_id = $this->db->insert_id($this->builds_table);
  	}

  	return $new_build_id;
  }


  /*
    function: update

    args :
          $id
          $name
          $notes
          [$active]: default: null
          [$open]: default: null



    returns:

    rev :
  */
  function update($id,$name,$notes,$active=null,$open=null)
  {
  	$sql = " UPDATE {$this->builds_table} " .
  	       " SET name='" . $this->db->prepare_string($name) . "'," .
  	       "     notes='" . $this->db->prepare_string($notes) . "'";

  	if( !is_null($active) )
  	{
  	   $sql .=" , active=" . intval($active);
  	}

  	if( !is_null($open) )
  	{
  	   $sql .=" , is_open=" . intval($open);
  	}


  	$sql .= " WHERE id={$id}";

  	$result = $this->db->exec_query($sql);
  	return $result ? 1 : 0;
  }





  /*
    function: delete

    args :
          $id


    returns:

    rev :
  */
  function delete($id)
  {

    //
  	$sql = " DELETE FROM executions " .
  	       " WHERE build_id={$id}";

  	$result=$this->db->exec_query($sql);

  	//
  	$sql = " DELETE FROM {$this->builds_table} " .
  	       " WHERE id={$id}";

  	$result=$this->db->exec_query($sql);
  	return $result ? 1 : 0;
  }


  /*
    function: get_by_id
              get information about a build

    args : id: build id

    returns: map with following keys
             id: build id
             name: build name
             notes: build notes
             active: build active status
             is_open: build open status
             testplan_id

    rev :
  */
  function get_by_id($id)
  {
  	$sql = "SELECT * FROM {$this->builds_table} WHERE id = {$id}";
  	$result = $this->db->exec_query($sql);
  	$myrow = $this->db->fetch_array($result);
  	return $myrow;
  }


} // end class build_mgr


// ##################################################################################
//
// Milestone Manager Class
//
// ##################################################################################
class milestone_mgr
{
	var $db;
  var $builds_table="builds";
 	var $cfield_design_values_table="cfield_design_values";
  var $cfield_execution_values_table="cfield_execution_values";
  var $cfield_testplan_design_values_table="cfield_testplan_design_values";  
  var $execution_bugs_table="execution_bugs";
  var $executions_table='executions';
  var $nodes_hierarchy_table='nodes_hierarchy';
	var $milestones_table='milestones';
  var $tcversions_table='tcversions';
  var $testplans_table="testplans";
	var $testplan_tcversions_table="testplan_tcversions";

  /*
   function:

   args :

   returns:

  */
	function milestone_mgr(&$db)
	{
		$this->db = &$db;
	}


  /*
    function: create()

    args :
            $tplan_id
            $name
            $target_date
            $low_priority: percentage
            $medium_priority: percentage
            $high_priority: percentage

    returns:

  */
  function create($tplan_id,$name,$date,$low_priority,$medium_priority,$high_priority)
  {
    $new_milestone_id=0;
  	$sql = "INSERT INTO {$this->milestones_table} (testplan_id,name,target_date,a,b,c) " .
  	       " VALUES (" . $tplan_id . ",'{$this->db->prepare_string($name)}','{$this->db->prepare_string($date)}'," . 
  	       $low_priority . "," .  $medium_priority . "," . $high_priority . ")";
  	$result = $this->db->exec_query($sql);

  	if ($result)
    {
    		$new_milestone_id = $this->db->insert_id($this->milestones_table);
    }

    return $new_milestone_id;
  }

  /*
    function: update

    args :
          $id
          $name
          $notes
          [$active]: default: 1
          [$open]: default: 1



    returns:

    rev :
  */
  function update($id,$name,$date,$low_priority,$medium_priority,$high_priority)
  {
	  $sql = "UPDATE {$this->milestones_table} SET name='{$this->db->prepare_string($name)}', " .
	         " target_date='{$this->db->prepare_string($date)}', " .
	         " a={$low_priority}, b={$medium_priority}, c={$high_priority} WHERE id={$id}";
	  $result = $this->db->exec_query($sql);
	  return $result ? 1 : 0;
  }



  /*
    function: delete

    args :
          $id


    returns:

  */
  function delete($id)
  {
  	$sql = "DELETE FROM {$this->milestones_table} WHERE id={$id}";
  	$result=$this->db->exec_query($sql);
  	return $result ? 1 : 0;
  }


  /*
    function: get_by_id

    args :
          $id
    returns:

    rev: 20090103 - franciscom - get test plan name.
  */
  function get_by_id($id)
  {
	  $sql=" SELECT M.id, M.name, M.a AS high_percentage, M.b AS medium_percentage, M.c AS low_percentage, " .
	       " M.target_date, M.testplan_id, NH.name as testplan_name " .   
         " FROM {$this->milestones_table} M, {$this->nodes_hierarchy_table} NH " .
  	     " WHERE M.id = {$id} AND NH.id=M.testplan_id";
  	$myrow = $this->db->fetchRowsIntoMap($sql,'id');
  	return $myrow;
  }

/*
  function: check_name_existence

  args:
       tplan_id: test plan id.
       milestone_name
      [milestone_id}: default: null
                      when is not null we add milestone_id as filter, this is useful
                      to understand if is really a duplicate when using this method
                      while managing update operations via GUI

  returns: 1 => name exists

  rev: 

*/
function check_name_existence($tplan_id,$milestone_name,$milestone_id=null,$case_sensitive=0)
{
 	$sql = " SELECT id, name" .
	       " FROM {$this->milestones_table} " .
	       " WHERE testplan_id = {$tplan_id} ";

	if($case_sensitive)
	{
	    $sql .= " AND name=";
	}
	else
	{
      $milestone_name=strtoupper($milestone_name);
	    $sql .= " AND UPPER(name)=";
	}
	$sql .= "'{$this->db->prepare_string($milestone_name)}'";

	if( !is_null($milestone_id) )
	{
	    $sql .= " AND id <> " . $this->db->prepare_int($milestone_id);
	}

  $result = $this->db->exec_query($sql);
  $status= $this->db->num_rows($result) ? 1 : 0;

	return $status;
}


  /*
    function: get_all_by_testplan
              get info about all milestones defined for a testlan
    args :
          tplan_id


    returns:

    rev :
  */
  function get_all_by_testplan($tplan_id)
  {

	  $sql=" SELECT M.id, M.name, M.a AS high_percentage, M.b AS medium_percentage, M.c AS low_percentage, " .
	       " M.target_date, M.testplan_id, NH.name as testplan_name " .   
         " FROM {$this->milestones_table} M, {$this->nodes_hierarchy_table} NH " .
	       " WHERE testplan_id={$tplan_id} AND NH.id = testplan_id " .
	       " ORDER BY M.target_date,M.name";
    $rs=$this->db->get_recordset($sql);
    return $rs;
  }


} // end class milestone_mgr

?>
