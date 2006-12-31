<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: cfield_mgr.class.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2006/12/31 16:15:45 $  $Author: franciscom $
 * @author franciscom
 *
 * 20061225 - franciscom - 
 *
**/

class cfield_mgr
{
	var $db;
	var $tree_manager;
	
	// I'm using the same codes used by Mantis
  var $custom_field_types = array(0=>'string',
                                  1=>'numeric',
                                  2=>'float',
                                  3=>'enum',
                                  4=>'email',
                                  5=>'checkbox',
                                  6=>'list',
                                  7=>'multiselection list',
                                  8=>'date');

  // only the types listed here can have custom fields
	var $node_types = array('testproject',
	                        'testsuite',
	                        'testcase',
	                        'testplan');

  
  
  /*
    function: cfield_mgr
              class constructor
  */
	function cfield_mgr(&$db)
	{
		$this->db = &$db;	
		$this->tree_manager = new tree($this->db);
	}

  /*
    function: get_available_types
    
    returns: hash with custom field available types
             key: numeric id
             value: short description
  */
	function get_available_types() 
	{
    return($this->custom_field_types);
  }

  /*
    function: get_allowed_nodes
    
    returns: hash with node types id, that can have custom fields.
             key:   short description (node_types.description)
             value: node_type_id      (node_types.id)
  */
	function get_allowed_nodes() 
	{
    $allowed_nodes=array();
    $tl_node_types=$this->tree_manager->get_available_node_types();
    foreach($this->node_types as $verbose_type )
    {
      $allowed_nodes[$verbose_type]=$tl_node_types[$verbose_type];  
    }
    return($allowed_nodes);
  }

  /*
    function: get_linked_cfields_at_design
              returns information about custom fields that can be used 
              at least at design time, with the value assigned (is any has been assigned). 
    

    $tproject_id: needed because is possible to associate/link 
                  a different set of custom field for every test project
                  
    $enabled    : 1 -> get custom fields that are has been configured
                       to be shown during specification design AND are enabled.

                       Remember that also exist custom fields
                       that can be only used during TEST CASE EXECUTION.

    
    [$node_type]: verbose id ('testcase', 'testsuite', etc) of a node type.
                  custom fields are linked also to different node types.
                  Example:
                  I can define a custom field "Aspect" with values
                  Performace, Usability and wnat use it only for test suites.
                   
    [$node_id]: identification of a node/element on node hierarchy.
                Needed when I want to get the value of custom fields 
                linked to a node.
                Example:
                Have two test cases (ID:9999, ID:89800), and want to get
                the value assigned to custom field "Operating System".
                I will do two calls to this method.
    
    returns: hash
             key: custom field id
                         
  */
  function get_linked_cfields_at_design($tproject_id,$enabled,$node_type=null,$node_id=null) 
  {
    $additional_join="";
    $additional_table="";
    $additional_values="";
  
    if( !is_null($node_type) )
    {
   		$hash_descr_id = $this->tree_manager->get_available_node_types();
      $node_type_id=$hash_descr_id[$node_type]; 
    
      $additional_join  .= " JOIN cfield_node_types CFNT ON CFNT.field_id=CF.id " .
                           " AND CFNT.node_type_id={$node_type_id} ";
    }
    if( !is_null($node_id) )
    {
      $additional_values .= ",CFDV.value AS design_value,CFDV.node_id AS node_id";
      $additional_join .= " LEFT OUTER JOIN cfield_design_values CFDV ON CFDV.field_id=CF.id " .
                          " AND CFDV.node_id={$node_id} ";
    }
    
    $sql="SELECT CF.*,CFTP.display_order" .
         $additional_values .
         " FROM custom_fields CF " .
         " JOIN cfield_testprojects CFTP ON CFTP.field_id=CF.id " .
         $additional_join .
         " WHERE CFTP.testproject_id={$tproject_id} " .
         " AND   CF.show_on_design=1     " . 
         " AND   CF.enable_on_design={$enabled} ";
         " ORDER BY display_order ";
    
    $map = $this->db->fetchRowsIntoMap($sql,'id');     
    return($map);                                 
  }


