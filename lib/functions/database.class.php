<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * @filesource $RCSfile: database.class.php,v $
 * @version $Revision: 1.13 $
 * @modified $Date: 2006/05/29 06:39:11 $ by $Author: franciscom $
 * @author Francisco Mancardi
 * 
 *
 * 20060523 - franciscom -  added build_sql_create_db()
 *
 * 20060511 - franciscom - added a couple of functions need to resolve postgres problems
 *                         with insert_id()
 *                       - fixed other minor bugs  
 *
 * 20060218 - franciscom - added get_recordset()
 *                         found bugs regarding calling exec_query in
 *                         class methods
*/
 
 # -------------------------------------------------------------------------------
 # This piece of software has been taken from Mantis and modified
 # to be used on TestLink (franciscom@sourceforgeusers.com)
 # -------------------------------------------------------------------------------
 # Mantis - a php based bugtracking system
 # Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 # Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 # This program is distributed under the terms and conditions of the GPL
 # See the README and LICENSE files for details
 # -------------------------------------------------------------------------------

require_once( dirname(__FILE__). '/../../third_party/adodb/adodb.inc.php' );
require_once( dirname(__FILE__). '/logging.inc.php' );

class database 
{
	var $db;
	var $queries_array = array();
	var $is_connected=false;
	var $nQuery = 0;
	var $overallDuration = 0;
	
  
	# ------------------------------------------------------
	# timer analysis
	function microtime_float() {
		list( $usec, $sec ) = explode( " ", microtime() );
		return ( (float)$usec + (float)$sec );
	}
  
  
	function database($db_type)
	{
	  $this->db = NewADOConnection($db_type);
	}


  // access to the ADODB object
	function get_dbmgr_object()
	{
	  return($this->db);
	}

	
	
	# Make a connection to the database
	function connect( $p_dsn, $p_hostname = null, $p_username = null, 
	                          $p_password = null, $p_database_name = null ) {
		
		

		$result = array('status' => 1, 'dbms_msg' => 'ok');
   	
		if(  $p_dsn === false ) {
			$t_result = @$this->db->Connect($p_hostname, $p_username, $p_password, $p_database_name );
		} else {
			$t_result = @$this->db->IsConnected();
		}
		
		if ( $t_result ) {
		  $this->is_connected = true;
		} else {
		  $result['status'] = 0;
		  $result['dbms_msg']=$this->error();
		}
		return ($result);
	}

	# --------------------
	# execute query, requires connection to be opened
	function exec_query( $p_query, $p_limit = -1, $p_offset = -1 )
	{
		$this->nQuery++;
		$t_start = $this->microtime_float();
		
		if ( ( $p_limit != -1 ) || ( $p_offset != -1 ) ) {
			$t_result = $this->db->SelectLimit( $p_query, $p_limit, $p_offset );
		} else {
			$t_result = $this->db->Execute( $p_query );
		}
		$t_elapsed = number_format( $this->microtime_float() - $t_start, 4);
		$this->overallDuration += $t_elapsed;
		$ec = 0;
		$emsg = null;

		//build loginfo
		$logLevel = 'DEBUG';
		$message = "SQL [". $this->nQuery . "] executed [took {$t_elapsed} secs]" .
		           "[all took {$this->overallDuration} secs]:\n\t" . $p_query;
		
		$this->overallDuration += $t_elapsed;	
		if (!$t_result)
		{
			$ec       = $this->error_num();
			$emsg     = $this->error_msg();
			$message .= "\nQuery failed: errorcode[" . $ec . "]". "\n\terrormsg:".$emsg;
			$logLevel = 'ERROR';
		}
		tLog($message,$logLevel);
		array_push ($this->queries_array, array( $p_query, $t_elapsed, $ec, $emsg ) );

		if ( !$t_result ) {
			echo $this->error($p_query);
			return false;
		} else {
			return $t_result;
		}
	}



	# --------------------
	function fetch_array( &$p_result ) {

		if ( $p_result->EOF ) {
			return false;
		}		

		# mysql obeys FETCH_MODE_BOTH, hence ->fields works, other drivers do not support this
		if( $this->db->databaseType == 'mysql' ) {	
			$t_array = $p_result->fields;
 			$p_result->MoveNext();
			return $t_array;
		} else { 
			$test = $p_result->GetRowAssoc(false);
			$p_result->MoveNext();
			return $test;
		}
	}

	# --------------------
	function db_result( $p_result, $p_index1=0, $p_index2=0 ) {

		if ( $p_result && ( $this->num_rows( $p_result ) > 0 ) ) {
			$p_result->Move($p_index1);
			$t_result = $p_result->GetArray();
			return $t_result[0][$p_index2];
		} else {
			return false;
		}
	}

