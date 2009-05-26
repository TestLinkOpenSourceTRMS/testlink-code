<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: cfield_mgr.class.php,v $
 * @version $Revision: 1.58 $
 * @modified $Date: 2009/05/26 20:21:50 $  $Author: franciscom $
 * @author franciscom
 *
 * 20090523 - franciscom - changes on show_on, enable_on logics
 * 20090426 - franciscom - new method getSizeLimit()
 * 20090420 - amitkhullar- BUGID-2410 - get_linked_cfields_at_testplan_design() - added logic to get data
 * 					                    for custom field values stores at test plan level.
 * 20090420 - franciscom - BUGID 2158 - get_linked_cfields_at_design() added filter on custom field id 
 *
 * 20090408 - franciscom - BUGID 2352 - added new method remove_all_scopes_values();
 *                                      changes in delete()
 *
 * 20090321 - franciscom - fixed bug due to missing code on get_linked_cfields_at_design()
 * 20090321 - franciscom - exportValueAsXML()
 * 20090303 - franciscom - get_linked_cfields_at_execution() - fixed bugs on query
 *                         and added logic to change fetch method.
 *
 * 20090223 - franciscom - get_linked_cfields_at_execution() - added logic
 *                         to use this method on report created by:
 *                         Amit Khullar - amkhullar@gmail.com
 *
 * 20080817 - franciscom - added logic give default logic to manage 
 *                         new custom field types that have no specific code.
 *
 * 20080816 - franciscom - new feature: user defined Custom Fields.
 *                         Important: 
 *                         solution is a mix of own ideas and Mantis 1.2.0a1 approach.
 *                         string_custom_field_input(), _build_cfield()
 *
 *                         added radio type, datetime type.
 *
 *
 * 20080810 - franciscom - documentation improvements
 *                         BUGID 1650 (REQ)
 *                         get_linked_cfields_at_design() - interface changes
 *
 * 20080304 - franciscom - prepare_string() before insert
 * 20080216 - franciscom - added testproject name to logAudit recorded information
 * 20071102 - franciscom - BUGID - Feature
 *            addition and refactoring of contributed code
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

// Copied from mantis, allow load of user custom implementations
// some sort of poor's man plugin
$cf_files=glob( TL_ABS_PATH . "custom/cf_*.php");
if( count($cf_files) > 0 )
{
    foreach($cf_files as $inc)
    {
        require_once($inc);  
    }   
}

class cfield_mgr
{
    const DEFAULT_INPUT_SIZE=50;
    const MULTISELECTIONLIST_WINDOW_SIZE=5;
    const TEXTAREA_MAX_SIZE = 255;

    // EDIT HERE IF YOU CUSTOMIZE YOUR DB
    // for text area custom field  40 x 6 -> 240 chars <= 255 chars table field size
    const TEXTAREA_DEFAULT_COLS = 70;
    const TEXTAREA_DEFAULT_ROWS = 4;

    const CF_ENABLED = 1;
    const ENABLED = 1;
    const DISABLED = 0;
    

	var $db;
	var $tree_manager;

  // Why we are doing this ?
  // To be ready in future to add a prefix on table names
  //
  var $users_table="users";
  var $builds_table="builds";


  var $object_table="custom_fields";
  var $custom_fields_table="custom_fields";
  var $cfield_design_values_table="cfield_design_values";
  var $cfield_execution_values_table="cfield_execution_values";
  var $cfield_testplan_design_values_table="cfield_testplan_design_values";
  var $cfield_testprojects_table='cfield_testprojects';
  var $cfield_node_types_table="cfield_node_types";

  var $execution_bugs_table="execution_bugs";
  var $executions_table='executions';
  var $tcversions_table='tcversions';

  var $nodes_hierarchy_table="nodes_hierarchy";
  var $node_types_table="node_types";


  // Hold string keys used on this object and pages that manages CF,
  // identifying in what areas/features something will be done
  // 'execution' => mainly on test execution pages,
  //                identifies TL features/pages to record test results
  // 
  // 'design'    => test suites, test cases creation
  //                identifies TL features/pages to create test specification
  // 
  // 'testplan_design' => link test cases to test plan (assign testcase option)
  //
  // IMPORTANT: this values are used as access keys in several properties of this object.
  //            then if you add one here, remember to update other properties.
  //
  // var $application_areas=array('execution','design','testplan_design');
  var $application_areas=array('execution','design','testplan_design');

	// I'm using the same codes used by Mantis (franciscom)
	//
	// Define type of custom fields managed.
	// Values will be displayed in "Custom Field Type" combo box when 
	// users create custom fields. No localization is applied
	// 
  // 20080809 - franciscom
  // Added specific type for test automation related custom fields.
  // Start at code 500
  var $custom_field_types = array(0=>'string',
                                  1=>'numeric',
                                  2=>'float',
                                  4=>'email',
                                  5=>'checkbox',
                                  6=>'list',
                                  7=>'multiselection list',
                                  8=>'date',
                                  9=>'radio',
                                  10=>'datetime',
							      20=>'text area',
							      500=>'script',
							      501=>'server');

  // Configures for what type of CF "POSSIBLE_VALUES" field need to be manage at GUI level
  // Keys of this map must be the values present in:
  // $this->custom_field_types
  // 
  var $possible_values_cfg = array('string' => 0,
                                   'numeric'=> 0,
                                   'float'=> 0,
                                   'email'=> 0,
                                   'checkbox' => 1,
                                   'list' => 1,
                                   'multiselection list' => 1,
                                   'date' => 0,
                                   'radio' => 1,
                                   'datetime' =>0,
								                   'text area' => 0,
								                   'script'=> 0,
								                   'server' => 0);

// only the types listed here can have custom fields
//var $node_types = array('testproject',
//                        'testsuite',
//                        'testcase',
//                        'testplan');
//
var $node_types = array('testsuite','testplan','testcase','requirement_spec','requirement');


  // 20090523 - changes in configuration
  //
  // Needed to manage user interface, when creating Custom Fields.
  // When user choose a item type (test case, etc), a javascript logic
  // uses this information to hide/show enable_on, and show_on combos.
  //
  // 0 => combo will not displayed
  //
  var $enable_on_cfg=array('execution' => array('testsuite' => 0,
	                                            'testplan'  => 0,
	                                            'testcase'  => 1,
	                                            'requirement_spec' => 0,
	                                            'requirement' => 0),
                           'design' => array('testsuite' => 1,
	                                         'testplan'  => 1,
	                                         'testcase'  => 1,
	                                         'requirement_spec' => 0,
	                                         'requirement' => 0),
                           'testplan_design' => array('testsuite' => 0,
	                                                  'testplan'  => 0,
	                                                  'testcase'  => 1,
	                                                  'requirement_spec' => 0,
	                                                  'requirement' => 0));

