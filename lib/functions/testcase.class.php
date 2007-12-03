<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testcase.class.php,v $
 * @version $Revision: 1.72 $
 * @modified $Date: 2007/12/03 10:19:28 $ $Author: franciscom $
 * @author franciscom
 *
 *
 * 20071203 - franciscom - get_last_execution(), added build_is_active, build_is_open         			
 *
 * 20071128 - franciscom - create_tcase_only() added key on ret struct
 * 20071113 - franciscom - added contribution on get_executions()
 * 20071101 - franciscom - import_file_types, export_file_types
 *
 * 20070930 - franciscom - REQ - BUGID 1078 -> show() interface changes 
 *
 * 20070701 - franciscom - create_new_version(), changes in return map.
 * 20070617 - franciscom - added include of users.inc.php                         
 * 20070602 - franciscom - added attachment copy on copy_to() method.
 *                         added attachment delete.
 *                         added remove of custom field values 
 *                         (design and execution) when removing 
 *                         test case or test case version.
 *   
 * 20070525 - franciscom - copy_cfields_design_values()
 * 20070501 - franciscom - added localization of custom field labels
 *
 * 20070302 - franciscom - fixed bug on get_linked_cfields_at_design()
 * 20070222 - franciscom - minor fix html_table_of_custom_field_values()
 * 20070105 - franciscom - changes in copy_to(),get_by_id()
 *
 * 20070104 - franciscom
 * 1. removed wrong method viewer_edit_new();
 * 2. custom field management continues.
 *
 * 20070102 - franciscom - solved bugs on delete, 
 *                         that produce a negative impact on performance.
 * 20061230 - franciscom - custom fields management
 *
 *
 */
require_once( dirname(__FILE__) . '/requirement_mgr.class.php' );
require_once( dirname(__FILE__) . '/assignment_mgr.class.php' );
require_once( dirname(__FILE__) . '/attachments.inc.php' );
require_once( dirname(__FILE__) . '/users.inc.php' );


// $g_tcImportTypes = array("XML" => "XML", "XLS" => "XLS" );
// $g_tcExportTypes = array("XML" => "XML","XLS" => "XLS" );
$g_tcFormatStrings = array ("XML" => lang_get('the_format_tc_xml_import')); 
							
define("TC_ALL_VERSIONS",0);
define("TC_LATEST_VERSION",-1);
define("TC_DEFAULT_ORDER",0);
define("TC_AUTOMATIC_ID",0);
define("TC_COPY_KEYWORDS",0);

class testcase extends tlObjectWithAttachments
{
	var $db;
	var $tree_manager;
	var $node_types_descr_id;
	var $node_types_id_descr;
	var $my_node_type;
  
  var $assignment_mgr;
  var $assignment_types;
  var $assignment_status;

  var $cfield_mgr;

  var $import_file_types = array("XML" => "XML", "XLS" => "XLS" );
  var $export_file_types = array("XML" => "XML");
  

