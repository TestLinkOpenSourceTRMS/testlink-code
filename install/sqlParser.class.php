<?php
// TestLink Open Source Project - http://testlink.sourceforge.net/
//
// @filesource  sqlParser.class.php
//
// @internal notes:
// Original work from: Etomite Installer SNUFFKIN/ Alex 2004
// Improved using code from MySQL Eventum
// 
//

class SqlParser {
	var $sql_errors;
	var $install_failed;

	var $db_conn;
	var $db_type;
  var $db_table_prefix;

	function __construct(&$db_conn,$db_type,$db_table_prefix='') 
	{
		$this->db_conn = $db_conn;
		$this->db_type = $db_type;
		$this->db_table_prefix = trim($db_table_prefix);
	}


  /*
    function: 

    args :
    
    returns: 
  */
  function process($filename) 
  {
    $new_value=null;

    // -----------------------------------------------------------------
    // part of this logic has been copied from the setup of EVENTUM 
    $contents = file($filename);
    $do_replace = trim($this->db_table_prefix) != '';
    
    // From PHP Manual Notes on using a class function as Filter
    // This FAILS!!!
    // $cfil = array_filter($contents,"only_good_sql");
    //
    $do_additional_replace=false;
    switch($this->db_type)
    {
      case 'mysql':
        $cfil = array_filter($contents,array($this,"only_good_mysql"));
      break;
          
      case 'postgres':
        $target['sequence'] = "SELECT setval('";
        $do_additional_replace=true; 
        $cfil = array_filter($contents,array($this,"only_good_sql"));
      break;
        
      case 'mssql':
        $cfil = array_filter($contents,array($this,"only_good_sql"));
      break;
    }

    $r2d2 = implode("", $cfil);
    // echo "<pre>debug 20090715 - \ - " . __FUNCTION__ . " --- "; print_r($r2d2); echo "</pre>";

    if( $do_replace)
    {
      $r2d2 = str_replace('/*prefix*/',$this->db_table_prefix,$r2d2);

      // just to solve problem with sequence on PostGres when creating 
      // start up data (need to find a better way)
      if($do_additional_replace)
      {
        foreach($target as $key => $value)
        {
        	if( !is_null($value) )
          {
        	  $new_value[$key] = $value . $this->db_table_prefix ;         
        	  $r2d2 = str_replace($value,$new_value[$key],$r2d2);
        	}
        }
      }
    }
   
    $num = 0;
    $sql_array = explode(";", $r2d2);
    foreach($sql_array as $sql_do) 
    {
      // Needed becuase explode() adds \r\n
      $sql_dodo =  trim(trim($sql_do, "\r\n "));			

      if( strlen($sql_dodo) > 0 )
      {
  			$num = $num + 1;
  			$status_ok=$this->db_conn->exec_query($sql_dodo);
  			if(!$status_ok)
  			{ 
  				$this->sql_errors[] = array("error" => $this->db_conn->error_msg(), "sql" => $sql_dodo);
  				$this->install_failed = true;
  			}
      }
	}  // foreach
}


function only_good_mysql($v)
{
  $comment_char='#';
  return($this->only_good_sql($v, $comment_char));
}


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


} // class end