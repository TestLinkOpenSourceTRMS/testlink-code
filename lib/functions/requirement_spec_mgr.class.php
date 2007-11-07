<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: requirement_spec_mgr.class.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/11/07 07:32:51 $ by $Author: franciscom $
 * @author Francisco Mancardi
 *
 * Manager for requirement specification (requirement container)
 *
 *
 * 20060908 - franciscom - 
*/
class requirement_spec_mgr
{
	var $db;
	var $tree_manager;
  var $cfield_mgr;

  var $import_file_types = array("csv" => "CSV",
                                 "csv_doors" => "CSV (Doors)", 
                                 "XML" => "XML");
                                 
  var $export_file_types = array("XML" => "XML");

  /*
    function: requirement_spec_mgr
              contructor 

    args: db: reference to db object
    
    returns: instance of requirement_spec_mgr

  */
	function requirement_spec_mgr(&$db) 
	{
		$this->db = &$db;
	  $this->tree_manager =  new tree($this->db);
		$this->cfield_mgr=new cfield_mgr($this->db);
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
    function: get_by_id
              
  
    args : id: test project
    
    returns: null if query fails
             map with test project info
  
  */
  function get_by_id($id)
  {
  	$sql = " SELECT * FROM req_specs WHERE id = {$id}";
  	$recordset = $this->db->get_recordset($sql);
  	return ($recordset ? $recordset[0] : null);
  }


  /*
    function: update

    args:
    
    returns: 

  */
  function update($id,$title, $scope, $countReq, $user_id, 
                  $type = TL_REQ_STATUS_NOT_TESTABLE)
  {
	  $result['status_ok'] = 1;
	  $result['msg'] = 'ok';

    $title=trim_and_limit($title);
    	
	  $db_now = $this->db->db_now();
		$sql = " UPDATE req_specs SET title='" . $this->db->prepare_string($title) . "', " .
		       " scope='" . $this->db->prepare_string($scope) . "', " .
		       " type='" . $this->db->prepare_string($type) . "', " .
		       " total_req ='" . $this->db->prepare_string($countReq) . "', " .
		       " modifier_id={$user_id},modification_ts={$db_now} WHERE id={$id}";
		       
		if (!$this->db->exec_query($sql))
		{
			$result['msg']=lang_get('error_updating_reqspec');
  	  $result['status_ok'] = 0;
	  }
	  
	  return $result; 
  }



  /*
    function: delete
              deletes:
              Requirements spec
              Requirements spec custom fields values
              Requirements ( Requirements spec children )
              Requirements custom fields values

    args: id: requirement spec id
    
    returns: 

  */
  function delete($id)
  {
   
    // Delete Custom fields
    $this->cfield_mgr->remove_all_design_values_from_node($id);
    
  	// delete requirements and coverage
  	$requirements_info = $this->get_requirements($id);
    if( !is_null($requirements_info) )
    {  	
  	  $the_reqs=array_keys($requirements_info);
  	  $this->cfield_mgr->remove_all_design_values_from_node($the_reqs);
    }
  	
  	$sql="DELETE FROM requirements WHERE srs_id={$id}";
  	$this->db->exec_query($sql); 
  		
  	// delete specification itself
  	$sql = "DELETE FROM req_specs WHERE id={$id}";
  	$result = $this->db->exec_query($sql); 
  	if($result)
  	{
  		$result = 'ok';
  	}
  	else
  	{
  		$result = 'The DELETE SRS request fails.';
  	}
  	return $result; 
  } // function end



  /*
    function: 

    args :
    
    returns: 

  */
  function get_requirements($id,$order_by=" ORDER BY node_order,req_doc_id,title")
  {
		$sql = "SELECT * FROM requirements WHERE srs_id={$id}"; 
	  $sql .= $order_by;
	  return $this->db->fetchRowsIntoMap($sql,'id');
  }


// ---------------------------------------------------------------------------------------
// Custom field related functions
// ---------------------------------------------------------------------------------------

/*
  function: get_linked_cfields
            Get all linked custom fields.
            Remember that custom fields are defined at system wide level, and
            has to be linked to a testproject, in order to be used.
            
            
  args: id: requirement spec id
        [parent_id]: node id of parent testproject of requirement spec.
                     need to understand to which testproject requirement spec belongs.
                     this information is vital, to get the linked custom fields.
                     Presence /absence of this value changes starting point
                     on procedure to build tree path to get testproject id.
                     
                     null -> use requirement spec id as starting point.
                     !is_null -> use this value as starting point.        
                             
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
	  $req_spec_info = $this->get_by_id($id); 
	  $tproject_id = $req_spec_info['testproject_id'];
	} 
	else
	{
	  $tproject_id = $parent_id;
	}
	$cf_map = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,$enabled,null,
	                                                          'requirement_spec',$id);
	
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