  // 0 => combo will not displayed
  // 20080809 - franciscom 
  // Added 'testplan_design' key
  var $show_on_cfg=array('execution'=>array('testsuite' => 1,
	                                          'testplan'  => 1,
	                                          'testcase'  => 1,
	                                          'requirement_spec' => 0,
	                                          'requirement' => 0 ),
                         'design' => array('testsuite' => 1,
	                                         'testplan'  => 1,
	                                         'testcase'  => 1,
	                                         'requirement_spec' => 0,
	                                         'requirement' => 0 ),
                         'testplan_design' => array('testsuite' => 1,
	                                         'testplan'  => 1,
	                                         'testcase'  => 1,
	                                         'requirement_spec' => 0,
	                                         'requirement' => 0 )
	                                         );

  // the name of html input will have the following format
  // <name_prefix>_<custom_field_type_id>_<progressive>
  //
  var $name_prefix='custom_field_';

  var $sizes = null;

  // EDIT HERE IF YOU CUSTOMIZE YOUR DB
  // must be equal to the lenght of:
  // value column on cfield_*_values tables
  // default_value column on custom_fields table
  // 0 -> no limit
  var $max_length_value=255;

  // EDIT HERE IF YOU CUSTOMIZE YOUR DB
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

		global $tlCfg;

		$gui_cfg = $tlCfg->gui;
		$this->sizes = $gui_cfg->custom_fields->sizes;
		
		if( !is_null($gui_cfg->custom_fields->types) )
		{
		    $this->custom_field_types +=$gui_cfg->custom_fields->types;
		    ksort($this->custom_field_types);
		}
    
