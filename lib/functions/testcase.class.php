<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testcase.class.php,v $
 * @version $Revision: 1.41 $
 * @modified $Date: 2007/01/06 15:16:26 $ $Author: franciscom $
 * @author franciscom
 *
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
 * 20061030 - franciscom - new argument in get_versions_status_quo()
 *                         corrected bug in get_linked_versions()
 *
 * 20061015 - franciscom - fixed bug on create()
 *                         instead of returning tcid, returned tcversion id ->WRONG
 *
 * 20061008 - franciscom - changes in interface in:
 *                         create()
 *                         create_tcase_only()
 * 
 * 20060726 - franciscom - create_tcversion() return array changed
 *                         default value changed for optional argument $tc_order
 *                         create(), update()
 *
 *
 * 20060425 - franciscom - changes in show() following Andreas Morsing advice (schlundus)
 * 20060423 - franciscom - added order_by_clause argument - get_keywords_map()
 * 20060323 - franciscom - create_tcversion() interface change added $version
 *
 */
require_once( dirname(__FILE__) . '/requirements.inc.php' );
require_once( dirname(__FILE__) . '/assignment_mgr.class.php' );


$g_tcImportTypes = array( 
							 "XML" => "XML",
							 );

$g_tcFormatStrings = array (
							"XML" => lang_get('the_format_tc_xml_import')
							); 
							
define("TC_ALL_VERSIONS",0);
define("TC_LATEST_VERSION",-1);
define("TC_DEFAULT_ORDER",0);
define("TC_AUTOMATIC_ID",0);
define("TC_COPY_KEYWORDS",0);

