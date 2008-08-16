<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: date_api.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2008/08/16 16:13:20 $ $Author: franciscom $
 * @author franciscom
 *
 * Piece copied form Mantis and adapted to TestLink needs
 *
 * rev : 20080816 - franciscom
 *       added code to manage datetime Custom Fields (Mantis contribution on 2005)
 *       
*/
 
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: date_api.php,v 1.2 2008/08/16 16:13:20 franciscom Exp $
	# --------------------------------------------------------

	### Date API ###

	# --------------------
	function create_month_option_list( $p_month = 0 ) {
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
	
	# --------------------
	function create_numeric_month_option_list( $p_month = 0 ) {
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

	# --------------------
	function create_day_option_list( $p_day = 0 ) {
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
	
	# --------------------
	function create_year_option_list( $p_year = 0 ) {
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
	
	# --------------------
	function create_year_range_option_list( $p_year = 0, $p_start = 0, $p_end = 0) {
	  $year_option='';
	  
		$t_current = date( "Y" ) ;
		$t_forward_years = 2; // config_get( 'forward_year_count' ) ;

		$t_start_year = $p_start ;
		if ($t_start_year == 0) {
			$t_start_year = $t_current ;
		}
		if ( ( $p_year < $t_start_year ) && ( $p_year != 0 ) ) {
			$t_start_year = $p_year ;
		}

		$t_end_year = $p_end ;
		if ($t_end_year == 0) {
			$t_end_year = $t_current + $t_forward_years ;
		}
		if ($p_year > $t_end_year) {
			$t_end_year = $p_year + $t_forward_years ;
		}

		for ($i=$t_start_year; $i <= $t_end_year; $i++) {
			if ($i == $p_year) {
				$year_option .= "<option value=\"$i\" selected=\"selected\"> $i </option>" ;
			} else {
				$year_option .= "<option value=\"$i\"> $i </option>" ;
			}
		}
	  return $year_option;
	}
	# --------------------

  # 20080816 - franciscom
  # Added contribution (done on mantis) to manage datetime
  #
	function create_date_selection_set( $p_name, $p_format, $p_date=0, 
	                                    $p_default_disable=false, $p_allow_blank=false, 
	                                    $p_year_start=0, $p_year_end=0) {
	  
	  $str_out='';
		$t_chars = preg_split('//', $p_format, -1, PREG_SPLIT_NO_EMPTY) ;
		if ( $p_date != 0 ) {
			// 20080816 - $t_date = preg_split('/-/', date( 'Y-m-d', $p_date), -1, PREG_SPLIT_NO_EMPTY) ;
      $t_date = preg_split('/-| |:/', date( 'Y-m-d H:i:s', $p_date), -1, PREG_SPLIT_NO_EMPTY) ;

		} else {
			// 20080816 -  $t_date = array( 0, 0, 0 );
			$t_date = array( 0, 0, 0, 0, 0, 0 );
		}

		$t_disable = '' ;
		if ( $p_default_disable == true ) {
			$t_disable = 'disabled' ;
		}
		$t_blank_line = '' ;
		if ( $p_allow_blank == true ) {
			$t_blank_line = "<option value=\"0\"></option>" ;
		}

		foreach( $t_chars as $t_char ) {
			if (strcmp( $t_char, "M") == 0) {
				$str_out .= "<select name=\"" . $p_name . "_month\" $t_disable>" ;
				$str_out .=  $t_blank_line ;
				$str_out .= create_month_option_list( $t_date[1] ) ;
				$str_out .= "</select>\n" ;
			}
			if (strcmp( $t_char, "m") == 0) {
				$str_out .= "<select  name=\"" . $p_name . "_month\" $t_disable>" ;
				$str_out .= $t_blank_line ;
				$str_out .= create_numeric_month_option_list( $t_date[1] ) ;
				$str_out .= "</select>\n" ;
			}
			if (strcasecmp( $t_char, "D") == 0) {
				$str_out .= "<select  name=\"" . $p_name . "_day\" $t_disable>" ;
				$str_out .= $t_blank_line ;
				$str_out .= create_day_option_list( $t_date[2] ) ;
				$str_out .= "</select>\n" ;
			}
			if (strcasecmp( $t_char, "Y") == 0) {
				$str_out .= "<select  name=\"" . $p_name . "_year\" $t_disable>" ;
				$str_out .= $t_blank_line ;
				$str_out .= create_year_range_option_list( $t_date[0], $p_year_start, $p_year_end ) ;
				$str_out .= "</select>\n" ;
			}
			
			// -----------------------------------------------------------------
			// 20080816 - franciscom
      if (strcasecmp( $t_char, "H") == 0) {
          $str_out .= "<select name=\"" . $p_name . "_hour\" $t_disable>" ;
          $str_out .= create_range_option_list($t_date[3], 0, 23); 
				  $str_out .= "</select>\n" ;
			}
      if (strcasecmp( $t_char, "i") == 0) {
          $str_out .= "<select name=\"" . $p_name . "_minute\" $t_disable>" ;
          $str_out .= create_range_option_list($t_date[4], 0, 59); 
				  echo "</select>\n" ;
			}
      if (strcasecmp( $t_char, "s") == 0) {
          $str_out .= "<select name=\"" . $p_name . "_second\" $t_disable>" ;
          $str_out .= create_range_option_list($t_date[5], 0, 59); 
				  $str_out .= "</select>\n" ;
			}
			// -----------------------------------------------------------------
		}
		return $str_out;
	}

   
  function create_range_option_list($p_value, $p_min, $p_max ) 
  {
      $option_list='';
      for ($idx=$p_min; $idx<=$p_max; $idx++) 
      {
          $selected='';
          $selected = ($idx+1 == $p_value) ? ' selected="selected" ' :'';
      	   $option_list .="<option value=\"$idx\" {$selected}> $idx </option>";
  	  }
  	  return $option_list;
  }
?>