	/*
    Very Important: this code is based on Mantis code.
    
    function: string_custom_field_input
              returns an string with the html need to display the custom field.
              
    args: $p_field_def: contains the definition of the custom field 
                        (including it's field id)
              
  */
	function string_custom_field_input($p_field_def)
	{

		$str_out='';
		$t_id = $p_field_def['id'];
		
	  $t_custom_field_value = $p_field_def['default_value'];	
	  if( isset($p_field_def['design_value']) )
		{
		  $t_custom_field_value = $p_field_def['design_value'];   
		}
    
		$t_custom_field_value = htmlspecialchars( $t_custom_field_value );
    //echo "<pre>debug 20061230 " . __FUNCTION__ . " --- "; print_r($t_custom_field_value); echo "</pre>";

		switch ($this->custom_field_types[$p_field_def['type']]) 
		{
  		case 'list':
   			$t_values = explode( '|', $p_field_def['possible_values']);

        $t_list_size =1;
  			$str_out .='<select name="custom_field_' . $t_id . '"';
  			$str_out .= ' size="' . $t_list_size . '">';
  
  			$t_selected_values = explode( '|', $t_custom_field_value );
   			foreach( $t_values as $t_option ) {
  				if( in_array( $t_option, $t_selected_values ) ) {
   					$str_out .='<option value="' . $t_option . '" selected> ' . $t_option . '</option>';
   				} else {
   					$str_out .='<option value="' . $t_option . '">' . $t_option . '</option>';
   				}
   			}
   			$str_out .='</select>';
			  break;

  		case 'string':
  		case 'email':
  		case 'float':
  		case 'numeric':
  			$str_out .= '<input type="text" name="custom_field_' . $t_id . '" size="80"';
			  if( 0 < $p_field_def['length_max'] ) 
			  {
				  $str_out .= ' maxlength="' . $p_field_def['length_max'] . '"';
			  } 
			  else 
			  {
				   $str_out .= ' maxlength="255"';
			  }
			  $str_out .= ' value="' . $t_custom_field_value .'"></input>';
			  break ;

  		
  		break;


  	}		
    return ($str_out);	
	} //function end


  /*
    function: design_values_to_db
              write values of custom fields that are used at design time.
              
    args: $hash: 
          key: custom_field_<cfield_id>. Example custom_field_67
          
          $node_id:           
  */
  function design_values_to_db($hash,$node_id)
  {                                  
    $cf_prefix='custom_field_';
    $len_cfp=strlen($cf_prefix);
    $cfid_pos=2;
    foreach($hash as $key => $value)
    {
      if( strncmp($key,$cf_prefix,$len_cfp) == 0 )
      {
        $dummy=explode('_',$key);
        $field_id=$dummy[$cfid_pos];

        // do I need to update or insert this value?
        $sql = "SELECT value FROM cfield_design_values " .
    		 			 " WHERE field_id={$field_id} AND	node_id={$node_id}";

	      $result = $this->db->exec_query($sql);
	      if($this->db->num_rows( $result ) > 0 ) 
	      {
	        
	        $sql = "UPDATE cfield_design_values " .
	               " SET value='{$value}' " .
    		 			   " WHERE field_id={$field_id} AND	node_id={$node_id}";
        }  
        else 
        {
          # Remark got from Mantis code:
			    # Always store the value, even if it's the dafault value
			    # This is important, as the definitions might change but the
			    #  values stored with a bug must not change
			    $sql = "INSERT INTO cfield_design_values " .
						     " ( field_id, node_id, value ) " .
					       " VALUES	( {$field_id}, {$node_id}, '{$value}' )";
			  }  
        $this->db->exec_query($sql);

	    } //if( strncmp  
    } //foreach($hash
    
  } //function end



