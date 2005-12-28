<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: sqlParser.class.php,v 1.3 2005/12/28 07:34:54 franciscom Exp $ */
// File: sqlParser.class.php
//       MySQL Dump Parser
//
// Rev :
//       20050804 - francisco.mancardi@gruppotesi.com
//       Improved using code from MySQL Eventum
//
//       Original work from: Etomite Installer SNUFFKIN/ Alex 2004
//
//

class SqlParser {
	var $host, $dbname, $prefix, $user, $password, $mysqlErrors;
	var $conn, $installFailed, $sitename, $adminname, $adminpass;

	function SqlParser($host, $user, $password, $db, $prefix='test_', $adminname, $adminpass) {
		$this->host = $host;
		$this->dbname = $db;
		$this->prefix = $prefix;
		$this->user = $user;
		$this->password = $password;
		$this->adminpass = $adminpass;
		$this->adminname = $adminname;
	}

	function connect() {
		$this->conn = mysql_connect($this->host, $this->user, $this->password);
		mysql_select_db($this->dbname, $this->conn);
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
  			do_sql_query($sql_do, $this->conn);
  			if($GLOBALS['db']->error_msg()) {
  				$this->mysqlErrors[] = array("error" => $GLOBALS['db']->error_msg(), "sql" => $sql_do);
  				$this->installFailed = true;
  			}
			}
		}
	}

	function close() {
		mysql_close($this->conn);
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