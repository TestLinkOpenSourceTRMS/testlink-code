<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Manages test plan operations and related items like Custom fields, 
 * Builds, Custom fields, etc
 *
 * @filesource	testplan.class.php
 * @package 	TestLink
 * @author 		franciscom
 * @copyright 	2007-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 *
 * @internal revisions
 *  20110415 - Julian - BUGID 4418 - Clean up priority usage within Testlink
 *	20110408 - franciscom - BUGID 4391: General Test Plan Metrics - 
 *							Results by Keywords does not work properly when platforms are used
 *	20110328 - franciscom - filter_cf_selection() fixed issue regarding simple types
 *	20110326 - franciscom - filter_cf_selection() make it safer
 *	20110322 - franciscom - BUGID 4343: Reports Failed Test Cases / ... -> Build is not shown	
 *							get_linked_tcversions() - error while refactoring.
 *
 *	20110317 - franciscom - BUGID 4328: Metrics dashboard - only active builds has to be used
 *							get_linked_tcversions() - new option forced_exec_status
 *													  build_id now can be an array of id
 *
 *	20110316 - franciscom - BUGID 4328: Metrics dashboard - only active builds has to be used
 *							get_linked_tcversions()
 *
 *	20110121 - franciscom - BUGID 4184 - MSSQL Ambiguous column name platform_id
 *  20110115 - franciscom - BUGID 4171 - changes on methods related to estimated execution time
 *  20110112 - franciscom - BUGID 4171 - changes on methods related to estimated execution time
 *  20110104 - asimon - BUGID 4118: Copy Test plan feature is not copying test cases for all platforms
 **/

/** related functionality */
require_once( dirname(__FILE__) . '/tree.class.php' );
require_once( dirname(__FILE__) . '/assignment_mgr.class.php' );
require_once( dirname(__FILE__) . '/attachments.inc.php' );

/**
 * class to coordinate and manage Test Plans
 * @package 	TestLink
 * @todo havlatm: create class testplanEdit (as extension of testplan class) and 
 *		move here create,edit,delete,copy related stuff
 * @TODO franciscom - absolutely disagree with suggested approach, see no value - 20090611
 */
class testplan extends tlObjectWithAttachments
{
	/** query options */
	const GET_ALL=null;
	const GET_ACTIVE_BUILD=1;
	const GET_INACTIVE_BUILD=0;
	const GET_OPEN_BUILD=1;
	const GET_CLOSED_BUILD=0;
	const ACTIVE_BUILDS=1;
	const ENABLED=1;

	/** @var database handler */
	var $db;

	var $tree_manager;
	var $assignment_mgr;
	var $cfield_mgr;
	var $tcase_mgr;
   
	var $assignment_types;
	var $assignment_status;

	/** message to show on GUI */
	var $user_feedback_message = '';

	var $node_types_descr_id;
	var $node_types_id_descr;

	var $import_file_types = array("XML" => "XML"); // array("XML" => "XML", "XLS" => "XLS" );
	
	/**
	 * testplan class constructor
	 * 
	 * @param resource &$db reference to database handler
	 */
	function __construct(&$db)
	{
	    $this->db = &$db;
	    $this->tree_manager = New tree($this->db);
		$this->node_types_descr_id=$this->tree_manager->get_available_node_types();
		$this->node_types_id_descr=array_flip($this->node_types_descr_id);
      
	    $this->assignment_mgr = new assignment_mgr($this->db);
	    $this->assignment_types = $this->assignment_mgr->get_available_types();
	    $this->assignment_status = $this->assignment_mgr->get_available_status();

    	$this->cfield_mgr = new cfield_mgr($this->db);
    	$this->tcase_mgr = New testcase($this->db);
		$this->platform_mgr = new tlPlatform($this->db);
   	
	    tlObjectWithAttachments::__construct($this->db,'testplans');
	}

	/**
	 * getter for import types 
 	 * @return array key: import file type code, value: import file type verbose description
 	 */
	function get_import_file_types()
	{
	    return $this->import_file_types;
	}

	/**
	 * creates a tesplan on Database, for a testproject.
	 * 
	 * @param string $name: testplan name
	 * @param string $notes: testplan notes
	 * @param string $testproject_id: testplan parent
	 * 
	 * @return integer status code
	 * 		if everything ok -> id of new testplan (node id).
	 * 		if problems -> 0.
	 */
	function create($name,$notes,$testproject_id,$is_active=1,$is_public=1)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$node_types=$this->tree_manager->get_available_node_types();
		$tplan_id = $this->tree_manager->new_node($testproject_id,$node_types['testplan'],$name);
		
		$active_status=intval($is_active) > 0 ? 1 : 0;
		$public_status=intval($is_public) > 0 ? 1 : 0;
		
		$sql = "/* $debugMsg */ " . 
		       " INSERT INTO {$this->tables['testplans']} (id,notes,testproject_id,active,is_public) " .
			   " VALUES ( {$tplan_id} " . ", '" . $this->db->prepare_string($notes) . "'," . 
			   $testproject_id . "," . $active_status . "," . $public_status . ")";
		$result = $this->db->exec_query($sql);
		$id = 0;
		if ($result)
		{
			$id = $tplan_id;
		}