	function testcase(&$db)
	{
		$this->db = &$db;	
		$this->tree_manager = New tree($this->db);
		$this->node_types_descr_id=$this->tree_manager->get_available_node_types();
		$this->node_types_id_descr=array_flip($this->node_types_descr_id);
		$this->my_node_type=$this->node_types_descr_id['testcase'];
		
		$this->assignment_mgr=New assignment_mgr($this->db);
		$this->assignment_types=$this->assignment_mgr->get_available_types(); 
		$this->assignment_status=$this->assignment_mgr->get_available_status();
		
		$this->cfield_mgr = new cfield_mgr($this->db);
		
		tlObjectWithAttachments::__construct($this->db,"nodes_hierarchy");
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




// 20061008 - franciscom - added
//                         [$check_duplicate_name]
//                         [$action_on_duplicate_name]
//
// 20060726 - franciscom - default value changed for optional argument $tc_order
//                         create(), update()
//
// 20060722 - franciscom - interface changes added [$id]
//            TC_AUTOMATIC_ID -> the id will be assigned by dbms
//            x -> this will be the id 
//                 Warning: no check is done before insert => can got error.
//   
// 20060425 - franciscom - - interface changes added $keywords_id
//
function create($parent_id,$name,$summary,$steps,
                $expected_results,$author_id,$keywords_id='',
                $tc_order=TC_DEFAULT_ORDER,$id=TC_AUTOMATIC_ID,
                $check_duplicate_name=0,
                $action_on_duplicate_name='generate_new',
                $execution_type=TESTCASE_EXECUTION_TYPE_MANUAL)
{
	$first_version = 1;
	$status_ok = 1;
	$ret = $this->create_tcase_only($parent_id,$name,$tc_order,$id,
                                  $check_duplicate_name,
                                  $action_on_duplicate_name,$execution_type);
	if($ret['msg'] == 'ok')
	{
		if(strlen(trim($keywords_id)))
		{
			$a_keywords = explode(",",$keywords_id);
			$this->addKeywords($ret['id'],$a_keywords);
		}
	
		$op = $this->create_tcversion($ret['id'],$first_version,$summary,$steps,
		                              $expected_results,$author_id);
		                              
		$ret['msg']=$op['msg'];
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
       $ret['status_ok']
       $ret['msg'] = 'ok';
	     $ret['new_name']
*/
function create_tcase_only($parent_id,$name,$order=TC_DEFAULT_ORDER,$id=TC_AUTOMATIC_ID,
                           $check_duplicate_name=0,
                           $action_on_duplicate_name='generate_new')
{
  $ret['id'] = -1;
  $ret['status_ok'] = 1;
  $ret['msg'] = 'ok';
	$ret['new_name'] = '';

		
 	if ($check_duplicate_name)
	{
    $sql = " SELECT count(*) AS qty FROM nodes_hierarchy " . 
		       " WHERE nodes_hierarchy.name = '" . $this->db->prepare_string($name) . "'" . 
		       " AND node_type_id = {$this->my_node_type} " .
		       " AND nodes_hierarchy.parent_id={$parent_id} "; 
		
		$result = $this->db->exec_query($sql);
		$myrow = $this->db->fetch_array($result);
		if( $myrow['qty'])
		{
			if ($action_on_duplicate_name == 'block')
			{
				$ret['status_ok'] = 0;
				$ret['msg'] = lang_get('testcase_name_already_exists');	
			} 
			else
			{
				$ret['status_ok'] = 1;      
				if ($action_on_duplicate_name == 'generate_new')
				{ 
					$name = config_get('prefix_name_for_copy') . " " . $name ;      
					$ret['new_name'] = $name;
				}
			}
		}       
	}
  
  if( $ret['status_ok'] )
  {
    $tcase_id = $this->tree_manager->new_node($parent_id,
                                               $this->my_node_type,$name,$order,$id);
    $ret['id'] = $tcase_id;
    $ret['msg'] = 'ok';
  }
  
  return $ret;
}

/*
  function: 

  args:
  
  returns: 

*/
function create_tcversion($id,$version,$summary,$steps,
                          $expected_results,$author_id,
                          $execution_type=TESTCASE_EXECUTION_TYPE_MANUAL)
{
	// get a new id
	$tcase_version_id = $this->tree_manager->new_node($id,$this->node_types_descr_id['testcase_version']);
	
	$sql = "INSERT INTO tcversions (id,version,summary,steps,expected_results,author_id,creation_ts)
  	      VALUES({$tcase_version_id},{$version},'" .  $this->db->prepare_string($summary) . "'," . 
	  	                           "'" . $this->db->prepare_string($steps) . "'," .
	  	                           "'" . $this->db->prepare_string($expected_results) . "'," . $author_id . "," .
                    	  	       $this->db->db_now() . ")";
	$result = $this->db->exec_query($sql);        
	$ret['msg']='ok';
	$ret['id']=$tcase_version_id;
	
	if (!$result)
	{
		$ret['msg'] = $this->db->error_msg();
	}
	
	return $ret;
}

/*
  function: get_by_name
            
  args: $name
  
  returns: hash
*/
function get_by_name($name)
{
	$sql = " SELECT nodes_hierarchy.id,nodes_hierarchy.name 
	         FROM nodes_hierarchy 
	         WHERE nodes_hierarchy.node_type_id = {$this->my_node_type}
	         AND nodes_hierarchy.name = '" . 
	         $this->db->prepare_string($name) . "'";

  $recordset = $this->db->get_recordset($sql);

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
	         FROM  nodes_hierarchy
	         WHERE nodes_hierarchy.node_type_id={$my_node_type}";
	$recordset = $this->db->get_recordset($sql);
	
	return $recordset;
}


/*
  function: show

  args :
        $smarty: reference to smarty object (controls viewer).
        $id: test case id
        $user_id: user requesting operation
        [$version_id]: you can work on ONE test case version, or on ALL
                       default: ALL
        [$action]
        [$msg_result]
        [$refresh_tree]: controls if tree view is refreshed after every operation.
                         default: yes
        [$user_feedback]:
        [$disable_edit]: used to overwrite user rights
                         default: 0 -> no
  
  returns: 
  
  rev :
       20070930 - franciscom - REQ - BUGID 1078 
       added disable_edit argument

*/
function show(&$smarty,$template_dir,$id, $user_id, $version_id=TC_ALL_VERSIONS, $action='', 
              $msg_result='', $refresh_tree='yes', $user_feedback='', $disable_edit=0)
{
  
  $req_mgr = new requirement_mgr($this->db);
	$gui_cfg = config_get('gui');
	$the_tpl = config_get('tpl');
	$tcase_cfg = config_get('testcase_cfg');

	$arrReqs = null;
	
	// 20070930 - franciscom
	$can_edit = $disable_edit == 0 ? has_rights($this->db,"mgt_modify_tc") : "no";

	if(is_array($id))
	{
		$a_id = $id;
	}
	else
	{
		$a_id = array($id);  
	}
 
	$tc_current_version = array();
	$tc_other_versions = array();
	$status_quo_map = array();
	$keywords_map = array();
	$arrReqs = array();
  
	foreach($a_id as $key => $tc_id)
	{
		$tc_array = $this->get_by_id($tc_id,$version_id);
		if (!$tc_array)
			continue;
			
		//get the status quo of execution and links of tc versions
		$status_quo_map[] = $this->get_versions_status_quo($tc_id);
		
		$keywords_map[] = $this->get_keywords_map($tc_id,' ORDER BY KEYWORD ASC ');
		$tc_array[0]['keywords'] = $keywords_map;
		$tc_current_version[] = array($tc_array[0]);
		
		$qta_versions = count($tc_array);
		if($qta_versions > 1)
		{
			$tc_other_versions[] = array_slice($tc_array,1);
		}
		else
		{
			$tc_other_versions[] = null;
		}
		
		// get assigned REQs
		// $arrReqs[] = $req_mgr->getReq4Tc($this->db,$tc_id);
    $arrReqs[] = $req_mgr->get_all_for_tcase($tc_id);

		// custom fields
		$cf_smarty=null;
		if( $gui_cfg->enable_custom_fields ) 
		{
		  $cf_smarty[] = $this->html_table_of_custom_field_values($tc_id);
		}
		$smarty->assign('cf',$cf_smarty);	
 	}
	$users = getAllUsers($this->db,null,'id');
	
	$smarty->assign('user_feedback',$user_feedback);
	$smarty->assign('tcase_cfg',$tcase_cfg);
	$smarty->assign('action',$action);
	$smarty->assign('users',$users);
	$smarty->assign('sqlResult',$msg_result);
	$smarty->assign('can_edit',$can_edit);
	$smarty->assign('can_delete_testcase',$can_edit);
	$smarty->assign('can_delete_version',$can_edit);
	$smarty->assign('status_quo',$status_quo_map);
	$smarty->assign('refresh_tree',$refresh_tree);
	$smarty->assign('testcase_curr_version',$tc_current_version);
	$smarty->assign('testcase_other_versions',$tc_other_versions);
	$smarty->assign('arrReqs',$arrReqs);
	$smarty->assign('view_req_rights', has_rights($this->db,"mgt_view_req")); 
	$smarty->assign('opt_requirements', isset($_SESSION['testprojectOptReqs']) ? $_SESSION['testprojectOptReqs'] : null); 	
	$smarty->assign('keywords_map',$keywords_map);
	$smarty->display($template_dir . $the_tpl['tcView']);
}




// 20060726 - franciscom - default value changed for optional argument $tc_order
//                         create(), update()
//
// 20060424 - franciscom - interface changes added $keywords_id
function update($id,$tcversion_id,$name,$summary,$steps,
                $expected_results,$user_id,$keywords_id='',$tc_order=TC_DEFAULT_ORDER)
{
	$status_ok = 0;
	
	$sql = " UPDATE nodes_hierarchy SET name='" . 
	         $this->db->prepare_string($name) . "' WHERE id= {$id}";
	
	$result = $this->db->exec_query($sql);
	$status_ok = $result ? 1: 0;

	if( $status_ok)
	{       
		// test case version
		$sql = " UPDATE tcversions SET summary='" . $this->db->prepare_string($summary) . "'," .
				" steps='" . $this->db->prepare_string($steps) . "'," .
				" expected_results='" . $this->db->prepare_string($expected_results) . "'," .
				" updater_id={$user_id}, modification_ts = " . $this->db->db_now() .
				" WHERE tcversions.id = {$tcversion_id}";
		
		$result = $this->db->exec_query($sql);
		$status_ok = $result ? 1: 0;
	}
	// keywords
	// update = delete + insert
	$this->deleteKeywords($id);   	 
	if(strlen(trim($keywords_id)))
	{
		$a_keywords = explode(",",$keywords_id);
		$this->addKeywords($id,$a_keywords);
	}

	return $status_ok;
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
	$exec_status = 'ALL';
	$linked_tcversions = $this->get_linked_versions($id,$exec_status);
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


/* 20060326 - franciscom - interface changed */
function delete($id,$version_id = TC_ALL_VERSIONS)
{
  $children=null;
  if($version_id == TC_ALL_VERSIONS)
  { 
    // I'm trying to speedup the next deletes   
    $sql="SELECT nodes_hierarchy.id FROM nodes_hierarchy ";
    if( is_array($id) )
    {
      $sql .= " WHERE nodes_hierarchy.parent_id IN (" .implode(',',$id) . ") ";
    }
    else
    {
      $sql .= " WHERE nodes_hierarchy.parent_id={$id} ";
    }
    
    $children_rs=$this->db->get_recordset($sql);
    foreach($children_rs as $value)
    {
      $children[]=$value['id'];
    }
  }
	$this->_execution_delete($id,$version_id,$children);
	$this->_blind_delete($id,$version_id,$children);

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
function get_linked_versions($id,$exec_status="ALL",$active_status='ALL')
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
			$sql = "SELECT NH.parent_id AS testcase_id, NH.id AS tcversion_id,
						tcversions.*,
						TTC.testplan_id, TTC.tcversion_id,NHB.name AS tplan_name
					FROM   nodes_hierarchy NH,tcversions,testplan_tcversions TTC,
					       nodes_hierarchy NHB 
					WHERE  TTC.tcversion_id = tcversions.id 
          {$active_filter}
					AND    tcversions.id = NH.id
					AND    NHB.id = TTC.testplan_id
					AND    NH.parent_id = {$id}"; 
			break;
			
    	case "EXECUTED":
    	// 20061030 - franciscom
			$sql = "SELECT NH.parent_id AS testcase_id, NH.id AS tcversion_id,
			             tcversions.*,
			             TTC.testplan_id, TTC.tcversion_id,
			             executions.id AS execution_id,NHB.name AS tplan_name
					FROM   nodes_hierarchy NH,tcversions,testplan_tcversions TTC,
					       executions,nodes_hierarchy NHB
					WHERE  TTC.tcversion_id = tcversions.id
          {$active_filter}
					AND    TTC.testplan_id = NHB.id 
					AND    TTC.testplan_id = executions.testplan_id
					AND    executions.tcversion_id = tcversions.id
					AND    NH.id = tcversions.id
					AND    NH.parent_id = {$id}"; 
		    break;
		    
	    case "NOT_EXECUTED":
			$sql = "SELECT NH.parent_id AS testcase_id, NH.id AS tcversion_id,
			             tcversions.*,
			             TTC.testplan_id, TTC.tcversion_id,NHB.name AS tplan_name
					FROM   nodes_hierarchy NH,tcversions,testplan_tcversions TTC,
					       nodes_hierarchy NHB 
					WHERE  TTC.tcversion_id = tcversions.id
			    {$active_filter}
					AND    tcversions.id = NH.id
      		AND    NHB.id = TTC.testplan_id
					AND    NH.parent_id = {$id}
					AND    tcversions.id NOT IN ( SELECT tcversion_id FROM executions
	                WHERE executions.tcversion_id = tcversions.id )";
	    	break;
  }
  
  $recordset = $this->db->fetchMapRowsIntoMap($sql,'tcversion_id','testplan_id');
  
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
function _blind_delete($id,$version_id=TC_ALL_VERSIONS,$children=null)
{
    $sql = array();

    if( $version_id == TC_ALL_VERSIONS)
    {    
	    $sql[]="DELETE FROM testcase_keywords WHERE testcase_id = {$id}";
	    $sql[]="DELETE FROM req_coverage WHERE testcase_id = {$id}";
	
	    $children_list=implode(',',$children);
	    $sql[]="DELETE FROM testplan_tcversions " . 
	           " WHERE tcversion_id IN ({$children_list})";

      $sql[]="DELETE FROM tcversions " .
	           " WHERE id IN ({$children_list})";
                                      
                                      
      // 20070602 - franciscom
      $this->deleteAttachments($id);                                      
      $this->cfield_mgr->remove_all_design_values_from_node($id);
                                      
      $item_id = $id;
    }
    else
    {
		  $sql[] = "DELETE FROM testplan_tcversions 
				        WHERE tcversion_id = {$version_id}";
      $sql[] = "DELETE FROM tcversions 
                WHERE tcversions.id = {$version_id}";
    	
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
function _execution_delete($id,$version_id=TC_ALL_VERSIONS,$children=null)
{
	  $sql = array();
		if( $version_id	== TC_ALL_VERSIONS )
		{ 
	    $children_list=implode(',',$children);
    	$sql[]="DELETE FROM execution_bugs 
        		  WHERE execution_id IN (SELECT id FROM executions 
            		                     WHERE tcversion_id IN ({$children_list}))";
             
      // 20070603 - franciscom       
      $sql[]="DELETE FROM cfield_execution_values " .
      		   " WHERE tcversion_id IN ({$children_list})";
                    		                                   
      $sql[]="DELETE FROM executions " .
      		   " WHERE tcversion_id IN ({$children_list})";
      		   
      		   
    }
    else
    {
    		$sql[]="DELETE FROM execution_bugs 
        	  	  WHERE execution_id IN (SELECT id FROM executions 
              		                     WHERE tcversion_id = {$version_id})";
    	
        // 20070603 - franciscom       
        $sql[]="DELETE FROM cfield_execution_values " .
        		   " WHERE tcversion_id = {$version_id}";

    		$sql[]="DELETE FROM executions " .
        		   " WHERE tcversion_id = {$version_id}";
    }
    
    foreach ($sql as $the_stm)
    {
    		$result = $this->db->exec_query($the_stm);
    }
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
function copy_to($id,$parent_id,$user_id,
                 $copy_keywords=0, 
                 $check_duplicate_name=0,
                 $action_on_duplicate_name='generate_new')
{
  $new_tc['id']=-1;
  $new_tc['status_ok']=0;
  $new_tc['msg']='ok';
	
	$tcase_info = $this->get_by_id($id);
	if ($tcase_info)
	{
		$new_tc = $this->create_tcase_only($parent_id,$tcase_info[0]['name'],
		                                   $tcase_info[0]['node_order'],TC_AUTOMATIC_ID,
                                       $check_duplicate_name,
                                       'generate_new');
		if ($new_tc['status_ok'])
		{
	    $ret['status_ok']=1;
 			foreach($tcase_info as $tcversion)
			{
				$this->create_tcversion($new_tc['id'],$tcversion['version'],
				                        $tcversion['summary'],$tcversion['steps'],
				                        $tcversion['expected_results'],$tcversion['author_id']);
			}
			if ($copy_keywords)
			{
				$this->copyKeywordsTo($id,$new_tc['id']);
			}
			$this->copy_cfields_design_values($id,$new_tc['id']);


      // 20070602 - franciscom
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
	$sql = "SELECT MAX(version) AS version FROM tcversions,nodes_hierarchy WHERE ".
		     " nodes_hierarchy.id = tcversions.id ".
	       " AND nodes_hierarchy.parent_id = {$id} ";
	
	$max_version = $this->db->fetchFirstRowSingleColumn($sql,'version');
	
	$tcInfo = null;
	if ($max_version)
	{
		$sql = "SELECT tcversions.* FROM tcversions,nodes_hierarchy ".
		       "WHERE version = {$max_version} AND nodes_hierarchy.id = tcversions.id".
			   " AND nodes_hierarchy.parent_id = {$id}";
		
		$tcInfo = $this->db->fetchFirstRow($sql);
	}
	return $tcInfo;
}


/*
  function: copy_tcversion

  args :
  
  returns: 

*/
function copy_tcversion($from_tcversion_id,$to_tcversion_id,$as_version_number,$user_id)
{
    $now = $this->db->db_now();
		$sql="INSERT INTO tcversions (id,version,author_id,creation_ts,
		                              summary,steps,expected_results,importance)
          SELECT {$to_tcversion_id} AS id, {$as_version_number} AS version,
                 {$user_id} AS author_id, {$now} AS creation_ts,
                 summary,steps,expected_results,importance
          FROM tcversions 
          WHERE id={$from_tcversion_id} ";
    $result = $this->db->exec_query($sql);
}	


/*
  function: get_by_id_bulk

  args :
  
  returns: 

*/
function get_by_id_bulk($id,$version_id=TC_ALL_VERSIONS, $get_active=0, $get_open=0)
{
	$where_clause="";
	$where_clause_names="";
	$tcid_list ="";
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
	
	$sql = " SELECT nodes_hierarchy.parent_id AS testcase_id, 
	                tcversions.*, users.first AS author_first_name, users.last AS author_last_name,
	                '' AS updater_first_name, '' AS updater_last_name
	         FROM nodes_hierarchy JOIN tcversions ON nodes_hierarchy.id = tcversions.id 
                          LEFT OUTER JOIN users ON tcversions.author_id = users.id
           {$where_clause} ORDER BY tcversions.version DESC";
  $recordset = $this->db->get_recordset($sql);
  
  
  if($recordset)
  {
  	 // get the names
	   $sql = " SELECT nodes_hierarchy.id AS testcase_id, nodes_hierarchy.name 
	            FROM nodes_hierarchy {$where_clause_names} ";
	   
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
  	
  	
	 $sql = " SELECT updater_id, users.first AS updater_first_name, users.last  AS updater_last_name
	           FROM nodes_hierarchy JOIN tcversions ON nodes_hierarchy.id = tcversions.id 
                           LEFT OUTER JOIN users ON tcversions.updater_id = users.id
             {$where_clause} and tcversions.updater_id IS NOT NULL ";
                           
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

         [version_id]: default TC_ALL_VERSIONS => all versions
                       can be an array.
                       Useful to retrieve only a subset of versions.
                       
         [active_status]: default 'ALL', range: 'ALL','ACTIVE','INACTIVE'
                          has effect for the following version_id values:
                          TC_ALL_VERSIONS,TC_LAST_VERSION, version_id is NOT an array 
  
         [open_status]: default 'ALL'
                        currently not used.
  
  returns: array when every element has following keys:
  

*/
function get_by_id($id,$version_id = TC_ALL_VERSIONS, $active_status='ALL',$open_status='ALL')
{
	$tcid_list = '';
	$where_clause = '';
  $active_filter='';
	
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
		if($version_id != TC_ALL_VERSIONS && $version_id != TC_LATEST_VERSION)
		{
			$where_clause .= " AND tcversions.id = {$version_id} ";
		}

    $active_status=strtoupper($active_status);
	  if($active_status !='ALL')
	  {
	    $active_filter=' AND tcversions.active=' . ($active_status=='ACTIVE' ? 1 : 0) . ' ';
    }

	}

	$sql = "SELECT	U.login AS updater_login,users.login as author_login,
		    NHB.name,NHB.node_order,NHA.parent_id AS testcase_id, tcversions.*, 
		    users.first AS author_first_name, 
		    users.last AS author_last_name, 
		    U.first AS updater_first_name, 
		    U.last  AS updater_last_name
        FROM nodes_hierarchy NHA
        JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id 
        JOIN tcversions ON NHA.id = tcversions.id 
        LEFT OUTER JOIN users ON tcversions.author_id = users.id 
        LEFT OUTER JOIN users U ON tcversions.updater_id = U.id  
        $where_clause 
        $active_filter
        ORDER BY tcversions.version DESC";


	if ($version_id != TC_LATEST_VERSION)
		$recordset = $this->db->get_recordset($sql);
	else
		$recordset = array($this->db->fetchFirstRow($sql));
		
	return ($recordset ? $recordset : null);
}


/*
  function: get_versions_status_quo
            Get linked and executed status quo.
            No info specific to testplan items where testacase can be linked to
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
		
		// 20061030 - franciscom
		$testplan_filter='';
		if(!is_null($testplan_id))
    {
      $testplan_filter=" AND E.testplan_id = {$testplan_id} ";
    }
    $execution_join=" LEFT OUTER JOIN executions E ON (E.tcversion_id = NH.id {$testplan_filter})";
		
		$sql="SELECT DISTINCT NH.id AS tcversion_id,
		                      T.tcversion_id AS linked,
		                      E.tcversion_id AS executed
		      FROM   nodes_hierarchy NH
		      LEFT OUTER JOIN testplan_tcversions T ON T.tcversion_id = NH.id
		      {$execution_join}
		      WHERE  NH.parent_id = {$id} {$tcversion_filter}"; 

		$recordset = $this->db->fetchRowsIntoMap($sql,'tcversion_id');
  	return($recordset);
}



/*
  function: get_exec_status
            Get information about executed and linked status in
            every testplan, testcase is linked to.

  args :id : testcase id
  
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
				          

*/
function get_exec_status($id)
{
		$sql="SELECT NH.parent_id AS tcase_id, NH.id AS tcversion_id,
		             T.tcversion_id AS linked,
		             E.tcversion_id AS executed, E.testplan_id AS exec_on_tplan,
		             T.testplan_id, NHB.name AS tplan_name, TCV.version
		      FROM   nodes_hierarchy NH
		      JOIN testplan_tcversions T ON T.tcversion_id = NH.id
		      JOIN tcversions TCV ON T.tcversion_id = TCV.id
		      JOIN nodes_hierarchy NHB ON T.testplan_id = NHB.id
		      LEFT OUTER JOIN executions E ON (E.tcversion_id = NH.id AND E.testplan_id=T.testplan_id)
		      WHERE  NH.parent_id = {$id}
		      ORDER BY version,tplan_name";
		      
			//$recordset = $this->db->fetchArrayRowsIntoMap($sql,'tcversion_id');
		$recordset = $this->db->fetchMapRowsIntoMap($sql,'tcversion_id','testplan_id');
  	return($recordset);
}
// -------------------------------------------------------------------------------



// -------------------------------------------------------------------------------
//                            Keyword related methods	
// -------------------------------------------------------------------------------
/*
  function: getKeywords

  args :
  
  returns: 

*/
function getKeywords($tcID,$kwID = null)
{
	$sql = "SELECT keyword_id,keywords.keyword,keywords.notes 
	        FROM testcase_keywords,keywords 
	        WHERE keyword_id = keywords.id AND testcase_id = {$tcID}";
	if (!is_null($kwID))
	{
		$sql .= " AND keyword_id = {$kwID}";
	}	
	$tcKeywords = $this->db->fetchRowsIntoMap($sql,'keyword_id');
	
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
	        FROM testcase_keywords,keywords 
	        WHERE keyword_id = keywords.id ";
	if (is_array($id))
		$sql .= " AND testcase_id IN (".implode(",",$id).") ";
	else
		$sql .= " AND testcase_id = {$id} ";
		
	$sql .= $order_by_clause;

	$map_keywords = $this->db->fetchColumnsIntoMap($sql,'keyword_id','keyword');
	return $map_keywords;
} 

function addKeyword($id,$kw_id)
{
	$kw = $this->getKeywords($id,$kw_id);
	if (sizeof($kw))
		return 1;
	$sql = " INSERT INTO testcase_keywords (testcase_id,keyword_id) " .
		     " VALUES ($id,$kw_id)";

	return ($this->db->exec_query($sql) ? 1 : 0);
}

function addKeywords($id,$kw_ids)
{
	$bSuccess = 1;
	$num_kws = sizeof($kw_ids);
	for($idx = 0; $idx < $num_kws; $idx++)
	{
		$bSuccess = $bSuccess && $this->addKeyword($id,$kw_ids[$idx]);
	}
	
	return $bSuccess;
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


function deleteKeywords($tcID,$kwID = null)
{
	$sql = " DELETE FROM testcase_keywords WHERE testcase_id = {$tcID} ";
	if (!is_null($kwID))
		$sql .= " AND keyword_id = {$kwID}";
	return $this->db->exec_query($sql);
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
                  build_id
                  build_name
                  build_is_active
                  build_is_open

*/
function get_executions($id,$version_id,$tplan_id,$build_id,$exec_id_order='DESC',$exec_to_exclude=null)
{
  // Contribution
  // Can get execution for any build
	$build_id_filter='';		
	if ( !is_null($build_id) )
	{
		$build_id_filter=" AND e.build_id = {$build_id} ";
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
			if($version_id != TC_ALL_VERSIONS)
			{
				$where_clause  .= " AND tcversions.id = {$version_id} ";
			}
	}
	
  if( !is_null($exec_to_exclude ) )
  {
  	
			if( is_array($exec_to_exclude))
			{
			    if(count($exec_to_exclude) > 0 )
			    {
			 	  	$exec_id_list = implode(",",$exec_to_exclude);
	        	$where_clause  .= " AND e.id NOT IN ({$exec_id_list}) ";
	        }
			}
			else
			{
	        $where_clause  .= " AND e.id <> {$exec_id_list} ";
			}
	}
  // --------------------------------------------------------------------	
  // 20071113 - franciscom - added JOIN builds b ON e.build_id=b.id
  //
  $sql="SELECT	NHB.name,NHA.parent_id AS testcase_id, tcversions.*, 
		    users.login AS tester_login,
		    users.first AS tester_first_name, 
		    users.last AS tester_last_name,
			  users.id AS tester_id, 
		    e.id AS execution_id, e.status, 
		    e.notes AS execution_notes, e.execution_ts, e.build_id AS build_id,
		    b.name AS build_name, b.active AS build_is_active, b.is_open AS build_is_open
	      FROM nodes_hierarchy NHA
        JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id 
        JOIN tcversions ON NHA.id = tcversions.id 
        JOIN executions e ON NHA.id = e.tcversion_id  
                                     AND e.testplan_id = {$tplan_id}
                                     {$build_id_filter}
        JOIN builds b ON e.build_id=b.id
        LEFT OUTER JOIN users ON e.tester_id = users.id 
        $where_clause 
        ORDER BY NHA.node_order ASC, NHA.parent_id ASC, execution_id {$exec_id_order}";
   

  $recordset = $this->db->fetchArrayRowsIntoMap($sql,'id');
  return($recordset ? $recordset : null);
}




/*
  function: get_last_execution

  args :
  
  returns: map:
           key: tcversion_id
           value: map with following keys:
            			execution_id
            			status: execution status
            			name: testcase name
            			testcase_id
            			tsuite_id: parent testsuite of testcase (node id)
            			id: tcversion id (node id)
            			version
                  summary: testcase spec. summary
                  steps: testcase spec. steps
                  expected_results: testcase spec. expected results
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
            			build_id
            			build_name
            			build_is_active
            			build_is_open
            			
   rev:
       added build_is_active, build_is_open         			

*/
function get_last_execution($id,$version_id,$tplan_id,$build_id,$get_no_executions=0)
{
	
	$build_id_filter='';
	$where_clause_1 = '';
	$where_clause_2= '';
	
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
			if($version_id != TC_ALL_VERSIONS)
			{
				$where_clause_1 = $where_clause . " AND NHA.id = {$version_id} ";
				$where_clause_2 = $where_clause . " AND tcversions.id = {$version_id} ";
			}
	}

  if( !is_null($build_id) )
  {
    $build_id_filter=" AND e.build_id = {$build_id} ";	
  } 
  $sql="SELECT MAX(e.id) AS execution_id, e.tcversion_id AS tcversion_id " .
  	   " FROM nodes_hierarchy NHA " .
       " JOIN executions e ON NHA.id = e.tcversion_id  AND e.testplan_id = {$tplan_id} " .
       " {$build_id_filter} AND e.status IS NOT NULL " .
       " $where_clause_1 GROUP BY tcversion_id";
       
  $recordset = $this->db->fetchColumnsIntoMap($sql,'tcversion_id','execution_id');

  $and_exec_id='';
  if( !is_null($recordset) )
  {
  	  $the_list = implode(",",$recordset);
  	  if( count($recordset) > 1 )
  	  {
  			$and_exec_id = " AND e.id IN (". $the_list . ") ";
  		}
  		else
  		{
  		  $and_exec_id = " AND e.id = $the_list ";
  		}
  }
  
  $executions_join=" JOIN executions e ON NHA.id = e.tcversion_id  
                                           AND e.testplan_id = {$tplan_id}
                                           {$and_exec_id} 
                                           {$build_id_filter} ";
  if( $get_no_executions )
  {
     $executions_join = " LEFT OUTER " . $executions_join;
  }
  else
  {
     $executions_join .= " AND e.status IS NOT NULL ";
  }

  // 20060921 - franciscom - 
  // added NHB.parent_id  to get same order as in the navigator tree
  //
  $sql="SELECT e.id AS execution_id, e.status,
        NHB.name,NHA.parent_id AS testcase_id, NHB.parent_id AS tsuite_id,
        tcversions.*, 
		    users.login AS tester_login,
		    users.first AS tester_first_name, 
		    users.last AS tester_last_name,
			  users.id AS tester_id, 
		    e.notes AS execution_notes, e.execution_ts, e.build_id,
		    builds.name AS build_name, builds.active AS build_is_active, builds.is_open AS build_is_open 
	      FROM nodes_hierarchy NHA
        JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id 
        JOIN tcversions ON NHA.id = tcversions.id 
        {$executions_join}
        LEFT OUTER JOIN builds     ON builds.id = e.build_id 
                           AND builds.testplan_id = {$tplan_id}                                
        LEFT OUTER JOIN users ON e.tester_id = users.id 
        $where_clause_2 
        ORDER BY NHB.parent_id ASC, NHA.node_order ASC, NHA.parent_id ASC, execution_id DESC";



  $recordset = $this->db->fetchRowsIntoMap($sql,'id');
  return($recordset ? $recordset : null);
}


/*
  function: exportTestCaseDataToXML

  args :
  
  returns: 

*/
function exportTestCaseDataToXML($tcase_id,$tcversion_id,$bNoXMLHeader = false,$optExport = array())
{
	$tc_data = $this->get_by_id($tcase_id,$tcversion_id);
	if ($optExport['KEYWORDS'])
	{
		$keywords = $this->getKeywords($tcase_id);
		if ($keywords);
		{
			$xmlKW = exportKeywordDataToXML($keywords,true);
			$tc_data[0]['xmlkeywords'] = $xmlKW;
		}
	}	
	$rootElem = "{{XMLCODE}}";
	if (isset($optExport['ROOTELEM']))
		$rootElem = $optExport['ROOTELEM'];
	$elemTpl = "\t".'<testcase name="{{NAME}}">'.	
						'<summary><![CDATA['."\n||SUMMARY||\n]]>".'</summary>'.
						'<steps><![CDATA['."\n||STEPS||\n]]>".'</steps>'.
						'<expectedresults><![CDATA['."\n||RESULTS||\n]]>".'</expectedresults>'.
						'||KEYWORDS||</testcase>'."\n";
	$info = array (
							"{{NAME}}" => "name",
							"||SUMMARY||" => "summary",
							"||STEPS||" => "steps",
							"||RESULTS||" => "expected_results",
							"||KEYWORDS||" => "xmlkeywords",
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
function get_version_exec_assignment($tcversion_id,$tplan_id)
{
	$sql = "SELECT T.tcversion_id AS tcversion_id,T.id AS feature_id," .
			"       UA.user_id,UA.type,UA.status,UA.assigner_id ".
			" FROM testplan_tcversions T " . 
			" LEFT OUTER JOIN user_assignments UA ON UA.feature_id = T.id " .
			" WHERE T.testplan_id={$tplan_id} " .
			" AND   T.tcversion_id = {$tcversion_id} " .
			" AND   (UA.type=" . $this->assignment_types['testcase_execution']['id'] . 
			"        OR UA.type IS NULL) ";
		
		  
	$recordset = $this->db->fetchRowsIntoMap($sql,'tcversion_id');
	return $recordset;
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
	// test case version
	$sql = " UPDATE tcversions SET active={$active_status}" .
			" WHERE tcversions.id = {$tcversion_id}";
	
	$result = $this->db->exec_query($sql);
	
	return $result ? 1: 0;
}


/*
  function: copy_attachments
            Copy attachments from source testcase to target testcase

  args : source_id
         target_id
  
  returns: -

*/
//SCHLUNDUS: copy attachments should be repository functionality
function copy_attachments($source_id,$target_id)
{
  $table_name = $this->attachmentTableName;
  $f_parts=null;
  $destFPath=null;
  $mangled_fname='';
  $status_ok=false;
  $repo_type=config_get('repositoryType');
  $repo_path=config_get('repositoryPath') .  DIRECTORY_SEPARATOR;
  
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
				$destFPath = $this->m_attachmentRepository->buildRepositoryFilePath($mangled_fname,$table_name,$target_id);
				$status_ok = copy($repo_path . $value['file_path'],$destFPath);
			}
			else
			{
				$file_contents = $this->m_attachmentRepository->getAttachmentContentFromDB($value['id']);
				$status_ok = sizeof($file_contents);
			}
			if($status_ok)
			{
				$attachment = new tlAttachment();
				$attachment->create($target_id,$table_name,$value['file_name'],$destFPath,$file_contents,$value['file_type'],$value['file_size'],$value['title']);
				$attachment->writeToDb($db);
			}
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
                     need to undertad to which testproject the testcase belongs.
                     this information is vital, to get the linked custom fields.
                     Presence /absence of this value changes starting point
                     on procedure to build tree path to get testproject id.
                     
                     null -> use testcase_id as starting point.
                     !is_null -> use this value as starting point.        
                             
        [show_on_execution]: default: null
                             1 -> filter on field show_on_execution=1
                                  include ONLY custom fields that can be viewed
                                  while user is execution testcases.
                                  
                             0 or null -> don't filter
        
        
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
function get_linked_cfields_at_design($id,$parent_id=null,$show_on_execution=null) 
{
	$enabled = 1;
	$tproject_mgr = new testproject($this->db);
	
	// 20070302 - added $id > 0
	$the_path = $this->tree_manager->get_path( (!is_null($id) && $id > 0) ? $id : $parent_id);
	$path_len = count($the_path);
	$tproject_id = ($path_len > 0)? $the_path[$path_len-1]['parent_id'] : $parent_id;
	
	$cf_map = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,$enabled,
	                                                          $show_on_execution,'testcase',$id);
	
	return $cf_map;
}

/*
  function: html_table_of_custom_field_inputs
            Return html code, implementing a table with custom fields labels
            and html inputs, for choosen testcase.
            Used to manage user actions on custom fields values.
            
            
  args: $id
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
                        (example during test case execution, several test cases)
                        that have the same custom fields.
                        In this kind of situation we can use the item id as name suffix.
                         
        
  returns: html string
  
*/
function html_table_of_custom_field_inputs($id,$parent_id=null,$scope='design',$name_suffix='') 
{
	$cf_smarty = '';
	
	if($scope == 'design')
	{
		$cf_map = $this->get_linked_cfields_at_design($id,$parent_id);
	}
	else
	{
		$cf_map = $this->get_linked_cfields_at_execution($id,$parent_id);
	}
	
	if(!is_null($cf_map))
	{
		$cf_smarty = "<table>";
		foreach($cf_map as $cf_id => $cf_info)
		{
      // 20070501 - franciscom
      $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label']));

			$cf_smarty .= '<tr><td class="labelHolder">' . htmlspecialchars($label) . ":</td><td>" .
				$this->cfield_mgr->string_custom_field_input($cf_info,$name_suffix) .
						"</td></tr>\n";
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
            
            
  args: $id
        [$scope]: 'design' -> use custom fields that can be used at design time (specification)
                  'execution' -> use custom fields that can be used at execution time.

        [$show_on_execution]: default: null
                              1 -> filter on field show_on_execution=1
                              0 or null -> don't filter
  
        [$execution_id]: null -> get values for all executions availables for testcase
                         !is_null -> only get values or this execution_id
                    
        [$testplan_id]: null -> get values for any tesplan to with testcase is linked
                        !is_null -> get values only for this testplan.

  returns: html string
  
*/
function html_table_of_custom_field_values($id,$scope='design',$show_on_execution=null,
                                           $execution_id=null,$testplan_id=null) 
{
	$cf_smarty = '';
	$PID_NO_NEEDED = null;
  
	if($scope=='design')
	{
		$cf_map = $this->get_linked_cfields_at_design($id,$PID_NO_NEEDED,$show_on_execution);
	}
	else 
	{
		$cf_map = $this->get_linked_cfields_at_execution($id,$PID_NO_NEEDED,$show_on_execution,
		                                                 $execution_id,$testplan_id);
	}
    
	if(!is_null($cf_map))
	{
		foreach($cf_map as $cf_id => $cf_info)
		{
			// if user has assigned a value, then node_id is not null
			if($cf_info['node_id'])
			{
	      // 20070501 - franciscom
        $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label']));

				$cf_smarty .= '<tr><td class="labelHolder">' . 
								htmlspecialchars($label) . ":</td><td>" .
								$this->cfield_mgr->string_custom_field_value($cf_info,$id) .
								"</td></tr>\n";
			}
		}
		
		if(strlen(trim($cf_smarty)) > 0)
		{
		  $cf_smarty = "<table>" . $cf_smarty . "</table>";
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

        [$execution_id]: null -> get values for all executions availables for testcase
                         !is_null -> only get values or this execution_id
                    
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
function get_linked_cfields_at_execution($id,$parent_id=null,$show_on_execution=null,
                                         $execution_id=null,$testplan_id=null) 
{
	$enabled = 1;
	$tproject_mgr = new testproject($this->db);
	
	$the_path=$this->tree_manager->get_path(!is_null($id) ? $id : $parent_id);
	$path_len = count($the_path);
	$tproject_id = ($path_len > 0)? $the_path[$path_len-1]['parent_id'] : $parent_id;
	
	
	// Warning:
	// I'm setting node type to test case, but $id is the tcversion_id, because
	// execution data is related to tcversion NO testcase
	//
	$cf_map = $this->cfield_mgr->get_linked_cfields_at_execution($tproject_id,$enabled,'testcase',
	                                                         $id,$execution_id,$testplan_id);
	return($cf_map);
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

} // end class
?>
