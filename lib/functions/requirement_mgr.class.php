<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: requirement_mgr.class.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2007/11/09 21:45:21 $ by $Author: franciscom $
 * @author Francisco Mancardi
 *
 * Manager for requirements.
 * Requirements are children of a requirement specification (requirements container)
 *
 *
 * 20060908 - franciscom - 
*/
//class requirement_mgr extends requirement_spec_mgr
class requirement_mgr
{
	var $db;
  var $cfield_mgr;
  var $object_table='requirements';
  var $requirement_spec_table='req_specs';
  
  /*
    function: requirement_mgr
              contructor 

    args: db: reference to db object
    
    returns: instance of requirement_mgr

  */
	function requirement_mgr(&$db) 
	{
		$this->db = &$db;
		$this->cfield_mgr=new cfield_mgr($this->db);
	}


  /*
    function: get_by_id
              
  
    args : id: test project
    
    returns: null if query fails
             map with test project info
  
  */
  function get_by_id($id)
  {
  	$sql = " SELECT REQ.*, REQ_SPEC.testproject_id, REQ_SPEC.title AS req_spec_title " .
  	       " FROM {$this->object_table} REQ, {$this->requirement_spec_table} REQ_SPEC" . 
  	       " WHERE REQ.srs_id = REQ_SPEC.id " .
  	       " AND REQ.id = {$id}";
  	$recordset = $this->db->get_recordset($sql);
  	return ($recordset ? $recordset[0] : null);
  }

  /*
    function: 

    args :
    
    returns: 

  */
  function create($srs_id,$reqdoc_id,$title, $scope,  $user_id, 
                  $status = TL_REQ_STATUS_VALID, $type = TL_REQ_STATUS_NOT_TESTABLE)
  {
  
	  $result['id'] = 0;
	  $result['status_ok'] = 0;
	  $result['msg'] = 'ko';
	
	  $field_size = config_get('field_size');

	  $reqdoc_id = trim_and_limit($reqdoc_id,$field_size->req_docid);
	  $title = trim_and_limit($title,$field_size->req_title);
		
	  $result = $this->check_basic_data($srs_id,$title,$reqdoc_id);
	  if($result['status_ok'])
	  {
		  $db_now = $this->db->db_now();
		  $sql = " INSERT INTO {$this->object_table} " .
		         " (srs_id, req_doc_id, title, scope, status, type, author_id, creation_ts)" .
			  	   " VALUES (" . $srs_id . ",'" . $this->db->prepare_string($reqdoc_id) . "','" . 
			  	    $this->db->prepare_string($title) . "','" . $this->db->prepare_string($scope) . "','" . 
			  	    $this->db->prepare_string($status) . "','" . $this->db->prepare_string($type) . "',"  .
			  	    "{$user_id}, {$db_now})";

		  if (!$this->db->exec_query($sql))
		  {
		 	  $result['msg'] = lang_get('error_inserting_req');
		  } 	
		  else
		  {
			  $result['id']=$this->db->insert_id($this->object_table);
  	    $result['status_ok'] = 1;
	      $result['msg'] = 'ok';
		  }
	  }
	  return $result; 
  } // function end