		return $id;
	}


	/**
	 * update testplan information
	 * 
	 * @param integer $id Test plan identifier
	 * @param string $name: testplan name
	 * @param string $notes: testplan notes
	 * @param boolean $is_active
	 * 
	 * @return integer result code (1=ok)
	 */
	function update($id,$name,$notes,$is_active=null,$is_public=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$do_update = 1;
		$result = null;
		// $active = to_boolean($is_active);
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
            $sql = "/* $debugMsg */ ";
			$sql .= "UPDATE {$this->tables['nodes_hierarchy']} " .
				    "SET name='" . $this->db->prepare_string($name) . "'" .
				    "WHERE id={$id}";
			$result = $this->db->exec_query($sql);
			
			if($result)
			{
				$add_upd='';
				if( !is_null($is_active) )
				{
					$add_upd .=',active=' . (intval($is_active) > 0 ? 1 : 0);
				}
				if( !is_null($is_public) )
				{
					$add_upd .=',is_public=' . (intval($is_public) > 0 ? 1:0);
				}
				
				$sql = " UPDATE {$this->tables['testplans']} " .
					" SET notes='" . $this->db->prepare_string($notes). "' " .
					" {$add_upd} WHERE id=" . $id;
				$result = $this->db->exec_query($sql);
			}
		}
		return ($result ? 1 : 0);
	}


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
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
        $sql = "/* $debugMsg */ ";
		$sql .= " SELECT testplans.*, NH.name " .
			    " FROM {$this->tables['testplans']} testplans, " .
			    " {$this->tables['nodes_hierarchy']} NH" .
			    " WHERE testplans.id=NH.id " .
			    " AND NH.name = '" . $this->db->prepare_string($name) . "'";
		
		if($tproject_id > 0 )
		{
			$sql .= " AND NH.parent_id={$tproject_id}";
		}
		
		$recordset = $this->db->get_recordset($sql);
		return($recordset);
	}


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
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
        $sql = "/* $debugMsg */ " .
		       " SELECT testplans.*,NH.name,NH.parent_id " .
			   " FROM {$this->tables['testplans']} testplans, " .
			   " {$this->tables['nodes_hierarchy']} NH " .
			   " WHERE testplans.id = NH.id AND  testplans.id = {$id}";
		$recordset = $this->db->get_recordset($sql);
		return($recordset ? $recordset[0] : null);
	}


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
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
        $sql = "/* $debugMsg */ " .
		       " SELECT testplans.*, NH.name " .
			   " FROM {$this->tables['testplans']} testplans, " .
			   " {$this->tables['nodes_hierarchy']} NH " .
			   " WHERE testplans.id=NH.id";
		$recordset = $this->db->get_recordset($sql);
		return $recordset;
	}

	/*
	  function: count_testcases
            get number of testcases linked to a testplan

	  args: id: testplan id

            [platform_id]: null => do not filter by platform
            
	  returns: number
	*/
	public function count_testcases($id,$platform_id=null)
	{
		$sql_filter = '';
		if( !is_null($platform_id) )
		{
			$sql_filter = " AND platform_id={$platform_id} ";
		}
		
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
        $sql = "/* $debugMsg */ " .
	           " SELECT COUNT(testplan_id) AS qty " .
		       " FROM {$this->tables['testplan_tcversions']} " .
			   " WHERE testplan_id={$id} {$sql_filter}";
		$recordset = $this->db->get_recordset($sql);
		$qty = 0;
		if(!is_null($recordset))
		{
			$qty = $recordset[0]['qty'];
		}
		return $qty;
	}


	/*
	  function: tcversionInfoForAudit
            get info regarding tcversions, to generate useful audit messages
            

	  args :
        $tplan_id: test plan id
        $items_to_link: map key=tc_id 
                        value: tcversion_id
	  returns: -

	  rev: 20080629 - franciscom - audit message improvements
	*/
	function tcversionInfoForAudit($tplan_id,&$items)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		
		// Get human readeable info for audit
		$ret=array();
		$tcase_cfg = config_get('testcase_cfg');
		$dummy=reset($items);
		
		list($ret['tcasePrefix'],$tproject_id) = $this->tcase_mgr->getPrefix($dummy);
		$ret['tcasePrefix'] .= $tcase_cfg->glue_character;
		
        $sql = "/* $debugMsg */ " .
		       " SELECT TCV.id, tc_external_id, version, NHB.name " .
			   " FROM {$this->tables['tcversions']} TCV,{$this->tables['nodes_hierarchy']} NHA, " .
			   " {$this->tables['nodes_hierarchy']} NHB " .
			   " WHERE NHA.id=TCV.id " .
			   " AND NHB.id=NHA.parent_id  " .
			   " AND TCV.id IN (" . implode(',',$items) . ")";
		
		$ret['info']=$this->db->fetchRowsIntoMap($sql,'id');  
		$ret['tplanInfo']=$this->get_by_id($tplan_id);                                                          
		
		return $ret;
	}


	/**
	 * associates version of different test cases to a test plan.
	 * this is the way to populate a test plan

 	 args :
        $id: test plan id
        $items_to_link: map key=tc_id 
                        value= map with
                               key: platform_id (can be 0)
                               value: tcversion_id
                        passed by reference for speed
	  returns: -

	  rev: 20080629 - franciscom - audit message improvements
	*/
	function link_tcversions($id,&$items_to_link,$userId)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		
		// Get human readeable info for audit
		$title_separator = config_get('gui_title_separator_1');
		$auditInfo=$this->tcversionInfoForAudit($id,$items_to_link['tcversion']);
		$platformInfo = $this->platform_mgr->getLinkedToTestplanAsMap($id);
		$platformLabel = lang_get('platform');
		
		// Important: MySQL do not support default values on datetime columns that are functions
		// that's why we are using db_now().
		$sql = "/* $debugMsg */ " .
		       "INSERT INTO {$this->tables['testplan_tcversions']} " .
			   "(testplan_id,author_id,creation_ts,tcversion_id,platform_id) " . 
			   " VALUES ({$id},{$userId},{$this->db->db_now()},";
        $features=null;
		foreach($items_to_link['items'] as $tcase_id => $items)
		{
			foreach($items as $platform_id => $tcversion)
			{
				$addInfo='';
				$result = $this->db->exec_query($sql . "{$tcversion}, {$platform_id})");
				if ($result)
				{
                    $features[$platform_id][$tcversion]=$this->db->insert_id($this->tables['testplan_tcversions']);					
					if( isset($platformInfo[$platform_id]) )
					{
						$addInfo = ' - ' . $platformLabel . ':' . $platformInfo[$platform_id];
					}
					$auditMsg=TLS("audit_tc_added_to_testplan",
								  $auditInfo['tcasePrefix'] . $auditInfo['info'][$tcversion]['tc_external_id'] . 
								  $title_separator . $auditInfo['info'][$tcversion]['name'],
								  $auditInfo['info'][$tcversion]['version'],
								  $auditInfo['tplanInfo']['name'] . $addInfo );
					
					logAuditEvent($auditMsg,"ASSIGN",$id,"testplans");
				}	
			}
		}
		return $features;
	}


	/*
	  function: setExecutionOrder

  	args :
        $id: test plan id
        $executionOrder: assoc array key=tcversion_id value=order
                         passed by reference for speed

  	returns: -
	*/
	function setExecutionOrder($id,&$executionOrder)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		foreach($executionOrder as $tcVersionID => $execOrder)
		{
			$execOrder=intval($execOrder);
			$sql="/* $debugMsg */ UPDATE {$this->tables['testplan_tcversions']} " .
				 "SET node_order={$execOrder} " .
				 "WHERE testplan_id={$id} " .
				 "AND tcversion_id={$tcVersionID}";
			$result = $this->db->exec_query($sql);
		}
	}
	

	/**
	 * Ignores Platforms, then if a test case version is linked to a test plan
	 * and two platforms, we will get item once. 
	 * Need to understand if in context where we want to use this method this is
	 * a problem
	 *
	 * 
	 * @internal revisions:
	 *   20100722 - asimon - added missing $debugMsg
	 */
	function get_linked_items_id($id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$sql = " /* $debugMsg */ ". 
			   " SELECT DISTINCT parent_id FROM {$this->tables['nodes_hierarchy']} NHTC " .
			   " JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.tcversion_id = NHTC.id " .
			   " WHERE TPTCV.testplan_id = {$id} ";
			   
		$linked_items = $this->db->fetchRowsIntoMap($sql,'parent_id');			     
		return $linked_items;
	}

	/**
	 * @internal revisions:
	 * 
	 */
	function get_linked_tcvid($id,$platformID)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$sql = " /* $debugMsg */ ". 
			   " SELECT tcversion_id FROM {$this->tables['testplan_tcversions']} " .
			   " WHERE testplan_id = " . intval($id) . 
			   " AND platform_id = " . intval($platformID) ;
			   
		$linked_items = $this->db->fetchRowsIntoMap($sql,'tcversion_id');			     
		return $linked_items;
	}

	/**
	 * @internal revisions:
	 * 
	 */
	function getFeatureID($id,$platformID,$tcversionID)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$sql = " /* $debugMsg */ ". 
			   " SELECT id FROM {$this->tables['testplan_tcversions']} " .
			   " WHERE testplan_id = " . intval($id) . 
			   " AND tcversion_id = " . intval($tcversionID) . 
			   " AND platform_id = " . intval($platformID) ;
			   
		$linked_items = $this->db->fetchRowsIntoMap($sql,'id');
		return !is_null($linked_items) ? key($linked_items) : -1;
	}

	
	

	/*
  	function: get_linked_tcversions
            get information about testcases linked to a testplan.


	IMPORTANT NOTICE
	A new logic has to be implemented to manage in the right way
	when caller reques NOT EXECUTED, but this info has to consider 
	ONLY ACTIVE BUILDS.
	We are going to implement a solution that will be good to solve
	issue:
	BUGID 4328: Metrics dashboard - only active builds has to be used
	but on this first implementation we are not going to check that 
	all options combinations (there are lots) produce coherent 
	output.
	We will (in future) to improve this.
	
	There are special situations like: 
		When we have JUST ONE build and we set it to inactive, if we request
		only NOT RUN test cases, all test cases has to be returned as NOT RUN.
		OK, this is a very special situation similar to have NO BUILD created.
		We need to add some logic to manage this.

  	args :
         id: testplan id
         [filters]: map with following keys
         	[tcase_id]: default null => get any testcase
         	            numeric      => just get info for this testcase
         	
         	[keyword_id]: default 0 => do not filter by keyword id
         	              numeric   => filter by keyword id
         	
         	
         	[assigned_to]: default NULL => do not filter by user assign.
         	               array() with user id to be used on filter
         	
         	[exec_status]: default NULL => do not filter by execution status
         	               character or array => filter by execution status=character
         	
         	[build_id]: default 0 or null => do not filter by build id
         	            numeric or array   => filter by build id / or list of build id
         	
         	[cf_hash]: default null => do not filter by Custom Fields values
         	
		 	
		 	[urgencyImportance] : filter only Tc's with certain (urgency*importance)-value 
		 	
		 	[tsuites_id]: default null.
		 	              If present only tcversions that are children of this testsuites
		 	              will be included
		 	[exec_type] default null -> all types. 
		 	[platform_id]              
		     		
         [options]: map with following keys
         	[output]: controls data type returned
         	          default: map -> map indexed by test case id (original return type)
         	                          Using this option is (in a certain way) as having
         	                          added DISTINCT on SQL clause.
         	                          YOU WILL GET ONLY LAST EXECUTION (means one record)
         	                          of each test case.
         	                          
         	                   mapOfMap: first key testcase_id, second key platform_id               
         	                            You GET ONLY LAST EXECUTION (means one record)
         	                            of each test case.

         	                   mapOfArray -> indexed by test case id but with an array
         	                                 where each element contains information
         	                                 according to Platform.
         	                                 Be carefull if you have multiple executions
         	                                 for same (testcase,platform) YOU WILL GET
         	                                 MULTIPLE ELEMENTS IN ARRAY
         	                                 
         	                  array -> indexed sequentially. 
         	                  
         	                            
			[build_active_status]:  values 'active', 'inactive', 'any' <- default
							 		when <> 'any' only executions test case versions can be analized.
							 		Remember that there is NO RECORD on executions table when
							 		test case version has not been executed, and we get BUILD ID
							 		from execution record.
							          	          
         	[only_executed]: default false => get executed and NOT executed
         	                                  get only executed tcversions

		 	[forced_exec_status] values null => does not force
		 							   other => this value will be forced on
		 						 			    output recordset.
         
         	[execution_details]: default NULL => by platftorm
         	                     add_build => by build AND platform
         	                             
         	[last_execution]: default false => return all executions
         	                          true => last execution ( MAX(E.id))
         	                          
         	[include_unassigned]: has effects only if [assigned_to] <> null.
         	                      default: false
         	                      true: also testcase not assigned will be retreived
         	
         	[details]: controls columns returned
         	           default 'simple'
         	           'full': add summary, steps and expected_results, and test suite name
         	           'summary': add summary 
		 	[steps_info]: controls if step info has to be added on output    
         	           default true
		 	[user_assignments_per_build]: contains a build ID, for which the
		 	                              assigned user shall get loaded    
		 	    
		 	    
	  returns: changes according options['output'] (see above)

           Notice:
           executed field: will take the following values
                           - NULL if the tc version has not been executed in THIS test plan.
                           - tcversion_id if has executions.

	rev :
		 20110317 - franciscom - BUGID 4328: Metrics dashboard - only active builds has to be used
		 						 add new option 
		 						 'forced_exec_status' values null => does not force
		 						 							other => this value will be forced on
		 						 									 output recordset.
								 'build_id' can be an array (to implement list of build id)

		 20110316 - franciscom - BUGID 4328: Metrics dashboard - only active builds has to be used
		 						 add new option 
		 						 'build_active_status' values 'active', 'inactive', 'any'
		 						 
		 20100727 - asimon - BUGID 3629: modified statement
		 20100721 - asimon - BUGID 3406: added user_assignments_per_build option
		 20100520 - franciscom - added option steps_info, to try to solve perfomance problems
		 						 allowing caller to ask for NO INFO ABOUT STEPS	
		 20100417 - franciscom - added importance on output data
		 		  				 BUGID 3356: "Failed Test Cases" report is not updated when a test case 
		 		  				 			  has been changed from "Failed" to "Passed"		 
		 		  				 			  
         20090814 - franciscom - interface changes due to platform feature
	*/
	public function get_linked_tcversions($id,$filters=null,$options=null)
	{
		$debugMsg = 'Class: ' . __CLASS__ . ' - Method:' . __FUNCTION__;
		
        $my = array ('filters' => '', 'options' => '');
		$tcversion_exec_type = array('join' => '', 'filter' => '');
		$tc_id = array('join' => '', 'filter' => '');
		$builds = array('join' => '', 'filter' => '', 'left_join' => ' ');
		$keywords = array('join' => '', 'filter' => '');
		$executions = array('join' => '', 'filter' => '');
		$platforms = array('join' => '', 'filter' => '');

		$executions['filter'] = '';
		$notrun['filter'] = null;
		$otherexec['filter'] = null;
		$more_tcase_fields = '';
		$join_for_parent = '';
		$more_parent_fields = '';
		$more_exec_fields='';
		
        $my['filters'] = array('tcase_id' => null, 'keyword_id' => 0,
                               'assigned_to' => null, 'exec_status' => null,
                               'build_id' => 0, 'cf_hash' => null,
                               'urgencyImportance' => null, 'tsuites_id' => null,
                               'platform_id' => null, 'exec_type' => null);
                               
        $my['options'] = array('only_executed' => false, 'only_not_executed' => false,
        					   'include_unassigned' => false,
                               'output' => 'map', 'details' => 'simple', 'steps_info' => false, 
                               'execution_details' => null, 'last_execution' => false,
                               'build_active_status' => 'any', 'forced_exec_status' => null);

 		// Cast to array to handle $options = null
		$my['filters'] = array_merge($my['filters'], (array)$filters);
		$my['options'] = array_merge($my['options'], (array)$options);
		
		// 20100830 - franciscom - bug found by Julian
		// BUGID 3867
		if (!is_null($my['filters']['exec_status'])) 
		{
			$my['filters']['exec_status'] = (array)$my['filters']['exec_status'];
		}
		$groupByPlatform=($my['options']['output']=='mapOfMap' || 
		                  $my['options']['output']=='mapOfMapExecPlatform') ? ',platform_id' : '';
		$groupByBuild=($my['options']['execution_details'] == 'add_build') ? ',build_id' : '';

		// BUGID 3406: user assignments per build 
		$ua_build = isset($my['options']['user_assignments_per_build']) ? 
		            $my['options']['user_assignments_per_build'] : 0;
		
		// BUGID 3629
		$ua_build_sql = $ua_build && is_numeric($ua_build) ? " AND UA.build_id={$ua_build} " : " ";
		
		$active_domain = array('active' => 1, 'inactive' => 0 , 'any' => null);
		$active_status = $active_domain[$my['options']['build_active_status']];
		
        // @TODO - 20091004 - franciscom
        // Think that this subquery in not good when we add execution filter
		// $last_exec_subquery = " AND E.id IN ( SELECT MAX(id) " .
		// 	 		             "               FROM  {$this->tables['executions']} executions " .
		// 			             "               WHERE testplan_id={$id} %EXECSTATUSFILTER%" .
		// 			             " GROUP BY tcversion_id,testplan_id {$groupByPlatform} {$groupByBuild} )";

		// I've had confirmation of BAD query;
		// BUGID 3356: "Failed Test Cases" report is not updated when a test case 
		// 		  				 			  has been changed from "Failed" to "Passed"		 
		//
        // SUBQUERY CAN NOT HAVE ANY KIND OF FILTERING other that test plan.
        // Adding exec status, means that we will get last exec WITH THIS STATUS, and not THE LATEST EXEC   

		// 20110316 - franciscom -
		// BUGID 4328: Metrics dashboard - only active builds has to be used
		// adding filtering by build_status is SAFE
		$last_exec_subquery = " AND E.id IN ( SELECT MAX(id) " .
			 		          "               FROM  {$this->tables['executions']} executions " ;
		if(!is_null($active_status))
		{
			$last_exec_subquery .= 	" JOIN {$this->tables['builds']} buildexec ON " .
									" buildexec.id = executions.build_id " .
									" AND buildexec.active = " . intval($active_status) ;
		}
		$last_exec_subquery .= " WHERE testplan_id={$id} " . 
					           " GROUP BY tcversion_id,testplan_id {$groupByPlatform} {$groupByBuild} )";
           
		$resultsCfg = config_get('results');
		$status_not_run=$resultsCfg['status_code']['not_run'];
		$sql_subquery='';
		
		// franciscom
		// WARNING: 
		// Order of analisys seems to be critic, because $executions['filter'] is overwritten
		// on some situation below if filtering on execution status is requested
		if( $my['options']['last_execution'] )
		{
			$executions['filter'] = " {$last_exec_subquery} ";
		}
		
		if( !is_null($my['filters']['platform_id']) )
		{
			$platforms['filter'] = " AND T.platform_id = {$my['filters']['platform_id']} ";
	    }

		// 20100417- Why to use a list ? - must be checked if is on
		if( !is_null($my['filters']['exec_type']) )
		{
			$tcversion_exec_type['filter'] = "AND TCV.execution_type IN (" . 
			                                 implode(",",(array)$my['filters']['exec_type']) . " ) ";     
		}
		
		// Based on work by Eugenia Drosdezki
		if( is_array($my['filters']['keyword_id']) )
		{
			// 0 -> no keyword, remove 
			if( $my['filters']['keyword_id'][0] == 0 )
			{
				array_shift($my['filters']['keyword_id']);
			}
			
			if(count($my['filters']['keyword_id']))
			{
				$keywords['filter'] = " AND TK.keyword_id IN (" . implode(',',$my['filters']['keyword_id']) . ")";          	
			}  
		}
		else if($my['filters']['keyword_id'] > 0)
		{
			$keywords['filter'] = " AND TK.keyword_id = {$my['filters']['keyword_id']} ";
		}
		
		if(trim($keywords['filter']) != "")
		{
			$keywords['join'] = " JOIN {$this->tables['testcase_keywords']} TK ON NHA.parent_id = TK.testcase_id ";
		}
		
		
		
		if (!is_null($my['filters']['tcase_id']) )
		{
			if( is_array($my['filters']['tcase_id']) )
			{
				$tc_id['filter'] = " AND NHA.parent_id IN (" . implode(',',$my['filters']['tcase_id']) . ")";          	
			}
			else if ($my['filters']['tcase_id'] > 0 )
			{
				$tc_id['filter'] = " AND NHA.parent_id = {$my['filters']['tcase_id']} ";
			}
		}

		// 20110317
		// not run && build_active_status = 'active', need special logic		
		//
		if(!is_null($my['filters']['exec_status']) )
		{
			// $executions['filter'] = '';
			// $notrun['filter'] = null;
			// $otherexec['filter'] = null;
			
			$notRunPresent = array_search($status_not_run,$my['filters']['exec_status']); 
			if($notRunPresent !== false)
			{
				$notrun['filter'] = " E.status IS NULL ";
				unset($my['filters']['exec_status'][$notRunPresent]);  
			}
			
			if(count($my['filters']['exec_status']) > 0)
			{
				$otherexec['filter']=" E.status IN ('" . implode("','",$my['filters']['exec_status']) . "') ";
				$executions['filter'] = " ( {$otherexec['filter']} {$last_exec_subquery} ) ";  
			}
			if( !is_null($notrun['filter']) )
			{
				if($executions['filter'] != "")
				{
					$executions['filter'] .= " OR ";
				}
				$executions['filter'] .= $notrun['filter'];
			}
			
			if($executions['filter'] != "")
			{
                // Just add the AND 
				$executions['filter'] = " AND ({$executions['filter']} )";     
			}
		}
		// --------------------------------------------------------------


		// --------------------------------------------------------------
		// Order of following block of sentences is CRITIC => read it
		// very, very carefully before do changes.
		// --------------------------------------------------------------
		// 20110317
		if($my['options']['execution_details'] == 'add_build')
		{
			$more_exec_fields .= 'E.build_id,B.name AS build_name, B.active AS build_is_active,';	

			// following setting can be changed due to other process, triggered
			// by other options
			$builds['join']=" LEFT OUTER JOIN {$this->tables['builds']} B ON B.id=E.build_id ";
	    }

		// BUGID 4328: Metrics dashboard - only active builds has to be used			   
		if( !is_null($active_status) )
		{
			if( is_null($notrun['filter']) )
			{
				$builds['join'] = " JOIN {$this->tables['builds']} B ON " .
								  " B.id = E.build_id AND B.active = " . intval($active_status) ;	
			}
			else
			{
				// in this situation we need to add LEFT OUTER JOIN on builds
				// and do not use $active_status
				//
				// When I have several builds, and I'm looking for not executed
				// test cases ON ACTIVE BUILDS, if I get a record for an INACTIVE BUILD
				// I will consider as a NON EXECUTION.
				//
				// Attention what will happends depend a LOT of output option,
				// because certain options return LAST record in recordset
				// and is in THIS situation when this explanation is valid.
				//
				$builds['join'] = " LEFT OUTER JOIN {$this->tables['builds']} B " .
								  " ON B.id=E.build_id ";
				$notrun['filter'] = " E.status IS NULL " .
									" OR (E.status IS NOT NULL AND B.active = 0) ";		

				$executions['filter'] = " AND ({$notrun['filter']} )";     
			}
		}
		// --------------------------------------------------------------

		// --------------------------------------------------------------
		// accepts a list (as array)
		if( !is_null($my['filters']['build_id']) )
		{
			// $builds['filter'] = " AND E.build_id={$my['filters']['build_id']} ";
			$dummy = (array)$my['filters']['build_id'];
			if( !in_array(0,$dummy) )
			{
				$builds['filter'] = " AND E.build_id IN (" . implode(",",$dummy) . ") ";
			}
		}
		
		// there are several situation where you need to use LEFT OUTER
		if(!$my['options']['only_executed'])
		{
			$executions['join'] = " LEFT OUTER ";
		}
		
		
		// platform feature
		$executions['join'] .= " JOIN {$this->tables['executions']} E ON " .
			                   " (NHA.id = E.tcversion_id AND " .
			                   " E.platform_id=T.platform_id AND " .
			                   " E.testplan_id=T.testplan_id {$builds['filter']}) ";
		// --------------------------------------------------------------
		
		switch($my['options']['details'])
		{
			case 'full':
			$more_tcase_fields .= 'TCV.summary,';
			$join_for_parent .= " JOIN {$this->tables['nodes_hierarchy']} NHC ON NHB.parent_id = NHC.id ";
			$more_parent_fields .= 'NHC.name as tsuite_name,';
			break;
			
			case 'summary':
			$more_tcase_fields .= 'TCV.summary,';
			break;
		
		}

		
	    // BUGID 4206 - added exec_on_build to output
	    // BUGID 3406 - assignments per build
	    // BUGID 3492 - Added execution notes to sql statement of get_linked_tcversions
		$sql = "/* $debugMsg */ " .
		       " SELECT NHB.parent_id AS testsuite_id, {$more_tcase_fields} {$more_parent_fields}" .
			   " NHA.parent_id AS tc_id, NHB.node_order AS z, NHB.name," .
			   " T.platform_id, PLAT.name as platform_name ,T.id AS feature_id, T.tcversion_id AS tcversion_id,  " .
			   " T.node_order AS execution_order, T.creation_ts AS linked_ts, T.author_id AS linked_by," .
			   " TCV.version AS version, TCV.active," .
			   " TCV.tc_external_id AS external_id, TCV.execution_type,TCV.importance," .
			   " E.id AS exec_id, E.tcversion_number," .
			   " E.tcversion_id AS executed, E.testplan_id AS exec_on_tplan, {$more_exec_fields}" .
			   " E.execution_type AS execution_run_type, E.testplan_id AS exec_on_tplan, " .
			   " E.execution_ts, E.tester_id, E.notes as execution_notes, E.build_id as exec_on_build, ".
		       " UA.build_id as assigned_build_id, " . // 3406
			   " UA.user_id,UA.type,UA.status,UA.assigner_id,T.urgency, ";
	    
	    // 20110317 - 150 years 'Unita Italia'
	    if( is_null($my['options']['forced_exec_status']) )
		{
			$sql .=	 " COALESCE(E.status,'" . $status_not_run . "') AS exec_status, ";
		}
		else
		{
			$sql .=	 " '{$my['options']['forced_exec_status']}'  AS exec_status, ";
		}	   		
		
		$sql .=" (urgency * importance) AS priority " .
			   " FROM {$this->tables['nodes_hierarchy']} NHA " .
			   " JOIN {$this->tables['nodes_hierarchy']} NHB ON NHA.parent_id = NHB.id " .
			   $join_for_parent .
			   " JOIN {$this->tables['testplan_tcversions']} T ON NHA.id = T.tcversion_id " .
			   " JOIN  {$this->tables['tcversions']} TCV ON NHA.id = TCV.id {$tcversion_exec_type['filter']} " .
			   " {$executions['join']} " .
			   " {$keywords['join']} " .
			   " {$builds['join']} " .
			   " LEFT OUTER JOIN {$this->tables['platforms']} PLAT ON PLAT.id = T.platform_id " .
			   " LEFT OUTER JOIN {$this->tables['user_assignments']} UA ON UA.feature_id = T.id " .
			   " {$ua_build_sql} " ; // BUGID 3406
		
		$sql .= " WHERE T.testplan_id={$id} {$keywords['filter']} {$tc_id['filter']} {$platforms['filter']}" .
			    " AND (UA.type={$this->assignment_types['testcase_execution']['id']} OR UA.type IS NULL) " . 
			    $executions['filter'];
		
		// If special user id TL_USER_ANYBODY is present in set of user id,
		// we will DO NOT FILTER by user ID
		if( !is_null($my['filters']['assigned_to']) && 
		    !in_array(TL_USER_ANYBODY,(array)$my['filters']['assigned_to']) )
		{  
			$sql .= " AND ";
			
			// Warning!!!:
			// If special user id TL_USER_NOBODY is present in set of user id
			// we will ignore any other user id present on set.
			if( in_array(TL_USER_NOBODY,(array)$my['filters']['assigned_to']) )
			{
				$sql .= " UA.user_id IS NULL "; 
			} 
			// BUGID 2455
            // new user filter "somebody" --> all asigned testcases
			else if( in_array(TL_USER_SOMEBODY,(array)$my['filters']['assigned_to']) )
			{
				$sql .= " UA.user_id IS NOT NULL "; 
			}
			else
			{
				$sql_unassigned="";
				if( $my['options']['include_unassigned'] )
				{
					$sql .= "(";
					$sql_unassigned=" OR UA.user_id IS NULL)";
				}
				// BUGID 2500
				$sql .= " UA.user_id IN (" . implode(",",(array)$my['filters']['assigned_to']) . ") " . $sql_unassigned;
			}
		}
		
		if (!is_null($my['filters']['urgencyImportance']))
		{
			$urgencyImportanceCfg = config_get("urgencyImportance");
			if ($my['filters']['urgencyImportance'] == HIGH)
			{
				$sql .= " AND (urgency * importance) >= " . $urgencyImportanceCfg->threshold['high'];
			}
			else if($my['filters']['urgencyImportance'] == LOW)
			{
				$sql .= " AND (urgency * importance) < " . $urgencyImportanceCfg->threshold['low'];
			}
			else
			{
				$sql .= " AND ( ((urgency * importance) >= " . $urgencyImportanceCfg->threshold['low'] . 
					    " AND  ((urgency * importance) < " . $urgencyImportanceCfg->threshold['high']."))) ";
			}		
		}
		
		// test suites filter
		if (!is_null($my['filters']['tsuites_id']))
		{
			$tsuiteSet = is_array($my['filters']['tsuites_id']) ? $my['filters']['tsuites_id'] : array($my['filters']['tsuites_id']);
			$sql .= " AND NHB.parent_id IN (" . implode(',',$tsuiteSet) . ")";
		}
		// BUGID 4184 - MSSQL Ambiguous column name platform_id
		$sql .= " ORDER BY testsuite_id,NHB.node_order,tc_id,T.platform_id,E.id ASC";

		switch($my['options']['output'])
		{ 
			case 'array':
			$recordset = $this->db->get_recordset($sql);
			break;

			case 'mapOfArray':
			$recordset = $this->db->fetchRowsIntoMap($sql,'tc_id',database::CUMULATIVE);
			break;
			
			case 'mapOfMap':
			// with this option we got just one record for each (testcase,platform)
			// no matter how many executions has been done
			
			$recordset = $this->db->fetchMapRowsIntoMap($sql,'tc_id','platform_id');
			break;
			
			case 'mapOfMapExecPlatform':
			// with this option we got just one record for each (platform, build)
			
			$recordset = $this->db->fetchMapRowsIntoMap($sql,'exec_id','platform_id');
			break;
			
			case 'map':
			default:
			$recordset = $this->db->fetchRowsIntoMap($sql,'tc_id');
			
			// 20070913 - jbarchibald
			// here we add functionality to filter out the custom field selections
			//
			// After addition of platform feature, this filtering can not be done
			// always with original filter_cf_selection().
			// Fisrt choice:
			// Enable this feature only if recordset maintains original structured
			//
			if (!is_null($my['filters']['cf_hash']) && !is_null($recordset)) {
				$recordset = $this->filter_cf_selection($recordset, $my['filters']['cf_hash']);
			}
			break;
		}
		
        // Multiple Test Case Steps Feature
        // added after Julian mail regarding perf problems building exec tree
        if( !is_null($recordset) && $my['options']['steps_info'])
        {
		    $itemSet = array_keys($recordset);
			switch($my['options']['output'])
			{ 
        	
				case 'mapOfArray':
				case 'mapOfMap':
	  				foreach($itemSet as $itemKey)
	  				{
	  					$keySet = array_keys($recordset[$itemKey]);
	  					$target = &$recordset[$itemKey];
	  					foreach($keySet as $accessKey)
	  					{
	  						$step_set = $this->tcase_mgr->get_steps($target[$accessKey]['tcversion_id']);
	  						$target[$accessKey]['steps'] = $step_set;
	  					}
	  				}
				break;
				
				case 'array':
				case 'map':
				default:
  					foreach($itemSet as $accessKey)
  					{
	  					$step_set = $this->tcase_mgr->get_steps($recordset[$accessKey]['tcversion_id']);
	  					$recordset[$accessKey]['steps'] = $step_set;
	  				} 
				break;
			}
        }

		return $recordset;
	}


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
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		
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
		
		$sql = " /* $debugMsg */ SELECT MAX(NHB.id) AS newest_tcversion_id, " .
			   " NHA.parent_id AS tc_id, NHC.name, T.tcversion_id AS tcversion_id," .
			   " TCVA.tc_external_id AS tc_external_id, TCVA.version AS version " .
			   " FROM {$this->tables['nodes_hierarchy']} NHA " .
			
			// NHA - will contain ONLY nodes of type testcase_version that are LINKED to test plan
			" JOIN {$this->tables['testplan_tcversions']} T ON NHA.id = T.tcversion_id " . 
			
			// Get testcase_version data for LINKED VERSIONS
			" JOIN {$this->tables['tcversions']} TCVA ON TCVA.id = T.tcversion_id" .
			
			// Work on Sibblings - Start
			// NHB - Needed to get ALL testcase_version sibblings nodes
			" JOIN {$this->tables['nodes_hierarchy']} NHB ON NHB.parent_id = NHA.parent_id " .
			
			// Want only ACTIVE Sibblings
			" JOIN {$this->tables['tcversions']} TCVB ON TCVB.id = NHB.id AND TCVB.active=1 " . 
			// Work on Sibblings - STOP 
			
			// NHC will contain - nodes of type TESTCASE (parent of testcase versions we are working on)
			// we use NHC to get testcase NAME ( testcase version nodes have EMPTY NAME)
			" JOIN {$this->tables['nodes_hierarchy']} NHC ON NHC.id = NHA.parent_id " .
			
			// Want to get only testcase version with id (NHB.id) greater than linked one (NHA.id)
			" WHERE T.testplan_id={$id} AND NHB.id > NHA.id" . $tc_id_filter .
			" GROUP BY NHA.parent_id, NHC.name, T.tcversion_id, TCVA.tc_external_id, TCVA.version  ";
		
		$sql2 = " SELECT SUBQ.name, SUBQ.newest_tcversion_id, SUBQ.tc_id, " .
			    " SUBQ.tcversion_id, SUBQ.version, SUBQ.tc_external_id, " .
			    " TCV.version AS newest_version " .
			    " FROM {$this->tables['tcversions']} TCV, ( $sql ) AS SUBQ " .
			    " WHERE SUBQ.newest_tcversion_id = TCV.id " .
			    " ORDER BY SUBQ.tc_id ";
		
		return $this->db->fetchRowsIntoMap($sql2,'tc_id');
	}


	/**
	 * Remove of records from user_assignments table
	 * @author franciscom
	 * 
	 * @param integer $id   : test plan id
	 * @param array $items: assoc array key=tc_id value=tcversion_id
	 * 
	 * @internal revisions:
	 *    20100725 - asimon - BUGID 3497 and hopefully also 3530
	 */
	function unlink_tcversions($id,&$items)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		if(is_null($items))
		{
			return;
		}
		
		// Get human readeable info for audit
		$gui_cfg = config_get('gui');
		$title_separator = config_get('gui_title_separator_1');
		$auditInfo=$this->tcversionInfoForAudit($id,$items['tcversion']);
		$platformInfo = $this->platform_mgr->getLinkedToTestplanAsMap($id);
		$platformLabel = lang_get('platform');

        $dummy = null;
		foreach($items['items'] as $tcase_id => $elem) 
		{
			foreach($elem as $platform_id => $tcversion_id) 
			{
				$dummy[] = "(tcversion_id = {$tcversion_id} AND platform_id = {$platform_id})";
			}
		}
		$where_clause = implode(" OR ", $dummy);
		
		/*
		 * asimon - BUGID 3497 and hopefully also 3530
		 * A very litte error, missing braces in the $where_clause, was causing this bug. 
		 * When one set of testcases is linked to two testplans, this statement should check 
		 * that the combination of testplan_id, tcversion_id and platform_id was the same, 
		 * but instead it checked for either testplan_id OR tcversion_id and platform_id.
		 * So every linked testcase with fitting tcversion_id and platform_id without execution
		 * was deleted, regardless of testplan_id.
		 * Simply adding braces around the where clause solves this.
		 * So innstead of: 
		 * SELECT id AS link_id FROM testplan_tcversions  
		 * WHERE testplan_id=12 AND (tcversion_id = 5 AND platform_id = 0) 
		 * OR (tcversion_id = 7 AND platform_id = 0) 
		 * OR (tcversion_id = 9 AND platform_id = 0) 
		 * OR (tcversion_id = 11 AND platform_id = 0)
		 * we need this:
		 * SELECT ... WHERE testplan_id=12 AND (... OR ...)
		 */ 
		$where_clause = " ( {$where_clause} ) ";
		
		// First get the executions id if any exist
		$sql=" SELECT id AS execution_id " .
			 " FROM {$this->tables['executions']} " .
			 " WHERE testplan_id = {$id} AND ${where_clause}";
		$exec_ids = $this->db->fetchRowsIntoMap($sql,'execution_id');
		
		if( !is_null($exec_ids) and count($exec_ids) > 0 )
		{
			// has executions
			$exec_ids = array_keys($exec_ids);
			$exec_id_where= " WHERE execution_id IN (" . implode(",",$exec_ids) . ")";
			
			// Remove bugs if any exist
			$sql=" DELETE FROM {$this->tables['execution_bugs']} {$exec_id_where} ";
			$result = $this->db->exec_query($sql);
			
			// now remove executions
			$sql=" DELETE FROM {$this->tables['executions']} " .
				 " WHERE testplan_id = {$id} AND ${where_clause}";
			$result = $this->db->exec_query($sql);
		}
		
		// ----------------------------------------------------------------
		// to remove the assignment to users (if any exists) we need the list of id
		$sql=" SELECT id AS link_id FROM {$this->tables['testplan_tcversions']} " .
			 " WHERE testplan_id={$id} AND {$where_clause} ";
		$link_ids = $this->db->fetchRowsIntoMap($sql,'link_id');
		$features = array_keys($link_ids);
		if( count($features) == 1)
		{
			$features=$features[0];
		}
		$this->assignment_mgr->delete_by_feature_id($features);
		// ----------------------------------------------------------------
		
		// Delete from link table
		$sql=" DELETE FROM {$this->tables['testplan_tcversions']} " .
			 " WHERE testplan_id={$id} AND {$where_clause} ";
		$result = $this->db->exec_query($sql);
		
		foreach($items['items'] as $tcase_id => $elem)
		{
			foreach($elem as $platform_id => $tcversion)
			{
				$addInfo='';
				if( isset($platformInfo[$platform_id]) )
				{
					$addInfo = ' - ' . $platformLabel . ':' . $platformInfo[$platform_id];
				}
				$auditMsg=TLS("audit_tc_removed_from_testplan",
							  $auditInfo['tcasePrefix'] . $auditInfo['info'][$tcversion]['tc_external_id'] . 
							  $title_separator . $auditInfo['info'][$tcversion]['name'],
							  $auditInfo['info'][$tcversion]['version'],
							  $auditInfo['tplanInfo']['name'] . $addInfo );
				
				logAuditEvent($auditMsg,"UNASSIGN",$id,"testplans");
			}
		}
		
	} // end function unlink_tcversions



	/**
	 * 
	 * @internal revisions
	 * 20100505 - franciscom - BUGID 3434
	 */
	function get_keywords_map($id,$order_by_clause='')
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$map_keywords=null;
		
		// keywords are associated to testcase id, then first
		// we need to get the list of testcases linked to the testplan
		// 
		// 20100505 - according to user report (BUGID 3434) seems that 
		// $linked_items = $this->get_linked_tcversions($id);
		// has performance problems.
		// Then make a choice do simple query here.
		//
		$sql = " /* $debugMsg */ ". 
			   " SELECT DISTINCT parent_id FROM {$this->tables['nodes_hierarchy']} NHTC " .
			   " JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.tcversion_id = NHTC.id " .
			   " WHERE TPTCV.testplan_id = {$id} ";
			   
		$linked_items = $this->db->fetchRowsIntoMap($sql,'parent_id');			     
		
		if( !is_null($linked_items) )
		{
			$tc_id_list = implode(",",array_keys($linked_items));
			
			$sql = " /* $debugMsg */ " .
				   " SELECT DISTINCT TCKW.keyword_id,KW.keyword " .
				   " FROM {$this->tables['testcase_keywords']} TCKW, " .
				   " {$this->tables['keywords']} KW " .
				   " WHERE TCKW.keyword_id = KW.id " .
				   " AND TCKW.testcase_id IN ( {$tc_id_list} ) " .
				   " {$order_by_clause} ";
			$map_keywords = $this->db->fetchColumnsIntoMap($sql,'keyword_id','keyword');
		}
		return ($map_keywords);
	}
	