		if( !is_null($gui_cfg->custom_fields->possible_values_cfg) )
		{
		    $this->possible_values_cfg +=$gui_cfg->custom_fields->possible_values_cfg;
		}
	}

    /**
     * 
     *
     */
    function getSizeLimit()
    {
        return $this->max_length_value;    
    }

  /*
    function: get_application_areas

    returns: 
    
    rev: 20080810 - franciscom
  */
	function get_application_areas()
	{
    return($this->application_areas);
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
    function: get_enable_on_cfg

    returns: hash with node types id, that can have custom fields with enabled_on_$ui_mode.
             key  : node_type_id      (node_types.id)
             value: 1 -> enable on exec can be configured by user


  */
function get_enable_on_cfg($ui_mode)
{
    $mgmt_cfg=array();
    $mgmt_cfg=$this->_get_ui_mgtm_cfg_for_node_type($this->enable_on_cfg[$ui_mode]);
    return($mgmt_cfg);
}


function get_show_on_cfg($ui_mode)
{
    $mgmt_cfg=array();
  	$mgmt_cfg=$this->_get_ui_mgtm_cfg_for_node_type($this->show_on_cfg[$ui_mode]);
    return($mgmt_cfg);
}




  /*
    function: _get_ui_mgtm_cfg_for_node_type
              utility method

    returns: hash with node types id.
             key  : node_type_id      (node_types.id)
             value: 1 -> enable on exec can be configured by user


  */
function _get_ui_mgtm_cfg_for_node_type($map_node_id_cfg)
{
    $enabled_mgmt=array();
    $tl_node_types=$this->tree_manager->get_available_node_types();
    foreach($this->node_types as $verbose_type)
    {
        $type_id=$tl_node_types[$verbose_type];
        if( isset($map_node_id_cfg[$verbose_type]) )
        {
          $enabled_mgmt[$type_id]=$map_node_id_cfg[$verbose_type];
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

    [$filters]:default: null
               map with keys:
               [$show_on_execution]: 1 -> filter on field show_on_execution=1
                                     0 or null or not exists -> don't filter

               [$show_on_testplan_design]: 1 -> filter on field show_on_execution=1
                                           0 or null or not exists -> don't filter

			   ['cfield_id']: if exists use it's value to filter on custom field id
                              null or not exists -> don't filter


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

    [$access_key]: default id, field name to use as access key in returned hash
    
    returns: hash
             key: custom field id


    rev :

		  20090420 - franciscom
          added new key cfield_id on filters

          20080811 - franciscom
          interface changes $show_on_execution -> $filters
         
          
          20070526 - franciscom
          changed order by clause

          20070101 - franciscom
          1. added filter on cfield_testprojects.active=1
          2. added new argument $show_on_execution


  */
  function get_linked_cfields_at_design($tproject_id,$enabled,$filters=null,
                                        $node_type=null,$node_id=null,$access_key='id')
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

    // 20080811 - franciscom - refactoring for BUGID 1650 (REQ)
    if( !is_null($filters) )
    {
        if( isset($filters['show_on_execution']) && !is_null($filters['show_on_execution']) )
        {
            $additional_filter .= " AND CF.show_on_execution=1 ";
        }   
        
        // 20090523 - franciscom
        // Probably this piece need to be changed to act on enable_on_ attribute
        // due to CF display logic refactoring
        // if( isset($filters['show_on_testplan_design']) && !is_null($filters['show_on_testplan_design']) )
        // {
        //     $additional_filter .= " AND CF.show_on_testplan_design=1 ";
        // }   
        if( isset($filters['show_on_testplan_design']) && !is_null($filters['show_on_testplan_design']) )
        {
            $additional_filter .= " AND CF.enable_on_testplan_design=1 ";
        }   
           
           
           
        // 20090420 - franciscom
        if( isset($filters['cfield_id']) && !is_null($filters['cfield_id']) )
        {
            $additional_filter .= " AND CF.id={$filters['cfield_id']} ";
        }
    }

    $sql="SELECT CF.*,CFTP.display_order" .
         $additional_values .
         " FROM {$this->object_table} CF " .
         " JOIN {$this->cfield_testprojects_table} CFTP ON CFTP.field_id=CF.id " .
         $additional_join .
         " WHERE CFTP.testproject_id={$tproject_id} " .
         " AND   CFTP.active=1     " .
         " AND   CF.show_on_design=1     " .
         " AND   CF.enable_on_design={$enabled} " .
         $additional_filter .
         " ORDER BY display_order,CF.id ";

    $map = $this->db->fetchRowsIntoMap($sql,$access_key);
    return($map);
  }


	/*
	  ====================================================================
    ** Very Imporant ** 
    This code is based on Mantis code.
    Initial development was based on 1.x.x versions.
    file:custom_field_api.php - function:print_custom_field_input()
    
    20080815: some changes are done to add more flexibility, and idea
              was compared with 1.2.0a1 Mantis implementation.
    ====================================================================          

    function: string_custom_field_input
              returns an string with the html needed to display the custom field.
   
              If no specific code is found to manage a custom field type,
              it will be used code that manage string type.

    args: $p_field_def: contains the definition of the custom field
                        (including it's field id)

          [$name_suffix]: if used must start with _.
                          example _TCID017

    returns: html string

    rev :
         20080816 - franciscom
         added code to manange user defined (and code developed) Custom Fields.
         Important: solution is a mix of own ideas and Mantis 1.2.0a1 approach

         20071006 - francisco.mancardi@gruppotesi.com
         Added field_size argument

         20070104 - franciscom - added 'multiselection list'

  */
	function string_custom_field_input($p_field_def,$name_suffix='',$field_size=0)
	{

		$str_out='';
		$t_id = $p_field_def['id'];
		$t_type = $p_field_def['type'];

	  $t_custom_field_value = $p_field_def['default_value'];
	  if( isset($p_field_def['value']) )
		{
		  $t_custom_field_value = $p_field_def['value'];
		}


    $verbose_type=trim($this->custom_field_types[$t_type]);
  	$t_custom_field_value = htmlspecialchars( $t_custom_field_value );

    $input_name="{$this->name_prefix}{$t_type}_{$t_id}{$name_suffix}";
    $size = isset($this->sizes[$verbose_type]) ? intval($this->sizes[$verbose_type]) : 0;
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
           $window_size = intval($size) > 1 ? $size : self::MULTISELECTIONLIST_WINDOW_SIZE;
           $t_name_suffix='[]';
           $t_multiple=' multiple="multiple" ';
           $t_list_size = count( $t_values );
           if($t_list_size > $window_size)
           {
            $t_list_size=$window_size;
           }
        }
        $html_identity=$input_name . $t_name_suffix;
        
  			$str_out .="<select name=\"{$html_identity}\" id=\"{$input_name}\" {$t_multiple}";
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
				  $str_out .= '<input type="checkbox" name="' . $input_name . '[]"' . " id=\"{$input_name}\"";
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
			$str_out .= $this->string_input_string($p_field_def,$input_name,$t_custom_field_value,$size);
  		  
  		  // $size = intval($size) > 0 ? $size : self::DEFAULT_INPUT_SIZE;
  			// $str_out .= '<input type="text" name="' . $input_name . '" size="' . $size .'"';
			  // if( 0 < $p_field_def['length_max'] )
			  // {
				//   $str_out .= ' maxlength="' . $p_field_def['length_max'] . '"';
			  // }
			  // else
			  // {
				//    $str_out .= ' maxlength="255"';
			  // }
			  // $str_out .= ' value="' . $t_custom_field_value .'"></input>';
			break ;

		case 'text area':
			$cols = intval($this->sizes['text area']['cols']);
			$rows = intval($this->sizes['text area']['rows']);
			if($cols <= 0)
			{
				$cols = self::TEXTAREA_DEFAULT_COLS;
			}
			if($rows <= 0)
			{
				$rows = self::TEXTAREA_DEFAULT_ROWS;
			}
			
			$counterId = $input_name . '_counter';
			$cf_current_size = self::TEXTAREA_MAX_SIZE - tlStringLen($t_custom_field_value);
			// call JS function for check max. size (255) from validate.js
			$js_function = '"textCounter(this.form.' . $input_name . ',document.getElementById(\''.
					$counterId.'\'),'.self::TEXTAREA_MAX_SIZE.');" ';
			$str_out .= '<textarea name="' . $input_name . '" ' . " id=\"{$input_name}\" " .
					'onKeyDown=' . $js_function . ' onKeyUp=' . $js_function . 'cols="' .
					$cols . '" rows="' . $rows . '">' . "{$t_custom_field_value}</textarea>\n";
			// show character counter
			$str_out .= '<span style="vertical-align: top; padding: 5px;">' .
					sprintf(lang_get('text_counter_feedback'), self::TEXTAREA_MAX_SIZE) .
					' <span id="' . $counterId .'">'.$cf_current_size.'</span>.</span>';
		break;

		case 'date':
      		$str_out .= create_date_selection_set($input_name,config_get('date_format'),
                                           $t_custom_field_value, false, true) ;
		break;
      
      case 'datetime':
      $cfg=config_get('gui');
      
      // Important
      // We can do this mix (get date format configuration from standard variable 
      // and time format from an specific custom field config) because string used 
      // for date_format on strftime() has no problem
      // on date() calls (that are used in create_date_selection_set() ).
      $datetime_format=config_get('date_format') . " " .$cfg->custom_fields->time_format;
      $str_out .=create_date_selection_set($input_name,$datetime_format,
                                           $t_custom_field_value, false, true,date( "Y" )-1) ;
      break;
      

      default:
      $dynamic_call='string_input_' . str_replace(' ', '_', $verbose_type);
      if( function_exists($dynamic_call) )
      {
          $str_out .= $dynamic_call($p_field_def, $input_name, $t_custom_field_value);      
      }
      else if( method_exists($this, $dynamic_call) )
      {
          $str_out .= $this->$dynamic_call($p_field_def, $input_name, $t_custom_field_value);
      }
      else
      {
          // treat it as an simple string  
     		  $str_out .= $this->string_input_string($p_field_def,$input_name,$t_custom_field_value,$size);
      }
      break;


  	}
  	return ($str_out);
	} //function end


  /*
    function: design_values_to_db
              write values of custom fields that are used at design time.

    args: $hash: contains info about CF gathered at user interface.
                 (normally $_REQUEST variable)
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

        if( $this->max_length_value > 0 && tlStringLen($value) > $this->max_length_value)
        {
           $value = substr($value,0,$this->max_length_value);
        }

        $safe_value=$this->db->prepare_string($value);
        if($this->db->num_rows( $result ) > 0 )
        {

          $sql = "UPDATE cfield_design_values " .
                 " SET value='{$safe_value}' " .
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
  				       " VALUES	( {$field_id}, {$node_id}, '{$safe_value}' )";
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
		if(is_null($cfield_ids))
			return;

    	$tproject_info = $this->tree_manager->get_node_hierachy_info($tproject_id);
		foreach($cfield_ids as $field_id)
		{
			$sql = "INSERT INTO cfield_testprojects " .
			   	" (testproject_id,field_id) " .
			   	" VALUES({$tproject_id},{$field_id})";

			if ($this->db->exec_query($sql))
			{
				$cf = $this->get_by_id($field_id);
				if ($cf)
				{
					logAuditEvent(TLS("audit_cfield_assigned",$cf[$field_id]['name'],$tproject_info['name']),
								            "ASSIGN",$tproject_id,"testprojects");
			    }					            
			}
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
  		if(is_null($cfield_ids))
			return;

    	$tproject_info = $this->tree_manager->get_node_hierachy_info($tproject_id);
		$auditMsg = $active_val ? "audit_cfield_activated" : "audit_cfield_deactivated";
		foreach($cfield_ids as $field_id)
		{
			$sql = "UPDATE cfield_testprojects " .
				     " SET active={$active_val} " .
				     " WHERE testproject_id={$tproject_id} " .
				     " AND field_id={$field_id}";

			if ($this->db->exec_query($sql))
			{
				$cf = $this->get_by_id($field_id);
				if ($cf)
					logAuditEvent(TLS($auditMsg,$cf[$field_id]['name'],$tproject_info['name']),
								        "SAVE",$tproject_id,"testprojects");
			}
		}
	} //function end

 
  /**
   * unlink_from_testproject
   * remove custom field links from target test project
   * N.B.: following Mantis Bugtracking System model,
   *       this operation will NOR remove all values assigned to 
   *       these custom fields .
   *  
   * @param int $tproject_id
   * @param array $cfield_ids 
   *
   */
	function unlink_from_testproject($tproject_id,$cfield_ids)
  	{
	  	if(is_null($cfield_ids))
	  	{
			return;
        }
        // $cfield_ids=(array)$cfield_ids;
        
        // just for audit porpouses
		$tproject_info = $this->tree_manager->get_node_hierachy_info($tproject_id);
		foreach($cfield_ids as $field_id)
		{
			// BUGID 0000677
			$sql = "DELETE FROM cfield_testprojects WHERE field_id = {$field_id}" .
				   " AND testproject_id = {$tproject_id} ";
			if ($this->db->exec_query($sql))
			{
				$cf = $this->get_by_id($field_id);
				if ($cf)
				{
					logAuditEvent(TLS("audit_cfield_unassigned",$cf[$field_id]['name'],$tproject_info['name']),
		         					 "ASSIGN",$tproject_id,"testprojects");
		        } 					 
			}
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

	  $sql="SELECT CF.*, CFNT.node_type_id,NT.description AS node_type" .
	       " FROM {$this->custom_fields_table} CF, {$this->cfield_node_types_table} CFNT," .
	       " {$this->node_types_table} NT" .
	       " WHERE CF.id=CFNT.field_id " .
	       " AND CFNT.node_type_id=NT.id " .
	       " AND name='{$my_name}' ";
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
    function: get_available_item_type
              get information about what item type (testcase,testplan, etc)
              can use this custom field

    args: $id: custom field id

    returns: 

  */
	function get_available_item_type($id)
	{
	  $sql=" SELECT CFNT.field_id,CFNT.node_type_id ".
	       " FROM {$this->cfield_node_types_table} CFNT, " .
	       "      {$this->nodes_types_table} NT " .
	       " WHERE NT.id=CFNT.node_type_id " .
	       " CFNt.field_id={$id} ";

    return($this->db->fetchRowsIntoMap($sql,'field_id'));
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
                 show_on_testplan_design
                 enable_on_testplan_design
                 node_type_id

    returns: -

    rev: 20080810 - franciscom - BUGID 1650

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
         "  show_on_testplan_design,enable_on_testplan_design, " .
         "  show_on_execution,enable_on_execution) " .
         " VALUES('{$my_name}','{$my_label}',{$cf['type']},'{$my_pvalues}', " .
         "        {$cf['show_on_design']},{$cf['enable_on_design']}," .
         "        {$cf['show_on_testplan_design']},{$cf['enable_on_testplan_design']}," .
         "        {$cf['show_on_execution']},{$cf['enable_on_execution']})";
    $result=$this->db->exec_query($sql);

   	if ($result)
  	{
  	  // at least for Postgres DBMS table name is needed.
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
                 show_on_testplan_design
                 enable_on_testplan_design
                 node_type_id

    returns: -
  */
	function update($cf)
	{
		$my_name = $this->db->prepare_string($cf['name']);
		$my_label = $this->db->prepare_string($cf['label']);
		$my_pvalues = $this->db->prepare_string($cf['possible_values']);

		$sql ="UPDATE custom_fields " .
			 " SET name='{$my_name}',label='{$my_label}'," .
			 "     type={$cf['type']},possible_values='{$my_pvalues}'," .
			 "     show_on_design={$cf['show_on_design']}," .
			 "     enable_on_design={$cf['enable_on_design']}," .
			 "     show_on_testplan_design={$cf['show_on_testplan_design']}," .
			 "     enable_on_testplan_design={$cf['enable_on_testplan_design']}," .
			 "     show_on_execution={$cf['show_on_execution']}," .
			 "     enable_on_execution={$cf['enable_on_execution']}" .
			 " WHERE id={$cf['id']}";
		$result = $this->db->exec_query($sql);

		if ($result)
		{
			$sql = "UPDATE cfield_node_types " .
				" SET node_type_id={$cf['node_type_id']}" .
				" WHERE field_id={$cf['id']}";
			$result = $this->db->exec_query($sql);
		}
		return $result ? 1 : 0;
  } //function end


  /**
   * delete()
   * Will delete custom field definition and also ALL assigned values
   * If custom field is linked to test projects, these links must be removed
   *
   */
	function delete($id)
	{
        // Before deleting definition I need to remove values
        if( $this->is_used($id) )
        {
            $this->remove_all_scopes_values($id);
		}
		$linked_tprojects = $this->get_linked_testprojects($id);
		if( !is_null($linked_tprojects) && count($linked_tprojects) > 0 )
		{
		    $target=array_keys($linked_tprojects);
		    foreach($target as $tproject_id)
		    {
                $this->unlink_from_testproject($tproject_id,(array)$id);
		    }
		}
		
		$sql="DELETE FROM cfield_node_types WHERE field_id={$id}";
		$result=$this->db->exec_query($sql);
		if($result)
		{
			$sql="DELETE FROM custom_fields WHERE id={$id}";
			$result=$this->db->exec_query($sql);
		}
		return $result ? 1 : 0;
	}


  /*
    function: is_used

    args: $id: custom field id

    returns: 1/0
    
    rev: 20080810 - franciscom - BUGID 1650
  */
	function is_used($id)
	{
	  $sql="SELECT field_id FROM {$this->cfield_design_values_table} " .
	       "WHERE  field_id={$id} " .
	       "UNION " .
	       "SELECT field_id FROM {$this->cfield_testplan_design_values_table} " .
	       "WHERE  field_id={$id} " .
	       "UNION " .
	       "SELECT field_id FROM {$this->cfield_execution_values_table} " .
	       "WHERE  field_id={$id} ";
	  $result=$this->db->exec_query($sql);
	  return($this->db->num_rows( $result ) > 0 ? 1 : 0);
	} //function end

  /*
    function: whoIsUsingMe

    args: $id: custom field id

    returns:
  */
	function whoIsUsingMe($id)
	{
	  $sql=" SELECT field_id,name ".
	       " FROM {$this->cfield_design_values_table} CFDV, ".
	       "      {$this->cfield_node_types_table} CFNT, " .
	       "      {$this->nodes_hierarchy_table} NH " .
	       " WHERE CFDV.field_id=CFNT.field_id " .
	       " AND NH.id=CFDV.node_id " .
	       " CFDV.field_id={$id} ";
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

			case 'datetime':
				if ($t_custom_field_value != null)
				{
				    // must remove %
				    // $t_date_format=str_replace("%","",config_get( 'timestamp_format'));
                    // $datetime_format=$t_date_format;
                    $t_date_format=str_replace("%","",config_get( 'date_format'));
                    $cfg=config_get('gui');
                    $datetime_format=$t_date_format . " " .$cfg->custom_fields->time_format;
                    $xdate=date( $datetime_format, $t_custom_field_value);
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
                                           $execution_id=null,$testplan_id=null,
                                           $access_key='id')
  {
    $base_values="CF.*,";
    $additional_join="";
    $additional_values="";
    $additional_filter="";
    $order_clause=" ORDER BY display_order,CF.id ";

    $fetchMethod='fetchRowsIntoMap';

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
    else
    {
        // This piece is useful for report implementation done by: Amit Khullar - amkhullar@gmail.com
        if( !is_null($testplan_id) )
        {
            $base_values ='';
            $additional_values .= ",CF.name,CF.label,CF.id,CFEV.value AS value,CFEV.tcversion_id AS node_id," .
                                  "EXEC.id AS exec_id, EXEC.tcversion_id,EXEC.tcversion_number," .
	                                "EXEC.execution_ts,EXEC.status AS exec_status,EXEC.notes AS exec_notes, " .
	                                "NHB.id AS tcase_id, NHB.name AS tcase_name, TCV.tc_external_id, " . 
                                  "B.id AS builds_id,B.name AS build_name, U.login AS tester " ;
            
            $additional_join .= " JOIN {$this->cfield_execution_values_table} CFEV ON CFEV.field_id=CF.id " .
                                " AND CFEV.testplan_id={$testplan_id} " .
                                " JOIN {$this->executions_table} EXEC ON CFEV.tcversion_id = EXEC.tcversion_id " .
                                " AND CFEV.execution_id = EXEC.id " ;
            
            $additional_join .= " JOIN {$this->builds_table} B ON B.id = EXEC.build_id " .
                                " AND B.testplan_id = EXEC.testplan_id " ;

            $additional_join .= " JOIN {$this->tcversions_table} TCV ON TCV.version = EXEC.tcversion_number " .
			                          " AND TCV.id = EXEC.tcversion_id " ;
            
            $additional_join .= " JOIN {$this->users_table} U ON  U.id = EXEC.tester_id " .
                                " JOIN {$this->nodes_hierarchy_table} NHA ON NHA.id = EXEC.tcversion_id " .
                                " JOIN {$this->nodes_hierarchy_table} NHB ON NHB.id = NHA.parent_id  " ;

			      // $order_clause="ORDER BY EXEC.tcversion_id,EXEC.status,EXEC.id";
            $order_clause="ORDER BY EXEC.tcversion_id,exec_status,exec_id";
            
            $fetchMethod='fetchArrayRowsIntoMap';
    
        }
    }

    $sql = "SELECT {$base_values} CFTP.display_order" .
           $additional_values .
           " FROM custom_fields CF " .
           " JOIN cfield_testprojects CFTP ON CFTP.field_id=CF.id " .
           $additional_join .
           " WHERE CFTP.testproject_id={$tproject_id} " .
           " AND   CFTP.active=1     " .
           " AND   CF.enable_on_execution={$enabled} " .
           " AND   CF.show_on_execution=1 {$order_clause} ";
 
    $map = $this->db->$fetchMethod($sql,$access_key);
    return $map;
  }




  /*
    function: execution_values_to_db
              write values of custom fields that are used at execution time.

    args: $hash: contains info about CF gathered at user interface.
                 (normally $_REQUEST variable)
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

        if( $this->max_length_value > 0 && tlStringLen($value) > $this->max_length_value)
        {
           $value = substr($value,0,$this->max_length_value);
        }
        $safe_value=$this->db->prepare_string($value);

        # Remark got from Mantis code:
  		  # Always store the value, even if it's the default value
  		  # This is important, as the definitions might change but the
  		  #  values stored with a bug must not change
  		  $sql = "INSERT INTO cfield_execution_values " .
  				     " ( field_id, tcversion_id, execution_id,testplan_id,value ) " .
  			       " VALUES	( {$field_id}, {$node_id}, {$execution_id}, {$testplan_id}, '{$safe_value}' )";

        $this->db->exec_query($sql);
      } //foreach($cfield
    } //if( !is_null($cfield) )

  } //function end



  /*
    function: _build_cfield
              support function useful for method used to write CF values to db:
              - design_values_to_db()
              - execution_values_to_db()
              - testplan_design_values_to_db()

    args: $hash:
           key: custom_field_<field_type_id>_<cfield_id>[_<name_suffix>][_<date_part>].
                Example custom_field_0_67 -> 0=> string field
                
                In certain situation we can get:
                custom_field_0_67_234
                0 => string field
                234 => item owner of CF.
                       this happens when you can have multiple times same CF on a page, as happens
                       on execution page if configure TL to work on all test cases in test suite,
                       or when you use CF on testplan_design.
                                                
                To understand [<_date_part>] read below on "Notes on DATE PART - _build_cfield"

           value: can be an array, or a string depending the <field_type_id>

           $cf_map: hash
           key: cfield_id
           value: custom field definition data


    returns: hash or null.

             key: cfield_id
             value: hash ('type_id'  => field_type_id,
                          'cf_value' => value)

    rev: 20080816 - franciscom
         - added code to manange user defined (and code developed) Custom Fields.
           Important: solution is a mix of own ideas and Mantis 1.2.0a1 approach
         - added logic to manage datetime custom field type.  
  */
  function _build_cfield($hash,$cf_map)
  {
    // carved in the stone
    $html_date_input_suffix = array('day' => true,
                                    'month' => true,
                                    'year' => true,
                                    'hour' => true,
                                    'minute' => true,
                                    'second' => true);

    $cf_prefix=$this->name_prefix;
    $len_cfp = tlStringLen($cf_prefix);
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
          // Notes on DATE PART - _build_cfield
          // 
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
          // When using Custom Fields on Execution
          // another piece is added (TC id) then for a date CF, 
          // date part indicator is Position 5, instead of 4
          //
          // When using Custom Fields on Testplan Design 
          // another piece is added (testplan_tcversion.id) then for a date CF, 
          // date part indicator is Position 5, instead of 4
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
        $verbose_type=trim($this->custom_field_types[$type_and_value['type_id']]);

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
          
          case 'datetime':
            if (($value['year'] == 0) || ($value['month'] == 0) || ($value['day'] == 0))
            {
              $cfield[$field_id]['cf_value']='';
            }
            else
            {
              // mktime(int hour, int minute, int second, int month, int day, int year); 
              // to avoid problems with date formats on strtotime().
              // I've used this PHP manual user's note:
              // thalesjacobi at thalesjacobi dot net (06-Nov-2007 09:50)
              // strtotime() reads the timestamp in en_US format if you want to change 
              // the date format with this number, you should previously know the format 
              // of the date you are trying to parse. Let's say you want to do this :
              // strftime("%Y-%m-%d",strtotime("05/11/2007"));
              // It will understand the date as 11th of may 2007, and not 5th of november 2007. 
              // In this case I would use: 
              // $date = explode("/","05/11/2007");
              // strftime("%Y-%m-%d",mktime(0,0,0,$date[1],$date[0],$date[2]));
              // Much reliable but you must know the date format before. 
              $cfield[$field_id]['cf_value']=mktime($value['hour'],$value['minute'],$value['second'],
                                                    $value['month'],$value['day'],$value['year']);
            }
          break;
         

          default:
            $dynamic_call='build_cfield_' . str_replace(' ', '_', $verbose_type);
            if( function_exists($dynamic_call) )
            {
                $cfield[$field_id]['cf_value']=$dynamic_call($value);      
            }
            else if( method_exists($this,$dynamic_call) )
            {
                $cfield[$field_id]['cf_value']=$this->$dynamic_call($value);      
            }
            else
            {
                $cfield[$field_id]['cf_value']=$value;
            }    
          break;

        }
      } // foreach
    }

    return($cfield);
 } // function end






 /*
   function: set_display_order

   args :  $tproject_id: needed because is possible to associate/link
                         a different set of custom field for every test project
           $map_field_id_display_order



   returns:

 */
 function set_display_order($tproject_id, $map_field_id_display_order)
 {
 	$tproject_info = $this->tree_manager->get_node_hierachy_info($tproject_id);
    foreach($map_field_id_display_order as $field_id => $display_order)
    {
		$sql = "UPDATE cfield_testprojects " .
		      " SET display_order=" . intval($display_order) .
		      " WHERE testproject_id={$tproject_id} " .
		      " AND field_id={$field_id} ";

		$this->db->exec_query($sql);

    }
	if ($tproject_info)
		logAuditEvent(TLS("audit_cfield_display_order_changed",$tproject_info['name']),"SAVE",$tproject_id,"testprojects");

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



/**
* function: getXMLServerParams
* @note Retrieves the XML Server Parameters specified through custom fields.
* @param: $node_id, <b>Accepts current node id from nodes hierarchy level</b>
* @return: An array of config params if found, else returns null
*
* rev:
*     20071102 - franciscom - refactoring
*     200710xx - creation - Swanand
**/
function getXMLServerParams($node_id)
{
  $srv_cfg = new stdClass();
  
  $node_type=$this->tree_manager->get_available_node_types();
	$node_info=$this->tree_manager->get_node_hierachy_info($node_id);
  $ret=null;

  if( !is_null($node_info) )
  {
		$prefix = "";
		$ret = array( 'xml_server_host' => null,
				        	'xml_server_port' => null,
					        'xml_server_path' => null);

		$node_info=$this->tree_manager->get_node_hierachy_info($node_id);


		if( $node_info['node_type_id'] == $node_type['testcase'])
		{
			$prefix = "tc_";
		}
		$srv_cfg->host=$prefix . "server_host";
		$srv_cfg->port=$prefix . "server_port";
		$srv_cfg->path=$prefix . "server_path";

		$sql = "SELECT cf.name, cfdv.value " .
		       "FROM cfield_design_values cfdv,custom_fields cf " .
		       "WHERE cfdv.field_id = cf.id AND cfdv.node_id = {$node_id}";

		$server_info = $this->db->fetchRowsIntoMap($sql,'name');
    $server_cfg_is_ok=0;
    $server_use_host_port=0;
    $server_use_path=0;

    if( (isset($server_info[$srv_cfg->host]) && $server_info[$srv_cfg->host] != "") &&
		    (isset($server_info[$srv_cfg->port]) && $server_info[$srv_cfg->port] != "") )
		{
		    $server_cfg_is_ok=1;
			  $ret['xml_server_host'] = $server_info[$srv_cfg->host];
			  $ret['xml_server_port'] = $server_info[$srv_cfg->port];
		}
		else if (isset($server_info[$srv_cfg->path]) && $server_info[$srv_cfg->path] != "")
		{
		    $server_cfg_is_ok=1;
			  $ret['xml_server_path'] = $server_info[$srv_cfg->path];
		}
		else
		{
			if($node_info['parent_id'] != "")
			{
				 $ret=$this->getXMLServerParams($node_info['parent_id']);
			}
		}
	} // if( !is_null($node_info) )

  return $ret;
} //function end



  /*
    function: testplan_design_values_to_db
              write values of custom fields that are used at testplan design time.

    args: $hash: contains info about CF gathered at user interface.
                 (normally $_REQUEST variable)
                 key: custom_field_<field_type_id>_<cfield_id>.
                      Example custom_field_0_67 -> 0=> string field

          $node_id: Remember that this CF are used to extend information
                    on test cases (tcversions) linked to test plans.
                    Then node_id can not point to other type of node than test case version,
                    then node_id will contain a tcversion_id.
                    
                    I have leave this argument to 
          
          
          
          $link_id: Remember that this CF are used to extend information
                    on test cases (tcversions) linked to test plans.
                    Link information is store in testplan_tcversions table,
                    $link_id points to this link (testplan_tcversions.id field)

          [$cf_map]:  hash -> all the custom fields linked and enabled
                              that are applicable to the node type of $node_id.

                              For the keys not present in $hash, we will write
                              an appropriate value according to custom field
                              type.
                              This is needed because when trying to udpate
                              with hash being $_REQUEST, $_POST or $_GET
                              some kind of custom fields (checkbox, list, multiple list)
                              when has been deselected by user.
                              
          [$hash_type]:  NEED TO BE COMMENTED
                         

    rev:
  */
  function testplan_design_values_to_db($hash,$node_id,$link_id,$cf_map=null,$hash_type=null)
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
        $sql = "SELECT value FROM {$this->cfield_testplan_design_values_table} " .
    		 			 " WHERE field_id={$field_id} AND	link_id={$link_id}";

        $result = $this->db->exec_query($sql);

        if( $this->max_length_value > 0 && tlStringLen($value) > $this->max_length_value)
        {
           $value = substr($value,0,$this->max_length_value);
        }

        $safe_value=$this->db->prepare_string($value);
        if($this->db->num_rows( $result ) > 0 )
        {

          $sql = "UPDATE {$this->cfield_testplan_design_values_table} " .
                 " SET value='{$safe_value}' " .
    		 			   " WHERE field_id={$field_id} AND	link_id={$link_id}";
        }
        else
        {
          # Remark got from Mantis code:
  		    # Always store the value, even if it's the dafault value
  		    # This is important, as the definitions might change but the
  		    #  values stored with a bug must not change
  		    $sql = "INSERT INTO {$this->cfield_testplan_design_values_table} " .
  					     " ( field_id, link_id, value ) " .
  				       " VALUES	( {$field_id}, {$link_id}, '{$safe_value}' )";
  		  }
        $this->db->exec_query($sql);
      } //foreach($cfield
    } //if( !is_null($cfield) )

  } //function end


  
  /*
    function: get_linked_cfields_at_testplan_design
              returns information about custom fields that can be used
              at least at testplan design time (test case assignment), 
              with the value assigned (is any has been assigned).


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

                IMPORTANT:
                Fot testplan_design Custom Field this will be a TCVERSION_ID,
                not a TESTCASE_ID


    [link_id]: points to testplan_tcversions.id field
    [testplan_id]


    returns: hash
             key: custom field id
             
             

  */
  function get_linked_cfields_at_testplan_design($tproject_id,$enabled,
                                                 $node_type=null,$node_id=null,
                                                 $link_id=null,$testplan_id=null,$access_key = 'id')
  {
    $additional_join="";
    $additional_values="";
    $additional_filter="";
    
    $order_by_clause = " ORDER BY display_order,CF.id ";
    $fetchMethod = 'fetchRowsIntoMap';

    if( !is_null($node_type) )
    {
   		$hash_descr_id = $this->tree_manager->get_available_node_types();
        $node_type_id=$hash_descr_id[$node_type];

        $additional_join  .= " JOIN {$this->cfield_node_types_table} CFNT ON CFNT.field_id=CF.id " .
                           " AND CFNT.node_type_id={$node_type_id} ";
    }
    
    /*
    if( !is_null($node_id) && !is_null($link_id) && !is_null($testplan_id) )
    {
      $additional_values .= ",CFEV.value AS value,CFEV.tcversion_id AS node_id";
      $additional_join .= " LEFT OUTER JOIN {$this->cfield_testplan_design_values} CFTDV ON CFEV.field_id=CF.id " .
                          " AND CFTDV.tcversion_id={$node_id} " .
                          " AND CFTDV.link_id={$link_id} " .
                          " AND CFTDV.testplan_id={$testplan_id} ";
    }
    */
    
    // if( !is_null($node_id) && !is_null($link_id) )
    // {
    //   $additional_values .= ",CFTDV.value AS value,{$node_id} AS node_id";
    //   $additional_join .= " LEFT OUTER JOIN {$this->cfield_testplan_design_values} CFTDV ON CFTDV.field_id=CF.id " .
    //                       " AND CFTDV.link_id={$link_id} " .
    // }
    
    //-amitkhullar - Created this logic to get the linked tcversions for a testplan 
    //                 that have custom field values at test plan level - BUGID 2410
    if( is_null($link_id) && !is_null($testplan_id))
    {
        $additional_values .= ",CFTDV.value AS value, CFTDV.link_id AS node_id, " . 
                              "NHB.id AS tcase_id, NHB.name AS tcase_name, " .
                              "TCV.tc_external_id ";
                               //"TCV.tc_external_id, exec.status ";
                               
        $additional_join .= "JOIN testplan_tcversions TPTC" .
                          " ON TPTC.testplan_id = {$testplan_id}" .
        				  " JOIN {$this->cfield_testplan_design_values_table} CFTDV " .
                          " ON CFTDV.field_id=CF.id " .
                          " AND CFTDV.link_id = TPTC.id ";
        
        $additional_join .= " JOIN {$this->tcversions_table} TCV ON TCV.id = TPTC.tcversion_id " .
		                    " AND TCV.id = TPTC.tcversion_id " .
         					" JOIN {$this->nodes_hierarchy_table} NHA ON NHA.id = TPTC.tcversion_id " .
                            " JOIN {$this->nodes_hierarchy_table} NHB ON NHB.id = NHA.parent_id  " ;
        
        //$additional_join .= " JOIN executions EXEC on TPTC.tcversion_id = EXEC.tcversion_id  ";
        
        $order_by_clause = " ORDER BY node_id,display_order,CF.id "; 
        $fetchMethod = 'fetchArrayRowsIntoMap';
        $access_key = 'node_id';
        
    }

    elseif( !is_null($link_id) )
    {
        $additional_values .= ",CFTDV.value AS value, CFTDV.link_id AS node_id";
        $additional_join .= " LEFT OUTER JOIN {$this->cfield_testplan_design_values_table} CFTDV " .
                            " ON CFTDV.field_id=CF.id " .
                            " AND CFTDV.link_id={$link_id} ";
    }

    
    
    $sql="SELECT CF.*,CFTP.display_order" .
         $additional_values .
         " FROM {$this->custom_fields_table} CF " .
         " JOIN {$this->cfield_testprojects_table} CFTP ON CFTP.field_id=CF.id " .
         $additional_join .
         " WHERE CFTP.testproject_id={$tproject_id} " .
         " AND   CFTP.active=1     " .
         " AND   CF.enable_on_testplan_design={$enabled} " .
         
         // 20090523 - franciscom 
         // missing refactoring when changing custom field management
         // " AND   CF.show_on_testplan_design=1 " .
         $order_by_clause;
    $map = $this->db->$fetchMethod($sql,$access_key);
    return($map);
  }

  /*
    function: string_input_radio
              returns an string with the html needed to display radio custom field.
              Is normally called by string_custom_field_input()

    args: p_field_def: contains the definition of the custom field
                       (including it's field id)

          p_input_name: html input name
          
          p_custom_field_value: html input value
                                htmlspecialchars() must be applied to this
                                argument by caller.

    returns: html string
  
    rev: 20080816 - franciscom
         based on Mantis 1.2.0a1 code
         
  */
  function string_input_radio($p_field_def, $p_input_name, $p_custom_field_value) 
  {
    $str_out='';
    $t_values = explode( '|', $p_field_def['possible_values']);                                        
    $t_checked_values = explode( '|', $p_custom_field_value );                                         
    foreach( $t_values as $t_option )                                                                  
    {                                                                                                  
      $str_out .= '<input type="radio" name="' . $p_input_name . '[]"';                               
      if( in_array( $t_option, $t_checked_values ) )                                                   
      {                                                                                                
    	  $str_out .= ' value="' . $t_option . '" checked="checked">&nbsp;' . $t_option . '&nbsp;&nbsp;';
      }                                                                                                
      else                                                                                             
      {                                                                                                
    	  $str_out .= ' value="' . $t_option . '">&nbsp;' . $t_option . '&nbsp;&nbsp;';                  
      }                                                                                                
    }
    return $str_out;
  }               

  /*
    function: build_cfield_radio
              support function useful for method used to write radio CF values to db.
              Is normally called by _build_cfield()
              
    args: custom_field_value: value to be converted to be written to db.
    
    returns: value converted
    
    rev: 20080816 - franciscom

  */
  function build_cfield_radio($custom_field_value) 
  {
      if( count($custom_field_value) > 1)
      {
        $value=implode('|',$custom_field_value);
      }
      else
      {
        $value=is_array($custom_field_value) ? $custom_field_value[0] :$custom_field_value;
      }
      return $value;
  }


/*
    function: string_input_string
              returns an string with the html needed to display custom field of type:
              string, email, numeric, float
               
              Is normally called by string_custom_field_input()

    args: p_field_def: contains the definition of the custom field
                       (including it's field id)

          p_input_name: html input name
          
          p_custom_field_value: html input value
                                htmlspecialchars() must be applied to this
                                argument by caller.
          
          p_size: html input size

    returns: html string
  
    rev: 20080817 - franciscom
         
  */
  function string_input_string($p_field_def, $p_input_name, $p_custom_field_value, $p_size) 
  {
      $str_out='';
    	$size = intval($p_size) > 0 ? $p_size : self::DEFAULT_INPUT_SIZE;
  		$str_out .= "<input type=\"text\" name=\"{$p_input_name}\" id=\"{$p_input_name}\" size=\"{$size}\" ";
			if( 0 < $p_field_def['length_max'] )
			{
			  $str_out .= ' maxlength="' . $p_field_def['length_max'] . '"';
			}
			else
			{
			   $str_out .= ' maxlength="255"';
			}
			$str_out .= ' value="' . $p_custom_field_value .'"></input>';
      return $str_out;
  }               



/**
 * exportValueAsXML
 * generate XML with custom field name, and custom field value
 * useful on export to XML method for items that can have custom fields,
 * example: test cases, test suites, req specification, etc.
 *
 * @param map $cfMap: key: custom file ID, value: map with at least keys 'name', 'value'
 *
 */
 function exportValueAsXML($cfMap)
 {
    $cfRootElem = "<custom_fields>{{XMLCODE}}</custom_fields>";
    $cfElemTemplate = "\t" . '<custom_field><name><![CDATA[' . "\n||NAME||\n]]>" . "</name>" .
	                         '<value><![CDATA['."\n||VALUE||\n]]>".'</value></custom_field>'."\n";
    $cfDecode = array ("||NAME||" => "name","||VALUE||" => "value");
	$cfXML = exportDataToXML($cfMap,$cfRootElem,$cfElemTemplate,$cfDecode,true);
  return $cfXML; 
 }


/**
 * remove_all_scopes_values
 * For a given custom field id remove all assigned values in any scope 
 *
 * @param int $id: custom field id
 * 
 * 
 *
 *
 */
function remove_all_scopes_values($id)
{
    // some sort of blind delete
    $sql=array();
    $sql[]="DELETE FROM {$this->cfield_design_values_table} WHERE field_id={$id} ";
    $sql[]="DELETE FROM {$this->cfield_execution_values_table} WHERE field_id={$id} ";
    $sql[]="DELETE FROM {$this->cfield_testplan_design_values_table} WHERE field_id={$id} ";
  
    foreach($sql as $s)
    {
        $this->db->exec_query($s);        
    }
}

/**
 * get_linked_testprojects
 * For a given custom field id return all test projects where is linked.
 *
 * @param int $id: custom field id
 *
 */
function get_linked_testprojects($id)
{
    $sql=" SELECT NH.id, NH.name " .
         " FROM {$this->cfield_testprojects_table} CFTP, {$this->nodes_hierarchy_table} NH " .
         " WHERE CFTP.testproject_id=NH.id " .
         " AND CFTP.field_id = {$id} ORDER BY NH.name ";

    $rs=$this->db->fetchRowsIntoMap($sql,'id');
    return $rs;
}

} // end class
?>