class testcase
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

	function testcase(&$db)
	{
		$this->db = &$db;	
		$this->tree_manager = New tree($this->db);
		$this->node_types_descr_id=$this->tree_manager->get_available_node_types();
		$this->node_types_id_descr=array_flip($this->node_types_descr_id);
		$this->my_node_type=$this->node_types_descr_id['testcase'];

    // 20060908 - franciscom
    $this->assignment_mgr=New assignment_mgr($this->db);
    $this->assignment_types=$this->assignment_mgr->get_available_types(); 
    $this->assignment_status=$this->assignment_mgr->get_available_status();

    // 20061230 - franciscom
  	$this->cfield_mgr=new cfield_mgr($this->db);
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
                $action_on_duplicate_name='generate_new')
{
	$first_version = 1;
	$status_ok = 1;
	$ret = $this->create_tcase_only($parent_id,$name,$tc_order,$id,
                                  $check_duplicate_name,
                                  $action_on_duplicate_name);
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
*/
function create_tcase_only($parent_id,$name,$order=TC_DEFAULT_ORDER,$id=TC_AUTOMATIC_ID,
                           $check_duplicate_name=0,
                           $action_on_duplicate_name='generate_new')
{
  $ret['id'] = -1;
  $ret['status_ok'] = 1;
  $ret['msg'] = 'ok';
		
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
					$ret['status_ok'] = 1;      
					$name = config_get('prefix_name_for_copy') . " " . $name ;      
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
20060726 - franciscom - struct of return array changed, added id
20060323 - franciscom - interface change added $version
*/
function create_tcversion($id,$version,$summary,$steps,
                          $expected_results,$author_id)
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
Every array element contains an assoc array with test suite info

*/
function get_all()
{
	$sql = " SELECT nodes_hierarchy.name, nodes_hierarchy.id
	         FROM  nodes_hierarchy
	         WHERE nodes_hierarchy.node_type_id={$my_node_type}";
	$recordset = $this->db->get_recordset($sql);
	
	return $recordset;
}


// 20060425 - franciscom - added $smarty argument (by reference) 
//                         can accept an array of id
//
function show(&$smarty,$id, $user_id, $version_id=TC_ALL_VERSIONS, $action='', 
              $msg_result='', $refresh_tree='yes')
{
  
  $gui_cfg = config_get('gui');
	$the_tpl = config_get('tpl');
	$arrReqs = null;
	$can_edit = has_rights($this->db,"mgt_modify_tc");

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
		$arrReqs[] = getReq4Tc($this->db,$tc_id);

    // 20061230 - franciscom
    // custom fields
    $cf_smarty=null;
    if( $gui_cfg->enable_custom_fields ) 
    {
      $cf_smarty[] = $this->html_table_of_custom_field_values($tc_id);
    }
    $smarty->assign('cf',$cf_smarty);	
 	}

  $smarty->assign('action',$action);
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
	
	$smarty->display($the_tpl['tcView']);
}



/*
  function: viewer_edit_new
            
            
  args: 
  
  returns: -
  
*/
function viewer_edit_new($amy_keys, $oFCK, $action, $parent_id, $id=null)
{
	$a_tpl = array( 'edit_testsuite' => 'containerEdit.tpl',
					        'new_testsuite'  => 'containerNew.tpl');
	

	$the_tpl = $a_tpl[$action];
	$smarty = new TLSmarty();
	$smarty->assign('sqlResult', null);
	$smarty->assign('containerID',$parent_id);	 
	
	$the_data = null;
	if ($action == 'edit_testsuite')
	{
		$the_data = $this->get_by_id($id);
		$name = $the_data['name'];
		$smarty->assign('containerID',$id);	
	
	
    // --------------------------------------------------------------------------------
    // 20061226 - franciscom
    // Custom fields
    if( $gui_cfg->enable_custom_fields ) 
    {
      $cf_smarty = $this->html_table_of_custom_field_inputs($id,$parent_id);
    } // if( $gui_cfg
    
    $smarty->assign('cf',$cf_smarty);	
    // --------------------------------------------------------------------------------
	}
	
	// fckeditor 
	foreach ($amy_keys as $key)
	{
		// Warning:
		// the data assignment will work while the keys in $the_data are identical
		// to the keys used on $oFCK.
		$of = &$oFCK[$key];
		$of->Value = isset($the_data[$key]) ? $the_data[$key] : null;
		$smarty->assign($key, $of->CreateHTML());
	}
	
	$smarty->assign('level', 'testsuite');
	$smarty->assign('name',$name);
	$smarty->assign('container_data',$the_data);
	
	$smarty->display($the_tpl);
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
	Need to check for every version of this test case:
	1. is linked to one of more test plans ?
	2. if anwser is yes then, need to check if has been executed => has records on executions table
	   
	2. if linked but never executed
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
	get for one tc all versions that are linked to test plans
	
	20061020 - franciscom - changed return type
	                        added test plan name in return data
*/
function get_linked_versions($id,$exec_status="ALL",$active_status='ALL')
{
	// exec_status = ALL,EXECUTED,NOT_EXECUTED
	//
	// active_status = ALL,ACTIVE,INACTIVE
	//
  $active_filter='';
  $active_status=strtoupper($active_status);
	if($active_status !='ALL')
	{
	  $active_filter=' AND tcversions.active=' . $active_status=='ACTIVE' ? 1 : 0;
  }
	// --------------------------------------------------------------------
	
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
*/
function _blind_delete($id,$version_id=TC_ALL_VERSIONS,$children=null)
{
    $sql = array();

    if( $version_id == TC_ALL_VERSIONS)
    {    
	    $sql[]="DELETE FROM testcase_keywords WHERE testcase_id = {$id}";
	    $sql[]="DELETE FROM req_coverage WHERE testcase_id = {$id}";
	
	    //$sql[]="DELETE FROM testplan_tcversions 
	    //        WHERE tcversion_id IN (SELECT nodes_hierarchy.id 
	    //                               FROM nodes_hierarchy WHERE nodes_hierarchy.parent_id = {$id})";
      //$sql[]="DELETE FROM tcversions 
      //        WHERE tcversions.id IN (SELECT nodes_hierarchy.id 
      //                                FROM nodes_hierarchy WHERE nodes_hierarchy.parent_id = {$id})";

	    $children_list=implode(',',$children);
	    $sql[]="DELETE FROM testplan_tcversions " . 
	           " WHERE tcversion_id IN ({$children_list})";

      $sql[]="DELETE FROM tcversions " .
	           " WHERE id IN ({$children_list})";
                                      
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
*/
function _execution_delete($id,$version_id=TC_ALL_VERSIONS,$children=null)
{
	  $sql = array();
		if( $version_id	== TC_ALL_VERSIONS )
		{ 
    	//	$sql[]="DELETE FROM execution_bugs 
      //    		  WHERE execution_id IN (SELECT id FROM executions 
      //        		                     WHERE tcversion_id IN (SELECT nodes_hierarchy.id 
      //            		                                        FROM nodes_hierarchy 
      //                		                                    WHERE nodes_hierarchy.parent_id = {$id}))";
      //
    	//$sql[]="DELETE FROM executions 
      //		    WHERE tcversion_id IN (SELECT id 
      //    		                       FROM nodes_hierarchy WHERE nodes_hierarchy.parent_id = {$id})";
      
	    $children_list=implode(',',$children);
    	$sql[]="DELETE FROM execution_bugs 
        		  WHERE execution_id IN (SELECT id FROM executions 
            		                     WHERE tcversion_id IN ({$children_list}))";
                    		                                   
      $sql[]="DELETE FROM executions " .
      		   " WHERE tcversion_id IN ({$children_list})";
    }
    else
    {
    		$sql[]="DELETE FROM execution_bugs 
        	  	  WHERE execution_id IN (SELECT id FROM executions 
              		                     WHERE tcversion_id = {$version_id})";
    	
    		$sql[]="DELETE FROM executions " .
        		   " WHERE tcversion_id = {$version_id}";
    }
    foreach ($sql as $the_stm)
    {
    		$result = $this->db->exec_query($the_stm);
    }
}

/*

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
  $ret['id']=-1;
  $ret['status_ok']=0;
  $ret['msg']='ok';
	
	$tcase_info = $this->get_by_id($id);
	if ($tcase_info)
	{
		$new_tc = $this->create_tcase_only($parent_id,$tcase_info[0]['name'],
		                                   $tcase_info[0]['node_order'],TC_AUTOMATIC_ID,
                                       $check_duplicate_name,
                                       'generate_new');

		if ($new_tc['status_ok'])
		{
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
		}
	}
	return($ret);
}
	
	
/* 20060323 - franciscom */
function create_new_version($id,$user_id)
{
  // get a new id
  $tcversion_id = $this->tree_manager->new_node($id,$this->node_types_descr_id['testcase_version']);
	
  // get last version for this test case
  $last_version_info =  $this->get_last_version_info($id);  
  $this->copy_tcversion($last_version_info['id'],$tcversion_id,$last_version_info['version']+1,$user_id);
    
  $ret['id'] = $tcversion_id;
  $ret['msg'] = 'ok';
  return ($ret);
}


/* 20060323 - franciscom */
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


/* 20060323 - franciscom	*/ 
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
	
// 20060313 - franciscom
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
	
  // 20060312 - franciscom
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


// 20070105 - added tc_order in the result
//
// 20061104 - interface changes
//
// id: testcase id
// [version_id]: default TC_ALL_VERSIONS => all versions
// [active_status]: default 'ALL', range: 'ALL','ACTIVE','INACTIVE'
//                  has effect for the following version_id values:
//                  TC_ALL_VERSIONS,TC_LAST_VERSION, version_id is NOT an array 
//  
// [open_status]: default 'ALL'
//
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

//
// args:
//       id: test case id
//       [tcversion_id]: can be a single value or an array
//
// returns a recorset with the following fields
//
// tcversion_id, linked , executed
//
// linked field: will take the following values
//               if $testplan_id == null
//                  NULL if the tc version is not linked to ANY TEST PLAN
//                  tcversion_id if linked
// 
//               if $testplan_id != null
//                  NULL if the tc version is not linked to $testplan_id
//
//
// executed field: will take the following values
//               if $testplan_id == null
//                 NULL if the tc version has not been executed in ANY TEST PLAN
//                 tcversion_id if has executions 
//
//               if $testplan_id != null
//                 NULL if the tc version has not been executed in $testplan_id
//
//
//
// rev :
//      20061030 - franciscom
//      added optional argument testplan_id
// 
//      20060430 - franciscom 
//      added new argument 
//
//      20060326 - franciscom
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
		//$recordset = $this->db->fetchArrayRowsIntoMap($sql,'tcversion_id');
  	return($recordset);
}
// -------------------------------------------------------------------------------


// 20061030 - franciscom
// 1. bug on LEFT JOIN condition
// 2. added order by
//
// 20061020 
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

// 20060423 - franciscom - added order_by_clause argument
// 
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
20060528 - franciscom - interface changes 

*/
function get_executions($id,$version_id,$tplan_id,$build_id,$exec_id_order='DESC',$exec_to_exclude=null)
{
	
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
  $sql="SELECT	NHB.name,NHA.parent_id AS testcase_id, tcversions.*, 
		    users.login AS tester_login,
		    users.first AS tester_first_name, 
		    users.last AS tester_last_name, 
		    e.id AS execution_id, e.status, 
		    e.notes AS execution_notes, e.execution_ts 
	      FROM nodes_hierarchy NHA
        JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id 
        JOIN tcversions ON NHA.id = tcversions.id 
        JOIN executions e ON NHA.id = e.tcversion_id  
                                     AND e.testplan_id = {$tplan_id}
                                     AND e.build_id = {$build_id} 
        LEFT OUTER JOIN users ON e.tester_id = users.id 
        $where_clause 
        ORDER BY NHA.node_order ASC, NHA.parent_id ASC, execution_id {$exec_id_order}";
   

  $recordset = $this->db->fetchArrayRowsIntoMap($sql,'id');
  return($recordset ? $recordset : null);
}




/* 20060330 - franciscom */
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
  $sql="SELECT MAX(e.id) AS execution_id, e.tcversion_id AS tcversion_id
  	    FROM nodes_hierarchy NHA
        JOIN executions e ON NHA.id = e.tcversion_id  
                                     AND e.testplan_id = {$tplan_id}
                                      {$build_id_filter}
                                      AND e.status IS NOT NULL
        $where_clause_1 
        GROUP BY tcversion_id";
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
		    e.notes AS execution_notes, e.execution_ts, e.build_id,
		    builds.name AS build_name 
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

// args:
//       id: test case id
//       [tcversion_id]: can be a single value or an array
//
// returns a recorset with the following fields
//
// tcversion_id, linked 
//
// linked field: will take the following values
//               NULL if the tc version is not linked to any test plan
//               tcversion_id if linked 
//
//
// rev : 
//       20060912 - franciscom
//
function get_version_exec_assignment($tcversion_id,$tplan_id)
{

$sql="SELECT T.tcversion_id AS tcversion_id,T.id AS feature_id," .
     "       UA.user_id,UA.type,UA.status,UA.assigner_id ".
     " FROM testplan_tcversions T " . 
     " LEFT OUTER JOIN user_assignments UA ON UA.feature_id = T.id " .
     " WHERE T.testplan_id={$tplan_id} " .
     " AND   T.tcversion_id = {$tcversion_id} " .
     " AND   (UA.type=" . $this->assignment_types['testcase_execution']['id'] . 
     "        OR UA.type IS NULL) ";

          
$recordset = $this->db->fetchRowsIntoMap($sql,'tcversion_id');
return($recordset);
}

// 20061104 - franciscom
function update_active_status($id,$tcversion_id,$active_status)
{
  // test case version
	$sql = " UPDATE tcversions SET active={$active_status}" .
  			 " WHERE tcversions.id = {$tcversion_id}";
	
  $result = $this->db->exec_query($sql);
	$status_ok = $result ? 1: 0;
	return $status_ok;
}


// ---------------------------------------------------------------------------------------
// Custom field related functions
// ---------------------------------------------------------------------------------------

/*
  function: get_linked_cfields_at_design
            
            
  args: $id
        [$parent_id]
        [$show_on_execution]: default: null
                              1 -> filter on field show_on_execution=1
                              0 or null -> don't filter
        
        
  returns: hash
  
  rev :
        20061231 - franciscom - added $parent_id
*/
function get_linked_cfields_at_design($id,$parent_id=null,$show_on_execution=null) 
{
  $enabled=1;
  $tproject_mgr= new testproject($this->db);
  
  $the_path=$this->tree_manager->get_path_new(!is_null($id) ? $id : $parent_id);
  $path_len=count($the_path);
  $tproject_id=($path_len > 0)? $the_path[$path_len-1]['parent_id'] : $parent_id;

  $cf_map=$this->cfield_mgr->get_linked_cfields_at_design($tproject_id,$enabled,
                                                          $show_on_execution,'testcase',$id);
  return($cf_map);
}

/*
  function: html_table_of_custom_field_inputs
            
            
  args: $id
        [$parent_id]
        [$scope]: 'design','execution'
        [$name_suffix]: must start with '_' (underscore).
                        Used when we display in a page several items
                        (example during test case execution, several test cases)
                        that have the same custom fields.
                        In this kind of situation we can use the item id as name suffix.
                         
        
  returns: html string
  
*/
function html_table_of_custom_field_inputs($id,$parent_id=null,$scope='design',$name_suffix='') 
{
  $cf_smarty='';
  
  if( $scope=='design' )
  {
    $cf_map=$this->get_linked_cfields_at_design($id,$parent_id);
  }
  else
  {
    $cf_map=$this->get_linked_cfields_at_execution($id,$parent_id);
  }
  
  if( !is_null($cf_map) )
  {
    foreach($cf_map as $cf_id => $cf_info)
    {
      $cf_smarty .= '<tr><td class="labelHolder">' . $cf_info['label'] . "</td><td>" .
                    $this->cfield_mgr->string_custom_field_input($cf_info,$name_suffix) .
                    "</td></tr>\n";
    } //foreach($cf_map
  }
  $cf_smarty = "<table>" . $cf_smarty . "</table>";
  return($cf_smarty);
}


/*
  function: html_table_of_custom_field_values
            
            
  args: $id
        [$scope]: 'design','execution'
        [$show_on_execution]: default: null
                              1 -> filter on field show_on_execution=1
                              0 or null -> don't filter
  
        [$execution_id]
        [$testplan_id]

  returns: html string
  
*/
function html_table_of_custom_field_values($id,$scope='design',$show_on_execution=null,
                                           $execution_id=null,$testplan_id=null) 
{

  $cf_smarty='';
  $PID_NO_NEEDED=null;
  
  if( $scope=='design' )
  {
    $cf_map=$this->get_linked_cfields_at_design($id,$PID_NO_NEEDED,$show_on_execution);
  }
  else 
  {
    $cf_map=$this->get_linked_cfields_at_execution($id,$PID_NO_NEEDED,$show_on_execution,
                                                   $execution_id,$testplan_id);
  }
    
  if( !is_null($cf_map) )
  {
    foreach($cf_map as $cf_id => $cf_info)
    {
      // if user has assigned a value, then node_id is not null
      if($cf_info['node_id'])
      {
        $cf_smarty .= '<tr><td class="labelHolder">' . $cf_info['label'] . "</td><td>" .
                      $this->cfield_mgr->string_custom_field_value($cf_info,$id) .
                      "</td></tr>\n";
      }
    }
  }
  $cf_smarty = "<table>" . $cf_smarty . "</table>";
  return($cf_smarty);
} // function end


/*
  function: get_linked_cfields_at_execution
            
            
  args: $id
        [$parent_id]
        [$show_on_execution]: default: null
                              1 -> filter on field show_on_execution=1
                              0 or null -> don't filter
        [$execution_id]
        [$testplan_id]
        
  returns: hash
  
  rev :
        20061231 - franciscom - added $parent_id
*/
function get_linked_cfields_at_execution($id,$parent_id=null,$show_on_execution=null,
                                         $execution_id=null,$testplan_id=null) 
{
  $enabled=1;
  $tproject_mgr= new testproject($this->db);
  
  $the_path=$this->tree_manager->get_path_new(!is_null($id) ? $id : $parent_id);
  $path_len=count($the_path);
  $tproject_id=($path_len > 0)? $the_path[$path_len-1]['parent_id'] : $parent_id;


  // Warning:
  // I'm setting node type to test case, but $id is the tcversion_id, because
  // execution data is related to tcversion NO testcase
  //
  $cf_map=$this->cfield_mgr->get_linked_cfields_at_execution($tproject_id,$enabled,'testcase',
                                                             $id,$execution_id,$testplan_id);
  return($cf_map);
}





} // end class
?>