/*
  args :
        [$keyword_id]: can be an array
*/
	function get_keywords_tcases($id,$keyword_id=0)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$CUMULATIVE=1;
		$map_keywords=null;
		
		// keywords are associated to testcase id, then first
		// we need to get the list of testcases linked to the testplan
		$linked_items = $this->get_linked_items_id($id);
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
				FROM {$this->tables['testcase_keywords']} testcase_keywords,
				{$this->tables['keywords']} keywords
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

        [user_id]
        [options]: default null
                   allowed keys:
                   items2copy: 
                   	          null: do a deep copy => copy following test plan child elements:
                              builds,linked tcversions,milestones,user_roles,priorities,
                              platforms,execution assignment.
                              
                              != null, a map with keys that controls what child elements to copy

				   copy_assigned_to:
				   tcversion_type: 
				                  null/'current' -> use same version present on source testplan
                                  'lastest' -> for every testcase linked to source testplan
                                               use lastest available version
                                               
       [mappings]: need to be documented                                        
  	returns: N/A
  	
  	
  	20101114 - franciscom - Because user assignment is done at BUILD Level, we will force
  							BUILD COPY no matter user choice if user choose to copy
  							Test Case assignment.
  							
  							
	*/
	function copy_as($id,$new_tplan_id,$tplan_name=null,$tproject_id=null,$user_id=null,
                     $options=null,$mappings=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		// BUGID 3846
		$cp_methods = array('copy_milestones' => 'copy_milestones',
			                'copy_user_roles' => 'copy_user_roles',
			                'copy_platforms_links' => 'copy_platforms_links');

		$mapping_methods = array('copy_platforms_links' => 'platforms');

		$my['options'] = array();

		// Configure here only elements that has his own table.
		$my['options']['items2copy']= array('copy_tcases' => 1,'copy_milestones' => 1, 'copy_user_roles' => 1, 
		                                    'copy_builds' => 1, 'copy_platforms_links' => 1);

		$my['options']['copy_assigned_to'] = 0;
		$my['options']['tcversion_type'] = null;

		$my['options'] = array_merge($my['options'], (array)$options);
		
		// get source testplan general info
		$rs_source=$this->get_by_id($id);
		
		if(!is_null($tplan_name))
		{
			$sql="/* $debugMsg */ UPDATE {$this->tables['nodes_hierarchy']} " .
				 "SET name='" . $this->db->prepare_string(trim($tplan_name)) . "' " .
				 "WHERE id={$new_tplan_id}";
			$this->db->exec_query($sql);
		}
		
		if(!is_null($tproject_id))
		{
			$sql="/* $debugMsg */ UPDATE {$this->tables['testplans']} SET testproject_id={$tproject_id} " .
				 "WHERE id={$new_tplan_id}";
			$this->db->exec_query($sql);
		}

		// BUGID 3846
		// copy builds and tcversions out of following loop, because of the user assignments per build
		// special measures have to be taken
		$build_id_mapping = null;
		if($my['options']['items2copy']['copy_builds']) {
			$build_id_mapping = $this->copy_builds($id,$new_tplan_id);
		}

		// Important Notice:
		// Since the addition of Platforms, test case versions are linked to Test Plan AND Platforms
		// this means, that not matter user choice, we will force Platforms COPY.
		// This is a lazy approach, instead of complex one that requires understand what Platforms
		// have been used on SOURCE Test Plan.
		//
		// copy test cases is an special copy
		if( $my['options']['items2copy']['copy_tcases'] )
		{
			$my['options']['items2copy']['copy_platforms_links'] = 1;
			// BUGID 3846
			$this->copy_linked_tcversions($id,$new_tplan_id,$user_id,$my['options'],$mappings, $build_id_mapping);
		}

		foreach( $my['options']['items2copy'] as $key => $do_copy )
		{
			if( $do_copy )
			{
				if( isset($cp_methods[$key]) )
				{
					$copy_method=$cp_methods[$key];
					if( isset($mapping_methods[$key]) && isset($mappings[$mapping_methods[$key]]))
					{
						$this->$copy_method($id,$new_tplan_id,$mappings[$mapping_methods[$key]]);
					}
					else
					{
						$this->$copy_method($id,$new_tplan_id);
					}	
				}
			}
		}
	} // end function copy_as



	/**
	 * $id: source testplan id
	 * $new_tplan_id: destination
	 */
	private function copy_builds($id,$new_tplan_id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$rs=$this->get_builds($id);

		// BUGID 3846
		$id_mapping = array();

		if(!is_null($rs))
		{
			foreach($rs as $build)
			{
				$sql=" /* $debugMsg */ INSERT INTO {$this->tables['builds']} (name,notes,testplan_id) " .
					"VALUES ('" . $this->db->prepare_string($build['name']) ."'," .
					"'" . $this->db->prepare_string($build['notes']) ."',{$new_tplan_id})";
				
				$this->db->exec_query($sql);

				// BUGID 3846
				$new_id = $this->db->insert_id($this->tables['builds']);
				$id_mapping[$build['id']] = $new_id;
			}
		}

		// BUGID 3846
		return $id_mapping;
	}


	/*
  	function: copy_linked_tcversions

  	args: id: source testplan id
        new_tplan_id: destination
        [options]
        	[tcversion_type]: default null -> use same version present on source testplan
                              'lastest' -> for every testcase linked to source testplan
                                      use lastest available version
        	[copy_assigned_to]: 1 -> copy execution assignments without role control                              

		[$mappings] useful when this method is called due to a Test Project COPY AS (yes PROJECT no PLAN)
					
  	returns:
  
 	 Note: test urgency is set to default in the new Test plan (not copied)
 	 
 	 @internal revisions
 	 20110104 - asimon - BUGID 4118: Copy Test plan feature is not copying test cases for all platforms
 	 20101114 - franciscom - BUGID 4017: Create plan as copy - Priorities are ALWAYS COPIED
	*/
	private function copy_linked_tcversions($id,$new_tplan_id,$user_id=-1, $options=null,$mappings=null, $build_id_mapping)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$my['options']['tcversion_type'] = null;
	    $my['options']['copy_assigned_to'] = 0;
		$my['options'] = array_merge($my['options'], (array)$options);
        $now_ts = $this->db->db_now();

		$sql="/* $debugMsg */ "; 
		if($my['options']['copy_assigned_to'])
		{
			// BUGID 3846
			$sql .= " SELECT TPTCV.*, COALESCE(UA.user_id,-1) AS tester, " .
					" COALESCE(UA.build_id,0) as assigned_build " .
			        " FROM {$this->tables['testplan_tcversions']} TPTCV " .
			        " LEFT OUTER JOIN {$this->tables['user_assignments']} UA ON " .
			        " UA.feature_id = TPTCV.id " .
			        " WHERE testplan_id={$id} ";
		}
		else
		{
			$sql .= " SELECT TPTCV.* FROM {$this->tables['testplan_tcversions']} TPTCV" .
			        " WHERE testplan_id={$id} ";
	    }

		$rs=$this->db->get_recordset($sql);
		if(!is_null($rs))
		{
			$tcase_mgr = new testcase($this->db);
			$doMappings = !is_null($mappings);

			// BUGID 3846
			$already_linked_versions = array();

			foreach($rs as $elem)
			{
				$tcversion_id = $elem['tcversion_id'];
				
				// Seems useless - 20100204
				$feature_id = $elem['id'];
				if( !is_null($my['options']['tcversion_type']) )
				{
					$sql="/* $debugMsg */ SELECT * FROM {$this->tables['nodes_hierarchy']} WHERE id={$tcversion_id} ";
					$rs2=$this->db->get_recordset($sql);
					$last_version_info = $tcase_mgr->get_last_version_info($rs2[0]['parent_id']);
					$tcversion_id = $last_version_info ? $last_version_info['id'] : $tcversion_id ;
				}
				
				// mapping need to be done with:
				// platforms
				// test case versions
				$platform_id = $elem['platform_id'];
				if( $doMappings )
				{
					if( isset($mappings['platforms'][$platform_id]) )
					{
						$platform_id = $mappings['platforms'][$platform_id]; 
					}
					if( isset($mappings['test_spec'][$tcversion_id]) )
					{
						$tcversion_id = $mappings['test_spec'][$tcversion_id]; 
					}
				}
				
				// BUGID 4017: Create plan as copy - Priorities are ALWAYS COPIED
				$sql = "/* $debugMsg */ " . 
				       " INSERT INTO {$this->tables['testplan_tcversions']} " .
					   " (testplan_id,tcversion_id,platform_id,node_order ";
				$sql_values	= " VALUES({$new_tplan_id},{$tcversion_id},{$platform_id}," .
					          " {$elem['node_order']} ";
				if( $my['options']['items2copy']['copy_priorities'] )
				{
					$sql .= ",urgency ";
					$sql_values	.= ",{$elem['urgency']}";
				}
				$sql .= " ) " . $sql_values . " ) ";  
					   
				// BUGID 3846
				// BUGID 4118: Copy Test plan feature is not copying test cases for all platforms
				if (!in_array($tcversion_id, $already_linked_versions[$platform_id])) {
					$this->db->exec_query($sql);
					$new_feature_id = $this->db->insert_id($this->tables['testplan_tcversions']);
					$already_linked_versions[$platform_id][] = $tcversion_id;
				}
				
				if($my['options']['copy_assigned_to'] && $elem['tester'] > 0)
				{
					$features_map = array();
					$feature_id=$new_feature_id;
					$features_map[$feature_id]['user_id'] = $elem['tester'];
					// BUGID 3846
					$features_map[$feature_id]['build_id'] = $build_id_mapping[$elem['assigned_build']];
					$features_map[$feature_id]['type'] = $this->assignment_types['testcase_execution']['id'];
					$features_map[$feature_id]['status']  = $this->assignment_status['open']['id'];
					$features_map[$feature_id]['creation_ts'] = $now_ts;
					$features_map[$feature_id]['assigner_id'] = $user_id;

					if ($features_map[$feature_id]['build_id'] != 0) {
						$this->assignment_mgr->assign($features_map);
					}
				}
				
			}
		}
	}


