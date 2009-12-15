<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 	TestLink
 * @author 		Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright 	2005-2009, TestLink community 
 * @version    	CVS: $Id: testcase.class.php,v 1.205 2009/12/15 20:42:29 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 *
 * 20091215 - franciscom - getPrefix() - changed in return type, to avoid in some situations
 *                                       a double call.
 * 20091207 - franciscom - get_last_execution() - internal bug 
 * 20091127 - franciscom - getByPathName() new method
 * 20091118 - franciscom - get_last_execution() - still working ond fixing bug when using self::ALL_VERSIONS
 * 20091113 - franciscom - get_last_execution() - fixed bug when using self::ALL_VERSIONS
 * 20091003 - franciscom - show() changes in template get logic
 * 20090927 - franciscom - new methods: getPathLayered(),getPathTopSuite()
 * 20090922 - franciscom - get_last_execution() - used COALESCE() to return code
 *                                                also code for NOT RUN status.
 * 20090831 - franciscom - added management of new field: preconditions
 *                         create(),update(),exportTestCaseDataToXML()
 *
 * 20090815 - franciscom - get_executions() - added platform related info
 *                                            interface changes.
 *                         get_last_execution() - added platform related info
 *
 * 20090720 - franciscom - found bug in get_linked_cfields_at_execution()
 *                         when calling cfield_mgr class method
 *
 * 20090718 - franciscom - new method buildCFLocationMap();
 * 20090716 - franciscom - get_last_execution() - BUGID 2692
 *                         interface changes.
 *
 * 20090713 - franciscom - solved bug on get_executions() (bad SQL statement).
 * 20090530 - franciscom - html_table_of_custom_field_inputs() changes in interface
 * 20090526 - franciscom - html_table_of_custom_field_values() - added scope 'testplan_design'
 * 20090521 - franciscom - get_by_id() added version_number argument
 * 20090517 - franciscom - changes to manage deleted users on:
 *                         get_executions(),get_last_execution()
 * 20090514 - franciscom - added locatization of custom fields label without audit warning 
 * 20090419 - franciscom - BUGID 2364 - show() changes on edit enabled logic
 * 20090414 - franciscom - BUGID 2378
 * 20090401 - franciscom - BUGID 2316 - changes to copy_to()
 * 20090329 - franciscom - html_table_of_custom_field_values() new option useful when
 *                         used on printing docs.
 *
 * 20090308 - franciscom - BUGID 2204 - create() fixed return of new version number
 * 20090220 - franciscom - BUGID 2129
 * 20090214 - franciscom - when getting execution info, executions.execution_type will be returned
 *                         with alias execution_run_type
 * 
 * 20090201 - franciscom - get_by_id_bulk() - added version_id filter
 * 20090131 - franciscom - new method get_assigned_to_user()
 * 20090120 - franciscom - create_tcase_only() - added new action_on_duplicate_name	     
 * 20090116 - franciscom - get_by_name() refactoring
 *                         get_linked_versions() - added tplan_id argument
 * 20090106 - franciscom - BUGID - exportTestCaseDataToXML() - added export of custom fields values
 * 20081103 - franciscom - new method setKeywords() - added by schlundus
 *                         removed useless code from getTestProjectFromTestCase()
 *
 * 20081103 - franciscom - change to show() to improve display when used in search test cases.
 * 20081015 - franciscom - delete() - improve controls to avoid bug if no children
 * 20080812 - franciscom - BUGID 1650 (REQ)
 *                         html_table_of_custom_field_inputs() interface changes
 *                         to manage custom fields with scope='testplan_design'
 *
 * 20080602 - franciscom - get_linked_versions() - internal changes due to BUG1504
 *                         get_exec_status() - interface and internal changes due to BUG1504
 *
 * 20080425 - franciscom - replacing DEFINE with const
 *                         new internat method updateKeywordAssignment()
 *
 * 20080420 - franciscom - update() added controls to avoid duplicate name,
 *                                  changed return type
 *
 * 20080409 - azl - added optional testSuite param to get_by_name function
 * 20080206 - franciscom - exportTestCaseDataToXML() - added externalid
 * 20080126 - franciscom - BUGID 1313
 * 20080120 - franciscom - show() interface changes
 * 20080119 - franciscom - copy_tcversion() added missed logic to manage tc_external_id
 * 20080114 - franciscom - new method getPrefix()
 */

/** related functionality */
require_once( dirname(__FILE__) . '/requirement_mgr.class.php' );
require_once( dirname(__FILE__) . '/assignment_mgr.class.php' );
require_once( dirname(__FILE__) . '/attachments.inc.php' );
require_once( dirname(__FILE__) . '/users.inc.php' );

/** list of supported format for Test case import/export */
$g_tcFormatStrings = array ("XML" => lang_get('the_format_tc_xml_import'));

/** @TODO havlatm: move consts to class? */
define("TC_ALL_VERSIONS",0);
define("TC_LATEST_VERSION",-1);
define("TC_DEFAULT_ORDER",0);
define("TC_AUTOMATIC_ID",0);
define("TC_COPY_KEYWORDS",0);

/**
 * class for Test case CRUD
 * @package 	TestLink
 */
class testcase extends tlObjectWithAttachments
{
    const AUTOMATIC_ID=0;
    const DEFAULT_ORDER=0;
    const ALL_VERSIONS=0;
    const LATEST_VERSION=-1;
    const AUDIT_OFF=0;
    const AUDIT_ON=1;
    const CHECK_DUPLICATE_NAME=1;
    const DONT_CHECK_DUPLICATE_NAME=0;
    const ENABLED=1;
    const ALL_TESTPLANS=null;
    const ANY_BUILD=null;
    const GET_NO_EXEC=1; 
    const ANY_PLATFORM=null;
    
        
    
	/** @var database handler */
	var $db;
	var $tree_manager;
	var $tproject_mgr;

	var $node_types_descr_id;
	var $node_types_id_descr;
	var $my_node_type;

	var $assignment_mgr;
	var $assignment_types;
	var $assignment_status;

	var $cfield_mgr;

	var $import_file_types = array("XML" => "XML", "XLS" => "XLS" );
	var $export_file_types = array("XML" => "XML");
	var $execution_types = array();

	/**
	 * testplan class constructor
	 * 
	 * @param resource &$db reference to database handler
	 */
	function testcase(&$db)
	{
		$this->db = &$db;
		$this->tproject_mgr = New testproject($this->db);
		$this->tree_manager = &$this->tproject_mgr->tree_manager;

		$this->node_types_descr_id=$this->tree_manager->get_available_node_types();
		$this->node_types_id_descr=array_flip($this->node_types_descr_id);
		$this->my_node_type=$this->node_types_descr_id['testcase'];

		$this->assignment_mgr=New assignment_mgr($this->db);
		$this->assignment_types=$this->assignment_mgr->get_available_types();
		$this->assignment_status=$this->assignment_mgr->get_available_status();

		$this->cfield_mgr = new cfield_mgr($this->db);

		$this->execution_types = array(TESTCASE_EXECUTION_TYPE_MANUAL => lang_get('manual'),
                                       TESTCASE_EXECUTION_TYPE_AUTO => lang_get('automated'));


		// tlObjectWithAttachments::__construct($this->db,"nodes_hierarchy");
		parent::__construct($this->db,"nodes_hierarchy");
	}


	/*
	  function: get_export_file_types
	            getter
	
	  args: -
	
	  returns: map
	           key: export file type code
	           value: export file type verbose description
	
	*/
	function get_export_file_types()
	{
	    return $this->export_file_types;
	}
	
	/*
	  function: get_impor_file_types
	            getter
	
	  args: -
	
	  returns: map
	           key: import file type code
	           value: import file type verbose description
	
	*/
	function get_import_file_types()
	{
	    return $this->import_file_types;
	}
	
	/*
	   function: get_execution_types
	             getter
	
	   args: -
	
	   returns: map
	            key: execution type code
	            value: execution type verbose description
	
	*/
	function get_execution_types()
	{
	    return $this->execution_types;
	}


	/**
	 * create a test case
	 */
	function create($parent_id,$name,$summary,$preconditions,$steps,
                    $expected_results,$author_id,$keywords_id='',
                    $tc_order=self::DEFAULT_ORDER,$id=self::AUTOMATIC_ID,
                    $check_duplicate_name=self::DONT_CHECK_DUPLICATE_NAME,
                    $action_on_duplicate_name='generate_new',
                    $execution_type=TESTCASE_EXECUTION_TYPE_MANUAL,$importance=2)
	{
		$status_ok = 1;
		
		
		$ret = $this->create_tcase_only($parent_id,$name,$tc_order,$id,
			                            $check_duplicate_name,
			                            $action_on_duplicate_name);
		// if($ret['msg'] == 'ok')
		if($ret["status_ok"])
		{
			if(trim($keywords_id) != "")
			{
				$a_keywords = explode(",",$keywords_id);
				$this->addKeywords($ret['id'],$a_keywords);
			}
			
			$version_number = 1;
			if(isset($ret['version_number']) && $ret['version_number'] < 0)
			{
				// We are in the special situation we are only creating a new version,
				// useful when importing test cases. Need to get last version number.
				// I do not use create_new_version() because it does a copy ot last version
				// and do not allow to set new values in different fields while doing this operation.
				$last_version_info = $this->get_last_version_info($ret['id']);
				$version_number = $last_version_info['version']+1;
				$ret['msg'] = sprintf($ret['msg'],$version_number);       
				
				// BUGID 2204
				$ret['version_number']=$version_number;
			}
			$op = $this->create_tcversion($ret['id'],$ret['external_id'],$version_number,$summary,
			                              $preconditions,$steps,$expected_results,$author_id,
			                              $execution_type,$importance);
			
			$ret['msg'] = $op['status_ok'] ? $ret['msg'] : $op['msg'];
		}
		return $ret;
	}

	/*
	20061008 - franciscom
	           added [$check_duplicate_name]
	                 [$action_on_duplicate_name]
	
	20060725 - franciscom - interface changes
	           [$order]
	
	           [$id]
	           0 -> the id will be assigned by dbms
	           x -> this will be the id
	                Warning: no check is done before insert => can got error.
	
	return:
	       $ret['id']
	       $ret['external_id']
	       $ret['status_ok']
	       $ret['msg'] = 'ok';
		     $ret['new_name']
		     
	rev: 20090120 - franciscom - added new action_on_duplicate_name	     
	*/
	function create_tcase_only($parent_id,$name,$order=self::DEFAULT_ORDER,$id=self::AUTOMATIC_ID,
	                           $check_duplicate_name=0,
	                           $action_on_duplicate_name='generate_new')
	{
	    $ret['id'] = -1;
	    $ret['external_id']=0;
	    $ret['status_ok'] = 1;
	    $ret['msg'] = 'ok';
		$ret['new_name'] = '';
		$ret['version_number'] = 1;
		$ret['has_duplicate'] = false;
		
	
	    $doCreate=true;
	 	if ($check_duplicate_name)
		{
	        $myrow = $this->getDuplicatesByName($name,$parent_id);		
			if( !is_null($myrow) && count($myrow) > 0 )
			{
		    $ret['has_duplicate'] = true;
			  switch($action_on_duplicate_name)
			  {
				    case 'block':
		            $doCreate=true;
				    	  $ret['status_ok'] = 0;
				    	  $ret['msg'] = sprintf(lang_get('testcase_name_already_exists'),$name);
				    break;
				    
				    case 'generate_new':
				        $doCreate=true;
				        $ret['status_ok'] = 1;
						    $ret['new_name'] = $name;
						    $name = config_get('prefix_name_for_copy') . " " . $name ;
						    $ret['msg'] = sprintf(lang_get('created_with_title'),$name);
						break;
				        
				    case 'create_new_version':
				        $doCreate=false;
	              $ret['id'] = key($myrow);            
		            $ret['external_id']=$myrow[$ret['id']]['tc_external_id'];
				        $ret['status_ok'] = 1;
						    $ret['new_name'] = $name;
		            $ret['version_number'] = -1;
						    $ret['msg'] = lang_get('create_new_version');
				    break;
				    
				    default:
				    break;
				}
			}
		}
	
	  if( $ret['status_ok'] && $doCreate)
	  {
	    // Get tproject id
	    $path2root=$this->tree_manager->get_path($parent_id);
	    $tproject_id=$path2root[0]['parent_id'];
	    $tcaseNumber=$this->tproject_mgr->generateTestCaseNumber($tproject_id);
	    $tcase_id = $this->tree_manager->new_node($parent_id,$this->my_node_type,$name,$order,$id);
	    $ret['id'] = $tcase_id;
	    $ret['external_id'] = $tcaseNumber;
	  }
	
	  return $ret;
	}
	