  /*
    function: remove_all_design_values_from_node
              remove the values of ALL custom fields linked to 
              a node. (example test case 5555)
              
    args: $node_id:           
  */
  function remove_all_design_values_from_node($node_id)
  {                                  
    $sql="DELETE FROM cfield_design_values" .
         " WHERE node_id={$node_id}";
    $this->db->exec_query($sql);
  } //function end
  
  
  /*
    function: get_all
              get the definition of all custom field defined in the system,
              or all custom fields with id not included in $id2exclude.         
              
    args: [$id2exclude]: array with custom field ids           
    
    returns: hash:
             key: custom field id
    
  */
  function get_all($id2exclude=null) 
  {
    $not_in_clause="";
    if( !is_null($id2exclude) )
    {
      $not_in_clause=" AND CF.id NOT IN (" .implode(',',$id2exclude) .") ";
    }
    $sql="SELECT CF.*,NT.description AS node_description,NT.id AS node_type_id " .
         " FROM custom_fields CF, " .
         "     cfield_node_types CFNT, " .
         "     node_types NT " .
         " WHERE CF.id=CFNT.field_id " .
         " AND NT.id=CFNT.node_type_id " .
         $not_in_clause .
         " ORDER BY CF.name";
    // $map = $this->db->fetchArrayRowsIntoMap($sql,'id');     
    $map = $this->db->fetchRowsIntoMap($sql,'id');     
    return($map);                                 
  }

  /*
    function: get_linked_to_testproject
              get definition of all custom fields linked to a test project.
                   
              
    args: $tproject_id
          [$active]: if not null will add the following filter " AND CFTP.active={$active}"           
    
    returns: hash:
             key: custom field id
    
  */
  function get_linked_to_testproject($tproject_id,$active=null)
  {
    $sql="SELECT CF.*,NT.description AS node_description,NT.id AS node_type_id, " .
         "       CFTP.display_order, CFTP.active " .  
         " FROM custom_fields CF, " .
         "      cfield_testprojects CFTP, " .
         "      cfield_node_types CFNT, " .
         "      node_types NT " .
         " WHERE CF.id=CFNT.field_id " .
         " AND   CF.id=CFTP.field_id " .
         " AND   NT.id=CFNT.node_type_id " .
         " AND   CFTP.testproject_id={$tproject_id} ";
     
    if( !is_null($active) )    
    {
      $sql .= " AND CFTP.active={$active} ";
    }
    $sql .= " ORDER BY display_order, CF.name";
    
    $map = $this->db->fetchRowsIntoMap($sql,'id');     
    return($map);                                 
  }
  
  
  /*
    function: link_to_testproject
              
                   
              
    args: $tproject_id
          $cfields_id: array()
    
    returns: -
  */
  function link_to_testproject($tproject_id,$cfield_ids)
  {
    foreach($cfield_ids as $field_id)
    {
      $sql="INSERT INTO cfield_testprojects " .
           " (testproject_id,field_id) " .
           " VALUES({$tproject_id},{$field_id})"; 
      
      $this->db->exec_query($sql);     
    }
  } //function end


  /*
    function: set_active_for_testproject
              set the value of active field
                   
              
    args: $tproject_id
          $cfields_id: array()
          $active_val: 1/0
    
    returns: -
  */
  function set_active_for_testproject($tproject_id,$cfield_ids,$active_val)
  {
    if( !is_null($cfield_ids) )
    {
      foreach($cfield_ids as $field_id)
      {
        $sql="UPDATE cfield_testprojects " .
             " SET active={$active_val} " .
             " WHERE testproject_id={$tproject_id} " .
             " AND field_id={$field_id}"; 
        
        $this->db->exec_query($sql);     
      }
    }
  } //function end



