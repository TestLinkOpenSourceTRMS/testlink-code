<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Date API
 *
 * @package     TestLink
 * @author      franciscom; Piece copied form Mantis and adapted to TestLink needs
 * @copyright   2002-2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * @copyright   2005-2014, TestLink community 
 * @filesource  date_api.php
 * @link        http://www.testlink.org/
 *
 * @internal revisions
 * @since 1.9.10
 *       
 */
 
/**
 * create html code for months combo-box
 * @param integer $p_month (optional) selected month
 * @return array 
 * @todo havlatm: do we use it? Remove?
 */
function create_month_option_list( $p_month = 0 ) 
{
  $month_option=''; 
  for ($i=1; $i<=12; $i++) {
    $month_name = date( 'F', mktime(0,0,0,$i,1,2000) );
    if ( $i == $p_month ) {
      $month_option .= "<option value=\"$i\" selected=\"selected\">$month_name</option>";
    } else {
      $month_option .= "<option value=\"$i\">$month_name</option>";
    }
  }
  return $month_option;
}

  
function create_numeric_month_option_list( $p_month = 0 ) 
{
  $month_option=''; 
  for ($i=1; $i<=12; $i++) {
    if ($i == $p_month) {
      $month_option .= "<option value=\"$i\" selected=\"selected\"> $i </option>" ;
    } else {
      $month_option .= "<option value=\"$i\"> $i </option>" ;
    }
  }
  return $month_option;
}


function create_day_option_list( $p_day = 0 ) 
{
  $day_option = '';
  for ($i=1; $i<=31; $i++) {
    if ( $i == $p_day ) {
      $day_option .= "<option value=\"$i\" selected=\"selected\"> $i </option>";
    } else {
      $day_option .= "<option value=\"$i\"> $i </option>";
    }
  }
  return $day_option;
}
  

function create_year_option_list( $p_year = 0 ) 
{
  $year_option = '';
  $current_year = date( "Y" );
  
  for ($i=$current_year; $i>1999; $i--) {
    if ( $i == $p_year ) {
      $year_option .= "<option value=\"$i\" selected=\"selected\"> $i </option>";
    } else {
      $year_option .= "<option value=\"$i\"> $i </option>";
    }
  }
  return $year_option;
}