	/*
	  function: create_tcversion
	
	  args:
	
	  returns:
	
	  rev: 20080113 - franciscom - interface changes added tc_ext_id
	
	*/
	function create_tcversion($id,$tc_ext_id,$version,$summary,$preconditions,$steps,
	                          $expected_results,$author_id,
	                          $execution_type=TESTCASE_EXECUTION_TYPE_MANUAL,$importance=2)
	{
		// get a new id
		$tcase_version_id = $this->tree_manager->new_node($id,$this->node_types_descr_id['testcase_version']);
	
		$sql = "INSERT INTO {$this->tables['tcversions']} " .
		     " (id,tc_external_id,version,summary,steps,expected_results,preconditions," . 
		     "author_id,creation_ts,execution_type,importance) " . 
	  	     " VALUES({$tcase_version_id},{$tc_ext_id},{$version},'" .
	  	     $this->db->prepare_string($summary) . "','" . $this->db->prepare_string($steps) . "'," .
		  	 "'" . $this->db->prepare_string($expected_results) .  "','" . 
		  	 $this->db->prepare_string($preconditions) . "'," . $author_id . "," .
	         $this->db->db_now() . ", {$execution_type},{$importance} )";
		$result = $this->db->exec_query($sql);
		$ret['msg']='ok';
		$ret['id']=$tcase_version_id;
		$ret['status_ok']=1;
	
		if (!$result)
		{
			$ret['msg'] = $this->db->error_msg();
		  	$ret['status_ok']=0;
		  	$ret['id']=-1;
		}
	
		return $ret;
	}
	
	
	/*
	  function: getDuplicatesByname
	
	  args: $name
	        $parent_id
	
	  returns: hash
	*/
	function getDuplicatesByName($name, $parent_id)
	{
	    $sql = " SELECT DISTINCT NHA.id,NHA.name,TCV.tc_external_id" .
			       " FROM {$this->tables['nodes_hierarchy']} NHA, " .
			       " {$this->tables['nodes_hierarchy']} NHB, {$this->tables['tcversions']} TCV  " .
			       " WHERE NHA.node_type_id = {$this->my_node_type} " .
			       " AND NHA.name = '{$this->db->prepare_string($name)}' " .
			       " AND NHB.parent_id=NHA.id " .
			       " AND TCV.id=NHB.id " .
			       " AND NHB.node_type_id = {$this->node_types_descr_id['testcase_version']} " .
			       " AND NHA.parent_id={$parent_id} ";
	
			$rs = $this->db->fetchRowsIntoMap($sql,'id');
	    if( is_null($rs) || count($rs) == 0 )
	    {
	        $rs=null;   
	    }
	    return $rs;
	}
	
	
	
	
	/*
	  function: get_by_name
	
	  args: $name
	        [$tsuite_name]: name of parent test suite
	        [$tproject_name]
	
	  returns: hash
	*/
	function get_by_name($name, $tsuite_name = '', $tproject_name = '')
	{
	    $recordset = null;
	    $filters_on = array('tsuite_name' => false, 'tproject_name' => false);
	
	    $tsuite_name = trim($tsuite_name);
	    $tproject_name = trim($tproject_name);
	    
	    $sql = " SELECT DISTINCT NH_TCASE.id,NH_TCASE.name,NH_TCASE_PARENT.id AS parent_id," .
	           " NH_TCASE_PARENT.name AS tsuite_name, TCV.tc_external_id " .
			       " FROM {$this->tables['nodes_hierarchy']} NH_TCASE, " .
			       " {$this->tables['nodes_hierarchy']} NH_TCASE_PARENT, " .
			       " {$this->tables['nodes_hierarchy']} NH_TCVERSIONS," .
			       " {$this->tables['tcversions']}  TCV  " .
			       " WHERE NH_TCASE.node_type_id = {$this->my_node_type} " .
			       " AND NH_TCASE.name = '{$this->db->prepare_string($name)}' " .
				   " AND TCV.id=NH_TCVERSIONS.id " .
			       " AND NH_TCVERSIONS.parent_id=NH_TCASE.id " .
			       " AND NH_TCASE_PARENT.id=NH_TCASE.parent_id ";
	   
		if($tsuite_name != "")
		{
			$sql .= " AND NH_TCASE_PARENT.name = '{$this->db->prepare_string($tsuite_name)}' " .
		            " AND NH_TCASE_PARENT.node_type_id = {$this->node_types_descr_id['testsuite']} ";
		}
		$recordset = $this->db->get_recordset($sql);
	    if(count($recordset) && $tproject_name != "")
	    {    
			list($tproject_info)=$this->tproject_mgr->get_by_name($tproject_name);
	        foreach($recordset as $idx => $tcase_info)
	        { 
	        	if( $this->get_testproject($tcase_info['id']) != $tproject_info['id'] )
	            {
	            	unset($recordset[$idx]);  
				}        
			}    
	    }
	
	    return $recordset;
	}
	
	
	/*
	get array of info for every test case
	without any kind of filter.
	Every array element contains an assoc array with testcase info
	
	*/
	function get_all()
	{
		$sql = " SELECT nodes_hierarchy.name, nodes_hierarchy.id
		         FROM  {$this->tables['nodes_hierarchy']} nodes_hierarchy
		         WHERE nodes_hierarchy.node_type_id={$my_node_type}";
		$recordset = $this->db->get_recordset($sql);
	
		return $recordset;
	}
	
	
	/*
	  function: show
	
	  args :
	        $smarty: reference to smarty object (controls viewer).
	        $id: test case id
	        [$version_id]: you can work on ONE test case version, or on ALL
	                       default: ALL
	
	        [viewer_args]: map with keys
	                       action
	                       msg_result
	                       refresh_tree: controls if tree view is refreshed after every operation.
	                                     default: yes
	                       user_feedback
	                       disable_edit: used to overwrite user rights
	                                        default: 0 -> no
	
	  returns:
	
	  rev :
	       20090215 - franciscom - added info about links to test plans
	       
	       20081114 - franciscom -
	       added arguments and options that are useful when this method is
	       used to display test case search results.
	       path_info: map: key: testcase id
	                       value: array with path to test case, where:
	                              element 0 -> test project name
	                              other elements test suites name
	       
	       new options on viewer_args: hilite_testcase_name,show_match_count
	       
	       20070930 - franciscom - REQ - BUGID 1078
	       added disable_edit argument
	
	*/
	function show(&$smarty,$template_dir,$id,$version_id = self::ALL_VERSIONS,
	              $viewer_args = null,$path_info=null,$mode=null)
	{
	    $status_ok = 1;
	
	    $gui = new stdClass();
	    $gui->parentTestSuiteName='';
	    $gui->path_info=$path_info;
		$gui->tprojectName='';
	    $gui->linked_versions=null;
		$gui->tc_current_version = array();
	    $gui->bodyOnLoad="";
	    $gui->bodyOnUnload="";
	    $gui->submitCode="";
	    $gui->dialogName='';
	
		$gui_cfg = config_get('gui');
		$the_tpl = config_get('tpl');
		$my_template = isset($the_tpl['tcView']) ? $the_tpl['tcView'] : 'tcView.tpl'; 

		$tcase_cfg = config_get('testcase_cfg');
	
		$req_mgr = new requirement_mgr($this->db);
		$requirements_feature=null;
		$tc_other_versions = array();
		$status_quo_map = array();
		$keywords_map = array();
		$arrReqs = array();
	    $userid_array = array();

	    
	    // 20090718 - franciscom
	    $cf_smarty = null;
	    $formatOptions=null;
	    $cfx=0;
        $filters=$this->buildCFLocationMap();
	    	     
	    if( !is_null($mode) && $mode=='editOnExec' )
	    {
	        // refers to two javascript functions present in testlink_library.js
	        // and logic used to refresh both frames when user call this
	        // method to edit a test case while executing it.
	        $gui->dialogName='tcview_dialog';
	        $gui->bodyOnLoad="dialog_onLoad($gui->dialogName)";
	        $gui->bodyOnUnload="dialog_onUnload($gui->dialogName)";
	        $gui->submitCode="return dialog_onSubmit($gui->dialogName)";
	    }
	
	    $viewer_defaults=array('title' => lang_get('title_test_case'),'show_title' => 'no',
	                           'action' => '', 'msg_result' => '','user_feedback' => '',
	                           'refresh_tree' => 'yes', 'disable_edit' => 0,
	                           'display_testproject' => 0,'display_parent_testsuite' => 0,
	                           'hilite_testcase_name' => 0,'show_match_count' => 0);
	
	    if( !is_null($viewer_args) && is_array($viewer_args) )
	    {
	        foreach($viewer_defaults as $key => $value)
	        {
	            if(isset($viewer_args[$key]) )
	            {
	                  $viewer_defaults[$key]=$viewer_args[$key];
	            }
	        }
	    }
	 
	    $gui->show_title=$viewer_defaults['show_title'];
	    $gui->display_testcase_path=!is_null($path_info);
	    $gui->hilite_testcase_name=$viewer_defaults['hilite_testcase_name'];
	    $gui->pageTitle=$viewer_defaults['title'];
	    $gui->show_match_count=$viewer_defaults['show_match_count'];
	    if($gui->show_match_count && $gui->display_testcase_path )
	    {
	        $gui->match_count=count($path_info);  
	    }
	
	    // fine grain control of operations
	    if( $viewer_defaults['disable_edit'] == 1 || has_rights($this->db,"mgt_modify_tc") == 'no' )
	    {
	        $mode = 'editDisabled';
	    }
	    $gui->show_mode=$mode;
	    $gui->can_do = $this->getShowViewerActions($mode);
	    
		if(is_array($id))
		{
			$a_id = $id;
		}
		else
		{
		    $status_ok = $id > 0 ? 1 : 0;
			$a_id = array($id);
		}
		if($status_ok)
	    {
	        $path2root=$this->tree_manager->get_path($a_id[0]);
	        $tproject_id=$path2root[0]['parent_id'];
	        $info=$this->tproject_mgr->get_by_id($tproject_id);
	        
	        // BUGID 2378
	        $testplans=$this->tproject_mgr->get_all_testplans($tproject_id,array('plan_status' =>1) );
	        $gui->has_testplans=!is_null($testplans) && count($testplans) > 0 ? 1 : 0;
	        $requirements_feature=$info['option_reqs'];
	        if( $viewer_defaults['display_testproject'] )
	        {
	            $gui->tprojectName=$info['name'];
	        }
	    
	        if( $viewer_defaults['display_parent_testsuite'] )
	        {
	            $parent_idx = count($path2root)-2;
	            $gui->parentTestSuiteName = $path2root[$parent_idx]['name'];
	        }
	    
	        $tcasePrefix = $this->tproject_mgr->getTestCasePrefix($tproject_id);
	        if(trim($tcasePrefix) != "")
	        {
	        	// Add To Testplan button will be disabled if the testcase doesn't belong to the current selected testproject
	        	$gui->can_do->add2tplan = 'no';
	        	if ($_SESSION['testprojectPrefix'] == $tcasePrefix)
	        	{
		    		$gui->can_do->add2tplan = $gui->can_do->add2tplan == 'yes' ? has_rights($this->db,"testplan_planning") : 'no';
				}
				$tcasePrefix .= $tcase_cfg->glue_character;
		   	}
	    }
	    
	    if (sizeof($a_id))
	    {
		  	$allTCKeywords = $this->getKeywords($a_id,null,'testcase_id',' ORDER BY keyword ASC ');
		  	$allReqs = $req_mgr->get_all_for_tcase($a_id);
		  	foreach($a_id as $key => $tc_id)
		  	{
		  		$tc_array = $this->get_by_id($tc_id,$version_id);
		  		if (!$tc_array)
		  		{
		  			continue;
		  		}
		  		
		  		$tc_array[0]['tc_external_id'] = $tcasePrefix . $tc_array[0]['tc_external_id'];
		  		// get the status quo of execution and links of tc versions
		  		$status_quo_map[] = $this->get_versions_status_quo($tc_id);
		  		$gui->linked_versions[] = $this->get_linked_versions($tc_id);
		  		$keywords_map[] = isset($allTCKeywords[$tc_id]) ? $allTCKeywords[$tc_id] : null;
		  		$tc_current = $tc_array[0];
		  		$gui->tc_current_version[] = array($tc_current);
		  		
		  		//Get UserID and Updater ID for current Version
		  		$userid_array[$tc_current['author_id']] = null;
		  		$userid_array[$tc_current['updater_id']] = null;
	    
		  		if(count($tc_array) > 1)
		  		{
		  			$tc_other_versions[] = array_slice($tc_array,1);
		  		}
		  		else
		  		{
		  			$tc_other_versions[] = null;
		  		}	
		  		
		  		//Get author and updater id for each version
		  		if ($tc_other_versions[0])
		  		{
		  			foreach($tc_other_versions[0] as $key => $version)
		  			{				
		  	  			$userid_array[$version['author_id']] = null;
		  	  			$userid_array[$version['updater_id']] = null;				
		  			}
		  		}
		  		$tcReqs = isset($allReqs[$tc_id]) ? $allReqs[$tc_id] : null;
		  		$arrReqs[] = $tcReqs;
		  		
		  		// foreach($verboseLocationCode as $locationKey => $locationCode)
		  		foreach($filters as $locationKey => $locationFilter)
		  		{ 
		  			//	function html_table_of_custom_field_values($id,$scope='design',$filters=null,$execution_id=null,
	                //                           $testplan_id=null,$tproject_id = null,
	                //                           $formatOptions=null,$link_id=null)
                    //
		  			$cf_smarty[$cfx][$locationKey] = 
		  				$this->html_table_of_custom_field_values($tc_id,'design',$locationFilter,
		  				                                         null,null,$tproject_id);
		  		}	
		  		$cfx++;
		  		
		  	} // foreach($a_id as $key => $tc_id)
	    } // if (sizeof($a_id))

	    // Removing duplicate and NULL id's
		unset($userid_array['']);
		$passeduserarray = array_keys($userid_array);

		$smarty->assign('cf',$cf_smarty);
		$smarty->assign('gui',$gui);
		$smarty->assign('refresh_tree',$viewer_defaults['refresh_tree']);
		$smarty->assign('sqlResult',$viewer_defaults['msg_result']);
		$smarty->assign('action',$viewer_defaults['action']);
		$smarty->assign('user_feedback',$viewer_defaults['user_feedback']);
		$smarty->assign('execution_types',$this->execution_types);
		$smarty->assign('tcase_cfg',$tcase_cfg);
		$smarty->assign('users',tlUser::getByIDs($this->db,$passeduserarray,'id'));
		$smarty->assign('status_quo',$status_quo_map);
		$smarty->assign('testcase_other_versions',$tc_other_versions);
		$smarty->assign('arrReqs',$arrReqs);
		$smarty->assign('view_req_rights', has_rights($this->db,"mgt_view_req"));
		$smarty->assign('opt_requirements',$requirements_feature);
		$smarty->assign('keywords_map',$keywords_map);
		$smarty->display($template_dir . $my_template);
	}
	
	
	
	
	// 20060726 - franciscom - default value changed for optional argument $tc_order
	//                         create(), update()
	//
	// 20060424 - franciscom - interface changes added $keywords_id
	function update($id,$tcversion_id,$name,$summary,$preconditions,$steps,
	                $expected_results,$user_id,$keywords_id='',
	                $tc_order=self::DEFAULT_ORDER,
	                $execution_type=TESTCASE_EXECUTION_TYPE_MANUAL,$importance=2)
	{
		$ret['status_ok'] = 1;
		$ret['msg'] = '';
		
		
		tLog("TC UPDATE ID=($id): exec_type=$execution_type importance=$importance");
	
		// Check if new name will be create a duplicate testcase under same parent
	  $checkDuplicates = config_get('check_names_for_duplicates');
	  if ($checkDuplicates)
	  {  	
		    $check = $this->tree_manager->nodeNameExists($name,$this->my_node_type,$id);
            $ret['status_ok'] = !$check['status']; 
            $ret['msg'] = $check['msg']; 
      }    
	
	  if($ret['status_ok'])
	  {    
	      $sql=array();
		    $sql[] = " UPDATE {$this->tables['nodes_hierarchy']} SET name='" .
		               $this->db->prepare_string($name) . "' WHERE id= {$id}";
	
		    // test case version
		    $sql[] = " UPDATE {$this->tables['tcversions']} tcversions " .
		             " SET summary='" . $this->db->prepare_string($summary) . "'," .
		    		 " steps='" . $this->db->prepare_string($steps) . "'," .
		    		 " expected_results='" . $this->db->prepare_string($expected_results) . "'," .
		    		 " updater_id={$user_id}, modification_ts = " . $this->db->db_now() . "," .
		    		 " execution_type={$execution_type}, importance={$importance} " . "," .
		    		 " preconditions='" . $this->db->prepare_string($preconditions) . "' " .
		    		 " WHERE tcversions.id = {$tcversion_id}";
	
	      foreach($sql as $stm)
	      {
	          $result = $this->db->exec_query($stm);
	          if( !$result )
	          {
		    	  $ret['status_ok'] = 0;
		    	  $ret['msg'] = $this->db->error_msg;
	              break;
	          }
	      }
	      
	      if( $ret['status_ok'] )
	      {      
		        $this->updateKeywordAssignment($id,$keywords_id);
		    }
	  }
	      
		return $ret;
	}
	
	
	/*
	  function: updateKeywordAssignment
	
	  args:
	  
	  returns: 
	
	*/
	private function updateKeywordAssignment($id,$keywords_id)
	{
		
		// To avoid false loggings, check is delete is needed
		$items = array();
		$items['stored'] = $this->get_keywords_map($id);
		if (is_null($items['stored']))
			$items['stored'] = array();
		$items['requested'] = array();
		
		if(trim($keywords_id) != "")
		{
			$a_keywords = explode(",",trim($keywords_id));
			$sql = " SELECT id,keyword " .
		       " FROM {$this->tables['keywords']} " .
		       " WHERE id IN (" . implode(',',$a_keywords) . ")";
		       
			$items['requested'] = $this->db->fetchColumnsIntoMap($sql,'id','keyword');
		}
		
		$items['common'] = array_intersect_assoc($items['stored'],$items['requested']);
		$items['new'] = array_diff_assoc($items['requested'],$items['common']);
		$items['todelete'] = array_diff_assoc($items['stored'],$items['common']);   
		
		if(!is_null($items['todelete']) && count($items['todelete']))
		{
			$this->deleteKeywords($id,array_keys($items['todelete']),self::AUDIT_ON);
		}
		
		if(!is_null($items['new']) && count($items['new']))
		{
			$this->addKeywords($id,array_keys($items['new']),self::AUDIT_ON);
		}
	}
	
