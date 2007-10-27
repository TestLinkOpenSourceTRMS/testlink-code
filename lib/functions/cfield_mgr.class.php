<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: cfield_mgr.class.php,v $
 * @version $Revision: 1.17 $
 * @modified $Date: 2007/10/27 16:41:05 $  $Author: franciscom $
 * @author franciscom
 *
 * 20071027 - franciscom - using Mantis (a php based bugtracking system)
 *                         logic, to improve custom field management
 *                         adding support for url on string custom fields.
 *
 *
 * 20070617 - franciscom - BUGID     insert_id() problems for Postgres and Oracle?
 * 20070501 - franciscom - limiting length of values while writting to db.
 * 20070429 - franciscom - added text area custom field
 *                         code contributed by Seweryn Plywaczyk  
 *
 * 20070227 - franciscom - BUGID 677
 * 20070110 - franciscom - solved bug set_active()
 *
 * 20070105 - franciscom - 
 * 1. solved bugs on design_values_to_db()
 * 2. refactoring - design_values_to_db()
 *                  execution_values_to_db()
 *                         
**/
require_once(dirname(__FILE__) . '/date_api.php');
require_once(dirname(__FILE__) . '/string_api.php');
class cfield_mgr
{
	var $db;
	var $tree_manager;
	
	// I'm using the same codes used by Mantis
  var $custom_field_types = array(0=>'string',
                                  1=>'numeric',
                                  2=>'float',
                                  4=>'email',
                                  5=>'checkbox',
                                  6=>'list',
                                  7=>'multiselection list',
                                  8=>'date',
							                   20=>'text area');

  // for what type of CF possible_values need 
  // to be manage at GUI level
  var $possible_values_cfg = array('string' => 0,
                                   'numeric'=> 0,
                                   'float'=> 0,
                                   'email'=> 0,
                                   'checkbox' => 1,
                                   'list' => 1,
                                   'multiselection list' => 1,
                                   'date' => 0,
								                   'text area' => 0);
    
     
  // only the types listed here can have custom fields
	//var $node_types = array('testproject',
	//                        'testsuite',
	//                        'testcase',
	//                        'testplan');
  //
	var $node_types = array('testsuite',
	                        'testplan',
	                        'testcase');
  

  // Need to manage user interface, when creating Custom Fields
  // For certain type of nodes, enable_on_exec has nosense
	var $enable_on_exec_cfg = array('testsuite' => 0,
	                                'testplan'  => 0,
	                                'testcase'  => 1);

  
  
  // the name of html input will have the following format
  // <name_prefix>_<custom_field_type_id>_<progressive>
  //
  var $name_prefix='custom_field_';
    
  var $sizes = null;

  // must be equal to the lenght of:
  // value column on cfield_*_values tables
  // default_value column on custom_fields table    
  // 0 -> no limit
  var $max_length_value=255;
  
  
  // must be equal to the lenght of:
  // possible_values column on custom_fields table
  // 0 -> no limit
  var $max_length_possible_values=255;
  
