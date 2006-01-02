<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: sqlParser.class.php,v 1.4 2006/01/02 13:47:11 franciscom Exp $ */
// File: sqlParser.class.php
//       MySQL Dump Parser
//
// Rev :
//       20060101 - fm
//       Refactoring after added ADODB support
//
//       20050804 - fm
//       Improved using code from MySQL Eventum
//
//       Original work from: Etomite Installer SNUFFKIN/ Alex 2004
//
//

class SqlParser {
	var $sql_errors;
	var $db_conn;
	var $install_failed;

	function SqlParser($db_conn) {
		$this->db_conn   = $db_conn;
	}

	function process($filename) {
		
		
		// -----------------------------------------------------------------
		// part of this logic has been copied from the setup of EVENTUM 
		$contents = file($filename);
		
		// From PHP Manual Notes on using a class function as Filter
		// This FAILS!!!
		// $cfil = array_filter($contents,"only_good_sql");
		//
    $cfil = array_filter($contents,array($this,"only_good_sql"));
    $r2d2 = implode("", $cfil);
    $sql_array = explode(";", $r2d2);
    // ----------------------------------------------------------------

    // print_r($sql_array);
    
		$num = 0;
		foreach($sql_array as $sql_do) {

      // Due to explode adds \r\n
      $sql_dodo =  trim($sql_do, "\r\n ");			
      if( strlen($sql_dodo) > 0 )
      {
  			$num = $num + 1;
  			$this->db_conn->exec_query($sql_do);
  			if($this->db_conn->error_msg()) {
  				$this->sql_errors[] = array("error" => $this->db_conn->error_msg(), "sql" => $sql_do);
  				$this->install_failed = true;
  			}
			}
		}
	}

  // 20050612 - fm
  function only_good_sql($v)
  {
  
  $use_v = true;
  $findme='#';
  
  // Must trim New Line for the strlen check
  $v_c = trim($v, "\r\n ");
  $pos = strpos($v_c, $findme);
  
  
  if ($pos === false) 
  {
     $use_v = true;
  } 
  else 
  {
    if ($pos == 0 )
    {
     $use_v = false;	
    }
  }
  
  // Empty line must not be used
  if( $use_v == true )
  {
    if ( strlen($v_c) == 0)
    {
      $use_v = false;
    }
  }
  
  
  return ($use_v);
  
  } // Function ends


}

?>