	/*
	  function: logKeywordChanges
	
	  args:
	  
	  returns: 
	
	*/
	function logKeywordChanges($old,$new)
	{
	
	   // try to understand the really new
	  
	}
	
	
	
	
	
	
	
	/*
	  function: check_link_and_exec_status
	            Fore every version of testcase (id), do following checks:
	
		          1. testcase is linked to one of more test plans ?
		          2. if anwser is yes then,check if has been executed => has records on executions table
	
	  args : id: testcase id
	
	  returns: string with following values:
	           no_links: testcase is not linked to any testplan
	           linked_but_not_executed: testcase is linked at least to a testplan
	                                    but has not been executed.
	
	           linked_and_executed: testcase is linked at least to a testplan and
	                                has been executed => has records on executions table.
	
	
	*/
	function check_link_and_exec_status($id)
	{
		$status = 'no_links';
	
		// get linked versions
		$linked_tcversions = $this->get_linked_versions($id);
		$has_links_to_testplans = is_null($linked_tcversions) ? 0 : 1;
	
		if($has_links_to_testplans)
		{
			// check if executed
			$linked_not_exec = $this->get_linked_versions($id,"NOT_EXECUTED");
	
			$status='linked_and_executed';
			if(count($linked_tcversions) == count($linked_not_exec))
			{
				$status = 'linked_but_not_executed';
			}
		}
		return $status;
	}
	
	
	/* 
	 
	rev: 20081015 - franciscom - added check to avoid bug due to no children
	
	*/
	function delete($id,$version_id = self::ALL_VERSIONS)
	{
	  $children=null;
	  $doit=true;
	  if($version_id == self::ALL_VERSIONS)
	  {
	    // I'm trying to speedup the next deletes
	    $sql="SELECT nodes_hierarchy.id FROM {$this->tables['nodes_hierarchy']} nodes_hierarchy";
	    if( is_array($id) )
	    {
	      $sql .= " WHERE nodes_hierarchy.parent_id IN (" .implode(',',$id) . ") ";
	    }
	    else
	    {
	      $sql .= " WHERE nodes_hierarchy.parent_id={$id} ";
	    }
	
	    $children_rs=$this->db->get_recordset($sql);
	    $doit=!is_null($children_rs); 
	    if( $doit )
	    {
	    foreach($children_rs as $value)
	    {
	      $children[]=$value['id'];
	    }
	  }
	  }                       
	
	  if( $doit )
	  {   
		$this->_execution_delete($id,$version_id,$children);
		$this->_blind_delete($id,$version_id,$children);
	  }   
	
		return 1;
	}
	
	/*
	  function: get_linked_versions
	            For a test case get information about versions linked to testplans.
	            Filters can be applied on:
	                                      execution status
	                                      active status
	
	  args : id: testcase id
	         [exec_status]: default: ALL, range: ALL,EXECUTED,NOT_EXECUTED
	         [active_status]: default: ALL, range: ALL,ACTIVE,INACTIVE
	         [tplan_id]
	
	    returns: map.
	           key: version id
	           value: map with following structure:
	                  key: testplan id
	                  value: map with following structure:
	
	                  testcase_id
	                  tcversion_id
	                  id -> tcversion_id (node id)
	                  version
	                  summary
	                  steps
	                  expected_results
	                  importance
	                  author_id
	                  creation_ts
	                  updater_id
	                  modification_ts
	                  active
	                  is_open
	                  testplan_id
	                  tplan_name
	*/
	function get_linked_versions($id,$exec_status="ALL",$active_status='ALL',$tplan_id=null)
	{
	  $active_filter='';
	  $active_status=strtoupper($active_status);
		if($active_status !='ALL')
		{
		  $active_filter=' AND tcversions.active=' . $active_status=='ACTIVE' ? 1 : 0;
	  }
	
		switch ($exec_status)
		{
			case "ALL":
				$sql = "SELECT NH.parent_id AS testcase_id, NH.id AS tcversion_id, " .
					   "tcversions.*, TTC.testplan_id, TTC.tcversion_id,NHB.name AS tplan_name " .
					   "FROM   {$this->tables['nodes_hierarchy']} NH," .
					   " {$this->tables['tcversions']} tcversions," .
					   " {$this->tables['testplan_tcversions']} TTC, " .
					   " {$this->tables['nodes_hierarchy']} NHB    " .
					   " WHERE  TTC.tcversion_id = tcversions.id {$active_filter} " .
					   " AND    tcversions.id = NH.id " . 
					   " AND    NHB.id = TTC.testplan_id " .
					   " AND    NH.parent_id = {$id}";
						    
	      if(!is_null($tplan_id))
	      {
	          $sql .= " AND TTC.testplan_id={$tplan_id} ";  
	      }  					    
	      $recordset = $this->db->fetchMapRowsIntoMap($sql,'tcversion_id','testplan_id');
		  break;
	
	    case "EXECUTED":
		      $recordset=$this->get_exec_status($id,$exec_status,$active_status,$tplan_id);
		  break;
	
		  case "NOT_EXECUTED":
		      $recordset=$this->get_exec_status($id,$exec_status,$active_status,$tplan_id);
	    break;
	  }
	    
	  return $recordset;
	}
	
	/*
		Delete the following info:
		req_coverage
		risk_assignment
		custom fields
		keywords
		links to test plans
		tcversions
		nodes from hierarchy
	
		rev:
		     20070602 - franciscom - delete attachments
	*/
	function _blind_delete($id,$version_id=self::ALL_VERSIONS,$children=null)
	{
	    $sql = array();
	
	    if( $version_id == self::ALL_VERSIONS)
	    {
		    $sql[]="DELETE FROM {$this->tables['testcase_keywords']} WHERE testcase_id = {$id}";
		    $sql[]="DELETE FROM {$this->tables['req_coverage']}  WHERE testcase_id = {$id}";
	
		    $children_list=implode(',',$children);
		    $sql[]="DELETE FROM {$this->tables['testplan_tcversions']}  " .
		           " WHERE tcversion_id IN ({$children_list})";
	
	      $sql[]="DELETE FROM {$this->tables['tcversions']}  " .
		         " WHERE id IN ({$children_list})";
	
	      $this->deleteAttachments($id);
	      $this->cfield_mgr->remove_all_design_values_from_node($id);
	
	      $item_id = $id;
	    }
	    else
	    {
			  $sql[] = " DELETE FROM {$this->tables['testplan_tcversions']}  WHERE tcversion_id = {$version_id}";
	          $sql[] = "DELETE FROM {$this->tables['tcversions']} WHERE id = {$version_id}";
	
	    	$item_id = $version_id;
	    }
	
	    foreach ($sql as $the_stm)
	    {
			  $result = $this->db->exec_query($the_stm);
	    }
	    $this->tree_manager->delete_subtree($item_id);
	}
	
	
	/*
	Delete the following info:
		bugs
		executions
	  cfield_execution_values
	*/
	function _execution_delete($id,$version_id=self::ALL_VERSIONS,$children=null)
	{
		  $sql = array();
			if( $version_id	== self::ALL_VERSIONS )
			{
		    $children_list=implode(',',$children);
	    	$sql[]="DELETE FROM {$this->tables['execution_bugs']}
	        		  WHERE execution_id IN (SELECT id FROM {$this->tables['executions']} 
	            		                     WHERE tcversion_id IN ({$children_list}))";
	
	      // 20070603 - franciscom
	      $sql[]="DELETE FROM {$this->tables['cfield_execution_values']}  " .
	      		   " WHERE tcversion_id IN ({$children_list})";
	
	      $sql[]="DELETE FROM {$this->tables['executions']}  " .
	      		   " WHERE tcversion_id IN ({$children_list})";
	
	
	    }
	    else
	    {
	    		$sql[]="DELETE FROM {$this->tables['execution_bugs']}
	        	  	  WHERE execution_id IN (SELECT id FROM {$this->tables['executions']}
	              		                     WHERE tcversion_id = {$version_id})";
	
	        // 20070603 - franciscom
	        $sql[]="DELETE FROM {$this->tables['cfield_execution_values']} " .
	        		   " WHERE tcversion_id = {$version_id}";
	
	    		$sql[]="DELETE FROM {$this->tables['executions']} " .
	        		   " WHERE tcversion_id = {$version_id}";
	    }
	
	    foreach ($sql as $the_stm)
	    {
	    		$result = $this->db->exec_query($the_stm);
	    }
	}
	
	
	/*
	  function: formatTestCaseIdentity
	
	  args: id: testcase id
	        external_id
	
	  returns: testproject id
	
	*/
	function formatTestCaseIdentity($id,$external_id)
	{
	    $path2root=$this->tree_manager->get_path($tc_id);
	    $tproject_id=$path2root[0]['parent_id'];
	    $tcasePrefix=$this->tproject_mgr->getTestCasePrefix($tproject_id);
	}
	
	
	/*
	  function: getPrefix
	
	  args: id: testcase id
	
	  returns: array(prefix,testproject id)
	
	*/
	function getPrefix($id)
	{
	    $path2root=$this->tree_manager->get_path($id);
	    $tproject_id=$path2root[0]['parent_id'];
	    $tcasePrefix=$this->tproject_mgr->getTestCasePrefix($tproject_id);
	    return array($tcasePrefix,$tproject_id);
	}
	
	
	
	
	/*
	  function: get_testproject
	            Given a testcase id get node id of testproject to which testcase belongs.
	  args :id: testcase id
	
	  returns: testproject id
	
	*/
	function get_testproject($id)
	{
	  $a_path = $this->tree_manager->get_path($id);
	  return ($a_path[0]['parent_id']);
	}
	
	/*
	20061008 - franciscom - added
	                        [$check_duplicate_name]
	                        [$action_on_duplicate_name]
	
	                        changed return type
	
	*/
	function copy_to($id,$parent_id,$user_id,$copy_also=null,
	                 $check_duplicate_name=0,
	                 $action_on_duplicate_name='generate_new')
	{
	    $new_tc['id']=-1;
	    $new_tc['status_ok']=0;
	    $new_tc['msg']='ok';
	
	    if( is_null($copy_also) )
	    {
	        $copy_also=array('keyword_assignments' => true,'requirement_assignments' => true);   
	    }
	    
		$tcase_info = $this->get_by_id($id);
		if ($tcase_info)
		{
			$new_tc = $this->create_tcase_only($parent_id,$tcase_info[0]['name'],
			                                   $tcase_info[0]['node_order'],self::AUTOMATIC_ID,
	                                           $check_duplicate_name,$action_on_duplicate_name);
			if ($new_tc['status_ok'])
			{
		        $ret['status_ok']=1;
	 			foreach($tcase_info as $tcversion)
				{
					$this->create_tcversion($new_tc['id'],$new_tc['external_id'],$tcversion['version'],
					                        $tcversion['summary'],$tcversion['preconditions'],$tcversion['steps'],
					                        $tcversion['expected_results'],$tcversion['author_id']);
				}
				
				// Conditional copies
				if (isset($copy_also['keyword_assignments']) && $copy_also['keyword_assignments'])
				{
					$this->copyKeywordsTo($id,$new_tc['id']);
				}
				
				if (isset($copy_also['requirement_assignments']) && $copy_also['requirement_assignments'])
				{
					$this->copyReqAssignmentTo($id,$new_tc['id']);
				}
				
				
				$this->copy_cfields_design_values($id,$new_tc['id']);
	            $this->copy_attachments($id,$new_tc['id']);
			}
		}
		return($new_tc);
	}
	
	
	/*
	  function: create_new_version()
	            create a new test case version, doing a copy of last test case version.
	
	  args : $id: testcase id
	         $user_id: who is doing this operation.
	
	  returns:
	          map:  id: node id of created tcversion
	                version: version number (i.e. 5)
	                msg
	
	  rev : 20070701 - franciscom - added version key on return map.
	*/
	function create_new_version($id,$user_id)
	{
	  // get a new id
	  $tcversion_id = $this->tree_manager->new_node($id,$this->node_types_descr_id['testcase_version']);
	
	  // get last version for this test case
	  $last_version_info =  $this->get_last_version_info($id);
	  $this->copy_tcversion($last_version_info['id'],$tcversion_id,$last_version_info['version']+1,$user_id);
	
	  $ret['id'] = $tcversion_id;
	  $ret['version'] = $last_version_info['version']+1;
	  $ret['msg'] = 'ok';
	  return $ret;
	}
	
	
	
	/*
	  function: get_last_version_info
	            Get information about last version (greater number) of a testcase.
	
	  args : id: testcase id
	
	  returns: map with following keys:
	
		  			 id -> tcversion_id
					   version
					   summary
					   steps
					   expected_results
					   importance
					   author_id
					   creation_ts
					   updater_id
					   modification_ts
					   active
					   is_open
	
	*/
	function get_last_version_info($id)
	{
		$sql = "SELECT MAX(version) AS version " .
		       " FROM {$this->tables['tcversions']} tcversions," .
		       " {$this->tables['nodes_hierarchy']} nodes_hierarchy WHERE ".
		       " nodes_hierarchy.id = tcversions.id ".
		       " AND nodes_hierarchy.parent_id = {$id} ";
	
		$max_version = $this->db->fetchFirstRowSingleColumn($sql,'version');
	
		$tcInfo = null;
		if ($max_version)
		{
			$sql = "SELECT tcversions.* FROM {$this->tables['tcversions']} tcversions," .
			       " {$this->tables['nodes_hierarchy']} nodes_hierarchy ".
			       "WHERE version = {$max_version} AND nodes_hierarchy.id = tcversions.id".
				   " AND nodes_hierarchy.parent_id = {$id}";
	
			$tcInfo = $this->db->fetchFirstRow($sql);
		}
		return $tcInfo;
	}
	
	
	/*
	  function: copy_tcversion
	
	  args:
	
	  returns:
	
	  rev: 20080119 - franciscom - tc_external_id management
	
	*/
	function copy_tcversion($from_tcversion_id,$to_tcversion_id,$as_version_number,$user_id)
	{
	    $now = $this->db->db_now();
	    $sql="INSERT INTO {$this->tables['tcversions']} (id,version,tc_external_id,author_id,creation_ts," .
	         "                        summary,steps,expected_results,importance,execution_type) " .
	         " SELECT {$to_tcversion_id} AS id, {$as_version_number} AS version, " .
	         "        tc_external_id, " .
	         "        {$user_id} AS author_id, {$now} AS creation_ts," .
	         "        summary,steps,expected_results,importance,execution_type " .
	         " FROM {$this->tables['tcversions']} " .
	         " WHERE id={$from_tcversion_id} ";
	
	    $result = $this->db->exec_query($sql);
	}
	
	
	/*
	  function: get_by_id_bulk
	
	            IMPORTANT CONSIDERATION: 
	            how may elements can be used in an SQL IN CLAUSE?
	            Think there is a limit ( on MSSQL 1000 ?)
	                                      
	  args :
	
	  returns:
	
	*/
	function get_by_id_bulk($id,$version_id=self::ALL_VERSIONS, $get_active=0, $get_open=0)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$where_clause="";
		$where_clause_names="";
		$tcid_list ="";
		$tcversion_id_filter="";
		$sql = "";
		$the_names = null;
		if( is_array($id) )
		{
			$tcid_list = implode(",",$id);
			$where_clause = " WHERE nodes_hierarchy.parent_id IN ($tcid_list) ";
			$where_clause_names = " WHERE nodes_hierarchy.id IN ($tcid_list) ";
		}
		else
		{
			$where_clause = " WHERE nodes_hierarchy.parent_id = {$id} ";
			$where_clause_names = " WHERE nodes_hierarchy.id = {$id} ";
		}
	  	if( $version_id != self::ALL_VERSIONS )
	  	{
	  	    $tcversion_id_filter=" AND tcversions.id IN (" . implode(",",(array)$version_id) . ") ";
	  	}
	