  /*
    function: cfield_mgr
              class constructor
  */
	function cfield_mgr(&$db)
	{
		$this->db = &$db;	
		$this->tree_manager = new tree($this->db);
    $gui_cfg=config_get('gui');
    $this->sizes=$gui_cfg->custom_fields->sizes;
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
    function: get_name_prefix
    
    returns: string
  */
	function get_name_prefix() 
	{
    return($this->name_prefix);
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
    function: get_enabled_on_exec_cfg
    
    returns: hash with node types id, that can have custom fields with enabled_on_exec.
             key  : node_type_id      (node_types.id)
             value: 1 -> enable on exec can be configured by user
             
             
  */
	function get_enable_on_exec_cfg() 
	{
    $enabled_mgmt=array();
    $tl_node_types=$this->tree_manager->get_available_node_types();
    
    foreach($this->node_types as $verbose_type)
    {
      $type_id=$tl_node_types[$verbose_type];
      if( isset($this->enable_on_exec_cfg[$verbose_type]) )
      {
        $enabled_mgmt[$type_id]=$this->enable_on_exec_cfg[$verbose_type];
      }
    }
    return($enabled_mgmt);
  }

  /*
    function: get_possible_values_cfg
    
    returns: hash 
             key  : cf_type_id      (see $custom_field_types)
             value: 1 -> possible values can be managed on UI.
             
             
  */
  function get_possible_values_cfg() 
	{
    $pv_cfg=array();
    $custom_field_types_id=array_flip($this->custom_field_types);
       
    foreach($this->possible_values_cfg as $verbose_cf_type => $use_on_ui)
    {
      $cf_type_id=$custom_field_types_id[$verbose_cf_type];
      $pv_cfg[$cf_type_id]=$use_on_ui;
    }
    return($pv_cfg);
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

    [$show_on_execution]: default: null
                          1 -> filter on field show_on_execution=1
                          0 or null -> don't filter
                          
    
    [$node_type]: default: null
                  verbose id ('testcase', 'testsuite', etc) of a node type.
                  custom fields are linked also to different node types.
                  Example:
                  I can define a custom field "Aspect" with values
                  Performace, Usability and wnat use it only for test suites.
                   
    [$node_id]: default: null
                identification of a node/element on node hierarchy.
                Needed when I want to get the value of custom fields 
                linked to a node.
                Example:
                Have two test cases (ID:9999, ID:89800), and want to get
                the value assigned to custom field "Operating System".
                I will do two calls to this method.
    
    returns: hash
             key: custom field id
                         

    rev :
          20070526 - franciscom
          changed order by clause
          
          20070101 - franciscom
          1. added filter on cfield_testprojects.active=1
          2. added new argument $show_on_execution
             
          
  */
  function get_linked_cfields_at_design($tproject_id,$enabled,$show_on_execution=null,
                                        $node_type=null,$node_id=null) 
  {
    $additional_join="";
    $additional_values="";
    $additional_filter="";
  
    if( !is_null($node_type) )
    {
   		$hash_descr_id = $this->tree_manager->get_available_node_types();
      $node_type_id=$hash_descr_id[$node_type]; 
    
      $additional_join  .= " JOIN cfield_node_types CFNT ON CFNT.field_id=CF.id " .
                           " AND CFNT.node_type_id={$node_type_id} ";
    }
    if( !is_null($node_id) )
    {
      $additional_values .= ",CFDV.value AS value,CFDV.node_id AS node_id";
      $additional_join .= " LEFT OUTER JOIN cfield_design_values CFDV ON CFDV.field_id=CF.id " .
                          " AND CFDV.node_id={$node_id} ";
    }
    
    if( !is_null($show_on_execution) )
    {
     $additional_filter .= " AND CF.show_on_execution=1 ";     
    }
    
    // 20070526 - added CF.id to order by
    $sql="SELECT CF.*,CFTP.display_order" .
         $additional_values .
         " FROM custom_fields CF " .
         " JOIN cfield_testprojects CFTP ON CFTP.field_id=CF.id " .
         $additional_join .
         " WHERE CFTP.testproject_id={$tproject_id} " .
         " AND   CFTP.active=1     " . 
         " AND   CF.show_on_design=1     " . 
         " AND   CF.enable_on_design={$enabled} " .
         $additional_filter .
         " ORDER BY display_order,CF.id ";

    $map = $this->db->fetchRowsIntoMap($sql,'id');     
    return($map);                                 
  }


	/*
    Very Important: this code is based on Mantis code.
    
    function: string_custom_field_input
              returns an string with the html need to display the custom field.
              
    args: $p_field_def: contains the definition of the custom field 
                        (including it's field id)
              
          [$name_suffix]: if used must start with _.
                          example _TCID017
    
    returns:          
    
    rev :
         20071006 - francisco.mancardi@gruppotesi.com
         Added field_size argument
         
         20070104 - franciscom - added 'multiselection list'
              
  */
	function string_custom_field_input($p_field_def,$name_suffix='',$field_size=0)
	{
    $WINDOW_SIZE_MULTILIST=5;
    $DEFAULT_SIZE=50;
    
    // for text area custom field  40 x 6 -> 240 chars <= 255 chars table field size
    $DEFAULT_COLS = 40;
    $DEFAULT_ROWS = 6;
    
		$str_out='';
		$t_id = $p_field_def['id'];
		$t_type = $p_field_def['type'];
		
	  $t_custom_field_value = $p_field_def['default_value'];	
	  if( isset($p_field_def['value']) )
		{
		  $t_custom_field_value = $p_field_def['value'];   
		}
    

    $verbose_type=$this->custom_field_types[$t_type];
  	$t_custom_field_value = htmlspecialchars( $t_custom_field_value );
    
    // 20070105 - franciscom
    $input_name="{$this->name_prefix}{$t_type}_{$t_id}{$name_suffix}";
    $size = isset($this->sizes[$verbose_type]) ? intval($this->sizes[$verbose_type]) : 0;
    
    // 20071006 - franciscom
    if( $field_size > 0)
    {
      $size=$field_size;
    }
    
		switch ($verbose_type) 
		{
  		case 'list':
  		case 'multiselection list':
   			$t_values = explode( '|', $p_field_def['possible_values']);
        if( $verbose_type == 'list' )
        {
           $t_multiple=' ';
           $t_list_size = intval($size) > 0 ? $size :1;
           $t_name_suffix=' ';
        }
        else
        {
           $window_size = intval($size) > 1 ? $size : $WINDOW_SIZE_MULTILIST;
           $t_name_suffix='[]';
           $t_multiple=' multiple="multiple" ';
           $t_list_size = count( $t_values );   
           if($t_list_size > $window_size)
           { 
            $t_list_size=$window_size;
           } 
        }
  			$str_out .='<select name="' . $input_name . $t_name_suffix . '"' . $t_multiple;
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

		  case 'checkbox':
			  $t_values = explode( '|', $p_field_def['possible_values']);
        $t_checked_values = explode( '|', $t_custom_field_value );
			  foreach( $t_values as $t_option ) 
			  {
				  $str_out .= '<input type="checkbox" name="' . $input_name . '[]"';
				  if( in_array( $t_option, $t_checked_values ) ) 
				  {
					  $str_out .= ' value="' . $t_option . '" checked="checked">&nbsp;' . $t_option . '&nbsp;&nbsp;';
				  } 
				  else 
				  {
					  $str_out .= ' value="' . $t_option . '">&nbsp;' . $t_option . '&nbsp;&nbsp;';
				  }
			  }
 			  break;



  		case 'string':
  		case 'email':
  		case 'float':
  		case 'numeric':
  		  $size = intval($size) > 0 ? $size : $DEFAULT_SIZE;
  			$str_out .= '<input type="text" name="' . $input_name . '" size="' . $size .'"';
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
			  
      case 'text area':
        $cols = intval($this->sizes['text area']['cols']);
        $rows = intval($this->sizes['text area']['rows']);
			  if($cols <= 0)
        {
           $cols = $DEFAULT_COLS;
        }
        if($rows <= 0)
        {
          $rows = $DEFAULT_ROWS;
        }
  		
			  $str_out .= '<textarea name="' . $input_name . '" ' .
			              'cols="' . $cols . '" rows="' . $rows . '">' .
			              "{$t_custom_field_value}</textarea>";            
  	  break;
     
      case 'date':
      $str_out .=create_date_selection_set($input_name, 
                                           config_get('date_format'), 
                                           $t_custom_field_value, false, true) ;
      break;

  	}		
    return ($str_out);	
	} //function end


  /*
    function: design_values_to_db
              write values of custom fields that are used at design time.
              
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
         20070525 - franciscom - added [hash_type], to reuse this method on
                                 class testcase method copy_cfields_design_values()
         20070501 - franciscom - limiting lenght of value before writting
         20070105 - franciscom - added $cf_map
         20070104 - franciscom - need to manage multiselection in a different way      
  */
  function design_values_to_db($hash,$node_id,$cf_map=null,$hash_type=null)
  {                                  
    if( is_null($hash) && is_null($cf_map) )
    {
       return;
    }
    if( is_null($hash_type) )
    {
      $cfield=$this->_build_cfield($hash,$cf_map);
    }
    else
    {
      $cfield=$hash;
    }
    
    if( !is_null($cfield) )
    {
      foreach($cfield as $field_id => $type_and_value)
      {
        $value = $type_and_value['cf_value'];
        
        // do I need to update or insert this value?
        $sql = "SELECT value FROM cfield_design_values " .
    		 			 " WHERE field_id={$field_id} AND	node_id={$node_id}";
  
        $result = $this->db->exec_query($sql);
        
        if( $this->max_length_value > 0 && strlen($value) > $this->max_length_value)
        {
           $value = substr($value,0,$this->max_length_value);   
        } 
        
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
      } //foreach($cfield
    } //if( !is_null($cfield) )
    
  } //function end



  /*
    function: remove_all_design_values_from_node
              remove the values of ALL custom fields linked to 
              a node. (example test case 5555)
              
    args: $node_id: single value or array
    
    returns: -
    
    rev :
          20070102 - franciscom - $node_id can be an array
                    
  */
  function remove_all_design_values_from_node($node_id)
  {             
    
    $sql="DELETE FROM cfield_design_values "; 
    if( is_array($node_id) )
    {
      
      $sql .= " WHERE node_id IN(" . implode(",",$node_id) . ") ";
    }         
    else
    {
      $sql .= " WHERE node_id={$node_id}";
    }            
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
    // 20070227 - why ??
    $this->set_active_for_testproject($tproject_id,$cfield_ids,1);
    
    
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
    if(!is_null($node_map))
    {
      $node_list=array_keys($node_map);  
      $node2del=null;
      foreach($node_list as $node_id)
      {
        $the_path=$this->tree_manager->get_path($node_id);
        if( !is_null($the_path) )
        {
          $root=array_pop($the_path);
          if($root['parent_id'] == $tproject_id)
          {
            $node2del[]=$node_id;  
          }
        }
      }
    }
  
    // BUGID 0000677 
    $sql="DELETE FROM cfield_testprojects " .
         $filter . " AND testproject_id = {$tproject_id} ";
    $this->db->exec_query($sql);     
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
  	  // 20070617 - franciscom - at least for Postgres DBMS table name is needed. 
  	  $field_id=$this->db->insert_id('custom_fields');
  	  
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
	# 
	# [$p_value_field]: field id, to point to the field value in $p_field_def
	#
	function string_custom_field_value( $p_field_def, $p_node_id,$p_value_field='value') 
	{
		
		$t_custom_field_value=htmlspecialchars($p_field_def[$p_value_field]);

		switch ($this->custom_field_types[$p_field_def['type']]) 
  	{
			case 'email':
				return "<a href=\"mailto:$t_custom_field_value\">$t_custom_field_value</a>";
				break;
	
			case 'enum':
			case 'list':
			case 'multiselection list':
			case 'checkbox':
				return str_replace( '|', ', ', $t_custom_field_value );
				break;
				
			case 'date':
				if ($t_custom_field_value != null) 
				{
				  // must remove %
				  $t_date_format=str_replace("%","",config_get( 'date_format'));
				  $xdate=date( $t_date_format, $t_custom_field_value);
					return  $xdate;
				}
				break ;
				
		  case 'text area':
        if ($t_custom_field_value != null) 
				{
					return nl2br($t_custom_field_value);
				}
        break;
	
			default:
			  // 20071027 - franciscom
			  // This code manages URLs
				return string_display_links( $t_custom_field_value );

				// return($t_custom_field_value);
		}
	}



  /*
    function: get_linked_cfields_at_execution
              returns information about custom fields that can be used 
              at least at executed, with the value assigned (is any has been assigned). 
    

    $tproject_id: needed because is possible to associate/link 
                  a different set of custom field for every test project
                  
    $enabled    : 1 -> get custom fields that are has been configured
                       to be shown during test case execution AND are enabled.

    [$node_type]: default: null
                  verbose id ('testcase', 'testsuite', etc) of a node type.
                  custom fields are linked also to different node types.
                  Example:
                  I can define a custom field "Aspect" with values
                  Performace, Usability and wnat use it only for test suites.
                   
    [$node_id]: default: null
                identification of a node/element on node hierarchy.
                Needed when I want to get the value of custom fields 
                linked to a node.
                Example:
                Have two test cases (ID:9999, ID:89800), and want to get
                the value assigned to custom field "Operating System".
                I will do two calls to this method.
    
    
    [execution_id]
    [testplan_id]
    
    
    returns: hash
             key: custom field id
                         

    rev :
          20070526 - franciscom
          changed order by clause
             
          
  */
  function get_linked_cfields_at_execution($tproject_id,$enabled,
                                           $node_type=null,$node_id=null,
                                           $execution_id=null,$testplan_id=null) 
  {
    $additional_join="";
    $additional_values="";
    $additional_filter="";
  
    if( !is_null($node_type) )
    {
   		$hash_descr_id = $this->tree_manager->get_available_node_types();
      $node_type_id=$hash_descr_id[$node_type]; 
    
      $additional_join  .= " JOIN cfield_node_types CFNT ON CFNT.field_id=CF.id " .
                           " AND CFNT.node_type_id={$node_type_id} ";
    }
    if( !is_null($node_id) && !is_null($execution_id) && !is_null($testplan_id) )
    {
      $additional_values .= ",CFEV.value AS value,CFEV.tcversion_id AS node_id";
      $additional_join .= " LEFT OUTER JOIN cfield_execution_values CFEV ON CFEV.field_id=CF.id " .
                          " AND CFEV.tcversion_id={$node_id} " .
                          " AND CFEV.execution_id={$execution_id} " .
                          " AND CFEV.testplan_id={$testplan_id} ";
    }
    
    // 20070526 - added CF.id to order by
    $sql="SELECT CF.*,CFTP.display_order" .
         $additional_values .
         " FROM custom_fields CF " .
         " JOIN cfield_testprojects CFTP ON CFTP.field_id=CF.id " .
         $additional_join .
         " WHERE CFTP.testproject_id={$tproject_id} " .
         " AND   CFTP.active=1     " . 
         " AND   CF.enable_on_execution={$enabled} " .
         " AND   CF.show_on_execution=1 " .
         " ORDER BY display_order,CF.id ";
    
    $map = $this->db->fetchRowsIntoMap($sql,'id');     
    return($map);                                 
  }




  /*
    function: execution_values_to_db
              write values of custom fields that are used at execution time.
              
    args: $hash: 
          key: custom_field_<field_type_id>_<cfield_id>. 
               Example custom_field_0_67 -> 0=> string field
          
          $node_id:
          $execution_id:
          $testplan_id:           
  
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
        20070501 - franciscom - limiting lenght of value before writting
  */
  function execution_values_to_db($hash,$node_id,$execution_id,$testplan_id,$cf_map=null)
  {                                  
    if( is_null($hash) && is_null($cf_map) )
    {
       return;
    }

    $cfield=$this->_build_cfield($hash,$cf_map);
    
    if( !is_null($cfield) )
    {
      foreach($cfield as $field_id => $type_and_value)
      {
        $value = $type_and_value['cf_value'];
        
        if( $this->max_length_value > 0 && strlen($value) > $this->max_length_value)
        {
           $value = substr($value,0,$this->max_length_value);   
        } 
        
        # Remark got from Mantis code:
  		  # Always store the value, even if it's the dafault value
  		  # This is important, as the definitions might change but the
  		  #  values stored with a bug must not change
  		  $sql = "INSERT INTO cfield_execution_values " .
  				     " ( field_id, tcversion_id, execution_id,testplan_id,value ) " .
  			       " VALUES	( {$field_id}, {$node_id}, {$execution_id}, {$testplan_id}, '{$value}' )";
  		  
        $this->db->exec_query($sql);
      } //foreach($cfield
    } //if( !is_null($cfield) )
   
  } //function end


  
  /*
    function: _build_cfield
              support function useful for:
              design_values_to_db()
              execution_values_to_db()
              

    args: $hash: 
           key: custom_field_<field_type_id>_<cfield_id>. 
                Example custom_field_0_67 -> 0=> string field
           value: can be an array, or a string depending the <field_type_id>
           
           $cf_map: hash
           key: cfield_id
           value: custom field definition data 

    
    returns: hash or null.
              
             key: cfield_id
             value: hash ('type_id'  => field_type_id,
                          'cf_value' => value)

  */
  function _build_cfield($hash,$cf_map)
  {
    // carved in the stone
    $html_date_input_suffix = array('day' => true, 
                                    'month' => true,
                                    'year' => true);
        
    $cf_prefix=$this->name_prefix;
    $len_cfp=strlen($cf_prefix);
    $cftype_pos=2;
    $cfid_pos=3;
    $cfield=null;
    
    // -------------------------------------------------------------------------
    if( !is_null($cf_map) )
    {
      foreach($cf_map as $key => $value)
      {
        $cfield[$key]=array("type_id"  => $value['type'],
                            "cf_value" => '');
      }
    }
    // -------------------------------------------------------------------------
    
    // -------------------------------------------------------------------------
    // Overwrite with values if custom field id exist
    if( !is_null($hash) )
    {
      foreach($hash as $key => $value)
      {
        if( strncmp($key,$cf_prefix,$len_cfp) == 0 )
        {
          // When using Custom Fields on Test Spec: 
          // key has this format (for every type except date )
          // custom_field_0_10 for every type except for type date.
          //
          // For date custom fields:
          // custom_field_8_10_day, custom_field_8_10_month, custom_field_8_10_year
          //
          // After explode()
          // Position 2: CF type
          // Position 3: CF id
          // Position 4: only available for date CF, is date part indicator 
          //
          // When using Custom Fields on Execution, another piece is added (TC id)
          // then for a date CF, date part indicator is Position 5, instead of 4
          //
          $dummy=explode('_',$key);
          $last_idx=count($dummy)-1;
          $the_value=$value;
          if( isset($html_date_input_suffix[$dummy[$last_idx]]) )
          {
            $the_value=array();
            if( isset($cfield[$dummy[$cfid_pos]]) )  
            {
              $the_value=$cfield[$dummy[$cfid_pos]]['cf_value'];
            }
            $the_value[$dummy[$last_idx]]=$value;
          }
          $cfield[$dummy[$cfid_pos]]=array("type_id"  => $dummy[$cftype_pos],
                                           "cf_value" => $the_value);
        }
      }
    } //if( !is_null($hash) )

    if( !is_null($cfield) )
    {
      foreach($cfield as $field_id => $type_and_value)
      {
        $value = $type_and_value['cf_value'];
        $verbose_type=$this->custom_field_types[$type_and_value['type_id']];        
    
        switch ($verbose_type) 
        {
          case 'multiselection list':
          case 'checkbox':
            if( count($value) > 1)
            {
              $value=implode('|',$value);
            }
            else
            {
              $value=is_array($value) ? $value[0] :$value;  
            }
            $cfield[$field_id]['cf_value']=$value;
          break;        

          case 'date':
            if (($value['year'] == 0) || ($value['month'] == 0) || ($value['day'] == 0)) 
            {
              $cfield[$field_id]['cf_value']='';
            }
            else
            {
              $cfield[$field_id]['cf_value']=strtotime($value['year'] . "-" . 
                                                       $value['month'] . "-" . $value['day']);
            }
          break;
          
          default:
            $cfield[$field_id]['cf_value']=$value;
          break;        
            
        }
      } // foreach
    }
      
    return($cfield);
 } // function end
  
 
 /*
   function: 
 
   args :  $tproject_id: needed because is possible to associate/link 
                         a different set of custom field for every test project
           $map_field_id_display_order
           
           

   returns: 

 */
 function set_display_order($tproject_id, $map_field_id_display_order)
 {
    foreach($map_field_id_display_order as $field_id => $display_order)
    {
       $sql="UPDATE cfield_testprojects " .
            " SET display_order=" . intval($display_order) .
            " WHERE testproject_id={$tproject_id} " . 
            " AND field_id={$field_id} ";

       $this->db->exec_query($sql);     
    }
 } // function end
  

 
 # code from mantis helper_api.php
 # --------------------
 # returns a tab index value and increments it by one.  This is used to give sequential tab index on 
 # a form.
 function helper_get_tab_index_value() {
	 static $tab_index = 0;
	 return ++$tab_index;
 }

 # --------------------
 # returns a tab index and increments internal state by 1.  This is used to give sequential tab index on
 # a form.  For example, this function returns: tabindex="1"
 function helper_get_tab_index() {
	 return 'tabindex="' . helper_get_tab_index_value() . '"';
 }


} // end class
?>