  /*
    function: unlink_from_testproject
              
    args: $tproject_id
          $cfields_id: array()
    
    returns: -
  */
  function unlink_from_testproject($tproject_id,$cfield_ids)
  {
    // Step 1: set to active
    $this->set_active($tproject_id,$cfield_ids,1);
    
    
    // Step 2: get all node id that has been linked
    //         to this cfields at design time
    if( is_array($cfield_ids) )
    {
       $filter=" WHERE field_id IN (" . implode(',',$cfield_ids) . ")";  
    }
    else
    {
       $filter=" WHERE field_id = {$cfield_ids} ";  
    }
    
    $sql=" SELECT node_id,field_id " .
         " FROM cfield_design_values {$filter}";
    $node_map=$this->db->fetchArrayRowsIntoMap($sql,'node_id');
    
    // now I need to get the path for every node
    //echo "<pre>debug 20061227 " . __FUNCTION__ . " --- "; print_r($node_map); echo "</pre>";
    if(!is_null($node_map))
    {
      $node_list=array_keys($node_map);  
      //echo "<pre>debug 20061227 \$node_list" . __FUNCTION__ . " --- "; print_r($node_list); echo "</pre>";
      $node2del=null;
      foreach($node_list as $node_id)
      {
        $the_path=$this->tree_manager->get_path_new($node_id);
        if( !is_null($the_path) )
        {
          $root=array_pop($the_path);
          //echo "<pre>debug 20061227 \$root" . __FUNCTION__ . " --- "; print_r($root); echo "</pre>";
          if($root['parent_id'] == $tproject_id)
          {
            $node2del[]=$node_id;  
          }
        }
      }
      //echo "<pre>debug 20061227 \$node2del" . __FUNCTION__ . " --- "; print_r($node2del); echo "</pre>";
      
    }
  } //function end



  /*
    function: get_by_name
              get custom field definition 
              
    args: $name: custom field name
    
    returns: hash
  */
	function get_by_name($name) 
	{
	  $my_name=$this->db->prepare_string(trim($name));
	  
	  $sql="SELECT CF.*, CFNT.node_type_id" .
	       " FROM custom_fields CF, cfield_node_types CFNT" .
	       " WHERE CF.id=CFNT.field_id " .
	       " AND   name='{$my_name}' ";
    return($this->db->fetchRowsIntoMap($sql,'id'));
  }

  /*
    function: get_by_id
              get custom field definition 
              
    args: $id: custom field id
    
    returns: hash
    
  */
	function get_by_id($id) 
	{
	  $sql="SELECT CF.*, CFNT.node_type_id" .
	       " FROM custom_fields CF, cfield_node_types CFNT" .
	       " WHERE CF.id=CFNT.field_id " .
	       " AND   CF.id={$id} ";
    return($this->db->fetchRowsIntoMap($sql,'id'));
  }



  /*
    function: create a custom field
              
    args: $hash: 
          keys   name
                 label
                 type
                 possible_values
                 show_on_design 
                 enable_on_design 
                 show_on_execute 
                 enable_on_execute
                 node_type_id
    
    returns: -
  */
	function create($cf) 
  {
    $ret = array('status_ok' => 0, 'id' => 0, 'msg' => 'ko');
	
    $my_name=$this->db->prepare_string($cf['name']);
    $my_label=$this->db->prepare_string($cf['label']);
    $my_pvalues=$this->db->prepare_string($cf['possible_values']);
    
    
    $sql="INSERT INTO custom_fields " .
         " (name,label,type,possible_values, " .
         "  show_on_design,enable_on_design, " . 
         "  show_on_execution,enable_on_execution) " .
         " VALUES('{$my_name}','{$my_label}',{$cf['type']},'{$my_pvalues}', " .
         "        {$cf['show_on_design']},{$cf['enable_on_design']}," .
         "        {$cf['show_on_execution']},{$cf['enable_on_execution']})";
    $result=$this->db->exec_query($sql);

   	if ($result)
  	{
  	  $field_id=$this->db->insert_id();
  	  
      $sql="INSERT INTO cfield_node_types " .
           " (field_id,node_type_id) " .
           " VALUES({$field_id},{$cf['node_type_id']}) ";
      $result=$this->db->exec_query($sql);
    }       

    if ($result)
	  { 
       $ret = array('status_ok' => 1, 'id' => $field_id, 'msg' => 'ok');
    }
    return($ret);
  } //function end