// Added contribution (done on mantis) to manage datetime
/** used in cfield_mgr.class.php */
function create_date_selection_set($p_name, $p_format, $p_date=0, $options=null)
{ 
  $opt= array('default_disable' =>false, 'allow_blank' => false, 
              'show_on_filters' => false, 'required' => '');
  
  $opt = array_merge($opt, (array)$options);

  $localeDateFormat = config_get('locales_date_format');
  $locale = (isset($_SESSION['locale'])) ? $_SESSION['locale'] : 'en_GB';
  $date_format = $localeDateFormat[$locale];
  $date_format_without_percent = str_replace('%', '', $date_format);
  
  // if calender shall be shown on filter position has to be fixed to fully display
  $calender_div_position = $opt['show_on_filters'] ? "fixed" : "absolute";
  
  $str_out='';
  $t_chars = preg_split('//', $p_format, -1, PREG_SPLIT_NO_EMPTY) ;
  if ( $p_date != 0 ) 
  {
    $t_date = preg_split('/-| |:/', date('Y-m-d H:i:s', $p_date), -1, PREG_SPLIT_NO_EMPTY) ;
  } 
  else 
  {
    $t_date = array(-1, -1, -1, -1, -1, -1);
  }

  $t_disable = '' ;
  $t_blank_line_date = '' ;
  $t_blank_line_time = '' ;
  if( $opt['default_disable'] == true ) 
  {
    $t_disable = 'disabled' ;
  }
  if( $opt['allow_blank'] == true ) 
  {
    $t_blank_line_date = "<option value=\"0\"></option>" ;
    $t_blank_line_time = "<option value=\"-1\"></option>" ;
  }
  
  $m = $t_date[1];
  $d = $t_date[2];
  $y = $t_date[0];

  // PHP on 32bit systems, when passing mktime(0,0,0,-1,-1,-1) returns false.
  // PHP on 64bit systems it returns a long negative value which causes the error.
  if( $m < 0 || $d < 0 || $y < 0 )
  {
    $time = 0;
  }
  else
  {  
    $time = mktime(0, 0, 0, $m, $d, $y);
  }
  
  $formatted_date = $time != 0 ? strftime($date_format, $time) : '';
  
  $str_out .= '<input type="text" name="' . $p_name.'_input" size="10" id="' . $p_name.'_input" ' .
              'value="' . $formatted_date . 
              '" onclick=showCal(\'' . $p_name . '\',\'' . $p_name.'_input\',\'' . $date_format_without_percent . '\'); READONLY/>' .
              '<img title="' . lang_get('show_calender') . '" src="' . TL_THEME_IMG_DIR . '/calendar.gif" ' .
              'onclick=showCal(\'' . $p_name . '\',\'' . $p_name.'_input\',\'' . $date_format_without_percent . '\'); > ' .
              '<img title="' . lang_get('clear_date') . '" src="' . TL_THEME_IMG_DIR . '/trash.png" ' .
              'onclick="javascript:var x = document.getElementById(\'' . $p_name . '_input\'); x.value = \'\';'.
              'var xh = document.getElementById(\'' . $p_name . '_hour\'); if(xh!=null) xh.selectedIndex=-1;' .
              'var xm = document.getElementById(\'' . $p_name . '_minute\'); if(xm!=null) xm.selectedIndex=-1;' .
              'var xs = document.getElementById(\'' . $p_name . '_second\'); if(xs!=null) xs.selectedIndex=-1;" > ' .
              '<div id="' . $p_name . '" style="position:' . $calender_div_position . ';z-index:1;"></div>';
  

  // Here we work with the TIME PART, that exists only when we require TIMESTAMP
  foreach( $t_chars as $t_char ) 
  {
    $common = $opt['required'] . " $t_disable>" ;
    if (strcasecmp( $t_char, "H") == 0) 
    {
      $mask = '<select name="%s_hour" id="%s_hour" ';    
      $str_out .= sprintf($mask,$p_name,$p_name) . $common . $t_blank_line_time ;
      $str_out .= create_range_option_list($t_date[3], 0, 23); 
      $str_out .= "</select>\n" ;
    }
    
    if (strcasecmp( $t_char, "i") == 0) 
    {
      $mask = '<select name="%s_minute" id="%s_minute" ';    
      $str_out .= sprintf($mask,$p_name,$p_name) . $common . $t_blank_line_time ;
      $str_out .= create_range_option_list($t_date[4], 0, 59); 
      $str_out .= "</select>\n" ;
    }
    
    if (strcasecmp( $t_char, "s") == 0) 
    {
      $mask = '<select name="%s_second" id="%s_second" ';    
      $str_out .= sprintf($mask,$p_name,$p_name) . $common . $t_blank_line_time ;
      $str_out .= create_range_option_list($t_date[5], 0, 59); 
      $str_out .= "</select>\n" ;
    }
  }
  return $str_out;
}


/**
 * 
 *
 */
function create_range_option_list($p_value, $p_min, $p_max ) 
{
  $option_list='';
  for ($idx=$p_min; $idx<=$p_max; $idx++) 
  {
    $selected='';
    $selected = ($idx == $p_value) ? ' selected="selected" ' :'';
    $option_list .="<option value=\"$idx\" {$selected}> $idx </option>";
  }
  return $option_list;
}

/*
 * @param string $ts timestamp
 *                   YYYY-MM-DD HH:MM:SS
 *                   YYYY-MM-DD HH:MM
 *
 * @link some help from stackoverflow
 */
function checkTimeStamp($ts)
{
  // Some MAGIC NUMBERS
  $preg_str = array();
  $preg_str[0] = "/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/";
  $preg_str[1] = "/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})$/";

  $value = trim($ts);
  
  // Check format not content
  $status_ok =  false;
  foreach($preg_str as $v)
  {
    $status_ok |= preg_match($v, $value);
  } 
  
  if(!$status_ok)
  {
    // Bye!
    throw new Exception("Invalid Timestamp format", 1);
  } 

  // Check content
  if( $status_ok )
  {
    // Check Domain
    $status_ok = (strtotime($value) !== FALSE);
    if( $status_ok )
    {
      $yyyymmdd = explode(' ',$value);
      $dp = explode('-',$yyyymmdd[0]);
      $status_ok = checkdate($dp[1], $dp[2], $dp[0]);
    }  

    if( !$status_ok )
    {
      // Bye!
      throw new Exception("Invalid Timestamp", 2);
    }  
  }  
}