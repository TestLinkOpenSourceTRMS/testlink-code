<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Custom fields for Test execution 
 *
 * @package 	  TestLink
 * @author 		  jbarchibald
 * @copyright 	2006,2013 TestLink community 
 * @filesource  exec_cfield_mgr.class.php
 * @link 		    http://www.teamst.org/index.php
 *
 * @internal revisions
 */

/**
 * custom fields for execution assignment
 * @package 	TestLink
 */
class exec_cfield_mgr extends cfield_mgr
{
	var $db;
  var $cf_map;

	/**
	 * Class constructor
	 * 
	 * @param resource &$db reference to the database handler
	 * @param integer project identifier
	 */
	function __construct(&$db,$tproject_id)
	{
        // you would think we could inherit the parent $db declaration
        // but it fails to work without this.
        $this->db = &$db;

        // instantiate the parent constructor.
        parent::__construct($this->db);

        $this->cf_map = $this->get_linked_cfields($tproject_id);

	}

/**
 * generate HTML of table rows for list of custom field imput
 * 
 * @param integer $htmlInputSize (optional) size of input field [chars]
 * @return string HTML 
 * @uses function string_custom_field_input() from the parent class.
 */
function html_table_of_custom_field_inputs($htmlInputSize=0)
{
  $cf_smarty = '';
	$cfTypeIDSet = array_flip($this->custom_field_types);
	$defaultSize = array($cfTypeIDSet['list'] => 3, $cfTypeIDSet['multiselection list'] => 3);

  $inputOpt = array('name_suffix' => '', 'field_size' => $htmlInputSize);

  if( !is_null($this->cf_map) )
  {
    foreach($this->cf_map as $cf_id => $cf_info)
    {
			// special input size for list and multiselect list
			if( $cf_info['type'] == $cfTypeIDSet['list'] || 
          $cf_info['type'] == $cfTypeIDSet['multiselection list']) 
      { 
				$inputOpt['field_size'] = $defaultSize[$cf_info['type']]; 
			} 
			
      // true => do not create input in audit log
      $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label'],null,true));
      $cf_smarty .= '<tr><td class="labelHolder">' . htmlspecialchars($label) . "</td><td>" .
                    $this->string_custom_field_input($cf_info,$inputOpt) .  "</td></tr>\n";
    }
  }
    
  return $cf_smarty;
}

    /**
     *      function: get_linked_cfields
     * 
     * @param integer $tproject_id
     * @return array
     * 
     * @internal rev :
     *      20080811 - franciscom - BUGID 1650 (REQ)
     *      20071006 - franciscom - interface changed
     */
    function get_linked_cfields($tproject_id)
    {

      $enabled=1;
      $filters=array('show_on_execution' => 1); // BUGID 1650 (REQ)

      // this is calling the parent method
      $cf = $this->get_linked_cfields_at_design($tproject_id,$enabled,$filters,'testcase');

      // does not make sence to include the text area here..
      // need to strip it out of the array..
      // make it a parameter if someone really wants to keep it.
      $custom_field_types_id=array_flip($this->custom_field_types);

      if( !is_null($cf) and count($cf) > 0 )
      {
        foreach ($cf as $key => $value )
        {
            if ($value['type'] == $custom_field_types_id['text area'] ) {
                unset($cf[$key]);
            }

            // untill I figure how to deal with the 3 elements of the date.. we exclude it as well.
            if ($value['type'] == $custom_field_types_id['date'] ) {
                unset($cf[$key]);
            }
			
			if ($value['type'] == $custom_field_types_id['datetime'] ) {
                unset($cf[$key]);
            }

            // the following is commented out because the custom field lists 
            // don't have something preselected anymore
            //
            // Need to debug how this will work as well.. there is always something selected by default.
            // if ($value['type'] == $custom_field_types_id['list'] ) {
                // unset($cf[$key]);
            //}
        }
      } // if( !is_null($cf) and count($cf) > 0 )
      return($cf);
    }

    /*
    function: field_names
         to return the field names of the custom fields for this testplan.
        this is need to determine what needs to be filtered and to mark default selections after
        refresh.

        NOTE: we do not need to add the [] suffix to the name. this is not need in PHP to parse the
        returned array .. it is only needed at the HTML level.

    args: none

    returns:  array => ($input_name)

    rev:

     */
    function field_names()
    {
      $input_name = array();

      if( !is_null($this->cf_map) )
      {
        foreach($this->cf_map as $cf_id => $cf_info)
        {
            $t_id = $cf_info['id'];
            $t_type = $cf_info['type'];
            $verbose_type=$this->custom_field_types[$t_type];

            $input_name[$t_id] = array('cf_name' => "{$this->name_prefix}{$t_type}_{$t_id}",
                           'verbose_type' => $verbose_type,
                           'type_id' => $t_type,
                           'id' => $t_id);
        }
      }

        return $input_name;
    }

/*
    function: get_set_values
         return the values set by the user to filter by..

    args: none

    returns:  array => {$id => $cf_selected}

    rev:

*/
    function get_set_values() {

    $cf_field_names = $this->field_names();
    $cf_selected = null;

    // get each of the custom fields and see if they are selected.
    foreach ($cf_field_names as $id => $value)
    {
        $cf_name = $value['cf_name']; // this should always be set..

        $cf_selected_name = isset($_REQUEST[$cf_name]) ? $_REQUEST[$cf_name] : null;

        if ($cf_selected_name) {
            switch($value['verbose_type']){

            case 'checkbox':
            case 'radio':
            case 'multiselection list':
                $cf_string = '';

                $firstPass = 1;
                $cf_seperator = '';

                foreach ($cf_selected_name as $key => $selectedValue) {
                    $cf_string .= $cf_seperator . $selectedValue;
                    if ($firstPass) {
                        $cf_seperator = '|';
                        $firstPass = 0;
                    }

                    $cf_selected[$id] = $cf_string;
                }

            break;

            case 'list':
            case 'numeric':
            case 'float':
            case 'email':
            case 'string':
                // BUGID 3301 - replaced POST with REQUEST
            	// $cf_tmp = isset($_POST[$cf_name]) ? $_POST[$cf_name] : null;
                $cf_tmp = isset($_REQUEST[$cf_name]) ? $_REQUEST[$cf_name] : null;

                if ($cf_tmp) {
                    $cf_selected[$id] = $cf_tmp;
                }

            break;

            default:

            break;

            } // end switch
        }
    } // end foreach

    return $cf_selected;
    }// end function
}
?>