	# --------------------
	# return the last inserted id
	function insert_id($p_table = null) 
	{
		if ( isset($p_table) && $this->db_is_pgsql() ) 
		{
			$query = "SELECT currval('".$p_table."_id_seq')";
			$result = $this->exec_query( $query );
			return $this->db_result($result);
		}
		return $this->db->Insert_ID( );
	}


  # Check is the database is PostgreSQL
	function db_is_pgsql() {
		$t_db_type = DB_TYPE;

		switch( $t_db_type ) {
			case 'postgres':
			// case 'postgres64': - 20060523
			case 'postgres7':
			case 'pgsql':
				return true;
		}

		return false;
	}


	# --------------------
	function db_table_exists( $p_table_name ) {
		return in_array ( $p_table_name , $this->db->MetaTables( "TABLE" ) ) ;
	}

	# --------------------
	function db_field_exists( $p_field_name, $p_table_name ) {
		return in_array ( $p_field_name , $this->db->MetaColumnNames( $p_table_name ) ) ;
	}




	# --------------------
	# Check if there is an index defined on the specified table/field and with
	# the specified type.
	#
	# @@@ thraxisp - this only works with MySQL
	#
	# $p_table: Name of table to check
	# $p_field: Name of field to check
	# $p_key: key type to check for (eg: PRI, MUL, ...etc)
	function key_exists_on_field( $p_table, $p_field, $p_key ) {
		$c_table = $this->db->prepare_string( $p_table );
		$c_field = $this->db->prepare_string( $p_field );
		$c_key   = $this->db->prepare_string( $p_key );

		$query = "DESCRIBE $c_table";
		$result = $this->exec_query( $query );
		
		$count = $this->num_rows( $result );
		for ( $i=0 ; $i < $count ; $i++ ) {
			$row = $this->db->fetch_array( $result );

			if ( $row['Field'] == $c_field ) {
				return ( $row['Key'] == $c_key );
			}
		}
		return false;
	}


	# --------------------
	# prepare a string before DB insertion
	# 20051226 - fm
	function prepare_string( $p_string ) {
  	$t_escaped = $this->db->qstr( $p_string, false );
  	
  	// from second char(1) to one before last(-1)
		return(substr($t_escaped,1,-1));
	}


	# --------------------
	# prepare an integer before DB insertion
	function prepare_int( $p_int ) {
		return (int)$p_int;
	}

	# --------------------
	# prepare a boolean before DB insertion
	function prepare_bool( $p_bool ) {
		return (int)(bool)$p_bool;
	}

	# --------------------
	# return current timestamp for DB
	function db_now() {
		return $this->db->DBTimeStamp(time());
	}

	# --------------------
	# generate a unixtimestamp of a date
	# > SELECT UNIX_TIMESTAMP();
	#	-> 882226357
	# > SELECT UNIX_TIMESTAMP('1997-10-04 22:23:00');
	#	-> 875996580
	function db_timestamp( $p_date=null ) {

		if ( null !== $p_date ) {
			$p_timestamp = $this->db->UnixTimeStamp($p_date);
		} else {
			$p_timestamp = time();
		}
		return $this->db->DBTimeStamp($p_timestamp) ;
	}

	function db_unixtimestamp( $p_date=null ) {

		if ( null !== $p_date ) {
			$p_timestamp = $this->db->UnixTimeStamp($p_date);
		} else {
			$p_timestamp = time();
		}
		return $p_timestamp ;
	}



	# --------------------
	# count queries
	function count_queries () {
		return count( $this->queries_array );
		}

	# --------------------
	# count unique queries
	function count_unique_queries () {

		$t_unique_queries = 0;
		$t_shown_queries = array();
		foreach ($this->queries_array as $t_val_array) {
			if ( ! in_array( $t_val_array[0], $t_shown_queries ) ) {
				$t_unique_queries++;
				array_push( $t_shown_queries, $t_val_array[0] );
			}
		}
		return $t_unique_queries;
		}

	# --------------------
	# get total time for queries
	function time_queries () {
		$t_count = count( $this->queries_array );
		$t_total = 0;
		for ( $i = 0; $i < $t_count; $i++ ) {
			$t_total += $this->queries_array[$i][1];
		}
		return $t_total;
	}



	# --------------------
	# close the connection.
	# Not really necessary most of the time since a connection is
	# automatically closed when a page finishes loading.
	function close() {
		$t_result = $this->db->Close();
	}


	# --------------------
	function error_num() {
		return $this->db->ErrorNo();
	}

	# --------------------
	function error_msg() {
		return $this->db->ErrorMsg();
	}