  /*
    function: 

    args :
    
    returns: 

  */

function update($id,$reqdoc_id,$title, $scope, $user_id, $status, $type,$skip_controls=0)
{
	$result = 'ok';
	$db_now = $this->db->db_now();
	$field_size=config_get('field_size');
	
	// get SRSid, needed to do controls
	$rs=$this->get_by_id($id);
  $srs_id=$rs['srs_id'];
	
	$reqdoc_id=trim_and_limit($reqdoc_id,$field_size->req_docid);
	$title=trim_and_limit($title,$field_size->req_title);

  $chk=$this->check_basic_data($srs_id,$title,$reqdoc_id,$id);
 
	if($chk['status_ok'] || $skip_controls)
	{
		$sql = "UPDATE requirements SET title='" . $this->db->prepare_string($title) . "', " .
		       " scope='" . $this->db->prepare_string($scope) . "', " .
		       " status='" . $this->db->prepare_string($status) . "', " .
		       " type='" . $this->db->prepare_string($type) . "', " .
		       " modifier_id={$user_id}, req_doc_id='" . $this->db->prepare_string($reqdoc_id) . "', " .
		       " modification_ts={$db_now}  WHERE id={$id}";
			
		if (!$this->db->exec_query($sql))
		 	$result = lang_get('error_updating_req');
	}
	else
	{
	  $result=$chk['msg']; 
	}
	
	return $result; 
}

/*
  function: 

  args :
  
  returns: 

*/
function delete($id)
{
	
	// Custom Fields
	
	// delete dependencies with test specification
	$sql = "DELETE FROM req_coverage WHERE req_id=" . $id;
	$result = $this->db->exec_query($sql); 

  
	if ($result)
	{
		$result = deleteAttachmentsFor($this->db,$id,"requirements");
  }

	if ($result)
	{
		$sql = "DELETE FROM {$this->object_table} WHERE id={$id}";
		$result = $this->db->exec_query($sql); 
	}

	if (!$result)
		$result = lang_get('error_deleting_req');
	else
		$result = 'ok';
		
	return $result; 
}







/** collect coverage of Requirement 
 * @param string $req_id ID of req.
 * @return assoc_array list of test cases [id, title]
 */
function get_coverage($id)
{
	$sql = "SELECT nodes_hierarchy.id,nodes_hierarchy.name 
	        FROM nodes_hierarchy, req_coverage
			    WHERE req_coverage.testcase_id = nodes_hierarchy.id
			    AND  req_coverage.req_id={$id}"; 
	return selectData($db,$sql);
}






