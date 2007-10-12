<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: sqlParser.class.php,v 1.6 2007/10/12 08:34:58 franciscom Exp $ */
// File: sqlParser.class.php
//       MySQL Dump Parser
//
// Rev :
//       20071011 - franciscom - MSSQL support
//       20060523 - franciscom - changes to add postgres support
//
//       20060101 - franciscom - Refactoring after added ADODB support
//
//       20050804 - franciscom - Improved using code from MySQL Eventum
//
//       Original work from: Etomite Installer SNUFFKIN/ Alex 2004
//
//

class SqlParser {
	var $sql_errors;
	var $db_conn;
	var $install_failed;
	var $db_type;

  // 20060523 - franciscom - interface changes
	function SqlParser(&$db_conn,$db_type) {
		$this->db_conn   = $db_conn;
		$this->db_type   = $db_type;
	}

	function process($filename) {
		
		// -----------------------------------------------------------------
		// part of this logic has been copied from the setup of EVENTUM 
		$contents = file($filename);
		
		// From PHP Manual Notes on using a class function as Filter
		// This FAILS!!!
		// $cfil = array_filter($contents,"only_good_sql");
		//
		switch($this->db_type)
    {
      case 'mysql':
      $cfil = array_filter($contents,array($this,"only_good_mysql"));
      break;
        
      case 'postgres':
      $cfil = array_filter($contents,array($this,"only_good_sql"));
      break;

      case 'mssql':
      $cfil = array_filter($contents,array($this,"only_good_sql"));
      break;

    }
    $r2d2 = implode("", $cfil);
    $sql_array = explode(";", $r2d2);
    // ----------------------------------------------------------------

    // echo "<pre>debug 20071011 - \$this->db_conn - " . __FUNCTION__ . " --- "; print_r($this->db_conn); echo "</pre>";
    
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
  			// else
  			// {
  			//   echo "OK!!!";  
  			// }
			}
		}
	}

  // 20050612 - fm
  function only_good_mysql($v)
  {
    $comment_char='#';
    return($this->only_good_sql($v, $comment_char));
  } // Function ends


  // 20060523 - fm
  function only_good_sql($v, $comment_char='-')
  {
  
  $use_v = true;
  $findme=$comment_char;
  
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