		$sql = " /* $debugMsg */ SELECT nodes_hierarchy.parent_id AS testcase_id, ".
		       " tcversions.*, users.first AS author_first_name, users.last AS author_last_name, " .
		       " '' AS updater_first_name, '' AS updater_last_name " .
		       " FROM {$this->tables['nodes_hierarchy']} nodes_hierarchy " .
		       " JOIN {$this->tables['tcversions']} tcversions ON nodes_hierarchy.id = tcversions.id " .
	           " LEFT OUTER JOIN {$this->tables['users']} users ON tcversions.author_id = users.id " .
	           " {$where_clause} {$tcversion_id_filter} ORDER BY tcversions.version DESC";
	  $recordset = $this->db->get_recordset($sql);
	
	  if($recordset)
	  {
	  	 // get the names
		 $sql = " /* $debugMsg */ " . 
		        " SELECT nodes_hierarchy.id AS testcase_id, nodes_hierarchy.name " .
		        " FROM {$this->tables['nodes_hierarchy']} nodes_hierarchy {$where_clause_names} ";
	
		 $the_names = $this->db->get_recordset($sql);
	     if($the_names)
	     {
	    	  foreach ($recordset as  $the_key => $row )
	    	  {
	          	reset($the_names);
	          	foreach($the_names as $row_n)
	          	{
	          		  if( $row['testcase_id'] == $row_n['testcase_id'])
	          		  {
	          		    $recordset[$the_key]['name']= $row_n['name'];
	          		    break;
	          		  }
	          	}
	  	    }
	  	 }
	
	
		 $sql = " /* $debugMsg */ " . 
		        " SELECT updater_id, users.first AS updater_first_name, users.last  AS updater_last_name " .
		        " FROM {$this->tables['nodes_hierarchy']} nodes_hierarchy " .
		        " JOIN {$this->tables['tcversions']} tcversions ON nodes_hierarchy.id = tcversions.id " .
	            " LEFT OUTER JOIN {$this->tables['users']} users ON tcversions.updater_id = users.id " .
	            " {$where_clause} and tcversions.updater_id IS NOT NULL ";
	
	    $updaters = $this->db->get_recordset($sql);
	
	    if($updaters)
	    {
	    	reset($recordset);
	    	foreach ($recordset as  $the_key => $row )
	    	{
	    		if ( !is_null($row['updater_id']) )
	    		{
	      		foreach ($updaters as $row_upd)
	      		{
	            if ( $row['updater_id'] == $row_upd['updater_id'] )
	            {
	              $recordset[$the_key]['updater_last_name'] = $row_upd['updater_last_name'];
	              $recordset[$the_key]['updater_first_name'] = $row_upd['updater_first_name'];
	              break;
	            }
	      		}
	      	}
	      }
	    }
	
	  }
	
	
	  return($recordset ? $recordset : null);
	}
	
	
	
	
	/*
	  function: get_by_id
	
	  args : id: can be a single testcase id or an array od testcase id.
	
	         [version_id]: default self::ALL_VERSIONS => all versions
	                       can be an array.
	                       Useful to retrieve only a subset of versions.
	                       null => means use version_number argument
	
	         [active_status]: default 'ALL', range: 'ALL','ACTIVE','INACTIVE'
	                          has effect for the following version_id values:
	                          self::ALL_VERSIONS,TC_LAST_VERSION, version_id is NOT an array
	
	         [open_status]: default 'ALL'
	                        currently not used.
	                        
	         [version_number]: default 1, version number displayed at User Interface               
	
	  returns: array when every element has following keys:
	
	
	*/
	function get_by_id($id,$version_id = self::ALL_VERSIONS, 
	                   $active_status='ALL',$open_status='ALL',$version_number=1)
	{
		$tcid_list = '';
		$where_clause = '';
		$active_filter = '';
	
		if(is_array($id))
		{
			$tcid_list = implode(",",$id);
			$where_clause = " WHERE NHA.parent_id IN ({$tcid_list}) ";
		}
		else
		{
			$where_clause = " WHERE NHA.parent_id = {$id} ";
		}
	
		if(is_array($version_id))
		{
		    $versionid_list = implode(",",$version_id);
		    $where_clause .= " AND tcversions.id IN ({$versionid_list}) ";
		}
		else
		{
		    // 20090521 - franciscom - search by human version number
		    if( is_null($version_id) )
		    {
		        $where_clause .= " AND tcversions.version = {$version_number} ";
		    }
		    else 
		    {
			    if($version_id != self::ALL_VERSIONS && $version_id != self::LATEST_VERSION)
			    {
			    	$where_clause .= " AND tcversions.id = {$version_id} ";
			    }
	        }
	        
			$active_status = strtoupper($active_status);
		  	if($active_status != 'ALL')
		  	{
		    	$active_filter =' AND tcversions.active=' . ($active_status=='ACTIVE' ? 1 : 0) . ' ';
	    	}
		}
	
		$sql = "SELECT U.login AS updater_login,users.login as author_login,
			     NHB.name,NHB.node_order,NHA.parent_id AS testcase_id, tcversions.*,
			     users.first AS author_first_name,
			     users.last AS author_last_name,
			     U.first AS updater_first_name,
			     U.last  AS updater_last_name
	         FROM {$this->tables['nodes_hierarchy']} NHA
	         JOIN {$this->tables['nodes_hierarchy']} NHB ON NHA.parent_id = NHB.id
	         JOIN {$this->tables['tcversions']} tcversions ON NHA.id = tcversions.id
	         LEFT OUTER JOIN {$this->tables['users']} users ON tcversions.author_id = users.id
	         LEFT OUTER JOIN {$this->tables['users']} U ON tcversions.updater_id = U.id
	         $where_clause
	         $active_filter
	         ORDER BY tcversions.version DESC";
	
	    
		if ($version_id != self::LATEST_VERSION)
		{
			$recordset = $this->db->get_recordset($sql);
		}
		else
		{
		    // 20090413 - franciscom - 
		    // But, how performance wise can be do this, instead of using MAX(version)
		    // and a group by? 
		    //                              
			$recordset = array($this->db->fetchFirstRow($sql));
	    }
	    
		return ($recordset ? $recordset : null);
	}
	
	
	/*
	  function: get_versions_status_quo
	            Get linked and executed status quo.
	            
	            IMPORTANT:
	            NO INFO SPECIFIC TO TESTPLAN ITEMS where testacase can be linked to
	            is returned.
	
	
	  args : id: test case id
	         [tcversion_id]: default: null -> get info about all versions.
	                         can be a single value or an array.
	
	
	         [testplan_id]: default: null -> all testplans where testcase is linked,
	                                         are analised to generate results.
	
	                        when not null, filter for testplan_id, to analise for
	                        generating results.
	
	
	
	  returns: map.
	           key: tcversion_id.
	           value: map with the following keys:
	
	           tcversion_id, linked , executed
	
	           linked field: will take the following values
	                         if $testplan_id == null
	                            NULL if the tc version is not linked to ANY TEST PLAN
	                            tcversion_id if linked
	
	                         if $testplan_id != null
	                            NULL if the tc version is not linked to $testplan_id
	
	
	           executed field: will take the following values
	                           if $testplan_id == null
	                              NULL if the tc version has not been executed in ANY TEST PLAN
	                              tcversion_id if has executions.
	
	                           if $testplan_id != null
	                              NULL if the tc version has not been executed in $testplan_id
	
	rev :
	
	*/
	function get_versions_status_quo($id, $tcversion_id=null, $testplan_id=null)
	{
	    $testplan_filter='';
	    $tcversion_filter='';
	    if(!is_null($tcversion_id))
	    {
	      if(is_array($tcversion_id))
	      {
	         $tcversion_filter=" AND NH.id IN (" . implode(",",$tcversion_id) . ") ";
	      }
	      else
	      {
	         $tcversion_filter=" AND NH.id={$tcversion_id} ";
	      }
	
	    }
	
	    $testplan_filter='';
		if(!is_null($testplan_id))
	    {
	      $testplan_filter=" AND E.testplan_id = {$testplan_id} ";
	    }
	    $execution_join=" LEFT OUTER JOIN {$this->tables['executions']} E " .
	                    " ON (E.tcversion_id = NH.id {$testplan_filter})";
	
	 	$sqlx=  " SELECT TCV.id,TCV.version " .
	            " FROM {$this->tables['nodes_hierarchy']} NHA " .
	            " JOIN {$this->tables['nodes_hierarchy']} NHB ON NHA.parent_id = NHB.id " .
	            " JOIN {$this->tables['tcversions']}  TCV ON NHA.id = TCV.id " .
	            " WHERE  NHA.parent_id = {$id}";
	            
		$version_id = $this->db->fetchRowsIntoMap($sqlx,'version');
	
		$sql="SELECT DISTINCT NH.id AS tcversion_id,T.tcversion_id AS linked, " .
		     " E.tcversion_id AS executed,E.tcversion_number,TCV.version " .
		     " FROM   {$this->tables['nodes_hierarchy']} NH " .
	         " JOIN {$this->tables['tcversions']} TCV ON (TCV.id = NH.id ) " .
		     " LEFT OUTER JOIN {$this->tables['testplan_tcversions']} T ON T.tcversion_id = NH.id " .
		     " {$execution_join} WHERE  NH.parent_id = {$id} {$tcversion_filter} ORDER BY executed DESC";
	
		$rs = $this->db->get_recordset($sql);
	
	    $recordset=array();
	    $template=array('tcversion_id' => '','linked' => '','executed' => '');
	    foreach($rs as $elem)
	    {
	      $recordset[$elem['tcversion_id']]=$template;  
	      $recordset[$elem['tcversion_id']]['tcversion_id']=$elem['tcversion_id'];  
	      $recordset[$elem['tcversion_id']]['linked']=$elem['linked'];  
	      $recordset[$elem['tcversion_id']]['version']=$elem['version'];  
	    }
	    
	    foreach($rs as $elem)
	    {
	      $tcvid=null;
	      if( $elem['tcversion_number'] != $elem['version'])
	      {
	      if( !is_null($elem['tcversion_number']) )
	      {
	            $tcvid=$version_id[$elem['tcversion_number']]['id'];
	        }    
	      }
	      else
	      {
	        $tcvid=$elem['tcversion_id'];
	      }
	      if( !is_null($tcvid) )
	      {
	          $recordset[$tcvid]['executed']=$tcvid;
	          $recordset[$tcvid]['version']=$elem['tcversion_number'];
	      }    
	    }
	    return($recordset);
	}
	
	
	
	/*
	  function: get_exec_status
	            Get information about executed and linked status in
	            every testplan, a testcase is linked to.
	
	  args : id : testcase id
	         [exec_status]: default: ALL, range: ALL,EXECUTED,NOT_EXECUTED
	         [active_status]: default: ALL, range: ALL,ACTIVE,INACTIVE
	
	
	  returns: map
	           key: tcversion_id
	           value: map:
	                  key: testplan_id
	                  value: map with following keys:
	
	                  tcase_id
	 				          tcversion_id
	  			          version
	  			          testplan_id
	  				        tplan_name
	  				        linked         if linked to  testplan -> tcversion_id
					          executed       if executed in testplan -> tcversion_id
					          exec_on_tplan  if executed in testplan -> testplan_id
	
	
	  rev: 
	      
	       20080531 - franciscom
	       Because we allow people to update test case version linked to test plan,
	       and to do this we update tcversion_id on executions to new version
	       maintaining the really executed version in tcversion_number (version number displayed
	       on User Interface) field we need to change algorithm.
	*/
	function get_exec_status($id,$exec_status="ALL",$active_status='ALL',$tplan_id=null)
	{
	    $active_status = strtoupper($active_status);
	  
	    // Get info about tcversions of this test case
	    $sqlx = " SELECT TCV.id,TCV.version,TCV.active" .
	          " FROM {$this->tables['nodes_hierarchy']} NHA " .
	          " JOIN {$this->tables['nodes_hierarchy']} NHB ON NHA.parent_id = NHB.id " .
	          " JOIN {$this->tables['tcversions']}  TCV ON NHA.id = TCV.id ";
	          
	    $where_clause = " WHERE  NHA.parent_id = {$id}";
	          
	    if(!is_null($tplan_id))
	    {
	        $sqlx .= " JOIN {$this->tables['testplan_tcversions']}  TTCV ON TTCV.tcversion_id = TCV.id ";
	        $where_clause .= " AND TTCV.tplan_id = {$tplan_id} "; 
	    }    
	    $sqlx .= $where_clause; 
		$version_id = $this->db->fetchRowsIntoMap($sqlx,'version');
	
	  
		$sql = "SELECT DISTINCT NH.parent_id AS tcase_id, NH.id AS tcversion_id,
			             T.tcversion_id AS linked,TCV.active,
			             E.tcversion_id AS executed, E.testplan_id AS exec_on_tplan,
			             E.tcversion_number,
			             T.testplan_id, NHB.name AS tplan_name, TCV.version
			      FROM   {$this->tables['nodes_hierarchy']} NH
			      JOIN {$this->tables['testplan_tcversions']}  T ON T.tcversion_id = NH.id
			      JOIN {$this->tables['tcversions']}  TCV ON T.tcversion_id = TCV.id
			      JOIN {$this->tables['nodes_hierarchy']} NHB ON T.testplan_id = NHB.id
			      LEFT OUTER JOIN {$this->tables['executions']}  E ON (E.tcversion_id = NH.id AND E.testplan_id=T.testplan_id)
			      WHERE  NH.parent_id = {$id} ";
			
		if(!is_null($tplan_id))
	    {
	        $sql .= " AND T.tplan_id = {$tplan_id} "; 
	    }    
	    $sql .= " ORDER BY version,tplan_name";
	
		$rs = $this->db->get_recordset($sql);
	
	    // set right tcversion_id, based on tcversion_number,version comparison
	    $item_not_executed = null;
	    $item_executed = null;
	    $link_info = null;
	    $in_set = null;
	    
	    if (sizeof($rs))
	    {
			foreach($rs as $idx => $elem)
			{
		    	if( $elem['tcversion_number'] != $elem['version'])
			    {
			        // Save to generate record for linked but not executed if needed
			        // (see below fix not executed section)
			        $link_info[$elem['tcversion_id']][]=$elem;    
		
			      // We are working with a test case version, that was used in a previous life of this test plan
			      // information about his tcversion_id is not anymore present in tables:
			      //
			      // testplan_tcversions
			      // executions
			      // cfield_execution_values.
			      //
			      // if has been executed, but after this operation User has choosen to upgrade tcversion 
			      // linked to testplan to a different (may be a newest) test case version.
			      //
			      // We can get this information using table tcversions using tcase id and version number 
			      // (value displayed at User Interface) as search key.
			      //
			      // Important:
			      // executions.tcversion_number:  maintain info about right test case version executed
			      // executions.tcversion_id    :  test case version linked to test plan. 
			      //
			      //
			      if( is_null($elem['tcversion_number']) )
			      {
			          // Not Executed
			          $rs[$idx]['executed']=null;
		            $rs[$idx]['tcversion_id']=$elem['tcversion_id'];
		            $rs[$idx]['version']=$elem['version'];
		            $rs[$idx]['linked']=$elem['tcversion_id'];
		            $item_not_executed[]=$idx;  
			      }
			      else
			      {
		            // Get right tcversion_id
		            $rs[$idx]['executed']=$version_id[$elem['tcversion_number']]['id'];
		            $rs[$idx]['tcversion_id']=$rs[$idx]['executed'];
		            $rs[$idx]['version']=$elem['tcversion_number'];
		            $rs[$idx]['linked']=$rs[$idx]['executed'];
		            $item_executed[]=$idx;
			      }
			      $version=$rs[$idx]['version'];
		        $rs[$idx]['active']=$version_id[$version]['active'];	      
		      }
		      else
		      {
		          $item_executed[]=$idx;  
		      }
		
			 	// needed for logic to avoid miss not executed (see below fix not executed)
			    $in_set[$rs[$idx]['tcversion_id']][$rs[$idx]['testplan_id']]=$rs[$idx]['tcversion_id'];
			}
	    }
	    else
	    	$rs = array();
	    // fix not executed
	    //
	    // need to add record for linked but not executed, that due to new
	    // logic to upate testplan-tcversions link can be absent
	    if(!is_null($link_info))
	    {
	        foreach($link_info as $idx => $elem)
		      {
	            foreach($elem as $value)
	            {
	              if( !isset($in_set[$idx][$value['testplan_id']]) ) 
	              {
	                  // missing record
	                  $value['executed']=null;
	                  $value['exec_on_tplan']=null;
	                  $value['tcversion_number']=null;
	                  $rs[]=$value;
	                  
	                  // Must Update list of not executed
	                  $kix=count($rs);
	                  $item_not_executed[]=$kix > 0 ? $kix-1 : $kix;
	              }  
	            }   
	        }
	    }
	    
	    // Convert to result map.
		  switch ($exec_status)
		  {
	        case 'NOT_EXECUTED':
	             $target=$item_not_executed;
	        break;
	
	        case 'EXECUTED':
	             $target=$item_executed;
	        break;
	        
	        default:
	        	$target = array_keys($rs);
	        	break;
	    }
	
	    $recordset = null;
	    if( !is_null($target) )   // minor fix - 20090716 - franciscom
	    {
	    	foreach($target as $idx)
			{
			     $elem=$rs[$idx];
	    	   if( $active_status=='ALL' ||
	    	       $active_status='ACTIVE' && $elem['active'] ||
	    	       $active_status='INACTIVE' && $elem['active']==0 )
	    	   {    
	    	       $recordset[$elem['tcversion_id']][$elem['testplan_id']]=$elem;
	    	   }    
	    	}
	    }		  
	    if( !is_null($recordset) )
	    {
	        ksort($recordset);
	    }
	    return $recordset;
	}
	// -------------------------------------------------------------------------------
	
	
	/*
	  function: getInternalID
	
	  args: stringID: external test case ID, an string with this components
	                  XXXXXGNN
	
	                  XXXXX: test case prefix, exists one for each test project
	                  G: glue character
	                  NN: test case number (generated using testprojects.tc_counter field)
	
	  returns: internal id (node id in nodes_hierarchy)
	
	
	  20080818 - franciscom - Dev Note
	  I'm a feeling regarding performance of this function.
	  Surelly adding a new column to tcversions (prefix) will simplify a lot this function.
	  Other choice (that I refuse to implement time ago) is to add prefix field
	  as a new nodes_hierarchy column.
	  This must be discussed with dev team if we got performance bottleneck trying
	  to get internal id from external one.
	 
	  
	  rev:
	      20090608 - franciscom - fixed error on management of numeric part (externalID)
	      20080126 - franciscom - BUGID 1313
	*/
	function getInternalID($stringID,$glueCharacter = null)
	{
		if (is_null($glueCharacter))
		{
			$cfg = config_get('testcase_cfg');
			$glueCharacter = $cfg->glue_character;
		}
		$internalID = 0;
		$pieces = explode($glueCharacter,$stringID);
		if(count($pieces) == 2)
		{
	    	$testCasePrefix = $this->db->prepare_string($pieces[0]);
	        $externalID = is_numeric($pieces[1]) ?  intval($pieces[1]) : 0;
	
			$sql = "SELECT DISTINCT NH.parent_id AS tcase_id" .
	               " FROM {$this->tables['tcversions']} TCV, {$this->tables['nodes_hierarchy']} NH" .
	               " WHERE TCV.id = NH.id " .
	               " AND  TCV.tc_external_id = {$externalID}";
	
			$testCases = $this->db->fetchRowsIntoMap($sql,'tcase_id');
			if(!is_null($testCases))
			{
	      		$sql = 	"SELECT id  FROM {$this->tables['testprojects']} " .
	               		"WHERE prefix = '" . $testCasePrefix . "'";
				$recordset = $this->db->get_recordset($sql);
				$tproject_id = $recordset[0]['id'];
	
				$tprojectSet = array();
				foreach($testCases as $tcaseID => $value)
				{
	        		$path2root = $this->tree_manager->get_path($tcaseID);
					if($tproject_id == $path2root[0]['parent_id'])
					{
	          			$internalID = $tcaseID;
						break;
	        		}
	      		}	
			}
		}
		return $internalID;
	}
	
	/*
	  function: filterByKeyword
	            given a test case id (or an array of test case id) 
	            and a keyword filter, returns for the test cases given in input
	            only which pass the keyword filter criteria.
	            
	
	  args :
	  
	  returns: 
	
	*/
	function filterByKeyword($id,$keyword_id=0, $keyword_filter_type='OR')
	{
	    $keyword_filter= '' ;
	    $subquery='';
	    
	    // test case filter
	    if( is_array($id) )
	    {
	        $testcase_filter = " AND testcase_id IN (" . implode(',',$id) . ")";          	
	    }
	    else
	    {
	        $testcase_filter = " AND testcase_id = {$id} ";
	    }    
	    
	    if( is_array($keyword_id) )
	    {
	        $keyword_filter = " AND keyword_id IN (" . implode(',',$keyword_id) . ")";          	
	        
	        if($keyword_filter_type == 'AND')
	        {
			        $subquery = "AND testcase_id IN (" .
			                    " SELECT MAFALDA.testcase_id FROM
			                      ( SELECT COUNT(testcase_id) AS HITS,testcase_id
			                        FROM {$this->tables['keywords']} K, {$this->tables['testcase_keywords']}
			                        WHERE keyword_id = K.id
			                        {$keyword_filter}
			                        GROUP BY testcase_id ) AS MAFALDA " .
			                    " WHERE MAFALDA.HITS=" . count($keyword_id) . ")";
			                 
	            $keyword_filter ='';
	        }    
	    }
	    else if( $keyword_id > 0 )
	    {
	        $keyword_filter = " AND keyword_id = {$keyword_id} ";
	    }
			
			$map_keywords = null;
			$sql = " SELECT testcase_id,keyword_id,keyword
			         FROM {$this->tables['keywords']} K, {$this->tables['testcase_keywords']}
			         WHERE keyword_id = K.id
			         {$testcase_filter}
			         {$keyword_filter} {$subquery}
				       ORDER BY keyword ASC ";
	
			// $map_keywords = $this->db->fetchRowsIntoMap($sql,'testcase_id');
			$map_keywords = $this->db->fetchMapRowsIntoMap($sql,'testcase_id','keyword_id');
	
			return($map_keywords);
	} //end function
	
	
	
	// -------------------------------------------------------------------------------
	//                            Keyword related methods
	// -------------------------------------------------------------------------------
	/*
	  function: getKeywords
	
	  args :
	
	  returns:
	
	*/
	function getKeywords($tcID,$kwID = null,$column = 'keyword_id',$orderByClause = null)
	{
		$sql = "SELECT keyword_id,keywords.keyword,keywords.notes,testcase_id
		        FROM {$this->tables['testcase_keywords']} testcase_keywords, {$this->tables['keywords']} keywords
		        WHERE keyword_id = keywords.id AND testcase_id ";
		$bCumulative = 0;
		if (is_array($tcID))
		{
			$sql .= " IN (".implode(",",$tcID).")";
			$bCumulative = 1;
		}
		else
		{
			$sql .=  "= {$tcID}";
		}
		if (!is_null($kwID))
		{
			$sql .= " AND keyword_id = {$kwID}";
		}
		if (!is_null($orderByClause))
		{
			$sql .= $orderByClause;
		}	
		$tcKeywords = $this->db->fetchRowsIntoMap($sql,$column,$bCumulative);
	
		return $tcKeywords;
	}
	
	
	/*
	  function: get_keywords_map
	
	  args: id: testcase id
	        [order_by_clause]: default: '' -> no order choosen
	                           must be an string with complete clause, i.e.
	                           'ORDER BY keyword'
	
	
	  returns: map with keywords information
	           key: keyword id
	           value: map with following keys.
	
	
	*/
	function get_keywords_map($id,$order_by_clause='')
	{
		$sql = "SELECT keyword_id,keywords.keyword
		        FROM {$this->tables['testcase_keywords']} testcase_keywords, {$this->tables['keywords']} keywords
		        WHERE keyword_id = keywords.id ";
		if (is_array($id))
			$sql .= " AND testcase_id IN (".implode(",",$id).") ";
		else
			$sql .= " AND testcase_id = {$id} ";
	
		$sql .= $order_by_clause;
	
		$map_keywords = $this->db->fetchColumnsIntoMap($sql,'keyword_id','keyword');
		return $map_keywords;
	}
	
	/*
	  function: 
	
	  args :
	  
	  returns: 
	
	*/
	function addKeyword($id,$kw_id,$audit=self::AUDIT_ON)
	{
		$kw = $this->getKeywords($id,$kw_id);
		if (sizeof($kw))
			return 1;
		$sql = " INSERT INTO {$this->tables['testcase_keywords']} (testcase_id,keyword_id) " .
			     " VALUES ($id,$kw_id)";
	
		$result = ($this->db->exec_query($sql) ? 1 : 0);
		if ($result)
		{
			$tcInfo = $this->tree_manager->get_node_hierachy_info($id);
			$keyword = tlKeyword::getByID($this->db,$kw_id);
			if ($keyword && $tcInfo && $audit == self::AUDIT_ON)
			{
				logAuditEvent(TLS("audit_keyword_assigned_tc",$keyword->name,$tcInfo['name']),
				                  "ASSIGN",$id,"nodes_hierarchy");
			}	
		}
		return $result;
	}
	
	/*
	  function: 
	
	  args :
	  
	  returns: 
	
	*/
	function addKeywords($id,$kw_ids,$audit = self::AUDIT_ON)
	{
		$bSuccess = 1;
		$num_kws = sizeof($kw_ids);
		for($idx = 0; $idx < $num_kws; $idx++)
		{
			$bSuccess = $bSuccess && $this->addKeyword($id,$kw_ids[$idx],$audit);
		}
	
		return $bSuccess;
	}
	/*
	  function: set's the keywords of the given testcase to the passed keywords
	
	  args :
	  
	  returns: 
	
	*/
	function setKeywords($id,$kw_ids,$audit = self::AUDIT_ON)
	{
		$result = $this->deleteKeywords($id);   	 
		if ($result && sizeof($kw_ids))
		{
			$result = $this->addKeywords($id,$kw_ids);
		}	
		return $result;
	}
	
	function copyKeywordsTo($id,$destID)
	{
		$bSuccess = true;
		$this->deleteKeywords($destID);
		$kws = $this->getKeywords($id);
		if ($kws)
		{
			foreach($kws as $k => $kwID)
			{
				$bSuccess = $bSuccess && $this->addKeyword($destID,$kwID['keyword_id']);
			}
		}
		return $bSuccess;
	}
	
	/*
	  function: 
	
	  args :
	  
	  returns: 
	
	*/
	function deleteKeywords($tcID,$kwID = null,$audit=self::AUDIT_ON)
	{
		$sql = " DELETE FROM {$this->tables['testcase_keywords']}  WHERE testcase_id = {$tcID} ";
		if (!is_null($kwID))
		{
			if(is_array($kwID))
		  	{
			    $sql .= " AND keyword_id IN (" . implode(',',$kwID) . ")";
			    $key4log=$kwID;
			}
			else
			{
			    $sql .= " AND keyword_id = {$kwID}";
			    $key4log = array($kwID);
			}    
		}	
		else
			$key4log = array_keys((array)$this->get_keywords_map($tcID));
			
		$result = $this->db->exec_query($sql);
		if ($result)
		{
			$tcInfo = $this->tree_manager->get_node_hierachy_info($tcID);
			if ($tcInfo && $key4log)
			{
				foreach($key4log as $key2get)
				{
					$keyword = tlKeyword::getByID($this->db,$key2get);
					if ($keyword && $audit==self::AUDIT_ON)
					{
						logAuditEvent(TLS("audit_keyword_assignment_removed_tc",$keyword->name,$tcInfo['name']),
						              "ASSIGN",$tcID,"nodes_hierarchy");
					}	
				}
			}
		}
	
		return $result;
	}
	
	// -------------------------------------------------------------------------------
	//                            END Keyword related methods
	// -------------------------------------------------------------------------------
	
	/*
	  function: get_executions
	            get information about all execution for a testcase version, on a testplan
	            on a build. Execution results are ordered by execution timestamp.
	
	            Is possible to filter certain executions
	            Is possible to choose Ascending/Descending order of results. (order by exec timestamp).
	
	
	  args : id: testcase (node id) - can be single value or array.
	         version_id: tcversion id (node id) - can be single value or array.
	         tplan_id: testplan id
	         build_id: if null -> do not filter by build_id
	         platform_id: if null -> do not filter by build_id
             options: default null, map with options.
	                  [exec_id_order] default: 'DESC' - range: ASC,DESC
	                  [exec_to_exclude]: default: null -> no filter
	                                    can be single value or array, this exec id will be EXCLUDED.
	
	
	  returns: map
	           key: tcversion id
	           value: array where every element is a map with following keys
	
	                  name: testcase name
	                  testcase_id
	                  id: tcversion_id
	                  version
	                  summary: testcase spec. summary
	                  steps: testcase spec. steps
	                  expected_results: testcase spec. expected results
	                  execution_type: see const.inc.php TESTCASE_EXECUTION_TYPE_ constants
	                  importance
	                  author_id: tcversion author
	                  creation_ts: timestamp of creation
	                  updater_id: last updater of specification
	                  modification_ts:
	                  active: tcversion active status
	                  is_open: tcversion open status
	                  tester_login
	                  tester_first_name
	                  tester_last_name
	                  tester_id
	                  execution_id
	                  status: execution status
	                  execution_notes
	                  execution_ts
	                  execution_run_type: see const.inc.php TESTCASE_EXECUTION_TYPE_ constants
	                  build_id
	                  build_name
	                  build_is_active
	                  build_is_open
	                  platform_id
	                  platform_name
	
	*/
	function get_executions($id,$version_id,$tplan_id,$build_id,$platform_id,$options=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
	    $my['options'] = array('exec_id_order' => 'DESC', 'exec_to_exclude' => null); 	
	    $my['options'] = array_merge($my['options'], (array)$options);
		
		$filterKeys = array('build_id','platform_id');
        foreach($filterKeys as $key)
        {
        	$filterBy[$key] = '';
        	if( !is_null($$key) )
        	{
        		$itemSet = implode(',', (array)$$key);
        		$filterBy[$key] = " AND e.{$key} IN ({$itemSet}) ";
        	}
        }
	
		// --------------------------------------------------------------------
		if( is_array($id) )
		{
			$tcid_list = implode(",",$id);
			$where_clause = " WHERE NHA.parent_id IN ({$tcid_list}) ";
		}
		else
		{
			$where_clause = " WHERE NHA.parent_id = {$id} ";
		}
	
		if( is_array($version_id) )
		{
		    $versionid_list = implode(",",$version_id);
		    $where_clause  .= " AND tcversions.id IN ({$versionid_list}) ";
		}
		else
		{
				if($version_id != self::ALL_VERSIONS)
				{
					$where_clause  .= " AND tcversions.id = {$version_id} ";
				}
		}
	
	  if( !is_null($my['options']['exec_to_exclude']) )
	  {
	
				if( is_array($my['options']['exec_to_exclude']))
				{
				    if(count($my['options']['exec_to_exclude']) > 0 )
				    {
				 	  	$exec_id_list = implode(",",$my['options']['exec_to_exclude']);
		        		$where_clause  .= " AND e.id NOT IN ({$exec_id_list}) ";
		        	}
				}
				else
				{
		        $where_clause  .= " AND e.id <> {$exec_id_list} ";
				}
		}
	  // --------------------------------------------------------------------
	  // 20090517 - to manage deleted users i need to change:
	  //            users.id AS tester_id => e.tester_id AS tester_id
	  // 20090214 - franciscom - e.execution_type -> e.execution_run_type
	  //
	  $sql="/* $debugMsg */ SELECT NHB.name,NHA.parent_id AS testcase_id, tcversions.*,
			    users.login AS tester_login,
			    users.first AS tester_first_name,
			    users.last AS tester_last_name,
				e.tester_id AS tester_id,
			    e.id AS execution_id, e.status,e.tcversion_number,
			    e.notes AS execution_notes, e.execution_ts, e.execution_type AS execution_run_type,
			    e.build_id AS build_id,
			    b.name AS build_name, b.active AS build_is_active, b.is_open AS build_is_open,
   			    e.platform_id,p.name AS platform_name
		    FROM {$this->tables['nodes_hierarchy']} NHA
	        JOIN {$this->tables['nodes_hierarchy']} NHB ON NHA.parent_id = NHB.id
	        JOIN {$this->tables['tcversions']} tcversions ON NHA.id = tcversions.id
	        JOIN {$this->tables['executions']} e ON NHA.id = e.tcversion_id
	                                             AND e.testplan_id = {$tplan_id}
	                                             {$filterBy['build_id']} {$filterBy['platform_id']}
	        JOIN {$this->tables['builds']}  b ON e.build_id=b.id
	        LEFT OUTER JOIN {$this->tables['users']} users ON users.id = e.tester_id
	        LEFT OUTER JOIN {$this->tables['platforms']} p ON p.id = e.platform_id
	        $where_clause
	        ORDER BY NHA.node_order ASC, NHA.parent_id ASC, execution_id {$my['options']['exec_id_order']}";
	
	  $recordset = $this->db->fetchArrayRowsIntoMap($sql,'id');
	  return($recordset ? $recordset : null);
	}
	
	
	/*
	  function: get_last_execution
	
	  args :
  
	
	  returns: map:
	           key: tcversions.id
	           value: map with following keys:
	            			execution_id
	            			status: execution status
	                  execution_type: see const.inc.php TESTCASE_EXECUTION_TYPE_ constants
	            			name: testcase name
	            			testcase_id
	            			tsuite_id: parent testsuite of testcase (node id)
	            			id: tcversion id (node id)
	            			version
	                  summary: testcase spec. summary
	                  steps: testcase spec. steps
	                  expected_results: testcase spec. expected results
	                  execution_type: type of execution desired
	            			importance
	                  author_id: tcversion author
	                  creation_ts: timestamp of creation
	                  updater_id: last updater of specification.
	            			modification_ts
	                  active: tcversion active status
	                  is_open: tcversion open status
	            			tester_login
	            			tester_first_name
	            			tester_last_name
	            			tester_id
	            			execution_notes
	            			execution_ts
	            			execution_run_type:  how the execution was really done
	            			build_id
	            			build_name
	            			build_is_active
	            			build_is_open
	
	   rev:
	       20090815 - franciscom - added platform_id argument
	       20090716 - franciscom - added options argument, removed get_no_executions
	       20080103 - franciscom - added execution_type
	
	*/
	function get_last_execution($id,$version_id,$tplan_id,$build_id,$platform_id,$options=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$resultsCfg = config_get('results');
		$status_not_run=$resultsCfg['status_code']['not_run'];

		$filterKeys = array('build_id','platform_id');
        foreach($filterKeys as $key)
        {
        	$filterBy[$key] = '';
        	if( !is_null($$key) )
        	{
        		$itemSet = implode(',', (array)$$key);
        		$filterBy[$key] = " AND e.{$key} IN ({$itemSet}) ";
        	}
        }
		$where_clause_1 = '';
		$where_clause_2 = '';
        $add_columns='';
	    $add_groupby='';
        $cumulativeMode=0;
       	$group_by = '';
        
		// getNoExecutions: 1 -> if testcase/version_id has not been executed return anyway
		//                       standard return structure.
		//                  0 -> default
		//
		// groupByBuild: 0 -> default, get last execution on ANY BUILD, then for a testcase/version_id
		//                    only a record will be present on return struture.
		//                    GROUP BY must be done ONLY BY tcversion_id
		//                  
		//               1 -> get last execution on EACH BUILD.
		//                    GROUP BY must be done BY tcversion_id,build_id
		//   
		$localOptions=array('getNoExecutions' => 0, 'groupByBuild' => 0);
        if(!is_null($options) && is_array($options))
        {
        	$localOptions=array_merge($localOptions,$options);		
        }
		if( is_array($id) )
		{
			$tcid_list = implode(",",$id);
			$where_clause = " WHERE NHA.parent_id IN ({$tcid_list}) ";
		}
		else
		{
			$where_clause = " WHERE NHA.parent_id = {$id} ";
		}

		if( is_array($version_id) )
		{
		    $versionid_list = implode(",",$version_id);
		    $where_clause_1 = $where_clause . " AND NHA.id IN ({$versionid_list}) ";
		    $where_clause_2 = $where_clause . " AND tcversions.id IN ({$versionid_list}) ";
	
		}
		else
		{
			if($version_id != self::ALL_VERSIONS)
			{
				$where_clause_1 = $where_clause . " AND NHA.id = {$version_id} ";
				$where_clause_2 = $where_clause . " AND tcversions.id = {$version_id} ";
			}
		}
		
		// This logic (is mine - franciscom) must be detailed better!!!!!
		$group_by = ' GROUP BY tcversion_id ';
        $add_fields = ', e.tcversion_id AS tcversion_id';
        if( $localOptions['groupByBuild'] )
        {
        	$add_fields .= ', e.build_id';
	        $group_by .= ', e.build_id';
            $cumulativeMode = 1;
	    	
	    	// Hummm!!! I do not understand why this can be needed
	    	$where_clause_1 = $where_clause;
	    	$where_clause_2 = $where_clause;
        }

		// $group_by .= $localOptions['groupByBuild'] ? $add_groupby : ''; 
		// $group_by = $set_group_by ? ' GROUP BY tcversion_id ' : '';
		// $group_by = ($group_by == '' && $add_groupby != '') ? ' GROUP BY ' : $group_by;  
		
		// we may be need to remove tcversion filter ($set_group_by==false)
		// $add_field = $set_group_by ? ', e.tcversion_id AS tcversion_id' : '';
        // $add_field = $localOptions['groupByBuild'] ? '' : ', e.tcversion_id AS tcversion_id';
	    // $where_clause_1 = $localOptions['groupByBuild'] ? $where_clause :  $where_clause_1;
	    // $where_clause_2 = $localOptions['groupByBuild'] ? $where_clause : $where_clause_2;
	    
      	// get list of max exec id, to be used filter in next query
      	// Here we can get:
      	// a) one record for each tcversion_id (ignoring build)
      	// b) one record for each tcversion_id,build
      	//
	  	$sql="/* $debugMsg */ " . 
	  	     " SELECT COALESCE(MAX(e.id),0) AS execution_id {$add_fields}" .
	  		 " FROM {$this->tables['nodes_hierarchy']} NHA " .
	  	     " JOIN {$this->tables['executions']} e ON NHA.id = e.tcversion_id AND e.testplan_id = {$tplan_id} " .
	  	     " {$filterBy['build_id']} {$filterBy['platform_id']}" .
	  	     " AND e.status IS NOT NULL " .
	  	     " $where_clause_1 {$group_by}";
	     
      	// 20090716 - order of columns changed
	  	$recordset = $this->db->fetchColumnsIntoMap($sql,'execution_id','tcversion_id');
	  	$and_exec_id='';
	  	if( !is_null($recordset) && count($recordset) > 0)
	  	{
	  		$the_list = implode(",", array_keys($recordset));
	  		if($the_list != '')
	  		{
	  			if( count($recordset) > 1 )
	  			{
	  				$and_exec_id = " AND e.id IN ($the_list) ";
	  			}
	  			else
	  			{
	  				$and_exec_id = " AND e.id = $the_list ";
	  			}
	  		}
	  	}
	  	
	  	$executions_join=" JOIN {$this->tables['executions']} e ON NHA.id = e.tcversion_id " .
	  	                 " AND e.testplan_id = {$tplan_id} {$and_exec_id} {$filterBy['build_id']} " .
	  	                 " {$filterBy['platform_id']} ";
	                   
	  if( $localOptions['getNoExecutions'] )
	  {
	     $executions_join = " LEFT OUTER " . $executions_join;
	  }
	  else
	  {
	  	 // @TODO understand if this condition is really needed - 20090716 - franciscom
	     $executions_join .= " AND e.status IS NOT NULL ";
	  }
	
	  // 20090517 - to manage deleted users i need to change:
	  //            users.id AS tester_id => e.tester_id AS tester_id
	  // 20090214 - franciscom - we need tcversions.execution_type and executions.execution_type
	  // 20090208 - franciscom
	  //            found bug due to use of tcversions.*, because field execution_type
	  //            exist on both execution and tcversion table.
	  //            At least with Postgres tcversions.execution_type was used always
	  //
	  // 20080103 - franciscom - added execution_type in recordset
	  // 20060921 - franciscom -
	  // added NHB.parent_id  to get same order as in the navigator tree
	  //
	  $sql= "/* $debugMsg */ SELECT e.id AS execution_id, " .
   			" COALESCE(e.status,'{$status_not_run}') AS status, " .
	        " e.execution_type AS execution_run_type," .
	        " NHB.name,NHA.parent_id AS testcase_id, NHB.parent_id AS tsuite_id," .
	        " tcversions.id,tcversions.tc_external_id,tcversions.version,tcversions.summary," .
	        " tcversions.preconditions," .
	        " tcversions.steps,tcversions.expected_results,tcversions.importance,tcversions.author_id," .
	        " tcversions.creation_ts,tcversions.updater_id,tcversions.modification_ts,tcversions.active," .
	        " tcversions.is_open,tcversions.execution_type," .
	        " users.login AS tester_login,users.first AS tester_first_name," .
			" users.last AS tester_last_name, e.tester_id AS tester_id," .
			" e.notes AS execution_notes, e.execution_ts, e.build_id,e.tcversion_number," .
			" builds.name AS build_name, builds.active AS build_is_active, builds.is_open AS build_is_open," .
	        " e.platform_id,p.name AS platform_name" .
		    " FROM {$this->tables['nodes_hierarchy']} NHA" .
	        " JOIN {$this->tables['nodes_hierarchy']} NHB ON NHA.parent_id = NHB.id" .
	        " JOIN {$this->tables['tcversions']} tcversions ON NHA.id = tcversions.id" .
	        " {$executions_join}" .
	        " LEFT OUTER JOIN {$this->tables['builds']} builds ON builds.id = e.build_id" .
	        "                 AND builds.testplan_id = {$tplan_id}" .
	        " LEFT OUTER JOIN {$this->tables['users']} users ON users.id = e.tester_id " .
   	        " LEFT OUTER JOIN {$this->tables['platforms']} p ON p.id = e.platform_id" .
	        " $where_clause_2" .
	        " ORDER BY NHB.parent_id ASC, NHA.node_order ASC, NHA.parent_id ASC, execution_id DESC";
      
		$recordset = $this->db->fetchRowsIntoMap($sql,'id',$cumulativeMode);
	  
	  	return($recordset ? $recordset : null);
	}
	
	
	/*
	  function: exportTestCaseDataToXML
	
	  args :
	
	  returns:
	
	  rev: 20090204 - franciscom - added export of node_order
	       20080206 - franciscom - added externalid
	
	*/
	function exportTestCaseDataToXML($tcase_id,$tcversion_id,$tproject_id=null,
	                                 $bNoXMLHeader = false,$optExport = array())
	{
	  static $reqMgr=null; 
	  if( is_null($reqMgr) )
	  {
	      $reqMgr = new requirement_mgr($this->db);      
	  }
	
		$tc_data = $this->get_by_id($tcase_id,$tcversion_id);
		if (!$tproject_id)
		{
			$tproject_id = $this->getTestProjectFromTestCase($tcase_id);
		}
	
		$cfMap = $this->get_linked_cfields_at_design($tcase_id,null,null,$tproject_id);
		
		if( !is_null($cfMap) && count($cfMap) > 0 )
		{
	      	$cfRootElem = "<custom_fields>{{XMLCODE}}</custom_fields>";
		    $cfElemTemplate = "\t" . "<custom_field>\n" .
		                             "\t<name><![CDATA[||NAME||]]></name>\n" .
		    	                       "\t<value><![CDATA[||VALUE||\n]]></value>\n</custom_field>\n";
		    $cfDecode = array ("||NAME||" => "name","||VALUE||" => "value");
		    $tc_data[0]['xmlcustomfields'] = exportDataToXML($cfMap,$cfRootElem,$cfElemTemplate,$cfDecode,true);
		} 
		
		if ($optExport['KEYWORDS'])
		{
			$keywords = $this->getKeywords($tcase_id);
			if ($keywords);
			{
				//SCHLUNDUS: should be refactored
				$xmlKW = exportKeywordDataToXML($keywords,true);
				$tc_data[0]['xmlkeywords'] = $xmlKW;
			}
		}
		
		// BUGID - requirements
	  $requirements = $reqMgr->get_all_for_tcase($tcase_id);
	  if( !is_null($requirements) && count($requirements) > 0 )
	  {
	  	$reqRootElem = "\t<requirements>\n{{XMLCODE}}\t</requirements>\n";
		$reqElemTemplate = "\t\t<requirement>\n" .
		                       "\t\t\t<req_spec_title><![CDATA[||REQ_SPEC_TITLE||]]></req_spec_title>\n" .
		                       "\t\t\t<doc_id><![CDATA[||REQ_DOC_ID||]]></doc_id>\n" .
		    	                 "\t\t\t<title><![CDATA[||REQ_TITLE||]]></title>\n" .
		    	                 "\t\t</requirement>\n";
		    	                 
		    $reqDecode = array ("||REQ_SPEC_TITLE||" => "req_spec_title",
		                        "||REQ_DOC_ID||" => "req_doc_id","||REQ_TITLE||" => "title");
		    $tc_data[0]['xmlrequirements'] = exportDataToXML($requirements,$reqRootElem,$reqElemTemplate,$reqDecode,true);
	  }
		
		$rootElem = "{{XMLCODE}}";
		if (isset($optExport['ROOTELEM']))
		{
			$rootElem = $optExport['ROOTELEM'];
		}
		$elemTpl = "\n".'<testcase internalid="{{TESTCASE_ID}}" name="{{NAME}}">' . "\n" .
				   "\t<node_order><![CDATA[||NODE_ORDER||]]></node_order>\n" .
				   "\t<externalid><![CDATA[||EXTERNALID||]]></externalid>\n" .
		           "\t<summary><![CDATA[||SUMMARY||]]></summary>\n" .
		           "\t<preconditions><![CDATA[||PRECONDITIONS||]]></preconditions>\n" .
		           "\t<steps><![CDATA[||STEPS||]]></steps>\n" .
		           "\t<expectedresults><![CDATA[||RESULTS||]]></expectedresults>\n" .
		           "||KEYWORDS||||CUSTOMFIELDS||||REQUIREMENTS||</testcase>\n";
	
	    // ||yyy||-> tags,  {{xxx}} -> attribute 
		$info = array("{{TESTCASE_ID}}" => "testcase_id",
					  "{{NAME}}" => "name",
					  "||NODE_ORDER||" => "node_order",
					  "||EXTERNALID||" => "tc_external_id",
					  "||SUMMARY||" => "summary",
					  "||PRECONDITIONS||" => "preconditions",
					  "||STEPS||" => "steps",
					  "||RESULTS||" => "expected_results",
				      "||KEYWORDS||" => "xmlkeywords",
					  "||CUSTOMFIELDS||" => "xmlcustomfields",
					  "||REQUIREMENTS||" => "xmlrequirements"
							);
		$xmlTC = exportDataToXML($tc_data,$rootElem,$elemTpl,$info,$bNoXMLHeader);
		return $xmlTC;
	}
	
	
	/*
	  function: get_version_exec_assignment
	            get information about user that has been assigned
	            test case version for execution on a testplan
	
	  args : tcversion_id: test case version id
	         tplan_id
	
	
	
	  returns: map
	           key: tcversion_id
	           value: map with following keys:
	 				          tcversion_id
					          feature_id: identifies row on table testplan_tcversions.
	
	
					          user_id:  user that has reponsibility to execute this tcversion_id.
					                    null/empty string is nodoby has been assigned
	
					          type    type of assignment.
					                  1 -> testcase_execution.
					                  See assignment_types tables for updated information
					                  about other types of assignemt available.
	
					          status  assignment status
					                  See assignment_status tables for updated information.
					                  1 -> open
	                          2 -> closed
	                          3 -> completed
	                          4 -> todo_urgent
	                          5 -> todo
	
					          assigner_id: who has assigned execution to user_id.
	
	
	
	*/
	// @TODO must be refactored due to platform feature
	function get_version_exec_assignment($tcversion_id,$tplan_id)
	{
		$sql =  "SELECT T.tcversion_id AS tcversion_id,T.id AS feature_id," .
				"       UA.user_id,UA.type,UA.status,UA.assigner_id ".
				" FROM {$this->tables['testplan_tcversions']}  T " .
				" LEFT OUTER JOIN {$this->tables['user_assignments']}  UA ON UA.feature_id = T.id " .
				" WHERE T.testplan_id={$tplan_id} " .
				" AND   T.tcversion_id = {$tcversion_id} " .
				" AND   (UA.type=" . $this->assignment_types['testcase_execution']['id'] .
				"        OR UA.type IS NULL) ";
	
	
		$recordset = $this->db->fetchRowsIntoMap($sql,'tcversion_id');
		return $recordset;
	}
	
	
	/**
	 * get_assigned_to_user()
	 * Given a user and a tesplan id, get all test case version id linked to
	 * test plan, that has been assigned for execution to user.
	 *
	 * @param int user_id
	 *
	 * @param mixed tproject_id list of test project id to search.  
	 *                          int or array
	 *
	 * @param array [tplan_id] list of test plan id to search.  
	 *                         null => all test plans
	 *
	 * @param object [options] options->mode='full_path'
	 *                         testcase name full path will be returned
	 *                         Only available when acces_keys ='testplan_testcase'
	 *                        
	 *                         options->access_keys
	 *                         possible values: 'testplan_testcase','testcase_testplan'
	 *                         changes access key in result map of maps.
	 *                         if not defined or null -> 'testplan_testcase' 
	 *
	 * @return map key: (test plan id or test case id depending on options->access_keys,
	 *                   default is test plan).
	 *
	 *             value: map key: (test case id or test plan id depending on options->access_keys,
	 *                              default is test case). 
	 *                        value:
	 *                         
	 * @since 20090131 - franciscom
	 *
	 */
	function get_assigned_to_user($user_id,$tproject_id,$tplan_id=null,$options=null)
	{
	    $filters=null;
	    $has_options=!is_null($options);
	    $access_key=array('testplan_id','testcase_id');
	    
	    
	    $sql="SELECT testprojects.id as testproject_id,TPTCV.testplan_id,TPTCV.tcversion_id, " .
	         "TCV.version,TCV.tc_external_id, NHB.id AS testcase_id, NHB.name, testprojects.prefix, " .
	         "UA.creation_ts ,UA.deadline_ts " .
	         "FROM {$this->tables['user_assignments']}  UA, {$this->tables['testplan_tcversions']}  TPTCV, " .
	         "{$this->tables['tcversions']}  TCV, {$this->tables['nodes_hierarchy']} NHA," .
	         " {$this->tables['nodes_hierarchy']} NHB, " .
	         "{$this->tables['nodes_hierarchy']} NHC, {$this->tables['testprojects']} testprojects " .
	         "WHERE UA.type={$this->assignment_types['testcase_execution']['id']} " .
	         "AND UA.user_id = {$user_id} " .
	         "AND testprojects.id IN (" . implode(',', array($tproject_id)) .") " .
	         "AND UA.feature_id=TPTCV.id AND TPTCV.tcversion_id=TCV.id  " .
	         "AND NHC.id=TPTCV.testplan_id AND NHC.parent_id=testprojects.id " .
	         "AND NHB.id=NHA.parent_id AND NHA.id=TCV.id " ;
	         
	         
	    if( !is_null($tplan_id) )
	    {
	        $filters=" AND TPTCV.testplan_id IN (" . implode(',',$tplan_id) . ") "; 
	    }     
	    $sql .= $filters;
	    
	    if( $has_options && isset($options->access_keys) )
	    {
	        switch($options->access_keys)
	        {
	            case 'testplan_testcase':
	            break;
	            
	            case 'testcase_testplan':   
	                $access_key=array('testcase_id','testplan_id');
	            break;
	        }
	    }
	    
	    $rs=$this->db->fetchMapRowsIntoMap($sql,$access_key[0],$access_key[1]);
	
	    if( $has_options && !is_null($rs))
	    {
	        if( isset($options->mode) )
	        {
	            switch($options->mode)
	            {
	                case 'full_path':
	                    if( !isset($options->access_keys) || 
	                        (is_null($options->access_keys) || $options->access_keys='testplan_testcase') )
	                    { 
	                        $tcaseSet=null;
	                        foreach($rs as $container)
	                        {
	                            foreach($container as $item)
	                            {
	                                if(!isset($tcaseSet[$item['testcase_id']]))
	                                {
	                                    $tcaseSet[$item['testcase_id']]=$item['testcase_id'];  
	                                }  
	                            }    
	                        }
	                        $path_info = $this->tree_manager->get_full_path_verbose($tcaseSet);
	                        // Remove test project piece and convert to string
	                        $flat_path=null;
	                        foreach($path_info as $tcase_id => $pieces)
	                        {
	                            unset($pieces[0]);
	                            $flat_path[$tcase_id]=implode('/',$pieces) . '/';  
	                        }
	
	                        $container_keys=array_keys($rs);
	                        foreach($container_keys as $idx)
	                        {
	                            foreach($rs[$idx] as $jdx => $item)
	                            {
	                                $rs[$idx][$jdx]['tcase_full_path']=$flat_path[$item['testcase_id']];
	                            }    
	                        }
	                    }
	                break;  
	            }  
	        }
	    }
	    return $rs;
	}
	
	
	
	/*
	  function: update_active_status
	
	  args : id: testcase id
	         tcversion_id
	         active_status: 1 -> active / 0 -> inactive
	
	  returns: 1 -> everything ok.
	           0 -> some error
	
	*/
	function update_active_status($id,$tcversion_id,$active_status)
	{
		$sql = " UPDATE {$this->tables['tcversions']} tcversions SET active={$active_status}" .
			   " WHERE tcversions.id = {$tcversion_id}";
	
		$result = $this->db->exec_query($sql);
	
		return $result ? 1: 0;
	}
	
	/*
	  function: update_order
	
	  args : id: testcase id
	         order
	
	  returns: -
	
	*/
	function update_order($id,$order)
	{
	  $result=$this->tree_manager->change_order_bulk(array($order => $id));  	
		return $result ? 1: 0;
	}
	
	
	/*
	  function: update_external_id
	
	  args : id: testcase id
	         external_id
	
	  returns: -
	
	*/
	function update_external_id($id,$external_id)
	{
	  $sql="UPDATE {$this->tables['tcversions']} " .
	       "SET tc_external_id={$external_id} " .
	       "WHERE id IN ( SELECT id FROM {$this->tables['nodes_hierarchy']} WHERE parent_id={$id} ) ";
	      
	  $result=$this->db->exec_query($sql);
		return $result ? 1: 0;
	}
	

	/** 
	 * Copy attachments from source testcase to target testcase
	 * 
	 * @ TODO schlundus, copy attachments should be a repository functionality 
	 **/
	function copy_attachments($source_id,$target_id)
	{
		$table_name = $this->attachmentTableName;
		$f_parts = null;
		$destFPath = null;
		$mangled_fname = '';
		$status_ok = false;
		$repo_type = config_get('repositoryType');
		$repo_path = config_get('repositoryPath') .  DIRECTORY_SEPARATOR;
		
		$attachments = $this->getAttachmentInfos($source_id);
		if(count($attachments) > 0)
		{
			foreach($attachments as $key => $value)
			{
				$file_contents = null;
				$f_parts = explode(DIRECTORY_SEPARATOR,$value['file_path']);
				$mangled_fname = $f_parts[count($f_parts)-1];
				
				if ($repo_type == TL_REPOSITORY_TYPE_FS)
				{
					$destFPath = $this->attachmentRepository->buildRepositoryFilePath($mangled_fname,$table_name,$target_id);
					$status_ok = copy($repo_path . $value['file_path'],$destFPath);
				}
				else
				{
					$file_contents = $this->attachmentRepository->getAttachmentContentFromDB($value['id']);
					$status_ok = sizeof($file_contents);
				}
				if($status_ok)
				{
					$attachment = new tlAttachment();
					$attachment->create($target_id,$table_name,$value['file_name'],
						                $destFPath,$file_contents,$value['file_type'],
						                $value['file_size'],$value['title']);
					$attachment->writeToDb($this->db);
				}
			}
		}
	}

	
	/**
	 * copyReqAssignmentTo
	 * copy requirement assignments for $from test case id to $to test case id 
	 *
	 *
	 */
	function copyReqAssignmentTo($from,$to)
	{
		static $req_mgr;
		if( is_null($req_mgr) )
		{
			$req_mgr=new requirement_mgr($this->db);
		}
		
		$requirements=$req_mgr->get_all_for_tcase($from);
		if( !is_null($requirements) )
		{
			$loop2do=count($requirements);
			for($idx=0; $idx < $loop2do; $idx++)
			{
				$items[$idx]=$requirements[$idx]['id'];
			}
			$req_mgr->assign_to_tcase($items,$to); 
		} 
	}
	
	/**
	 * 
	 *
	 */
	private function getShowViewerActions($mode)
	{
	    // fine grain control of operations
	    $viewerActions= new stdClass();
	    $viewerActions->edit='no';
	    $viewerActions->delete_testcase='no';
	    $viewerActions->delete_version='no';
	    $viewerActions->deactivate='no';
	    $viewerActions->create_new_version='no';
	    $viewerActions->export='no';
	    $viewerActions->move='no';
	    $viewerActions->copy='no';
	    $viewerActions->add2tplan='no';
	
	    switch ($mode) 
	    {
	        case 'editOnExec':
	            $viewerActions->edit='yes';
	            $viewerActions->create_new_version='yes';    
	        break;
	
	        case 'editDisabled':
	        break;
	
	        default:
	        foreach($viewerActions as $key => $value)
	        {
	            $viewerActions->$key='yes';        
	        }
	        break;
	    }
	    return $viewerActions;     
	}
	
	/**
     * given an executio id delete execution and related data.
     *
     */
    function deleteExecution($executionID)
    {
        $whereClause = " WHERE execution_id = {$executionID} "; 
		$sql = array("DELETE FROM {$this->tables['execution_bugs']} {$whereClause} ", 
		             "DELETE FROM {$this->tables['cfield_execution_values']} {$whereClause} ",
		             "DELETE FROM {$this->tables['executions']} WHERE id = {$executionID}" );
	
		foreach ($sql as $the_stm)
		{
			$result = $this->db->exec_query($the_stm);
			if (!$result)
			{
				break;
			}
		}
    }
	
	
	
	
	// ---------------------------------------------------------------------------------------
	// Custom field related functions
	// ---------------------------------------------------------------------------------------
	
	/*
	  function: get_linked_cfields_at_design
	            Get all linked custom fields that must be available at design time.
	            Remember that custom fields are defined at system wide level, and
	            has to be linked to a testproject, in order to be used.
	
	  args: id: testcase id
	        [parent_id]: node id of parent testsuite of testcase.
	                     need to understand to which testproject the testcase belongs.
	                     this information is vital, to get the linked custom fields.
	                     Presence /absence of this value changes starting point
	                     on procedure to build tree path to get testproject id.
	
	                     null -> use testcase_id as starting point.
	                     !is_null -> use this value as starting point.
	
	        [$filters]:default: null
	                    
	                   map with keys:
	
	                   [show_on_execution]: default: null
	                                        1 -> filter on field show_on_execution=1
	                                             include ONLY custom fields that can be viewed
	                                             while user is execution testcases.
	                   
	                                        0 or null -> don't filter
	
	                   [show_on_testplan_design]: default: null
	                                              1 -> filter on field show_on_testplan_design=1
	                                                   include ONLY custom fields that can be viewed
	                                                   while user is designing test plan.
	                                              
	                                              0 or null -> don't filter
	
	                   [location] new concept used to define on what location on screen
			                      custom field will be designed.
			                      Initally used with CF available for Test cases, to
			                      implement pre-requisites.
                                  null => no filtering
	
	
	                   More comments/instructions on cfield_mgr->get_linked_cfields_at_design()
	                   
	  returns: map/hash
	           key: custom field id
	           value: map with custom field definition and value assigned for choosen testcase,
	                  with following keys:
	
	            			id: custom field id
	            			name
	            			label
	            			type: custom field type
	            			possible_values: for custom field
	            			default_value
	            			valid_regexp
	            			length_min
	            			length_max
	            			show_on_design
	            			enable_on_design
	            			show_on_execution
	            			enable_on_execution
	            			display_order
	            			value: value assigned to custom field for this testcase
	            			       null if for this testcase custom field was never edited.
	
	            			node_id: testcase id
	            			         null if for this testcase, custom field was never edited.
	
	
	  rev :
	       20070302 - check for $id not null, is not enough, need to check is > 0
	
	*/
	function get_linked_cfields_at_design($id,$parent_id=null,$filters=null,$tproject_id = null)
	{
		if (!$tproject_id)
		{
			$tproject_id = $this->getTestProjectFromTestCase($id,$parent_id);
		}
		$cf_map = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,
		                                                          self::ENABLED,$filters,'testcase',$id);
		return $cf_map;
	}
	
	
	
	/*
	  function: getTestProjectFromTestCase
	
	  args: id: testcase id
	        [parent_id]: node id of parent testsuite of testcase.
	                     need to understand to which testproject the testcase belongs.
	                     this information is vital, to get the linked custom fields.
	                     Presence /absence of this value changes starting point
	                     on procedure to build tree path to get testproject id.
	
	                     null -> use testcase_id as starting point.
	                     !is_null -> use this value as starting point.
	*/
	function getTestProjectFromTestCase($id,$parent_id)
	{
		$the_path = $this->tree_manager->get_path( (!is_null($id) && $id > 0) ? $id : $parent_id);
		$path_len = count($the_path);
		$tproject_id = ($path_len > 0)? $the_path[0]['parent_id'] : $parent_id;
		
		return $tproject_id;
	}

		
	/*
	  function: html_table_of_custom_field_inputs
	            Return html code, implementing a table with custom fields labels
	            and html inputs, for choosen testcase.
	            Used to manage user actions on custom fields values.
	
	
	  args: $id: IMPORTANT: 
	             we can receive 0 in this arguments and THERE IS NOT A problem
	             if parent_id arguments has a value.
	             Because argument id or parent_id are used to understand what is
	             testproject where test case belong, in order to get custom fields
	             assigned/linked to test project. 
	                     
	                
	        [parent_id]: node id of parent testsuite of testcase.
	                     need to undertad to which testproject the testcase belongs.
	                     this information is vital, to get the linked custom fields.
	                     Presence /absence of this value changes starting point
	                     on procedure to build tree path to get testproject id.
	
	                     null -> use testcase_id as starting point.
	                     !is_null -> use this value as starting point.
	
	        [$scope]: 'design' -> use custom fields that can be used at design time (specification)
	                  'execution' -> use custom fields that can be used at execution time.
	
	        [$name_suffix]: must start with '_' (underscore).
	                        Used when we display in a page several items
	                        example:
	                                during test case execution, several test cases
	                                during testplan design (assign test case to testplan).
	                        
	                        that have the same custom fields.
	                        In this kind of situation we can use the item id as name suffix.
	
	        [link_id]: default null
	                   scope='testplan_design'.
	                   link_id=testplan_tcversions.id this value is also part of key
	                   to access CF values on new table that hold values assigned
	                   to CF used on the 'tesplan_design' scope.
	                   
	                   scope='execution'
	                   link_id=execution id
	                   
	
	        [tplan_id]: default null
	                    used when scope='execution' and YOU NEED to get input with value
	                    related to link_id
	
	        [tproject_id]: default null
	                       used to speedup feature when this value is available.
	
	
	  returns: html string
	  
	  rev: 20080811 - franciscom - BUGID 1650 (REQ)
	
	*/
	function html_table_of_custom_field_inputs($id,$parent_id=null,$scope='design',$name_suffix='',
	                                           $link_id=null,$tplan_id=null,
	                                           $tproject_id = null,$filters=null)
	{
		$cf_smarty = '';
	
	  // BUGID 1650
	  $cf_scope=trim($scope);
	  $method_name='get_linked_cfields_at_' . $cf_scope;
	  
	  switch($cf_scope)
	  {
	      case 'testplan_design':
	          $cf_map = $this->$method_name($id,$parent_id,null,$link_id,null,$tproject_id);    
	      break;
	
	      case 'design':
	          // added $filters
	      		$cf_map = $this->$method_name($id,$parent_id,$filters,$tproject_id);    
	      break;
	      	
	      case 'execution':
	          $cf_map = $this->$method_name($id,$parent_id,null,$link_id,$tplan_id,$tproject_id);    
	      break;
	        
	  }
	  
		if(!is_null($cf_map))
		{
			$cf_smarty = "<table>";
			foreach($cf_map as $cf_id => $cf_info)
			{
	            // true => do not create input in audit log
	            $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label'],null,true));
	
				// Want to give an html id to <td> used as labelHolder, to use it in Javascript
				// logic to validate CF content
				$cf_html_string = $this->cfield_mgr->string_custom_field_input($cf_info,$name_suffix);
				
				// extract input html id
				$dummy = explode(' ', strstr($cf_html_string,'id="custom_field_'));
	     	    $td_label_id = str_replace('id="', 'id="label_', $dummy[0]);
				$cf_smarty .= "<tr><td class=\"labelHolder\" {$td_label_id}>" . htmlspecialchars($label) . 
				              ":</td><td>{$cf_html_string}</td></tr>\n";
			}
			$cf_smarty .= "</table>";
	
		}
	
		return $cf_smarty;
	}
	
	
	/*
	  function: html_table_of_custom_field_values
	            Return html code, implementing a table with custom fields labels
	            and custom fields values, for choosen testcase.
	            You can think of this function as some sort of read only version
	            of html_table_of_custom_field_inputs.
	
	
	  args: $id: Very Important!!!
	             scope='design'    -> this is a testcase id
	             scope='execution' -> this is a testcase VERSION id
	             scope='testplan_design' -> this is a testcase VERSION id 
	              
	        [$scope]: 'design' -> use custom fields that can be used at design time (specification)
	                  'execution' -> use custom fields that can be used at execution time.
	                  'testplan_design' 
	
	        [$filters]:default: null
	                    
	                   map with keys:
	
	                   [show_on_execution]: default: null
	                                        1 -> filter on field show_on_execution=1
	                                             include ONLY custom fields that can be viewed
	                                             while user is execution testcases.
	                   
	                                        0 or null -> don't filter
	
	                   [show_on_testplan_design]: default: null
	                                              1 -> filter on field show_on_testplan_design=1
	                                                   include ONLY custom fields that can be viewed
	                                                   while user is designing test plan.
	                                              
	                                              0 or null -> don't filter
	
	                   [location] new concept used to define on what location on screen
			                      custom field will be designed.
			                      Initally used with CF available for Test cases, to
			                      implement pre-requisites.
                                  null => no filtering
	                   
	                   More comments/instructions on cfield_mgr->get_linked_cfields_at_design()
	                              
	
	        [$execution_id]: null -> get values for all executions availables for testcase
	                         !is_null -> only get values or this execution_id
	
	        [$testplan_id]: null -> get values for any tesplan to with testcase is linked
	                        !is_null -> get values only for this testplan.
	
	        [$tproject_id]
	        [$formatOptions]
	        [$link_id]: default null
	                   used only when scope='testplan_design'.
	                   link_id=testplan_tcversions.id this value is also part of key
	                   to access CF values on new table that hold values assigned
	                   to CF used on the 'tesplan_design' scope.
	
	
	  returns: html string
	
	*/
	function html_table_of_custom_field_values($id,$scope='design',$filters=null,$execution_id=null,
	                                           $testplan_id=null,$tproject_id = null,
	                                           $formatOptions=null,$link_id=null)
	{
	    $td_style='class="labelHolder"' ;
	    $add_table=true;
	    $table_style='';
	    if( !is_null($formatOptions) )
	    {
	        $td_style=isset($formatOptions['td_css_style']) ? $formatOptions['td_css_style'] : $td_style;
	        $add_table=isset($formatOptions['add_table']) ? $formatOptions['add_table'] : true;
	        $table_style=isset($formatOptions['table_css_style']) ? $formatOptions['table_css_style'] : $table_style;
	    } 
		
		$cf_smarty = '';
		
		$location=null; // no filter
        $filterKey='location';
        if( isset($filters[$filterKey]) && !is_null($filters[$filterKey]) )
        {
            $location = $filters[$filterKey];
        }

	    switch($scope)
	    {
	        case 'design':
	            $cf_map = $this->get_linked_cfields_at_design($id,null,$filters,$tproject_id);
	        break;
	    
	        case 'testplan_design':
	            $cf_map = $this->get_linked_cfields_at_testplan_design($id,null,$filters,$link_id,
	                                                                   $testplan_id,$tproject_id);
	        break;
	    
	        case 'execution':
	            $cf_map = $this->get_linked_cfields_at_execution($id,null,$filters,$execution_id,
	                                                             $testplan_id,$tproject_id,$location);
	        break;
	    }   
	       
		if(!is_null($cf_map))
		{
			foreach($cf_map as $cf_id => $cf_info)
			{
				// if user has assigned a value, then node_id is not null
				if(isset($cf_info['node_id']) )
				{
	                // true => do not create input in audit log
	                $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label'],null,true));
	
					$cf_smarty .= "<tr><td {$td_style}> " .
									htmlspecialchars($label) . ":</td><td>" .
									$this->cfield_mgr->string_custom_field_value($cf_info,$id) .
									"</td></tr>\n";
				}
			}
	
			if((trim($cf_smarty) != "") && $add_table)
			{
				$cf_smarty = "<table {$table_style}>" . $cf_smarty . "</table>";
			}
		}
		return $cf_smarty;
	} // function end
	
	
	/*
	  function: get_linked_cfields_at_execution
	
	
	  args: $id
	        [$parent_id]
	        [$show_on_execution]: default: null
	                              1 -> filter on field show_on_execution=1
	                              0 or null -> don't filter
	                              //@TODO - 20090718 - franciscom 
	                              // this filter has any sense ? 
	                              // review and remove if needed
	
	
	        [$execution_id]: null -> get values for all executions availables for testcase
	                         !is_null -> only get values or this execution_id
	
	        [$testplan_id]: null -> get values for any tesplan to with testcase is linked
	                        !is_null -> get values only for this testplan.
	
	        [$tproject_id]:
	
	  returns: hash
	           key: custom field id
	           value: map with custom field definition, with keys:
	
					          id: custom field id
	          				name
	          				label
	          				type
	          				possible_values
	          				default_value
	          				valid_regexp
	          				length_min
	          				length_max
	          				show_on_design
	          				enable_on_design
	          				show_on_execution
	          				enable_on_execution
	          				display_order
	
	
	*/
	function get_linked_cfields_at_execution($id,$parent_id=null,$show_on_execution=null,
	                                         $execution_id=null,$testplan_id=null,
	                                         $tproject_id = null, $location=null)
	{
		$thisMethod=__FUNCTION__;
		if (!$tproject_id)
		{
		    $tproject_id = $this->getTestProjectFromTestCase($id,$parent_id);
		}
			
		// VERY IMPORTANT WARNING:
		// I'm setting node type to test case, but $id is the tcversion_id, because
		// execution data is related to tcversion NO testcase
		//
		$cf_map = $this->cfield_mgr->$thisMethod($tproject_id,self::ENABLED,'testcase',
		                                         $id,$execution_id,$testplan_id,'id',
		                                         $location);
		return $cf_map;
	}
	
	
	/*
	  function: copy_cfields_design_values
	            Get all cfields linked to any testcase of this testproject
	            with the values presents for $from_id, testcase we are using as
	            source for our copy.
	
	  args: from_id: source testcase id
	        to_id: target testcase id
	
	  returns: -
	
	*/
	function copy_cfields_design_values($from_id,$to_id)
	{
	  // Get all cfields linked to any testcase of this test project
	  // with the values presents for $from_id, testcase we are using as
	  // source for our copy
	  $cfmap_from=$this->get_linked_cfields_at_design($from_id);
	
	  $cfield=null;
	  if( !is_null($cfmap_from) )
	  {
	    foreach($cfmap_from as $key => $value)
	    {
	      $cfield[$key]=array("type_id"  => $value['type'],
	                          "cf_value" => $value['value']);
	    }
	  }
	  $this->cfield_mgr->design_values_to_db($cfield,$to_id,null,'tcase_copy_cfields');
	}
	
	
	/*
	  function: get_linked_cfields_at_testplan_design
	
	
	  args: $id
	        [$parent_id]
	
	        [$filters]:default: null
	                    
	                   map with keys:
	
	                   [show_on_execution]: default: null
	                                        1 -> filter on field show_on_execution=1
	                                             include ONLY custom fields that can be viewed
	                                             while user is execution testcases.
	                   
	                                        0 or null -> don't filter
	
	                   [show_on_testplan_design]: default: null
	                                              1 -> filter on field show_on_testplan_design=1
	                                                   include ONLY custom fields that can be viewed
	                                                   while user is designing test plan.
	                                              
	                                              0 or null -> don't filter
	
	                   More comments/instructions on cfield_mgr->get_linked_cfields_at_design()
	
	        [$link_id]: 
	
	        [$testplan_id]: null -> get values for any tesplan to with testcase is linked
	                        !is_null -> get values only for this testplan.
	
	  returns: hash
	           key: custom field id
	           value: map with custom field definition, with keys:
	
					          id: custom field id
	          				name
	          				label
	          				type
	          				possible_values
	          				default_value
	          				valid_regexp
	          				length_min
	          				length_max
	          				show_on_design
	          				enable_on_design
	          				show_on_execution
	          				enable_on_execution
	          				display_order
	
	
	*/
	function get_linked_cfields_at_testplan_design($id,$parent_id=null,$filters=null,
	                                               $link_id=null,$testplan_id=null,$tproject_id = null)
	{
		if (!$tproject_id)
		{
		    $tproject_id = $this->getTestProjectFromTestCase($id,$parent_id);
		}	
		
		// Warning:
		// I'm setting node type to test case, but $id is the tcversion_id, because
		// link data is related to tcversion NO testcase
		//
		$cf_map = $this->cfield_mgr->get_linked_cfields_at_testplan_design($tproject_id,self::ENABLED,'testcase',
		                                                                   $id,$link_id,$testplan_id);
		return $cf_map;
	}
	
	
	/**
	 * returns map with key: verbose location (see custom field class $locations
	 *                  value: array with fixed key 'location'
	 *                         value: location code
	 *
	 */
	function buildCFLocationMap()
	{
		$ret = $this->cfield_mgr->buildLocationMap('testcase');
		return $ret;
    }
	
	
	/**
	 * given a set of test cases, will return a map with 
	 * test suites name that form test case path to root test suite.
	 *
	 *                  example:
	 *
	 *                  communication devices [ID 4]
	 *                      |__ Subspace channels [ID 20]
	 *                             |
	 *                             |__ TestCase100
	 *                             |  
	 *                             |__ short range devices [ID 21]
	 *	                                    |__ TestCase1
	 *                                      |__ TestCase2
     *
     * if test case set: TestCase100,TestCase1
     *
     *   4  Communications
     *  20 	Communications/Subspace channels
     *  21 	Communications/Subspace channels/short range devices
     *                
     *                
	 * returns map with key: test suite id
	 *                  value: test suite path to root
	 *
	 *
	 */
	function getPathLayered($tcaseSet)
	{
		$xtree=null;
		foreach($tcaseSet as $item)
    	{
			$path_info = $this->tree_manager->get_path($item); 
    		$testcase = end($path_info);
    		
    		// This check is useful when you have several test cases with same parent test suite
    		if( !isset($xtree[$testcase['parent_id']]['value']) )
    		{
    			$level=0;
				foreach($path_info as $elem)
				{
                    $level++;
					$prefix = isset($xtree[$elem['parent_id']]['value']) ? ($xtree[$elem['parent_id']]['value'] . '/') : '';
					if( $elem['node_table'] == 'testsuites' )
					{
						$xtree[$elem['id']]['value'] = $prefix . $elem['name'];
						$xtree[$elem['id']]['level']=$level;
					}	
				}
			}
		}	
		return $xtree;
	} // getPathLayered($tcaseSet)



    /**
	 * 
 	 *
 	 */
	function getPathTopSuite($tcaseSet)
	{
		$xtmas=null;
		foreach($tcaseSet as $item)
    	{
			$path_info = $this->tree_manager->get_path($item); 
    		$top = current($path_info);
    		$xtmas[$item] = array( 'name' => $top['name'], 'id' => $top['id']);
		}	
		return $xtmas;
	} // getPathTopSuite($tcaseSet)
	
	
	
    /*
	  function: getByPathName
	            pathname format
	            Test Project Name::SuiteName::SuiteName::...::Test case name
	
	  args: $pathname
	  returns: hash
	*/
	function getByPathName($pathName,$pathSeparator='::')
	{
	    $recordset = null;
		$retval=null;
	
        // First get root -> test project name and leaf => test case name	    
	    $parts = explode($pathSeparator,$pathName);
	    $partsQty = count($parts);
	    $tprojectName = $parts[0];
	    $tsuiteName = $parts[$partsQty-2];
	    $tcaseName = end($parts);

	    // get all testcases on test project with this name and parent test suite
        $recordset = $this->get_by_name($tcaseName, $tsuiteName ,$tprojectName);
        if( !is_null($recordset) && count($recordset) > 0 )
        {
        	foreach($recordset as $value)
        	{
  		        $dummy = $this->tree_manager->get_full_path_verbose($value['id']);
                $sx = implode($pathSeparator,current($dummy)) . $pathSeparator . $tcaseName;
                if( strcmp($pathName,$sx ) == 0 )
                {
                	
                	$retval = $value;
                	break;
                }
        	}
	    }
	    return $retval;
	}
	
	
} // end class
?>