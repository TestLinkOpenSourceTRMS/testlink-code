<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: _cf_radio_head.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2009/09/21 09:27:53 $ by $Author: franciscom $
 *
 * Example of user defined custom fields
 *
 * 
 * IMPORTANT: 
 *           You also need to add configuration in custom_config.inc.php
 *           $tlCfg->custom_fields->types = array(100 => 'radio head');
 *           $tlCfg->custom_fields->possible_values_cfg = array('radio head' => 1);
 *
 * 
 * 
 *  -----------------------------------------------------------------------------
*/
/*
    function: string_input_radio_head
              returns an string with the html need to display "radio head" custom field.
              Is normally called by string_custom_field_input()

    args: p_field_def: contains the definition of the custom field
                       (including it's field id)

          p_input_name: html input name
          
          p_custom_field_value: html input value
                                htmlspecialchars() must be applied to this
                                argument by caller.

    returns: html string
  
    rev: based on Mantis 1.2.0a1 code
         
  */
function string_input_radio_head($p_field_def, $p_input_name, $p_custom_field_value ) 
{
  $str_out='';
  $t_values = explode( '|', $p_field_def['possible_values']);                                        
  $t_checked_values = explode( '|', $p_custom_field_value );                                         
  foreach( $t_values as $t_option )                                                                  
  {                                                                                                  
    $str_out .= '<input type="radio" title="I am Radio Head Example" name="' . $p_input_name . '[]"';                               
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
    function: build_cfield_radio_head
              support function useful for method used to write "radio head" CF values to db.
              Is normally called by _build_cfield()
              
    args: custom_field_value: value to be converted to be written to db.
    
    returns: value converted
    
  
  */
  
function build_cfield_radio_head($custom_field_value) 
{
    if( count($value) > 1)
    {
      $value=implode('|',$custom_field_value);
    }
    else
    {
      $value=is_array($custom_field_value) ? $custom_field_value[0] :$custom_field_value;
    }
    return $value;
  
}
?>  