  /*
    function: update a custom field
              
    args: $hash: 
          keys   name
                 label
                 type
                 possible_values
                 show_on_design 
                 enable_on_design 
                 show_on_execute 
                 enable_on_execute
                 node_type_id
    
    returns: -
  */
	function update($cf) 
  {
    $my_name=$this->db->prepare_string($cf['name']);
    $my_label=$this->db->prepare_string($cf['label']);
    $my_pvalues=$this->db->prepare_string($cf['possible_values']);
    
    $sql="UPDATE custom_fields " .
         " SET name='{$my_name}',label='{$my_label}'," .
         "     type={$cf['type']},possible_values='{$my_pvalues}'," .
         "     show_on_design={$cf['show_on_design']}," .
         "     enable_on_design={$cf['enable_on_design']}," . 
         "     show_on_execution={$cf['show_on_execution']}," .
         "     enable_on_execution={$cf['enable_on_execution']}" .
         " WHERE id={$cf['id']}";
    $result=$this->db->exec_query($sql);
    
    $sql="UPDATE cfield_node_types " .
         " SET node_type_id={$cf['node_type_id']}" .
         " WHERE field_id={$cf['id']}";
    $result=$this->db->exec_query($sql);

  } //function end


  /*
    function: update a custom field
              
    args: $id
    
    returns: -
  */
	function delete($id) 
  {
     $sql="DELETE FROM cfield_node_types ".
          " WHERE field_id={$id}";
     $result=$this->db->exec_query($sql);
     
     if($result)
     {
       $sql="DELETE FROM custom_fields".
            " WHERE id={$id}";
       $result=$this->db->exec_query($sql);
     }     
  }


  /*
    function: is_used
              
    args: $id: custom field id
    
    returns: 1/0
  */
	function is_used($id)
	{
	  $sql="SELECT field_id FROM cfield_design_values " .
	       "WHERE  field_id={$id} " .
	       "UNION " .
	       "SELECT field_id FROM cfield_execution_values " .
	       "WHERE  field_id={$id} ";
	  $result=$this->db->exec_query($sql);
	  return($this->db->num_rows( $result ) > 0 ? 1 : 0);
	} //function end



	 /*
    function: name_is_unique
              
    args: $id
          $name 
    
    returns: 1 => name is unique
  */
	function name_is_unique($id,$name)
	{
    $cf=$this->get_by_name($name);
    $status=0;
    
    if( is_null($cf) || isset($cf[$id]) )
    {
       $status=1;
    }
    return($status);
  } //function end



  # --------------------
	# Adapted from Mantis code
	# Prepare a string containing a custom field value for display
	# $p_field_def 		  definition of the custom field
	# $p_node_id	bug id to display the custom field value for
	function string_custom_field_value( $p_field_def, $p_node_id) 
	{
		
		$t_custom_field_value=htmlspecialchars($p_field_def['design_value']);

		switch ($this->custom_field_types[$p_field_def['type']]) 
  	{
			case 'email':
				return "<a href=\"mailto:$t_custom_field_value\">$t_custom_field_value</a>";
				break;
	
			case 'enum':
			case 'list':
			case 'multilist':
			case 'checkbox':
				return str_replace( '|', ', ', $t_custom_field_value );
				break;
				
			case 'date':
				if ($t_custom_field_value != null) 
				{
					return date( config_get( 'short_date_format'), $t_custom_field_value) ;
				}
				break ;
			default:
				return($t_custom_field_value);
		}
	}


  
} // end class
?>