/*
  function: copy_milestones

  args: id: source testplan id
        new_tplan_id: destination

  returns:

  rev : 
        20090910 - franciscom - added start_date
        
        20070519 - franciscom
        changed date to target_date, because date is an Oracle reverved word.
*/
	private function copy_milestones($tplan_id,$new_tplan_id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$rs=$this->get_milestones($tplan_id);
		if(!is_null($rs))
		{
			foreach($rs as $mstone)
			{
				// BUGID 3430 - need to check if start date is NOT NULL
				$add2fields = '';
				$add2values = '';
				$use_start_date = strlen(trim($mstone['start_date'])) > 0;
				if( $use_start_date )
				{
					$add2fields = 'start_date,';
					$add2values = "'" . $mstone['start_date'] . "',";
				}

				$sql = "INSERT INTO {$this->tables['milestones']} (name,a,b,c,target_date,{$add2fields} testplan_id)";				
                $sql .= " VALUES ('" . $this->db->prepare_string($mstone['name']) ."'," .
					    $mstone['high_percentage'] . "," . $mstone['medium_percentage'] . "," . 
					    $mstone['low_percentage'] . ",'" . $mstone['target_date'] . "', {$add2values}{$new_tplan_id})";
				$this->db->exec_query($sql);
			}
		}
	}


	/**
	 * Get all milestones for a Test Plan
	 * @param int $tplan_id Test Plan identificator
	 * @return array of arrays TBD fields description 
	 */
	function get_milestones($tplan_id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		
		$sql=" /* $debugMsg */ SELECT id, name, a AS high_percentage, b AS medium_percentage, c AS low_percentage, " .
		     "target_date, start_date,testplan_id " .       
		     "FROM {$this->tables['milestones']} " .
		     "WHERE testplan_id={$tplan_id} ORDER BY target_date,name";
		return $this->db->get_recordset($sql);
	}


	/**
	 * Copy user roles to a new Test Plan
	 * 
	 * @param int $source_id original Test Plan id
	 * @param int $target_id new Test Plan id
	 */
	private function copy_user_roles($source_id, $target_id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$sql = "/* $debugMsg */ SELECT user_id,role_id FROM {$this->tables['user_testplan_roles']} " .
		       " WHERE testplan_id={$source_id} ";
		$rs = $this->db->get_recordset($sql);
		if(!is_null($rs))
		{
	    	foreach($rs as $elem)
	    	{
	      		$sql="INSERT INTO {$this->tables['user_testplan_roles']}  " .
	           		"(testplan_id,user_id,role_id) " .
	           		"VALUES({$target_id}," . $elem['user_id'] ."," . $elem['role_id'] . ")";
	      		$this->db->exec_query($sql);
			}
		}
	}


	/**
	 * Gets all testplan related user roles
	 *
	 * @param integer $id the testplan id
	 * @return array assoc map with keys taken from the user_id column
	 **/
	function getUserRoleIDs($id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$sql = " /* $debugMsg */ SELECT user_id,role_id FROM {$this->tables['user_testplan_roles']} " .
		       "WHERE testplan_id = {$id}";
		$roles = $this->db->fetchRowsIntoMap($sql,'user_id');
		return $roles;
	}


	/**
	 * Inserts a testplan related role for a given user
	 *
	 * @param int $userID the id of the user
	 * @param int $id the testplan id
	 * @param int $roleID the role id
	 * 
	 * @return integer returns tl::OK on success, tl::ERROR else
	 **/
	
	function addUserRole($userID,$id,$roleID)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$status = tl::ERROR;
		$sql = " /* $debugMsg */ INSERT INTO {$this->tables['user_testplan_roles']} (user_id,testplan_id,role_id) VALUES " .
			   " ({$userID},{$id},{$roleID})";
		if ($this->db->exec_query($sql))
		{
			$testPlan = $this->get_by_id($id);
			$role = tlRole::getByID($this->db,$roleID,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
			$user = tlUser::getByID($this->db,$userID,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
			if ($user && $testPlan && $role)
			{
				logAuditEvent(TLS("audit_users_roles_added_testplan",$user->getDisplayName(),
				              $testPlan['name'],$role->name),"ASSIGN",$id,"testplans");
			}
			$status = tl::OK;
		}
		return $status;
	}


	/**
	 * Deletes all testplan related role assignments for a given testplan
	 *
	 * @param int $id the testplan id
	 * @return tl::OK  on success, tl::FALSE else
	 **/
	function deleteUserRoles($id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$status = tl::ERROR;
		$sql = " /* $debugMsg */ DELETE FROM {$this->tables['user_testplan_roles']} " .
		       " WHERE testplan_id = {$id}";
		if ($this->db->exec_query($sql))
		{
			$testPlan = $this->get_by_id($id);
			if ($testPlan)
			{
				logAuditEvent(TLS("audit_all_user_roles_removed_testplan",
				              $testPlan['name']),"ASSIGN",$id,"testplans");
			}
			$status = tl::OK;
		}
		return $status;
	}


	/**
	 * Delete test plan and all related link to other items
	 *
 	 */
	function delete($id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$the_sql=array();
		$main_sql=array();
		
		$this->deleteUserRoles($id);
		$getFeaturesSQL = " /* $debugMsg */ SELECT id FROM {$this->tables['testplan_tcversions']} WHERE testplan_id={$id} "; 
		$the_sql[]="DELETE FROM {$this->tables['milestones']} WHERE testplan_id={$id}";
		
		// CF used on testplan_design are linked by testplan_tcversions.id
		$the_sql[]="DELETE FROM {$this->tables['cfield_testplan_design_values']} WHERE link_id ".
			       "IN ({$getFeaturesSQL})";

		// BUGID 3465: Delete Test Project - User Execution Assignment is not deleted
		$the_sql[]="DELETE FROM {$this->tables['user_assignments']} WHERE feature_id ".
			       "IN ({$getFeaturesSQL})";
		
		$the_sql[]="DELETE FROM {$this->tables['risk_assignments']} WHERE testplan_id={$id}";
		$the_sql[]="DELETE FROM {$this->tables['testplan_platforms']} WHERE testplan_id={$id}";

		$the_sql[]="DELETE FROM {$this->tables['testplan_tcversions']} WHERE testplan_id={$id}";

		$the_sql[]="DELETE FROM {$this->tables['cfield_execution_values']} WHERE testplan_id={$id}";
		$the_sql[]="DELETE FROM {$this->tables['user_testplan_roles']} WHERE testplan_id={$id}";
		
		
		// When deleting from executions, we need to clean related tables
		$the_sql[]="DELETE FROM {$this->tables['execution_bugs']} WHERE execution_id ".
				   "IN (SELECT id FROM {$this->tables['executions']} WHERE testplan_id={$id})";
		$the_sql[]="DELETE FROM {$this->tables['executions']} WHERE testplan_id={$id}";
		$the_sql[]="DELETE FROM {$this->tables['builds']} WHERE testplan_id={$id}"; //BUGID 3845		
		
		foreach($the_sql as $sql)
		{
			$this->db->exec_query($sql);
		}
		
		$this->deleteAttachments($id);
		
		$this->cfield_mgr->remove_all_design_values_from_node($id);
		// ------------------------------------------------------------------------
		
		// Finally delete from main table
		$main_sql[]="DELETE FROM {$this->tables['testplans']} WHERE id={$id}";
		$main_sql[]="DELETE FROM {$this->tables['nodes_hierarchy']} WHERE id={$id}";
		
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
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$sql = " /* $debugMsg */ SELECT id, name " .
			" FROM {$this->tables['builds']} WHERE testplan_id = {$id} ";
		
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


	/*
	  function: get_max_build_id
	
	  args :
	        $id     : test plan id.
	
	  returns:
	*/
	function get_max_build_id($id,$active = null,$open = null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$sql = " /* $debugMsg */ SELECT MAX(id) AS maxbuildid " .
			" FROM {$this->tables['builds']} " .
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
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		// BUGID 0002776
		$sql = " /* $debugMsg */ SELECT NHTSUITE.name, NHTSUITE.id, NHTSUITE.parent_id" . 
			   " FROM {$this->tables['testplan_tcversions']}  TPTCV, {$this->tables['nodes_hierarchy']}  NHTCV, " .
			   " {$this->tables['nodes_hierarchy']} NHTCASE, {$this->tables['nodes_hierarchy']} NHTSUITE " . 
			   " WHERE TPTCV.tcversion_id = NHTCV.id " .
			   " AND NHTCV.parent_id = NHTCASE.id " .
			   " AND NHTCASE.parent_id = NHTSUITE.id " .
			   " AND TPTCV.testplan_id = " . $id . " " .
			   " GROUP BY NHTSUITE.name,NHTSUITE.id,NHTSUITE.parent_id " .
			   " ORDER BY NHTSUITE.name" ;
		
		$recordset = $this->db->get_recordset($sql);
		
		// Now the recordset contains testsuites that have child test cases.
		// However there could potentially be testsuites that only have grandchildren/greatgrandchildren
		// this will iterate through found test suites and check for 
		$superset = $recordset;
		foreach($recordset as $value)
		{
			$superset = array_merge($superset, $this->get_parenttestsuites($value['id']));
		}    
		
		// At this point there may be duplicates
		$dup_track = array();
		foreach($superset as $value)
		{
			if (!array_key_exists($value['id'],$dup_track))
			{
				$dup_track[$value['id']] = true;
				$finalset[] = $value;
			}        
		}    
		
		// Needs to be alphabetical based upon name attribute 
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
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

	    $sql = " /* $debugMsg */ SELECT name, id, parent_id " .
		       "FROM {$this->tables['nodes_hierarchy']}  NH " .
		       "WHERE NH.node_type_id <> {$this->node_types_descr_id['testproject']} " .
		       "AND NH.id = " . $id;
		    
	    $recordset = $this->db->get_recordset($sql);
	    $myarray = array();
	    if (count($recordset) > 0)
	    {        
		    // 20100611 - franciscom
		    // $myarray = array(array('name'=>$recordset[0]['name'], 'id'=>$recordset[0]['id']));
		    $myarray = array($recordset[0]);
		    $myarray = array_merge($myarray, $this->get_parenttestsuites($recordset[0]['parent_id'])); 
	    }
	    
	    return $myarray;            
	}


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
	                  release_date
	
	  rev :
	  20101101 - franciscom - added closed_on_date
	*/
	function get_builds($id,$active=null,$open=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$sql = " /* $debugMsg */ " . 
			   " SELECT id,testplan_id, name, notes, active, is_open,release_date,closed_on_date " .
			   " FROM {$this->tables['builds']} WHERE testplan_id = {$id} " ;
		
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


	/**
	 * Get a build belonging to a test plan, using build name as access key
	 *
	 * @param int $id test plan id
	 * @param string $build_name
	 * 
	 * @return array [id,testplan_id, name, notes, active, is_open]
	 */
	function get_build_by_name($id,$build_name)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$safe_build_name=$this->db->prepare_string(trim($build_name));
		
		$sql = " /* $debugMsg */ SELECT id,testplan_id, name, notes, active, is_open " .
			" FROM {$this->tables['builds']} " .
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
	 * Get a build belonging to a test plan, using build id as access key
	 *
	 * @param int $id test plan id
	 * @param int $build_id
	 *
	 * @return array [id,testplan_id, name, notes, active, is_open]
	 */
	function get_build_by_id($id,$build_id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$sql = " /* $debugMsg */ SELECT id,testplan_id, name, notes, active, is_open " .
			" FROM {$this->tables['builds']} BUILDS " .
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
	 * Get the number of builds of a given Testplan
	 *
	 * @param int tplanID test plan id
	 *
	 * @return int number of builds
	 * 
	 * @internal revisions:
	 * 20100217 - asimon - added parameters active and open to get only number of active/open builds
	 */
	function getNumberOfBuilds($tplanID, $active = null, $open = null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		
		$sql = "/* $debugMsg */ SELECT count(id) AS num_builds FROM {$this->tables['builds']} builds " .
			       "WHERE builds.testplan_id = " . $tplanID;
		
		if( !is_null($active) )
	 	{
	 	   $sql .= " AND builds.active=" . intval($active) . " ";
	 	}
	 	if( !is_null($open) )
	 	{
	 	   $sql .= " AND builds.is_open=" . intval($open) . " ";
	 	}
		
	 	return $this->db->fetchOneValue($sql);
	}

	
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
	function check_build_name_existence($tplan_id,$build_name,$build_id=null,$case_sensitive=0)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$sql = " /* $debugMsg */ SELECT id, name, notes " .
			" FROM {$this->tables['builds']} " .
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
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$sql = " /* $debugMsg */ SELECT builds.id, builds.name, builds.notes " .
			" FROM {$this->tables['builds']} builds " .
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
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$sql = " /* $debugMsg */ INSERT INTO {$this->tables['builds']} (testplan_id,name,notes,active,is_open) " .
			" VALUES ('". $tplan_id . "','" .
			$this->db->prepare_string($name) . "','" .
			$this->db->prepare_string($notes) . "'," .
			"{$active},{$open})";
		
		$new_build_id = 0;
		$result = $this->db->exec_query($sql);
		if ($result)
		{
			$new_build_id = $this->db->insert_id($this->tables['builds']);
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


	/* Get Custom Fields  Detail which are enabled on Execution of a TestCase/TestProject.
	  function: get_linked_cfields_id
	
	  args: $testproject_id 
	
	  returns: hash map of id : label
	
	  rev :
	
	*/
	
	function get_linked_cfields_id($tproject_id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$field_map = new stdClass();
		
		$sql = " /* $debugMsg */ SELECT field_id,label
			FROM {$this->tables['cfield_testprojects']} cfield_testprojects, 
			{$this->tables['custom_fields']} custom_fields
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

	/*
	  function: html_table_of_custom_field_inputs
	            
	            
	  args: $id
	        [$parent_id]: need when you call this method during the creation
	                      of a test suite, because the $id will be 0 or null.
	                      
	        [$scope]: 'design','execution'
	        
	  returns: html string
	  
	*/
	function html_table_of_custom_field_inputs($id,$parent_id=null,$scope='design',$name_suffix='',$input_values=null) 
	{
		$cf_smarty='';
	  	$method_suffix = $scope=='design' ? $scope : 'execution';
	  	$method_name = "get_linked_cfields_at_{$method_suffix}";
	  	$cf_map=$this->$method_name($id,$parent_id);

		if(!is_null($cf_map))
		{
			$cf_smarty = $this->cfield_mgr->html_table_inputs($cf_map,$name_suffix,$input_values);
        }
	  	return($cf_smarty);
	}


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
	    	$label_css_style=' class="labelHolder" ' ;
   		$value_css_style = ' ';

		$add_table=true;
		$table_style='';
		if( !is_null($formatOptions) )
		{
			$label_css_style = isset($formatOptions['label_css_style']) ? $formatOptions['label_css_style'] : $label_css_style;
			$value_css_style = isset($formatOptions['value_css_style']) ? $formatOptions['value_css_style'] : $value_css_style;

			$add_table=isset($formatOptions['add_table']) ? $formatOptions['add_table'] : true;
			$table_style=isset($formatOptions['table_css_style']) ? $formatOptions['table_css_style'] : $table_style;
		} 
		
		// BUGID 3989
	    $show_cf = config_get('custom_fields')->show_custom_fields_without_value;
	    
		if( $scope=='design' )
		{
			$cf_map=$this->get_linked_cfields_at_design($id,$parent_id,$filters);
		}
		else
		{
			$cf_map=$this->get_linked_cfields_at_execution($id);
		}
		
		if( !is_null($cf_map) )
		{
			foreach($cf_map as $cf_id => $cf_info)
			{
				// if user has assigned a value, then node_id is not null
				// BUGID 3989
				if(isset($cf_info['node_id']) || $cf_info['node_id'] || $show_cf)
				{
					// true => do not create input in audit log
					$label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label'],null,true));
					$cf_smarty .= "<tr><td {$label_css_style}>" . htmlspecialchars($label) . "</td>" .
								  "<td {$value_css_style}>" .
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


	/*
	  function: filter_cf_selection
	
	  args :
	        $tp_tcs - this comes from get_linked_tcversion
	        $cf_hash [cf_id] = value of cfields to filter by.
	
	  returns: array filtered by selected custom fields.
	
	  @internal revisions
	  20110326 - franciscom - added some logic to avoid issues if cfvalue is ''
	  
	*/
	function filter_cf_selection($tp_tcs, $cf_hash)
	{
		$new_tp_tcs = null;
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		
		// BUGID 3809 - Radio button based Custom Fields not working		
		// BUGID 3995 Custom Field Filters not working properly since the cf_hash is array	
		$or_clause = '';
		$cf_query = '';
		$ignored = 0;
		$doFilter = false;
		$doIt = true;
		
		if (isset($cf_hash)) 
		{
			$countmain = 1;
			foreach ($cf_hash as $cf_id => $cf_value) 
			{
				// single value or array?
				if (is_array($cf_value)) 
				{
					$count = 1;
					$cf_query .= $or_clause;
					foreach ($cf_value as $value) 
					{
						if ($count > 1) 
						{
							$cf_query .= " AND ";
						}
						$cf_query .= " ( CFD.value LIKE '%{$value}%' AND CFD.field_id = {$cf_id} )";
						$count++;
					}
				} 
				else 
				{
					// Because cf value can NOT exists on DB depending on system config.
					if( trim($cf_value) != '')
					{
						$cf_query .= $or_clause;
						$cf_query .= " ( CFD.value LIKE '%{$cf_value}%' AND CFD.field_id = {$cf_id} ) ";
					}	
					else
					{
						$ignored++;
					}	
				}

				if($or_clause == '')
				{
					$or_clause = ' OR ';
				}
			}
			
			// grand finale
			if( $cf_query != '')
			{
				$cf_query =  " AND ({$cf_query}) ";
				$doFilter = true;
			}	
		}
        $cf_qty = count($cf_hash) - $ignored;		                              		
        $doIt = !$doFilter;
		foreach ($tp_tcs as $tc_id => $tc_value)
		{
			if( $doFilter )
			{
				// BUGID 2877 - Custom Fields linked to TC versions
				$sql = " /* $debugMsg */ SELECT CFD.value FROM {$this->tables['cfield_design_values']} CFD," .
					   " {$this->tables['nodes_hierarchy']} NH" .
					   " WHERE CFD.node_id = NH.id " .
					   " AND NH.parent_id = {$tc_value['tc_id']} " .
					   " {$cf_query} ";

				$rows = $this->db->fetchColumnsIntoArray($sql,'value'); //BUGID 4115
			
				// if there exist as many rows as custom fields to be filtered by => tc does meet the criteria
				$doIt = (count($rows) == $cf_qty);
			}	
			if( $doIt ) 
			{
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
	       itemSet: default null  - can be an arry with test case VERSION ID
	
	  returns: sum of CF values for all testcases linked to testplan
	
	  rev: 
	  	   20110112 - franciscom - we missed refactoring of this method when have changed
	  	   						   how CF values at design time are linked to test cases.
	  	   						   Before 1.9 linked to Test Case ID
	  	   						   After 1.9 linked to Test case VERSION ID
	  	   						   
	  	   						   Another think to consider is:
	  	   						   After platform addition we need to consider all platforms
	  	   						   
	  	   						    		
	  	   20080820 - franciscom
	*/
	function get_estimated_execution_time($id,$itemSet=null,$platformID=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$estimated = array('platform' => array(), 'totalMinutes' => 0, 'totalTestCases' => 0);
		$cf_info = $this->cfield_mgr->get_by_name('CF_ESTIMATED_EXEC_TIME');
		
		// CF exists ?
		if( ($status_ok=!is_null($cf_info)) )
		{
			$cfield_id=key($cf_info);
		}
		
		if( $status_ok)
		{
			$tcVersionIDSet = array();
			$getOpt = array('outputFormat' => 'mapAccessByID' , 'addIfNull' => true);
			$platformSet = array_keys($this->getPlatforms($id,$getOpt));
			
			$sql = " /* $debugMsg */ ";
			if( DB_TYPE == 'mysql')
			{
				$sql .= " SELECT SUM(value) ";
			} 
			else if ( DB_TYPE == 'postgres' || DB_TYPE == 'mssql' )
			{
				$sql .= " SELECT SUM(CAST(value AS NUMERIC)) ";
			}       
			
			$sql .= " AS SUM_VALUE FROM {$this->tables['cfield_design_values']} CFDV " .
					" WHERE CFDV.field_id={$cfield_id} ";

			if( is_null($itemSet) )
			{
				// 20110112 - franciscom
				// we need to loop over all linked PLATFORMS (if any)
				$tcVersionIDSet = array();
				foreach($platformSet as $platfID)
				{
					if(is_null($platformID) || $platformID == $platfID )
					{ 
						$linkedItems = $this->get_linked_tcvid($id,$platfID);  
						if( (!is_null($linkedItems)) )
						{
							$tcVersionIDSet[$platfID]= array_keys($linkedItems);
						}
					}	
				}
			}
			else
			{
				// Important NOTICE
				// we can found SOME LIMITS on number of elements on IN CLAUSE
				//
				// need to make as many set as platforms linked to test plan
				$sql4tplantcv = " /* $debugMsg */ SELECT tcversion_id, platform_id " .
								" FROM {$this->tables['testplan_tcversions']} " .
								" WHERE testplan_id=" . intval($id)  .
								" AND tcversion_id IN (" . implode(',',$itemSet) . ")";
				
				if( !is_null($platformID) )
				{
					$sql4tplantcv .= " AND platform_id= " . intval($platformID);
				}				
			
				$rs = $this->db->fetchColumnsIntoMap($sql4tplantcv,'platform_id','tcversion_id',
													 database::CUMULATIVE);
				foreach($rs as $platfID => $elem)
				{
					$tcVersionIDSet[$platfID] = array_values($elem);  	
				}	
			}
		}  
		
		if($status_ok)
		{
			// Important NOTICE
			// we can found SOME LIMITS on number of elements on IN CLAUSE
			//
			$estimated = array('platform' => array(), 'totalMinutes' => 0, 'totalTestCases' => 0);
			foreach($tcVersionIDSet as $platfID => $items)
			{	
				$sql2exec = $sql . " AND node_id IN (" . implode(',',$items) . ")";
				$dummy = $this->db->fetchOneValue($sql2exec);
				$estimated['platform'][$platfID]['minutes'] = is_null($dummy) ? 0 : $dummy;
				$estimated['platform'][$platfID]['tcase_qty'] = count($items);
				
				$estimated['totalMinutes'] += $estimated['platform'][$platfID]['minutes'];
				$estimated['totalTestCases'] += $estimated['platform'][$platfID]['tcase_qty'];
			}	
		}
		return $estimated;
	}    


	/*
	  function: get_execution_time
	            Takes all executions or a subset of executions, regarding a testplan and 
	            computes SUM of values assigned AT EXECUTION TIME to custom field named CF_EXEC_TIME
	
	            IMPORTANT:
	            1. at time of this writting (20081207) this CF can be of type: string,numeric or float.
	            2. YOU NEED TO USE . (dot) as decimal separator (US decimal separator?) or
	               sum will be wrong. 
	            
	  args:id testplan id
	       $execIDSet: default null
	
	  returns: sum of CF values for all testcases linked to testplan
	
	  rev: 
	  @internal revision
	  20120115 - franciscom - BUGID 4171
	*/
	function get_execution_time($id,$execIDSet=null,$platformID=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$total_time = array('platform' => array(), 'totalMinutes' => 0, 'totalTestCases' => 0);
		$targetSet = array();
		
		$cf_info = $this->cfield_mgr->get_by_name('CF_EXEC_TIME');
		
		// CF exists ?
		if( ($status_ok=!is_null($cf_info)) )
		{
			$cfield_id=key($cf_info);
		}
		
		if( $status_ok)
		{
			$getOpt = array('outputFormat' => 'mapAccessByID' , 'addIfNull' => true);
			$platformSet = array_keys($this->getPlatforms($id,$getOpt));

			// ----------------------------------------------------------------------------
			$sql="SELECT SUM(CAST(value AS NUMERIC)) ";
			if( DB_TYPE == 'mysql')
			{
				$sql="SELECT SUM(value) ";
			} 
			else if ( DB_TYPE == 'postgres' || DB_TYPE == 'mssql' )
			{
				$sql="SELECT SUM(CAST(value AS NUMERIC)) ";
			}        
			$sql .= " AS SUM_VALUE FROM {$this->tables['cfield_execution_values']} CFEV " .
					" WHERE CFEV.field_id={$cfield_id} " .
					" AND testplan_id={$id} ";
			// ----------------------------------------------------------------------------
						
			if( is_null($execIDSet) )
			{
				// we will compute time for ALL linked and executed test cases,
				// just for LAST executed TCVERSION
				// mapOfMap -> secondary key Platform ID
				$options = array('only_executed' => true, 'output' => 'mapOfMap');
				
				$filters = null;
				if( !is_null($platformID) )
				{	 
					$filters = array('platform_id' => $platformID);
				}
				$executed = $this->get_linked_tcversions($id,$filters,$options); 
				if( ($status_ok=!is_null($executed)) )
				{
					$tc2loop = array_keys($executed);
					foreach($tc2loop as $tcase_id)
					{
						$p2loop = array_keys($executed[$tcase_id]);
						foreach($p2loop as $platf_id)
						{
							$targetSet[$platf_id][]=$executed[$tcase_id][$platf_id]['exec_id'];
						}	
					}    
				}    
			}
			else
			{
				// 20110115 - franciscom
				// If user has passed in a set of exec id, we assume that
				// he has make a good work, i.e. if he/she wanted just analize 
				// executions for just a PLATFORM he/she has filtered BEFORE
				// passing in input to this method the item set.
				// Then we will IGNORE value of argument platformID to avoid
				// run a second (and probably useless query).
				// We will use platformID JUST as index for output result
				
				if( is_null($platformID) )
				{
					throw new Exception(__FUNCTION_ . ' When you pass $execIDSet an YOU NEED TO PROVIDE a platform ID');
				}	
				$targetSet[$platformID] = $execIDSet;
			}
		}  
	
		if($status_ok)
		{
			// Important NOTICE
			// we can found SOME LIMITS on number of elements on IN CLAUSE
			//
			$estimated = array('platform' => array(), 'totalMinutes' => 0, 'totalTestCases' => 0);
			foreach($targetSet as $platfID => $items)
			{	
				$sql2exec = $sql . " AND execution_id IN (" . implode(',',$items) . ")";

				$dummy = $this->db->fetchOneValue($sql2exec);
				$total_time['platform'][$platfID]['minutes'] = is_null($dummy) ? 0 : $dummy;
				$total_time['platform'][$platfID]['tcase_qty'] = count($items);

				$total_time['totalMinutes'] += $total_time['platform'][$platfID]['minutes'];
				$total_time['totalTestCases'] += $total_time['platform'][$platfID]['tcase_qty'];
			}	
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
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$sql = 	" /* $debugMsg */ SELECT id,testplan_id, name, notes, active, is_open " .
				" FROM {$this->tables['builds']} " . 
				" WHERE testplan_id = {$id} AND id < {$build_id}" ;
		
		if( !is_null($active) )
		{
			$sql .= " AND active=" . intval($active) . " ";
		}
		
		$recordset = $this->db->fetchRowsIntoMap($sql,'id');
		return $recordset;
	}
	

	/**
	 * returns set of tcversions that has same execution status
	 * in every build present on buildSet.
	 * ATTENTION!!!: this does not work for not_run status
	 */
	 /*           
	  args: id: testplan id
	        buildSet: builds to analise.
	        status: status code
	revisions:
		20101215 - asimon - BUGID 4023: correct filtering also with platforms
	*/
	function get_same_status_for_build_set($id, $buildSet, $status, $platformid=NULL)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$node_types=$this->tree_manager->get_available_node_types();
		$resultsCfg = config_get('results');
		
		$num_exec = count($buildSet);
		$build_in = implode(",", $buildSet);
		$status_in = implode("',", (array)$status);
		
		// BUGID 4023
		$tcversionPlatformString = "";
		$executionPlatformString = "";
		
		if($platformid) {
			$tcversionPlatformString = "AND T.platform_id=$platformid";
			$executionPlatformString = "AND E.platform_id=$platformid";
		}	
				
		// 20101202 - asimon - fixed filtering issues when filtering for multiple statuses
		$first_results = null;
		
		if( in_array($resultsCfg['status_code']['not_run'], (array)$status) )
		{
			
			$sql = " /* $debugMsg */ SELECT distinct T.tcversion_id,E.build_id,NH.parent_id AS tcase_id " .
				" FROM {$this->tables['testplan_tcversions']}  T " .
				" JOIN {$this->tables['nodes_hierarchy']}  NH ON T.tcversion_id=NH.id " .
				" AND NH.node_type_id={$node_types['testcase_version']} " .
				" LEFT OUTER JOIN {$this->tables['executions']} E ON T.tcversion_id = E.tcversion_id " .
				" AND T.testplan_id=E.testplan_id AND E.build_id IN ({$build_in}) " .
				" WHERE T.testplan_id={$id} AND E.build_id IS NULL ";
			
			$first_results = $this->db->fetchRowsIntoMap($sql,'tcase_id');
		}
		
		$sql = " SELECT EE.status,SQ1.tcversion_id, NH.parent_id AS tcase_id, COUNT(EE.status) AS exec_qty " .
			" FROM {$this->tables['executions']} EE, {$this->tables['nodes_hierarchy']} NH," .
			" (SELECT E.tcversion_id,E.build_id,MAX(E.id) AS last_exec_id " .
			" FROM {$this->tables['executions']} E " .
			" WHERE E.build_id IN ({$build_in}) " .
			" GROUP BY E.tcversion_id,E.build_id) AS SQ1 " .
			" WHERE EE.build_id IN ({$build_in}) " .
			" AND EE.status IN ('" . $status . "') AND NH.node_type_id={$node_types['testcase_version']} " .
			" AND SQ1.last_exec_id=EE.id AND SQ1.tcversion_id=NH.id " .
			" GROUP BY status,SQ1.tcversion_id,NH.parent_id" .
			" HAVING count(EE.status)= {$num_exec} " ;
		
		$recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
		
		if (count($first_results)) {
			foreach ($first_results as $key => $value) {
				$recordset[$key] = $value;
			}
		}
		
		return $recordset;
	}


	/**
	 * BUGID 2455, BUGID 3026
	 * find any builds which have the wanted status in the build set
	 * 
	 * @author asimon
	 * @param integer $id Build ID
	 * @param array $buildSet build set to check
	 * @param array $status status to look for
	 * @return array $recordset set of builds which match the search criterium
	 * @internal revisions:
	 *		20101215 - asimon - BUGID 4023: correct filtering also with platforms
	 */
	function get_status_for_any_build($id, $buildSet, $status, $platformid=NULL) 
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$node_types=$this->tree_manager->get_available_node_types();
		$resultsCfg = config_get('results');

		$build_in = implode(",", $buildSet);
		$status_in = implode("','", (array)$status);
		
		// BUGID 4023
		$tcversionPlatformString = "";
		$executionPlatformString = "";
		
		if($platformid) {
			$tcversionPlatformString = "AND T.platform_id=$platformid";
			$executionPlatformString = "AND E.platform_id=$platformid";
		}	

		// 20101202 - asimon - fixed filtering issues when filtering for multiple statuses
		$first_results = null;
		
		if( in_array($resultsCfg['status_code']['not_run'], (array)$status) ) {
			//not run status
			// BUGID 4023
			$sql = "/* $debugMsg */ SELECT distinct T.tcversion_id,E.build_id,NH.parent_id AS tcase_id " .
				   " FROM {$this->tables['testplan_tcversions']}  T " .
				   " JOIN {$this->tables['nodes_hierarchy']}  NH ON T.tcversion_id=NH.id " .
				   " AND NH.node_type_id={$node_types['testcase_version']} " .
				   " LEFT OUTER JOIN {$this->tables['executions']} E ON T.tcversion_id = E.tcversion_id $executionPlatformString " .
				   " AND T.testplan_id=E.testplan_id AND E.build_id IN ({$build_in}) " .
				   " WHERE T.testplan_id={$id} $tcversionPlatformString AND E.build_id IS NULL ";
			$first_results = $this->db->fetchRowsIntoMap($sql,'tcase_id');
		} 
		
		//anything else
		$sql = "/* $debugMsg */ SELECT EE.status,SQ1.tcversion_id, NH.parent_id AS tcase_id," .
		       " COUNT(EE.status) AS exec_qty " .
			   " FROM {$this->tables['executions']} EE, {$this->tables['nodes_hierarchy']} NH," .
			   " (SELECT E.tcversion_id,E.build_id,MAX(E.id) AS last_exec_id " .
			   "  FROM {$this->tables['executions']} E " .
			   "  WHERE E.build_id IN ({$build_in}) GROUP BY E.tcversion_id,E.build_id) AS SQ1 " .
			   " WHERE EE.build_id IN ({$build_in}) " .
			   " AND EE.status IN ('" . $status_in . "') AND NH.node_type_id={$node_types['testcase_version']} " .
			   " AND SQ1.last_exec_id=EE.id AND SQ1.tcversion_id=NH.id " .
			   " GROUP BY status,SQ1.tcversion_id,NH.parent_id";
		
		$recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
		
		if (count($first_results)) {
			foreach ($first_results as $key => $value) {
				$recordset[$key] = $value;
			}
		}
		
		return $recordset;
	}

	
	/**
	 * BUGID 2455, BUGID 3026
	 * find all builds for which a testcase has not been executed
	 * 
	 * @author asimon
	 * @param integer $id Build ID
	 * @param array $buildSet build set to check
	 * @return array $new_set set of builds which match the search criterium
	 * @internal revisions:
	 *		20101215 - asimon - BUGID 4023: correct filtering also with platforms
	 */
	function get_not_run_for_any_build($id, $buildSet, $platformid=NULL) {
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$node_types=$this->tree_manager->get_available_node_types();
		
		$results = array();
		
		// BUGID 4023
		$tcversionPlatformString = "";
		$executionPlatformString = "";
		
		if($platformid) {
			$tcversionPlatformString = "AND T.platform_id=$platformid";
			$executionPlatformString = "AND E.platform_id=$platformid";
		}
	
		foreach ($buildSet as $build) {
			$sql = "/* $debugMsg */ SELECT distinct T.tcversion_id, E.build_id, E.status, NH.parent_id AS tcase_id " .
				   " FROM {$this->tables['testplan_tcversions']} T " .
				   " JOIN {$this->tables['nodes_hierarchy']} NH ON T.tcversion_id=NH.id  AND NH.node_type_id=4 " .
				   " LEFT OUTER JOIN {$this->tables['executions']} E ON T.tcversion_id = E.tcversion_id " .
				   " AND T.testplan_id=E.testplan_id AND E.build_id=$build $executionPlatformString" .
				   " WHERE T.testplan_id={$id} AND E.status IS NULL $tcversionPlatformString";
			$results[] = $this->db->fetchRowsIntoMap($sql,'tcase_id');
		}
		
		$recordset = array();
		foreach ($results as $result) 
		{
			if (!is_null($result) && (is_array($result)) ) //BUGID 3806
			{
				$recordset = array_merge_recursive($recordset, $result);
			}
		} 
		$new_set = array();
		foreach ($recordset as $key => $val) {
			$new_set[$val['tcase_id']] = $val;
		}
		
		return $new_set;
	}


	/**
	 * link platforms to a new Test Plan
	 * 
	 * @param int $source_id original Test Plan id
	 * @param int $target_id new Test Plan id
	 * @param array $mappings: key source platform id, target platform id
	 *                         USED when copy is done to a test plan that BELONGS to
	 *                         another Test Project.
	 */
	private function copy_platforms_links($source_id, $target_id, $mappings = null)
	{
    	$sourceLinks = $this->platform_mgr->getLinkedToTestplanAsMap($source_id);
    	if( !is_null($sourceLinks) )
    	{
    		$sourceLinks = array_keys($sourceLinks);
    		if( !is_null($mappings) )
    		{
    			foreach($sourceLinks as $key => $value)
    			{
    				$sourceLinks[$key] = $mappings[$value];
    			}
    		}
    		$this->platform_mgr->linkToTestplan($sourceLinks,$target_id);
    	}
	}

    /**
	 * 
 	 *
 	 * outputFormat: 
 	 *				'array',
 	 *				'map', 
 	 *				'mapAccessByID' => map access key: id
	 *				'mapAccessByName' => map access key: name
	 *
	 * 20100711 - franciscom - BUGID 3564
 	 */
    function getPlatforms($id,$options=null)
    {
        $my['options'] = array('outputFormat' => 'array', 'addIfNull' => false);
	    $my['options'] = array_merge($my['options'], (array)$options);
        switch($my['options']['outputFormat'])
        {
        	case 'map':
        		$platforms = $this->platform_mgr->getLinkedToTestplanAsMap($id);
        	break;
        	
        	default:
        		$opt = array('outputFormat' => $my['options']['outputFormat']);
        		$platforms = $this->platform_mgr->getLinkedToTestplan($id,$opt);
        	break;
        } 	
    	
    	if( $my['options']['addIfNull'] && is_null($platforms) )
		{
			$platforms = array( 0 => '');
		}
    	return $platforms; 
    }

	/**
	 * Logic to determine if platforms should be visible for a given testplan.
	 * @return bool true if the testplan has one or more linked platforms;
	 *              otherwise false.
	 */
    function hasLinkedPlatforms($id)
    {
    	return $this->platform_mgr->platformsActiveForTestplan($id);
    }	



    /**
     * changes platform id on a test plan linked test case versions for
     * a target platform.
     * Corresponding executions information is also updated
     *
	 * @param id: test plan id
	 * @param from: plaftorm id to update (used as filter criteria).
	 * @param to: new plaftorm id value
	 * @param tcversionSet: default null, can be array with tcversion id
	 *                      (used as filter criteria).
	 *
 	 *
 	 */
    function changeLinkedTCVersionsPlatform($id,$from,$to,$tcversionSet=null)
    {
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    	$sqlFilter = '';
    	if( !is_null($tcversionSet) )
    	{
			$sqlFilter = " AND tcversion_id IN (" . implode(',',(array)$tcversionSet) . " ) ";
    	}
    	$whereClause = " WHERE testplan_id = {$id} AND platform_id = {$from} {$sqlFilter}";

        $sqlStm = array();
		$sqlStm[] = "/* {$debugMsg} */ " . 
		            " UPDATE {$this->tables['testplan_tcversions']} " .
			        " SET platform_id = {$to} " . $whereClause;

		$sqlStm[] = "/* {$debugMsg} */" .
		            " UPDATE {$this->tables['executions']} " .
			        " SET platform_id = {$to} " . $whereClause;

        foreach($sqlStm as $sql)
        {
			$this->db->exec_query($sql);		
		}
    }

    /**
     *
	 * @param id: test plan id
	 * @param platformSet: default null, used as filter criteria.
	 * @return map: key platform id, values count,platform_id
 	 */
	public function countLinkedTCVersionsByPlatform($id,$platformSet=null)
	{
		$sqlFilter = '';
		if( !is_null($platformSet) )
		{
			$sqlFilter = " AND platform_id IN (" . implode(',',(array)$platformSet). ") ";
		}
		$sql = " SELECT COUNT(testplan_id) AS qty,platform_id " .
		       " FROM {$this->tables['testplan_tcversions']} " .
			   " WHERE testplan_id={$id} {$sqlFilter} " .
			   " GROUP BY platform_id ";
		$rs = $this->db->fetchRowsIntoMap($sql,'platform_id');
		return $rs;
	}


    /**
     * get detailed information of test case versions linke to test plan an NOT executed
     * gives detaile for each platform and build combination
     *
     * @deprecated 1.9
     *
	 * @param id: test plan id
	 * @param filters: optional, map with following keys
	 *                 build_id: contains a build id (just one) to be filtered
	 *                 platform_id: contains a platform id (just one) to be filtered
	 *
	 * @param options: optional map with following keys
	 *                 group_by_platform_tcversion: true -> in this way we will get one record
	 *                                              for each platform no matter on how many builds
	 *                                              test case version has not been executed.
	 *                                              when this option is set, filters are ignored
	 * @return map: 
 	 */
	public function getNotExecutedLinkedTCVersionsDetailed($id,$filters=null,$options=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$resultsCfg = config_get('results');
		$status_not_run=$resultsCfg['status_code']['not_run'];
        $executions_join = "";

        $my['filters'] = array('build_id' => 0,'platform_id' => null);
		$my['filters'] = array_merge($my['filters'], (array)$filters);

        $my['options'] = array('group_by_platform_tcversion' => false);
		$my['options'] = array_merge($my['options'], (array)$options);

		$sqlFilter = "";
        foreach($my['filters'] as $key => $value)
        {
        	if( !is_null($value) && $value > 0)
        	{
        		$sqlFilter .= " AND {$key} = {$value} "; 
        	}	
        } 
        
        if($my['options']['group_by_platform_tcversion'])
        {
			$build_fields = " ";
            $build_join = " ";
			$executions_join = " E.tcversion_id=TPTCV.tcversion_id " .
			                   " AND E.testplan_id = TPTCV.testplan_id " .
			                   " AND E.platform_id = TPTCV.platform_id ";
		    $sqlFilter = "";
        }
        else
        {
			$build_fields = " B.id AS build_id, B.name AS build_name, " .
			                " B.release_date AS build_release_date, " .
			                " B.closed_on_date AS build_closed_on_date,";
            $build_join = " JOIN {$this->tables['builds']} B ON  B.testplan_id=TPTCV.testplan_id " ;
			$executions_join = " E.build_id=B.id AND E.tcversion_id=TPTCV.tcversion_id " .
			                   " AND E.testplan_id = TPTCV.testplan_id " .
			                   " AND E.platform_id = TPTCV.platform_id ";
        }

		$sql = "/* {$debugMsg} */ ";
		$sql .= "SELECT COALESCE(E.status,'" . $status_not_run . "') AS exec_status, " .
		        $build_fields .
		        " PLAT.name AS platform_name," . 
		        " NODE_TCASE.parent_id AS testsuite_id, NODE_TCASE.name AS name, NODE_TCASE.id AS tc_id," .
		        " NODE_TCASE.node_order," .
		        " TPTCV.id AS feature_id, TPTCV.testplan_id, TPTCV.tcversion_id, " .
		        " TPTCV.node_order AS exec_node_order, TPTCV.author_id AS linked_by," .
		        " TPTCV.creation_ts AS link_creation_ts, TPTCV.platform_id, " . 
			    " TCV.version AS version, TCV.active, TCV.summary, " .
			    " TCV.tc_external_id AS external_id, TCV.execution_type," .
				" COALESCE(UA.user_id,0) AS assigned_to, " .
				" (urgency * importance) AS priority " .
				" FROM {$this->tables['testplan_tcversions']} TPTCV " .
				$build_join .
				" /* get test case version info */ " .
				" JOIN {$this->tables['tcversions']} TCV ON TCV.id=TPTCV.tcversion_id " .
				" /* get test case name */ " .
				" JOIN {$this->tables['nodes_hierarchy']} NODE_TCV ON NODE_TCV.id=TPTCV.tcversion_id " .
				" JOIN {$this->tables['nodes_hierarchy']} NODE_TCASE ON NODE_TCASE.id=NODE_TCV.parent_id " .
				" /* get platform name */ " .
				" LEFT OUTER JOIN {$this->tables['platforms']} PLAT ON " .
				" PLAT.id=TPTCV.platform_id " .
				" /* get assigned user id */ " .
				" LEFT OUTER JOIN {$this->tables['user_assignments']} UA ON UA.feature_id = TPTCV.id " .
				" LEFT OUTER JOIN {$this->tables['executions']} E ON " .
				$executions_join .
				" WHERE TPTCV.testplan_id={$id} {$sqlFilter} AND E.status IS NULL " .
				" ORDER BY testsuite_id, node_order";

        $result = $this->db->get_recordset($sql);
 		return $result;
	}

    /**
     *
	 * @param tplan_id: test plan id
	 * @return map: 
	 *
	 * @internal revisions
	 * 20100610 - eloff - BUGID 3515 - take platforms into account
 	 */
	public function getStatusTotals($tplan_id)
	{
		$code_verbose = $this->getStatusForReports();
	
		$filters=null;
		$options=array('output' => 'mapOfMap');
		$execResults = $this->get_linked_tcversions($tplan_id,$filters,$options);
	
		$totals = array('total' => 0,'not_run' => 0);
		foreach($code_verbose as $status_code => $status_verbose)
		{
			$totals[$status_verbose]=0;
		}
		foreach($execResults as $key => $testcases)
		{
			foreach($testcases as $testcase)
			{
				$totals['total']++;
				$totals[$code_verbose[$testcase['exec_status']]]++;
			}
		}
		return $totals;
	}


    /**
	 * DocBlock with nested lists
 	 *
 	 */
	public function getStatusForReports()
	{
		// This will be used to create dynamically counters if user add new status
		$resultsCfg = config_get('results');
		foreach( $resultsCfg['status_label_for_exec_ui'] as $tc_status_verbose => $label)
		{
			$code_verbose[$resultsCfg['status_code'][$tc_status_verbose]] = $tc_status_verbose;
		}
		if( !isset($resultsCfg['status_label_for_exec_ui']['not_run']) )
		{
			$code_verbose[$resultsCfg['status_code']['not_run']] = 'not_run';
		}
		return $code_verbose;
	}

    /**
     *
	 * @param tplan_id: test plan id
	 * @return map:
	 *
	 *	'type' => 'platform'
	 *	'total_tc => ZZ
	 *	'details' => array ( 'passed' => array( 'qty' => X)
	 *	                     'failed' => array( 'qty' => Y)
	 *	                     'blocked' => array( 'qty' => U)
	 *                       ....)
	 *
	 * @internal revision
	 * 20100610 - eloff - BUGID 3515 - rewrite inspired by getStatusTotals()
	 * 20100201 - franciscom - BUGID 3121
	 */
	public function getStatusTotalsByPlatform($tplan_id)
	{
		$code_verbose = $this->getStatusForReports();

		$filters=null;
		$options=array('output' => 'mapOfMap');
		$execResults = $this->get_linked_tcversions($tplan_id,$filters,$options);

		$code_verbose = $this->getStatusForReports();
		$platformSet = $this->getPlatforms($tplan_id, array('outputFormat' => 'map'));
		$totals = null;
		$platformIDSet = is_null($platformSet) ? array(0) : array_keys($platformSet);

		foreach($platformIDSet as $platformID)
		{
			$totals[$platformID]=array(
				'type' => 'platform',
				'name' => $platformSet[$platformID],
				'total_tc' => 0,
				'details' => array());
			foreach($code_verbose as $status_code => $status_verbose)
			{
				$totals[$platformID]['details'][$status_verbose]['qty'] = 0;
			}
		}
		foreach($execResults as $key => $testcases)
		{
			foreach($testcases as $platform_id => $testcase)
			{
				$totals[$platform_id]['total_tc']++;
				$totals[$platform_id]['details'][$code_verbose[$testcase['exec_status']]]['qty']++;
			}
		}
		return $totals;
	}

	/**
	 * @param int $tplan_id test plan id
	 * @return map:
	 *	'type' => 'priority'
	 *	'total_tc => ZZ
	 *	'details' => array ( 'passed' => array( 'qty' => X)
	 *	                     'failed' => array( 'qty' => Y)
	 *	                     'blocked' => array( 'qty' => U)
	 *	                      ....)
	 *
	 * @internal revision
	 * 20100614 - eloff - refactor to same style as the other getStatusTotals...()
	 * 20100206 - eloff - BUGID 3060
	 */
	public function getStatusTotalsByPriority($tplan_id)
	{
		$code_verbose = $this->getStatusForReports();
		$urgencyCfg = config_get('urgency');
		$prioSet = array(
			HIGH => lang_get($urgencyCfg['code_label'][HIGH]),
			MEDIUM => lang_get($urgencyCfg['code_label'][MEDIUM]),
			LOW => lang_get($urgencyCfg['code_label'][LOW]));
		$totals = array();

		foreach($prioSet as $prioCode => $prioLabel)
		{
			$totals[$prioCode]=array('type' => 'priority',
				'name' => $prioLabel,
				'total_tc' => 0,
				'details' => null);
			foreach($code_verbose as $status_code => $status_verbose)
			{
				$totals[$prioCode]['details'][$status_verbose]['qty']=0;
			}
		}
		$filters = null;
		$options=array('output' => 'mapOfMap');
		$execResults = $this->get_linked_tcversions($tplan_id,$filters,$options);

		foreach($execResults as $testcases)
		{
			foreach($testcases as $testcase)
			{
				$prio_level = $this->urgencyImportanceToPriorityLevel($testcase['priority']);
				$totals[$prio_level]['total_tc']++;
				$totals[$prio_level]['details'][$code_verbose[$testcase['exec_status']]]['qty']++;
			}
		}
		return $totals;
	}

	/**
	 * get last execution status analised by keyword, used to build reports.
	 *
	 * @param tplan_id: test plan id
	 * @return map: key: keyword id
	 *              value: map with following structure
	 *
	 * @internal revision
	 * 20110408 - franciscom - 	BUGID 4391: General Test Plan Metrics - 
	 *							Results by Keywords does not work properly when platforms are used
	 *
 	 */
	public function getStatusTotalsByKeyword($tplan_id)
	{
		$code_verbose = $this->getStatusForReports();
		$totals = null;
		$filters=null;
		$options = array('output' => 'mapOfMap');  // this way we get info by platform
		$execResults = $this->get_linked_tcversions($tplan_id,$filters,$options);

		if( !is_null($execResults) )
		{
			$tcaseSet = array_keys($execResults);
			$kw=$this->tcase_mgr->getKeywords($tcaseSet,null,'keyword_id',' ORDER BY keyword ASC ');
			if( !is_null($kw) )
			{
				$keywordSet = array_keys($kw);
				foreach($keywordSet as $keywordID)
				{
					$totals[$keywordID]['type'] = 'keyword';
					$totals[$keywordID]['name']=$kw[$keywordID][0]['keyword'];
					$totals[$keywordID]['notes']=$kw[$keywordID][0]['notes'];
					$totals[$keywordID]['total_tc'] = 0;
					foreach($code_verbose as $status_code => $status_verbose)
					{
						$totals[$keywordID]['details'][$status_verbose]['qty']=0;
					}
				}

				foreach($keywordSet as $keywordID)
				{
					foreach($kw[$keywordID] as $kw_tcase)
					{	
						// BUGID 4391 
						// here we need to do multiple loops according platforms
						$platformSet = array_keys($execResults[$kw_tcase['testcase_id']]);
						foreach($platformSet as $platID)
						{
							$status = $execResults[$kw_tcase['testcase_id']][$platID]['exec_status'];
							$totals[$keywordID]['total_tc']++;
							$totals[$keywordID]['details'][$code_verbose[$status]]['qty']++;
						}
					}
				}
			}
		}

		return $totals;
	}

    /**
     * 
	 * @param id: test plan id
	 * @return map: 
 	 *             key: user id
 	 *             value: map with key=platform id
 	 *                             value: map with keys: 'total' and verbose status
 	 *                                             values: test case count.
 	 *                              
 	 */
	public function getStatusTotalsByAssignedTesterPlatform($id)
	{
		$code_verbose = $this->getStatusForReports();
		$filters = null;
		$user_platform = null;
		$options = array('output' => 'mapOfMap');
    	$execResults = $this->get_linked_tcversions($id,$filters,$options);
    	
	    if( !is_null($execResults) )
	    {
	    	$tcaseSet = array_keys($execResults);
            foreach($tcaseSet as $tcaseID)
            {
            	$testcaseInfo=$execResults[$tcaseID];
            	$platformIDSet = array_keys($execResults[$tcaseID]);
            	foreach($platformIDSet as $platformID)
            	{
            		
            		$testedBy = $testcaseInfo[$platformID]['tester_id'];
            		$assignedTo = $testcaseInfo[$platformID]['user_id'];
            		$assignedTo = !is_null($assignedTo) && $assignedTo > 0 ? $assignedTo : TL_USER_NOBODY;
            		$execStatus = $testcaseInfo[$platformID]['exec_status'];
					
            		// to avoid errors due to bad or missing config
            		$verboseStatus = isset($code_verbose[$execStatus]) ? $code_verbose[$execStatus] : $execStatus;
            		
            		// 20100425 - francisco.mancardi@gruppotesi.com
            		if( $assignedTo != TL_USER_NOBODY )
            		{
            			if( !isset($user_platform[$assignedTo][$platformID]) )
            			{
            				$user_platform[$assignedTo][$platformID]['total']=0;
            			}
            			
            			if( !isset($user_platform[$assignedTo][$platformID][$verboseStatus]) )
            			{
            				$user_platform[$assignedTo][$platformID][$verboseStatus]=0;
            			}
            		}   
            		
            		$testerBoy = is_null($testedBy) ? $assignedTo : $testedBy; 
            		if( !isset($user_platform[$testerBoy][$platformID]) )
            		{
            			$user_platform[$testerBoy][$platformID]['total']=0;
            		}
            		
            		if( !isset($user_platform[$testerBoy][$platformID][$verboseStatus]) )
            		{
            			$user_platform[$testerBoy][$platformID][$verboseStatus]=0;
            		}   
                    
            		$user_platform[$testerBoy][$platformID]['total']++;
            		$user_platform[$testerBoy][$platformID][$verboseStatus]++;
                   
				}
            } 
        }
        return $user_platform;
    }

    /**
     * 
	 * @param id: test plan id
	 * @return map: 
 	 *             key: user id
 	 *             value: map with key=platform id
 	 *                             value: map with keys: 'total' and verbose status
 	 *                                             values: test case count.
 	 *                              
 	 */
	public function getStatusTotalsByAssignedTester($id)
	{
		$unassigned = lang_get('unassigned');
		$data_set = $this->getStatusTotalsByAssignedTesterPlatform($id);
	    if( !is_null($data_set) )
	    {
			$code_verbose = $this->getStatusForReports();

	    	$userSet = array_keys($data_set);
	    	// need to find a better way (with less overhead and data movement) to do this
            $userCol=tlUser::getByIDs($this->db,$userSet,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
            foreach($userSet as $assignedTo)
            {
            	$user_platform[$assignedTo]['type'] = 'assignedTester';
            	$user_platform[$assignedTo]['name'] = $unassigned; 
            	if( $assignedTo > 0 )
            	{
            		$user_platform[$assignedTo]['name'] = $userCol[$assignedTo]->getDisplayName();;
            	}
            	$user_platform[$assignedTo]['total_tc'] = 0;
            	
   				foreach($code_verbose as $status_code => $status_verbose)
			    {
					$user_platform[$assignedTo]['details'][$status_verbose]['qty']=0;
			    }
            	
            	// this will be removed from final result
            	$user_platform[$assignedTo]['details']['total']['qty'] = 0;
            	
            	$platformIDSet = array_keys($data_set[$assignedTo]);
            	foreach($platformIDSet as $platformID)
            	{
            		foreach( $data_set[$assignedTo][$platformID] as $verboseStatus => $counter)
            		{
            			if( !isset($user_platform[$assignedTo]['details'][$verboseStatus]) )
            			{
            				$user_platform[$assignedTo]['details'][$verboseStatus]['qty']=0;
            			}   
            		    $user_platform[$assignedTo]['details'][$verboseStatus]['qty'] += $counter;
            		}
				}
				$user_platform[$assignedTo]['total_tc']=$user_platform[$assignedTo]['details']['total']['qty'];
				unset($user_platform[$assignedTo]['details']['total']);
            } 
        }
        return $user_platform;
    }


    /**
     * 
	 * @param id: test plan id
	 * @return map: 
 	 */
	public function getStatusByAssignedTesterPlatform($id)
	{
		$filters = null;
		$info = null;
		$options = array('output' => 'mapOfMap');
    	$execResults = $this->get_linked_tcversions($id,$filters,$options);
	    if( !is_null($execResults) )
	    {
	    	$tcaseSet = array_keys($execResults);
            foreach($tcaseSet as $tcaseID)
            {
            	$testcaseInfo=$execResults[$tcaseID];
            	$platformIDSet = array_keys($execResults[$tcaseID]);
            	foreach($platformIDSet as $platformID)
            	{
            		$assignedTo = $testcaseInfo[$platformID]['user_id'];
            		$assignedTo = !is_null($assignedTo) && $assignedTo > 0 ? $assignedTo : TL_USER_NOBODY;   
					$info[$assignedTo][$tcaseID][$platformID] = $testcaseInfo[$platformID]['exec_status'];
				}
            } 
        }

        return $info;
    }

	/**
	 * 
 	 *
     */
	function tallyResultsForReport($results)
	{
		if ($results == null)
		{
			return null;
		}
		$na_string = lang_get('not_aplicable');
		$keySet = array_keys($results);
		foreach($keySet as $keyID)
		{
			$results[$keyID]['percentage_completed'] = 0;
			$totalCases = $results[$keyID]['total_tc'];
			$target = &$results[$keyID]['details']; 
			if ($totalCases != 0)
			{
				$results[$keyID]['percentage_completed'] = 
						number_format((($totalCases - $target['not_run']['qty']) / $totalCases) * 100,2);
						
				foreach($target as $status_verbose => $qty)
				{
					$target[$status_verbose]['percentage']=(($target[$status_verbose]['qty']) / $totalCases) * 100;
					$target[$status_verbose]['percentage']=number_format($target[$status_verbose]['percentage'],2);
				}
			} else {
				// 20100722 - asimon: if $target[$status_verbose]['percentage'] is not set,
				// it causes warnings in the template later, so it has to be set here
				// if $totalCases == 0 to avoid later undefined index warnings in the log
				foreach($target as $status_verbose => $qty) {
					$target[$status_verbose]['percentage'] = $na_string;
				}
			}
		}
		return $results;
	} // end function


	/**
	 * getTestCaseSiblings()
	 *
	 * @internal revisions
	 * 20100520 - franciscom - missed platform_id piece on join
	 */
	function getTestCaseSiblings($id,$tcversion_id,$platform_id)
	{
		$sql = 	" SELECT NHTSET.name as testcase_name,NHTSET.id AS testcase_id , NHTCVSET.id AS tcversion_id," .
        		" NHTC.parent_id AS testsuite_id, " .
        		" TPTCVX.id AS feature_id, TPTCVX.node_order " .
				" from {$this->tables['testplan_tcversions']} TPTCVMAIN " .
				" JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = TPTCVMAIN.tcversion_id " . 
				" JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " . 
				" JOIN {$this->tables['nodes_hierarchy']} NHTSET ON NHTSET.parent_id = NHTC.parent_id " .
				" JOIN {$this->tables['nodes_hierarchy']} NHTCVSET ON NHTCVSET.parent_id = NHTSET.id " .
				" JOIN {$this->tables['testplan_tcversions']} TPTCVX " . 
				" ON TPTCVX.tcversion_id = NHTCVSET.id " .
				" AND TPTCVX.testplan_id = TPTCVMAIN.testplan_id " .
				" AND TPTCVX.platform_id = TPTCVMAIN.platform_id " .
				" WHERE TPTCVMAIN.testplan_id = {$id} AND TPTCVMAIN.tcversion_id = {$tcversion_id} " .
				" AND TPTCVMAIN.platform_id = {$platform_id} " .
				" ORDER BY node_order,testcase_name ";
		$siblings = $this->db->fetchRowsIntoMap($sql,'tcversion_id');
		return $siblings;
	}


	/**
	 * getTestCaseNextSibling()
	 *
	 */
	function getTestCaseNextSibling($id,$tcversion_id,$platform_id)
	{
		$sibling = null;
    	$brothers_and_sisters = $this->getTestCaseSiblings($id,$tcversion_id,$platform_id);
    	$tcversionSet = array_keys($brothers_and_sisters);
    	$elemQty = count($tcversionSet);
    	$dummy = array_flip($tcversionSet);
        $pos = $dummy[$tcversion_id]+1;
        $sibling_tcversion = $pos < $elemQty ? $tcversionSet[$pos] : 0;
        if( $sibling_tcversion > 0 )
        {
        	$sibling = array('tcase_id' => $brothers_and_sisters[$sibling_tcversion]['testcase_id'],
        	                 'tcversion_id' => $sibling_tcversion);
        }
        return $sibling;
    }

    /**
     * Convert a given urgency and importance to a priority level using
     * threshold values in $tlCfg->priority_levels.
     *
     * @param mixed $urgency Urgency of the testcase.
     *      If this is the only parameter given then interpret it as
     *      $urgency*$importance.
     * @param mixed $importance Importance of the testcase. (Optional)
     *
     * @return int HIGH, MEDIUM or LOW
     */
    public function urgencyImportanceToPriorityLevel($urgency, $importance=null)
    {
        $urgencyImportance = intval($urgency) * (is_null($importance) ? 1 : intval($importance)) ;
        
        //BUGID 4418 - clean up priority usage
        return priority_to_level($urgencyImportance);
    }


	// -------------------
    /**
     * 
	 * @param id: test plan id
	 * @return map: 
 	 *             key: user id
 	 *             value: map with key=platform id
 	 *                             value: map with keys: 'total' and verbose status
 	 *                                             values: test case count.
 	 *                              
 	 */
	public function getStatusTotalsByTesterPlatform($id)
	{
		$code_verbose = $this->getStatusForReports();
		$filters = null;
		$user_platform = null;
		$options = array('output' => 'mapOfMap');
    	$execResults = $this->get_linked_tcversions($id,$filters,$options);
    	
	    if( !is_null($execResults) )
	    {
	    	$tcaseSet = array_keys($execResults);
            foreach($tcaseSet as $tcaseID)
            {
            	$testcaseInfo=$execResults[$tcaseID];
            	$platformIDSet = array_keys($execResults[$tcaseID]);
            	foreach($platformIDSet as $platformID)
            	{
            		$testedBy = $testcaseInfo[$platformID]['tester_id'];
            		$testedBy = !is_null($testedBy) && $testedBy > 0 ? $testedBy : TL_USER_NOBODY;
            		$execStatus = $testcaseInfo[$platformID]['exec_status'];
            		
            		// to avoid errors due to bad or missing config
            		$verboseStatus = isset($code_verbose[$execStatus]) ? $code_verbose[$execStatus] : $execStatus;
            		
            		if( !isset($user_platform[$testedBy][$platformID]) )
            		{
            			$user_platform[$testedBy][$platformID]['total']=0;
            		}
            		
            		if( !isset($user_platform[$testedBy][$platformID][$verboseStatus]) )
            		{
            			$user_platform[$testedBy][$platformID][$verboseStatus]=0;
            		}   
            		$user_platform[$testedBy][$platformID]['total']++;
            		$user_platform[$testedBy][$platformID][$verboseStatus]++;
				}
            } 
        }
	    
        return $user_platform;
    }

    /**
     * 
	 * @param id: test plan id
	 * @return map: 
 	 *             key: user id
 	 *             value: map with key=platform id
 	 *                             value: map with keys: 'total' and verbose status
 	 *                                             values: test case count.
 	 *                              
 	 */
	public function getStatusTotalsByTester($id)
	{
		$unassigned = lang_get('unassigned');
		$data_set = $this->getStatusTotalsByAssignedTesterPlatform($id);
	    if( !is_null($data_set) )
	    {
			$code_verbose = $this->getStatusForReports();

	    	$userSet = array_keys($data_set);
	    	// need to find a better way (with less overhead and data movement) to do this
            $userCol=tlUser::getByIDs($this->db,$userSet,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
            foreach($userSet as $testedBy)
            {
            	$user_platform[$testedBy]['type'] = 'tester';
            	$user_platform[$testedBy]['name'] = $unassigned; 
            	if( $testedBy > 0 )
            	{
            		$user_platform[$testedBy]['name'] = $userCol[$testedBy]->getDisplayName();;
            	}
            	$user_platform[$testedBy]['total_tc'] = 0;
            	
   				foreach($code_verbose as $status_code => $status_verbose)
			    {
					$user_platform[$testedBy]['details'][$status_verbose]['qty']=0;
			    }
            	
            	// this will be removed from final result
            	$user_platform[$testedBy]['details']['total']['qty'] = 0;
            	
            	$platformIDSet = array_keys($data_set[$assignedTo]);
            	foreach($platformIDSet as $platformID)
            	{
            		foreach( $data_set[$testedBy][$platformID] as $verboseStatus => $counter)
            		{
            			if( !isset($user_platform[$testedBy]['details'][$verboseStatus]) )
            			{
            				$user_platform[$testedBy]['details'][$verboseStatus]['qty']=0;
            			}   
            		    $user_platform[$testedBy]['details'][$verboseStatus]['qty'] += $counter;
            		}
				}
				$user_platform[$testedBy]['total_tc']=$user_platform[$testedBy]['details']['total']['qty'];
				unset($user_platform[$testedBy]['details']['total']);
            } 
        }
        return $user_platform;
    }


	/**
	 * create XML string with following structure
	 *
	 *	<?xml version="1.0" encoding="UTF-8"?>
	 *	  <testplan>
	 *	    <name></name>
	 *	    <platforms>
	 *	      <platform>
	 *	        <name> </name>
	 *	        <internal_id> </internal_id>
	 *	      </platform>
	 *	      <platform>
	 *	      ...
	 *	      </platform>
	 *	    </platforms>
	 *	    <executables>
	 *	      <link>
	 *	        <platform>
	 *	          <name> </name>
	 *	        </platform>
	 *	        <testcase>
	 *	          <name> </name>
	 *	          <externalid> </externalid>
	 *	          <version> </version>
	 *	          <execution_order> </execution_order>
	 *	        </testcase>
	 *	      </link>
	 *	      <link>
	 *	      ...
	 *	      </link>
	 *	    </executables>
	 *	  </testplan>	 
	 *	</xml>
 	 *
 	 */
	function exportLinkedItemsToXML($id)
	{
		$item_info = $this->get_by_id($id);
						
		// Linked platforms
		$xml_root = "<platforms>{{XMLCODE}}\n</platforms>";

		// ||yyy||-> tags,  {{xxx}} -> attribute 
		// tags and attributes receive different treatment on exportDataToXML()
		//
		// each UPPER CASE word in this map is a KEY, that MUST HAVE AN OCCURENCE on $elemTpl
		//
		$xml_template = "\n\t" . 
						"<platform>" . 
        				"\t\t" . "<name><![CDATA[||PLATFORMNAME||]]></name>" .
        				"\t\t" . "<internal_id><![CDATA[||PLATFORMID||]]></internal_id>" .
      					"\n\t" . "</platform>";
    					
		$xml_mapping = null;
		$xml_mapping = array("||PLATFORMNAME||" => "platform_name", "||PLATFORMID||" => 'id');

		$mm = (array)$this->platform_mgr->getLinkedToTestplanAsMap($id);
		$loop2do = count($mm);
		if( $loop2do > 0 )
		{ 
			$items2loop = array_keys($mm);
			foreach($items2loop as $itemkey)
			{
				$mm[$itemkey] = array('platform_name' => $mm[$itemkey], 'id' => $itemkey);
			}
		}
		$linked_platforms = exportDataToXML($mm,$xml_root,$xml_template,$xml_mapping,('noXMLHeader'=='noXMLHeader'));

		// Linked test cases
		$xml_root = "\n<executables>{{XMLCODE}}\n</executables>";
		$xml_template = "\n\t" . 
						"<link>" . "\n" .
						"\t\t" . "<platform>" . "\n" . 
						"\t\t\t" . "<name><![CDATA[||PLATFORMNAME||]]></name>" . "\n" .
						"\t\t" . "</platform>" . "\n" . 
						"\t\t" . "<testcase>" . "\n" . 
			            "\t\t\t" . "<name><![CDATA[||NAME||]]></name>\n" .
			            "\t\t\t" . "<externalid><![CDATA[||EXTERNALID||]]></externalid>\n" .
			            "\t\t\t" . "<version><![CDATA[||VERSION||]]></version>\n" .
			            "\t\t\t" . "<execution_order><![CDATA[||EXECUTION_ORDER||]]></execution_order>\n" .
						"\t\t" . "</testcase>" . "\n" . 
						"</link>" . "\n" .

		$xml_mapping = null;
		$xml_mapping = array("||PLATFORMNAME||" => "platform_name","||EXTERNALID||" => "external_id",							
							 "||NAME||" => "name","||VERSION||" => "version",
							 "||EXECUTION_ORDER||" => "execution_order");

		$mm = $this->get_linked_tcversions($id,null,array('output' => 'array'));
		
		$linked_testcases = exportDataToXML($mm,$xml_root,$xml_template,$xml_mapping,('noXMLHeader'=='noXMLHeader'));

		$item_info['linked_platforms'] = $linked_platforms;
		$item_info['linked_testcases'] = $linked_testcases;
		$xml_root = "\n\t<testplan>{{XMLCODE}}\n\t</testplan>";
		$xml_template = "\n\t\t" . "<name><![CDATA[||TESTPLANNAME||]]></name>" . "\n" .
						"\t\t||LINKED_PLATFORMS||\n" . "\t\t||LINKED_TESTCASES||\n";

		$xml_mapping = null;
		$xml_mapping = array("||TESTPLANNAME||" => "name","||LINKED_PLATFORMS||" => "linked_platforms",							
							 "||LINKED_TESTCASES||" => "linked_testcases");
						 
		$xml = exportDataToXML(array($item_info),$xml_root,$xml_template,$xml_mapping);

		// for debug - 
		// file_put_contents('c:\testplan.class.php.xml',$xml,FILE_APPEND);                             
		// file_put_contents('c:\testplan.class.php.xml',$xml);                             
		return $xml;
	}




	/**
	 * create XML string with following structure
	 *
	 *	<?xml version="1.0" encoding="UTF-8"?>
	 *	
	 * @param mixed context: map with following keys 
	 *						 platform_id: MANDATORY
	 *						 build_id: OPTIONAL
	 *						 tproject_id: OPTIONAL
	 */
	function exportTestPlanDataToXML($id,$context,$optExport = array())
	{
		$platform_id = $context['platform_id'];
		if( !isset($context['tproject_id']) || is_null($context['tproject_id']) )
		{
			$dummy = $this->tree_manager->get_node_hierarchy_info($id);
			$context['tproject_id'] = $dummy['parent_id'];
		}
		$context['tproject_id'] = intval($context['tproject_id']);
		
		$xmlTC = null;


		// CRITIC - this has to be firt population of item_info.
		// Other processes adds info to this map.
		$item_info = $this->get_by_id($id);
		
		// Need to get family
		// $tplan_spec = $this->tree_manager->get_subtree($id,tree::USE_RECURSIVE_MODE);
		$nt2exclude = array('testplan' => 'exclude_me','requirement_spec'=> 'exclude_me',
		                    'requirement'=> 'exclude_me');
		$nt2exclude_children = array('testcase' => 'exclude_my_children',
		                             'requirement_spec'=> 'exclude_my_children');

		$my = array();
		
		// this can be a litte weird but ...
		// when 
		// 'order_cfg' => array("type" =>'exec_order'
		// additional info test plan id, and platform id are used to get
		// a filtered view of tree.
		//
		$order_cfg = array("type" =>'exec_order',"tplan_id" => $id);
		if( $context['platform_id'] > 0 )
		{
			$order_cfg['platform_id'] = $context['platform_id'];
		}
		$my['options']=array('recursive' => true, 'order_cfg' => $order_cfg,
							 'remove_empty_nodes_of_type' => $this->tree_manager->node_descr_id['testsuite']);
 		$my['filters'] = array('exclude_node_types' => $nt2exclude,'exclude_children_of' => $nt2exclude_children);
    	$tplan_spec = $this->tree_manager->get_subtree($context['tproject_id'],$my['filters'],$my['options']);

		// -----------------------------------------------------------------------------------------------------
		// Generate test project info 
		$tproject_mgr = new testproject($this->db);
		$tproject_info = $tproject_mgr->get_by_id($context['tproject_id']);

		// ||yyy||-> tags,  {{xxx}} -> attribute 
		// tags and attributes receive different treatment on exportDataToXML()
		//
		// each UPPER CASE word in this map is a KEY, that MUST HAVE AN OCCURENCE on $elemTpl
		//
		$xml_template = "\n\t" . 
						"<testproject>" . 
        				"\t\t" . "<name><![CDATA[||TESTPROJECTNAME||]]></name>" .
        				"\t\t" . "<internal_id><![CDATA[||TESTPROJECTID||]]></internal_id>" .
      					"\n\t" . "</testproject>";
    					
    	$xml_root = "{{XMLCODE}}";					
		$xml_mapping = null;
		$xml_mapping = array("||TESTPROJECTNAME||" => "name", "||TESTPROJECTID||" => 'id');
		$mm = array();
		$mm[$context['tproject_id']] = array('name' => $tproject_info['name'], 'id' => $context['tproject_id']);
		$item_info['testproject'] = exportDataToXML($mm,$xml_root,$xml_template,$xml_mapping,
					   									('noXMLHeader'=='noXMLHeader'));
		// -----------------------------------------------------------------------------------------------------
		
		// -----------------------------------------------------------------------------------------------------
		// get target platform (if exists)
		$target_platform = '';
		if( $context['platform_id'] > 0)
		{
			$info = $this->platform_mgr->getByID($context['platform_id']);
			// ||yyy||-> tags,  {{xxx}} -> attribute 
			// tags and attributes receive different treatment on exportDataToXML()
			//
			// each UPPER CASE word in this map is a KEY, that MUST HAVE AN OCCURENCE on $elemTpl
			//
			$xml_template = "\n\t" . 
							"<platform>" . 
        					"\t\t" . "<name><![CDATA[||PLATFORMNAME||]]></name>" .
        					"\t\t" . "<internal_id><![CDATA[||PLATFORMID||]]></internal_id>" .
      						"\n\t" . "</platform>";
    						
    		$xml_root = "{{XMLCODE}}";					
			$xml_mapping = null;
			$xml_mapping = array("||PLATFORMNAME||" => "platform_name", "||PLATFORMID||" => 'id');

			$mm = array();
			$mm[$context['platform_id']] = array('platform_name' => $info['name'], 'id' => $context['platform_id']);
		    $item_info['target_platform'] = exportDataToXML($mm,$xml_root,$xml_template,$xml_mapping,
		    			   									('noXMLHeader'=='noXMLHeader'));
		    $target_platform = "\t\t||TARGET_PLATFORM||\n";
		}
		// -----------------------------------------------------------------------------------------------------

		// -----------------------------------------------------------------------------------------------------
		// get Build info (if possible)
		$target_build = '';
		if( isset($context['build_id']) &&  $context['build_id'] > 0)
		{
			$dummy = $this->get_builds($id);
			$info = $dummy[$context['build_id']];
			
			// ||yyy||-> tags,  {{xxx}} -> attribute 
			// tags and attributes receive different treatment on exportDataToXML()
			//
			// each UPPER CASE word in this map is a KEY, that MUST HAVE AN OCCURENCE on $elemTpl
			//
			$xml_template = "\n\t" . 
							"<build>" . 
        					"\t\t" . "<name><![CDATA[||BUILDNAME||]]></name>" .
        					"\t\t" . "<internal_id><![CDATA[||BUILDID||]]></internal_id>" .
      						"\n\t" . "</build>";
    						
    		$xml_root = "{{XMLCODE}}";					
			$xml_mapping = null;
			$xml_mapping = array("||BUILDNAME||" => "name", "||BUILDID||" => 'id');

			$mm = array();
			$mm[$context['build_id']] = array('name' => $info['name'], 'id' => $context['build_id']);
		    $item_info['target_build'] = exportDataToXML($mm,$xml_root,$xml_template,$xml_mapping,
		    			   									('noXMLHeader'=='noXMLHeader'));
		    $target_build = "\t\t||TARGET_BUILD||\n";
		}
		// -----------------------------------------------------------------------------------------------------

		// -----------------------------------------------------------------------------------------------------
		// get test plan contents (test suites and test cases)
		$item_info['testsuites'] = null;
		if( !is_null($tplan_spec) && ($loop2do = count($tplan_spec['childNodes'])) > 0)
		{
			$item_info['testsuites'] = '<testsuites>' . $this->exportTestSuiteDataToXML($tplan_spec,$context['tproject_id']) . 
									   '</testsuites>';
		} 
		
		$xml_root = "\n\t<testplan>{{XMLCODE}}\n\t</testplan>";
		$xml_template = "\n\t\t" . "<name><![CDATA[||TESTPLANNAME||]]></name>" . "\n" .
						"\t\t||TESTPROJECT||\n" . $target_platform  . $target_build  . "\t\t||TESTSUITES||\n";

		$xml_mapping = null;
		$xml_mapping = array("||TESTPLANNAME||" => "name", "||TESTPROJECT||" => "testproject",
							 "||TARGET_PLATFORM||" => "target_platform","||TARGET_BUILD||" => "target_build",
							 "||TESTSUITES||" => "testsuites");

		$zorba = exportDataToXML(array($item_info),$xml_root,$xml_template,$xml_mapping);
		
		return $zorba;
	}


	/**
	 * DocBlock with nested lists
 	 *
     */
	private function exportTestSuiteDataToXML($container,$tproject_id)
	{
		static $keywordMgr;
		static $getLastVersionOpt = array('output' => 'minimun');
		static $tcaseMgr;
		static $tsuiteMgr;
		static $tcaseExportOptions;
		
		if(is_null($keywordMgr))
		{
			$tcaseExportOptions = array('CFIELDS' => true, 'KEYWORDS' => 'true');
  	    	$keywordMgr = new tlKeyword();      
			$tsuiteMgr = new testsuite($this->db);
		}	
		
		$xmlTC = null;
		$cfXML = null;
		$kwXML = null;
		
		if( isset($container['id']) )
		{
			$kwMap = $tsuiteMgr->getKeywords($container['id']);
			if ($kwMap)
			{
				$kwXML = "<keywords>" . $keywordMgr->toXMLString($kwMap,true) . "</keywords>";
			}	

	    	$cfMap = (array)$tsuiteMgr->get_linked_cfields_at_design($container['id'],null,null,$tproject_id);
			if( count($cfMap) > 0 )
			{
			    $cfXML = $this->cfield_mgr->exportValueAsXML($cfMap);
			} 
			
			$tsuiteData = $tsuiteMgr->get_by_id($container['id']);
	    	$xmlTC = "\n\t<testsuite name=\"" . htmlspecialchars($tsuiteData['name']). '" >' .
	    	         "\n\t\t<node_order><![CDATA[{$tsuiteData['node_order']}]]></node_order>" .
			         "\n\t\t<details><![CDATA[{$tsuiteData['details']}]]>" .
			         "\n\t\t{$kwXML}{$cfXML}</details>";
	    }
		$childNodes = isset($container['childNodes']) ? $container['childNodes'] : null ;
		if( !is_null($childNodes) )
		{
		    $loop_qty=sizeof($childNodes); 
		    for($idx = 0;$idx < $loop_qty;$idx++)
		    {
		    	$cNode = $childNodes[$idx];
				switch($cNode['node_table'])
		    	{
		    		case 'testsuites':
		    			$xmlTC .= $this->exportTestSuiteDataToXML($cNode,$tproject_id);
		    		break;
		    		
		    		case 'testcases':
		    	    	if( is_null($tcaseMgr) )
		    	    	{
		    			    $tcaseMgr = new testcase($this->db);
		    			}
		    			// testcase::LATEST_VERSION,
		    			$xmlTC .= $tcaseMgr->exportTestCaseDataToXML($cNode['id'],$cNode['tcversion_id'],
		    			                                             $tproject_id,testcase::NOXMLHEADER,
		    			                                             $tcaseExportOptions);
		    		break;
		    	}
		    }
		}   

		if( isset($container['id']) )
		{
			$xmlTC .= "</testsuite>"; 
        }
		return $xmlTC;
	}

} // end class testplan


// ######################################################################################
/** 
 * Build Manager Class 
 * @package TestLink
 **/
class build_mgr extends tlObject
{
	/** @var database handler */
	var $db;

	/** 
	 * class constructor 
	 * 
	 * @param resource &$db reference to database handler
	 **/
	function build_mgr(&$db)
	{
   		parent::__construct();
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
          [release_date]: YYYY-MM-DD


    returns:

    rev :
  */
	function create($tplan_id,$name,$notes = '',$active=1,$open=1,$release_date='')
	{
		$targetDate=trim($release_date);
		$sql = " INSERT INTO {$this->tables['builds']} " .
			" (testplan_id,name,notes,release_date,active,is_open,creation_ts) " .
			" VALUES ('". $tplan_id . "','" .
			$this->db->prepare_string($name) . "','" .
			$this->db->prepare_string($notes) . "',";
		if($targetDate == '')
		{
			$sql .= "NULL,";
		}       
		else
		{
			$sql .= "'" . $this->db->prepare_string($targetDate) . "',";
		}
		
		
		// Important: MySQL do not support default values on datetime columns that are functions
		// that's why we are using db_now().
		$sql .= "{$active},{$open},{$this->db->db_now()})"; 	                     
		
		
		$new_build_id = 0;
		$result = $this->db->exec_query($sql);
		if ($result)
		{
			$new_build_id = $this->db->insert_id($this->tables['builds']);
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
          [$release_date]=''    FORMAT YYYY-MM-DD
          [$closed_on_date]=''  FORMAT YYYY-MM-DD

    returns:

    rev :
  */
	function update($id,$name,$notes,$active=null,$open=null,$release_date='',$closed_on_date='')
	{
		$closure_date = '';
		$targetDate=trim($release_date);
		$sql = " UPDATE {$this->tables['builds']} " .
			" SET name='" . $this->db->prepare_string($name) . "'," .
			"     notes='" . $this->db->prepare_string($notes) . "'";
		
		if($targetDate == '')
		{
			$sql .= ",release_date=NULL";
		}       
		else
		{
			$sql .= ",release_date='" . $this->db->prepare_string($targetDate) . "'";
		}
		if( !is_null($active) )
		{
			$sql .=" , active=" . intval($active);
		}
		
		if( !is_null($open) )
		{
			$open_status=intval($open) ? 1 : 0; 
			$sql .=" , is_open=" . $open_status;
			
			if($open_status == 1)
			{
				$closure_date = ''; 
			}
		}
		
		if($closure_date == '')
		{
			$sql .= ",closed_on_date=NULL";
		}       
		else
		{
			// may be will be useful validate date format
			$sql .= ",closed_on_date='" . $this->db->prepare_string($closure_date) . "'";
		}
		
		$sql .= " WHERE id={$id}";
		$result = $this->db->exec_query($sql);
		return $result ? 1 : 0;
	}

	/**
	 * Delete a build
	 * 
	 * @param integer $id
	 * @return integer status code
	 * 
	 * @internal revisions:
	 *  20100716 - asimon - BUGID 3406: delete user assignments with build
	 */
	function delete($id)
	{
		// 20090611 - franciscom
		// Need to be fixed, because execution bugs are not delete
		
		$sql = " DELETE FROM {$this->tables['executions']}  " .
			" WHERE build_id={$id}";
		
		$result=$this->db->exec_query($sql);
		
		// 3406 - delete user assignments with build
		$sql = " DELETE FROM {$this->tables['user_assignments']}  " .
			" WHERE build_id={$id}";		
		$result=$this->db->exec_query($sql);
		
		$sql = " DELETE FROM {$this->tables['builds']} " .
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
		$sql = "SELECT * FROM {$this->tables['builds']} WHERE id = {$id}";
		$result = $this->db->exec_query($sql);
		$myrow = $this->db->fetch_array($result);
		return $myrow;
	}

	/**
	 * Set date of closing build
	 * 
	 * @param integer $id Build identifier
	 * @param string $targetDate, format YYYY-MM-DD. can be null
	 * 
	 * @return TBD TBD
	 */
	function setClosedOnDate($id,$targetDate)
	{
		$sql = " UPDATE {$this->tables['builds']} ";
		
		if( is_null($targetDate) )
		{
			$sql .= " SET closed_on_date=NULL ";
		}
		else
		{
			$sql .= " SET closed_on_date='" . $this->db->prepare_string($targetDate) . "'";  	    
		}
		$sql .= " WHERE id={$id} "; 

		$result = $this->db->exec_query($sql);
	}


} // end class build_mgr


// ##################################################################################
/** 
 * Milestone Manager Class 
 * @package TestLink
 **/
class milestone_mgr extends tlObject
{
	/** @var database handler */
	var $db;

	/** 
	 * class constructor 
	 * 
	 * @param resource &$db reference to database handler
	 **/
	function milestone_mgr(&$db)
	{
        parent::__construct();
		$this->db = &$db;
	}

  /*
    function: create()

    args :
            $tplan_id
            $name
            $target_date: string with format: 
            $start_date: 
            $low_priority: percentage
            $medium_priority: percentage
            $high_priority: percentage

    returns:

  */
	function create($tplan_id,$name,$target_date,$start_date,$low_priority,$medium_priority,$high_priority)
	{
		$new_milestone_id=0;
		$dateFields=null;
		$dateValues=null;
		$dateKeys=array('target_date','start_date');
		
		// check dates
		foreach($dateKeys as $varname)
		{
			$value=	trim($$varname);
			if($value != '') 
			{
				if (($time = strtotime($value)) == -1 || $time === false) 
				{
                   die (__FUNCTION__ . ' Abort - Invalid date');
                }
				$dateFields[]=$varname;	
		        $dateValues[]=" '{$this->db->prepare_string($value)}' ";
			}
		}
		$additionalFields='';
		if( !is_null($dateFields) )
		{
			$additionalFields= ',' . implode(',',$dateFields) ;
			$additionalValues= ',' . implode(',',$dateValues) ;
		}
		$sql = "INSERT INTO {$this->tables['milestones']} " .
		       " (testplan_id,name,a,b,c{$additionalFields}) " .
			   " VALUES (" . $tplan_id . ",'{$this->db->prepare_string($name)}'," .
			   $low_priority . "," .  $medium_priority . "," . $high_priority . 
			   $additionalValues . ")";
		$result = $this->db->exec_query($sql);
		
		if ($result)
		{
			$new_milestone_id = $this->db->insert_id($this->tables['milestones']);
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
	function update($id,$name,$target_date,$start_date,$low_priority,$medium_priority,$high_priority)
	{
		$sql = "UPDATE {$this->tables['milestones']} " . 
		       " SET name='{$this->db->prepare_string($name)}', " .
			   " target_date='{$this->db->prepare_string($target_date)}', " .
			   " start_date='{$this->db->prepare_string($start_date)}', " .
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
		$sql = "DELETE FROM {$this->tables['milestones']} WHERE id={$id}";
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
			 " M.target_date, M.start_date, M.testplan_id, NH.name as testplan_name " .   
			 " FROM {$this->tables['milestones']} M, {$this->tables['nodes_hierarchy']} NH " .
			 " WHERE M.id = {$id} AND NH.id=M.testplan_id";
		$myrow = $this->db->fetchRowsIntoMap($sql,'id');
		return $myrow;
	}

	/**
	 * check existence of milestone name in Test Plan
	 * 
	 * @param integer $tplan_id  test plan id.
	 * @param string $milestone_name milestone name
	 * @param integer $milestone_id default: null
	 *                when is not null we add milestone_id as filter, this is useful
	 *                to understand if is really a duplicate when using this method
	 *                while managing update operations via GUI
	 * 
	 * @return integer 1 => name exists
	 */
	function check_name_existence($tplan_id,$milestone_name,$milestone_id=null,$case_sensitive=0)
	{
		$sql = " SELECT id, name FROM {$this->tables['milestones']} " .
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
			 " M.target_date, M.start_date, M.testplan_id, NH.name as testplan_name " .   
			 " FROM {$this->tables['milestones']} M, {$this->tables['nodes_hierarchy']} NH " .
			 " WHERE testplan_id={$tplan_id} AND NH.id = testplan_id " .
			 " ORDER BY M.target_date,M.name";
		$rs=$this->db->get_recordset($sql);
		return $rs;
	}


} // end class milestone_mgr
?>