  /*
    function: 

    args :
    
    returns: 

  */
function check_basic_data($srs_id,$title,$reqdoc_id,$id = null)
{
	$req_cfg = config_get('req_cfg');
	
	$my_srs_id=$srs_id;
	
	$ret['status_ok'] = 1;
	$ret['msg'] = '';
	
	if (!strlen($title))
	{
		$ret['status_ok'] = 0;
		$ret['msg'] = lang_get("warning_empty_req_title");
	}
	
	if (!strlen($reqdoc_id))
	{
		$ret['status_ok'] = 0;
		$ret['msg'] .=  " " . lang_get("warning_empty_reqdoc_id");
	}
	
	if($ret['status_ok'])
	{
		$ret['msg'] = 'ok';
		
		if($req_cfg->reqdoc_id->is_system_wide)
		{
			// req doc id MUST BE unique inside the whole DB
      $my_srs_id=null;
		}
		$rs = $this->get_by_docid($reqdoc_id,$my_srs_id);

		
		if(!is_null($rs) && (is_null($id) || !isset($rs[$id])))
		{
			$ret['msg'] = lang_get("warning_duplicate_reqdoc_id");
			$ret['status_ok'] = 0;  		  
		}
	} 
	
	return $ret;
}



/*
  function: get_by_docid

  args:
  
  returns: 

*/
function get_by_docid($reqdoc_id,$srs_id=null)
{
	$sql = "SELECT * FROM requirements " .
	       " WHERE req_doc_id='" . $this->db->prepare_string($reqdoc_id) . "'";

  if( !is_null($srs_id) )
  {
    $sql .=	 " AND srs_id={$srs_id}";
  }

	return($this->db->fetchRowsIntoMap($sql,'id'));
}

/*
  function: get_by_title

  args:
  
  returns: 

*/
function get_by_title($title,$ignore_case=0)
{
	$output = array();
	$sql = "SELECT * FROM {$this->object_table} ";

  $the_title=$this->db->prepare_string($title);	
	
	if($ignore_case)
	{
	  $sql .= " WHERE UPPER(title)='" . strupper($the_title) . "'";
	}
	else
	{
	   $sql .= " WHERE title='{$the_title}'";
	}       
	       
	$result = $this->db->exec_query($sql);
	if (!empty($result)) {
		$output = $this->db->fetch_array($result);
	}
	
	return $output;
} // function end



/*
  function: 

  args :
  
  returns: 

*/
function create_tc_from_requirement($mixIdReq,$srs_id, $user_id)
{
  $tcase_mgr = new testcase($this->db);
  $tree_mgr=New tree($this->db);
  
 	define('DEFAULT_TC_ORDER',0);
  define('AUTOMATIC_ID',0);
  define('NO_KEYWORDS','');
  
	$g_req_cfg = config_get('req_cfg');
	$g_field_size = config_get('field_size');
	$auto_testsuite_name = $g_req_cfg->default_testsuite_name;
  $node_descr_type=$tree_mgr->get_available_node_types();
  $empty_steps='';
  $empty_results='';

  
	$output = null;
	if (is_array($mixIdReq)) {
		$arrIdReq = $mixIdReq;
	} else {
		$arrIdReq = array($mixIdReq);
	}
	if ( $g_req_cfg->use_req_spec_as_testsuite_name )
	{
	  // SRS Title
   	$sql = " SELECT * FROM {$this->requirement_spec_table} WHERE id = {$srs_id}";
  	$arrSpec = $this->db->get_recordset($sql);
	  $testproject_id=$arrSpec[0]['testproject_id'];
	  $auto_testsuite_name = substr($arrSpec[0]['title'],0,$g_field_size->testsuite_name);
	}
	
	// find container with the following characteristics:
	// 1. testproject_id is its father
	// 2. has the searched name
	$sql="SELECT id FROM nodes_hierarchy NH " .
	     " WHERE name='" . $this->db->prepare_string($auto_testsuite_name) . "' " .
	     " AND parent_id=" . $testproject_id . " " .
	     " AND node_type_id=" . $node_descr_type['testsuite'];
	             
	          
	$result = $this->db->exec_query($sql);
  if ($this->db->num_rows($result) == 1) {
		$row = $this->db->fetch_array($result);
		$tsuite_id = $row['id'];
	}
	else {
		// not found -> create
		tLog('test suite:' . $auto_testsuite_name . ' was not found.');
    $tsuite_mgr=New testsuite($this->db);
    $new_tsuite=$tsuite_mgr->create($testproject_id,$auto_testsuite_name,$g_req_cfg->testsuite_details);
    $tsuite_id=$new_tsuite['id'];
	}

	//create TC
	foreach ($arrIdReq as $execIdReq) 
	{
		$reqData = $this->get_by_id($execIdReq);

	  $tcase=$tcase_mgr->create($tsuite_id,$reqData['title'],
	                            $g_req_cfg->testcase_summary_prefix . $reqData['scope'] , 
	                            $empty_steps,$empty_results,$user_id,NO_KEYWORDS,
	                            DEFAULT_TC_ORDER,AUTOMATIC_ID,
		                          config_get('check_names_for_duplicates'),
		                          config_get('action_on_duplicate_name'));

		// create coverage dependency
		if (!$this->assign_to_tcase($reqData['id'],$tcase['id']) ) {
			$output = 'Test case: ' . $reqData['title'] . "was not created </br>";
		}
	}

	return (!$output) ? 'ok' : $output;
}


/*
  function: 

  args :
  
  returns: 

*/
function assign_to_tcase($req_id,$testcase_id)
{
	$output = 0;
	
	if ($testcase_id && $req_id)
	{
		$sql = " SELECT COUNT(*) AS num_cov FROM req_coverage " .
		       " WHERE req_id={$req_id}  AND testcase_id={$testcase_id}";
		$result = $this->db->exec_query($sql);

    $row = $this->db->fetch_array($result);
		if ($row['num_cov'] == 0)
		{
			// create coverage dependency
			$sql = "INSERT INTO req_coverage (req_id,testcase_id) " .
			       "VALUES ({$req_id},{$testcase_id})";
			             
			$result = $this->db->exec_query($sql);
			if ($this->db->affected_rows() == 1)
			{
				$output = 1;
			}
		}
		else
		{
			$output = 1;
		}
	}

	return $output;
}

/*
  function: 

  args :
  
  returns: 

*/
function unassign_from_tcase($req_id,$testcase_id)
{
	$output = 0;
	$sql = " DELETE FROM req_coverage " .
	       " WHERE req_id={$req_id} " .
	       " AND testcase_id={$testcase_id}";
	             
	$result = $this->db->exec_query($sqlReqCov);

	if ($this->db->affected_rows() == 1)
	{
		$output = 1;
	}
	return $output;
}


/*
  function: 

  args :
  
  returns: 

*/
function get_relationships($req_id)
{
	$sql = " SELECT nodes_hierarchy.id,nodes_hierarchy.name " .
	       " FROM nodes_hierarchy, req_coverage " .
			   " WHERE req_coverage.testcase_id = nodes_hierarchy.id " .
			   " AND  req_coverage.req_id={$req_id}"; 
			    
	return ($this->db->get_recordset($sql));	
}





/*
  function: 

  args :
  
  returns: 

*/
function check_title($title)
{
  $ret=array('status_ok' => 1, 'msg' => 'ok');
   
	if (strlen($title) == 0)
	{
	  $ret['status_ok']=0;
		$ret['msg'] = lang_get("warning_empty_req_title");
  }
  		
	return $ret;
}

/*
  function: 

  args :
          $nodes: array with req_id in order
  returns: 

*/
function set_order($map_id_order)
{

	foreach($map_id_order as $order => $node_id)
	{
		$order = abs(intval($order));
		$node_id = intval($node_id);
	  $sql = " UPDATE {$this->object_table} " .
	         " SET node_order = {$order} WHERE id = {$node_id}";
	  $result = $this->db->exec_query($sql);
	}

}






// ---------------------------------------------------------------------------------------
// Custom field related functions
// ---------------------------------------------------------------------------------------

/*
  function: get_linked_cfields
            Get all linked custom fields.
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
function get_linked_cfields($id,$parent_id=null) 
{
	$enabled = 1;

	if (!is_null($id) && $id > 0)
	{
    $req_info = $this->get_by_id($id);
	  $tproject_id = $req_info['testproject_id'];
	} 
	else
	{
	  $tproject_id = $parent_id;
	}
	$cf_map = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,$enabled,null,
	                                                          'requirement',$id);
	
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
function html_table_of_custom_field_inputs($id,$parent_id=null,$name_suffix='') 
{
	$cf_smarty = '';
  $cf_map = $this->get_linked_cfields($id,$parent_id);
	
	if(!is_null($cf_map))
	{
		$cf_smarty = "<table>";
		foreach($cf_map as $cf_id => $cf_info)
		{
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
function html_table_of_custom_field_values($id) 
{
	$cf_smarty = '';
	$PID_NO_NEEDED = null;
  
  $cf_map = $this->get_linked_cfields($id,$PID_NO_NEEDED);

    
	if(!is_null($cf_map))
	{
		foreach($cf_map as $cf_id => $cf_info)
		{
			// if user has assigned a value, then node_id is not null
			if($cf_info['node_id'])
			{
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
    function: values_to_db
              write values of custom fields.
              
    args: $hash: 
          key: custom_field_<field_type_id>_<cfield_id>. 
               Example custom_field_0_67 -> 0=> string field
          
          $node_id:           
          
          [$cf_map]:  hash -> all the custom fields linked and enabled
                              that are applicable to the node type of $node_id.
                              
                              For the keys not present in $hash, we will write
                              an appropriate value according to custom field
                              type.
                              
                              This is needed because when trying to udpate
                              with hash being $_REQUEST, $_POST or $_GET
                              some kind of custom fields (checkbox, list, multiple list)
                              when has been deselected by user.
                              
          
    rev:
  */
  function values_to_db($hash,$node_id,$cf_map=null,$hash_type=null)
  {
    $this->cfield_mgr->design_values_to_db($hash,$node_id,$cf_map,$hash_type);
  }


} // class end
?>