	# --------------------
	# returns a message string with: 
	# error num, error msg and query.
	#
	function error( $p_query=null ) {
		$msg= $this->error_num() . " - " . $this->error_msg();
		
		if ( null !== $p_query ) 
		{
			$msg .= " - " . $p_query ;
		} 
		return $msg;
	}

	# --------------------
	function num_rows( $p_result ) {
		return $p_result->RecordCount( );
	}

	# --------------------
	function affected_rows() {
		return $this->db->Affected_Rows( );
	}


	/**
	 * Fetches the first column first row 
	 *
	 * @param string $query the query to be executed
	 * @param string $column the name of the column which shall be returned
	 * @return mixed the value of the column
	 **/
	function fetchFirstRowSingleColumn($query,$column)
	{
		$value = null;
		$row = $this->fetchFirstRow($query);
		if ($row)
			$value = $row[$column];
		
		return $value;
	}

	/**
	 * Fetches the first row (in a assoc-array)
	 *
	 * @param string $query the query to be executed
	 * @return array the first row
	 **/
	function fetchFirstRow($query)
	{
		$result = $this->exec_query($query);
		$row = null;
		if ($result)
			$row = $this->fetch_array($result);
		
		return $row;
	}
	
	
	/**
	 * Fetches all values for a given column of all returned rows
	 *
	 * @param string $query the query to be executed
	 * @param string $column the name of the column
	 * @return array an enumerated array, which contains all the values
	 **/
	function fetchColumnsIntoArray($query,$column)
	{
		$items = null;
		$result = $this->exec_query($query);
		if ($result)
		{
			while($row = $this->fetch_array($result))
				$items[] = $row[$column];
		}
	
		return $items;
	}

	/**
	 * Fetches all rows into a map whose keys are the values of columns
	 *
	 * @param string $query the query to be executed
	 * @param string $column the name of the column
	 * @return array an assoc array whose keys are the values from the columns
	 * 				 of the rows
	 **/
	function fetchRowsIntoMap($query,$column)
	{
		$items = null;
		$result = $this->exec_query($query);
		if ($result)
		{
			while($row = $this->fetch_array($result))
			{
				$items[$row[$column]] = $row;
			}
		}
		
		return $items;
	}
	/**
	 * Fetches the values of two columns from all rows into a map
	 *
	 * @param string $query the query to be executed
	 * @param string $column1 the name of the column (keys for the map)
	 * @param string $column2 the name of the second column (values of the map)
	 * @return array return an assoc array whose keys are the values of column1 
	 *				 and the values are the values of column2 
	 **/
	function fetchColumnsIntoMap($query,$column1,$column2)
	{
		$result = $this->exec_query($query);
		$items = null;
		if ($result)
		{
			while ($myrow = $this->fetch_array($result))
			{
				$items[$myrow[$column1]] = $myrow[$column2];
			}	
		}
		
		return $items;
	}

	/**
	 * database server information
	 *
	 * wrapper for adodb method ServerInfo
	 *
	 * @return assoc array members 'version' and 'description'
	 *
	 * @rev: 
	 *      20051231- fm
	 **/
	function get_version_info()
	{
		$version=$this->db->ServerInfo();
		return $version;
	}


	// 20060218 - franciscom
	// the old selectData with new name.
	//
	function get_recordset($sql)
	{
		$output = null;

		$result = $this->exec_query($sql);
		if ($result)
		{
			while($row = $this->fetch_array($result))
			{
				$output[] = $row;
			}	
		}
		return $output;
	}


	/**
	 * Fetches all rows into a map whose keys are the values of columns
	 *
	 * @param string $query the query to be executed
	 * @param string $column the name of the column
	 * @return array an assoc array whose keys are the values from the columns
	 * 				 of the rows
	 **/
	function fetchArrayRowsIntoMap($query,$column)
	{
		$items = null;
		$result = $this->exec_query($query);
		if ($result)
		{
			while($row = $this->fetch_array($result))
			{
				$items[$row[$column]][] = $row;
			}
		}
		
		return $items;
	}


  // 20060523 - franciscom
  function build_sql_create_db($db_name)
  {
    $db_type=$this->db->databaseType;
    $sql='';
    
    switch($db_type)
    {
      case 'postgres7':
      $sql = 'CREATE DATABASE "' . $this->prepare_string($db_name) . '" ' . "WITH ENCODING='UNICODE' "; 
      break;
 
      case 'mssql':
      $sql = 'CREATE DATABASE "' . $this->prepare_string($db_name) . '" '; 
      break;
      
      case 'mysql':
      default:
      $sql = "CREATE DATABASE `" . $this->prepare_string($db_name) . "` CHARACTER SET utf8 "; 
      break;
    }
    return ($sql);
  }



}
?>