<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * @package 	TestLink
 * @author 		Francisco Mancardi
 * @author 		Mantis Team
 * @copyright 	2006 TestLink community 
 * @copyright 	2002-2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * 				(Parts of code has been adapted from Mantis BT)
 * @version    	CVS: $Id: database.class.php,v 1.55.2.5 2011/01/15 19:23:45 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 *
 * 20101212 - franciscon - BUGID 4093: MSSQL - Problems on fetch_array() 
 * 20100111 - franciscon - BUGID - display debug_print_backtrace() when query fails
 * 20090720 - franciscom - fetchRowsIntoMap() - added some error management code 
 * 20090202 - franciscom - BUGID 1318 - fetchFirstRowSingleColumn() added new control
 * 20081129 - franciscom - Added CUMULATIVE constant
 * 20081116 - franciscom - fetchColumnsIntoMap() added cumulative argument
 *
 * 20080722 - franciscom -  trying to solve memory usage problems, have add option
 *                          to enable/disable query execution log.
 *                          Setted to DISABLE by default.
 *
 * 20080722 - franciscom -  problems with MSSQL and ADODB_FETCH_ASSOC
 * 20080315 - franciscom -  due to problems with PostGres with $ADODB_COUNTRECS=FALSE;
 *                          return to default mode ($ADODB_COUNTRECS=TRUE;)
 *
 * 20080204 - franciscom -  setting ADODB_FETCH_ASSOC as default fetch mode
 * 20060708 - franciscom -  changed Connect() to NConnect(), to avoid
 *                          problems due to connection reuse, when
 *                          you wanto to connect to more than one database at once
 *                          See ADODB manuals
 */
 
/** 
 * As stated on ADODB documentation this set will improve performance but have a side
 * effect, for DBMS like POSTGRES method num_rows() will return ALWAYS -1, causing problems
 */
$ADODB_COUNTRECS = TRUE;

// To use a different version of ADODB that provided with TL, use a similar bunch of lines
// on custom_config.inc.php
if( !defined('TL_ADODB_RELATIVE_PATH') )
{
    define('TL_ADODB_RELATIVE_PATH','/../../third_party/adodb/adodb.inc.php' );
}
require_once( dirname(__FILE__). TL_ADODB_RELATIVE_PATH );
require_once( dirname(__FILE__). '/logging.inc.php' );

/**
 * TestLink wrapper for ADODB component
 * @package 	TestLink
 */
class database 
{
	const CUMULATIVE=1;
	
	var $db;
	var $queries_array = array();
	var $is_connected=false;
	var $nQuery = 0;
	var $overallDuration = 0;
	var $dbType;
	
	private $logEnabled=0;
	private $logQueries=0;
  
	// timer analysis
	function microtime_float() {
		list( $usec, $sec ) = explode( " ", microtime() );
		return ( (float)$usec + (float)$sec );
	}
	
	function setLogEnabled($value)
	{
	    $this->logEnabled=$value?1:0;
	}
	
	function getLogEnabled($value)
	{
	    return $this->logEnabled;
	}
	
	function setLogQueries($value)
	{
	    $this->logQueries=$value?1:0;
	}
	
	function getLogQueries($value)
	{
	    return $this->logQueries;
	}

  
	function database($db_type)
	{
		$this->dbType = $db_type;
		$fetch_mode = ADODB_FETCH_ASSOC;
		$this->db = NewADOConnection($this->dbType);
		
		// added to reduce memory usage (before this setting we used ADODB_FETCH_BOTH)
		if($this->dbType == 'mssql')
		{
			$fetch_mode = ADODB_FETCH_BOTH;
		}
		$this->db->SetFetchMode($fetch_mode);
	}


	// access to the ADODB object
	function get_dbmgr_object()
	{
		return($this->db);
	}

	
	
	/** Make a connection to the database */
	# 20060708 - franciscom -  changed Connect() to NConnect() see ADODB Manuals
	function connect( $p_dsn, $p_hostname = null, $p_username = null, 
	                          $p_password = null, $p_database_name = null ) 
	{
		$result = array('status' => 1, 'dbms_msg' => 'ok');
		
		if(  $p_dsn === false ) {
			$t_result = $this->db->NConnect($p_hostname, $p_username, $p_password, $p_database_name );
		} else {
			$t_result = $this->db->IsConnected();
		}
		
		if ( $t_result ) {
			$this->is_connected = true;
		} else {
			$result['status'] = 0;
			$result['dbms_msg']=$this->error();
		}
		return ($result);
	}


	/** 
	 * execute SQL query, 
	 * requires connection to be opened
	 * 
	 * @param string $p_query SQL request
	 * @param integer $p_limit (optional) number of rows
	 * @param integer $p_offset (optional) begining row number
	 * 
	 * @return boolean result of request 
	 **/
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
		           "[all took {$this->overallDuration} secs]:\n\t\t" . $p_query;
		
		$this->overallDuration += $t_elapsed;	
		if (!$t_result)
		{
			$ec       = $this->error_num();
			$emsg     = $this->error_msg();
			$message .= "\nQuery failed: errorcode[" . $ec . "]". "\n\terrormsg:".$emsg;
			$logLevel = 'ERROR';
		}
		if($this->logEnabled)
		{
		    tLog($message,$logLevel,"DATABASE");
		}
		
		// 20080927 - may be this causes lot of memory usage
		if($this->logQueries)
		{
			array_push ($this->queries_array, array( $p_query, $t_elapsed, $ec, $emsg ) );
		}

		if ( !$t_result ) 
		{
			tLog("ERROR ON exec_query() - database.class.php <br />" . $this->error(htmlspecialchars($p_query)) . 
				 "<br />THE MESSAGE : $message ", 'ERROR', "DATABASE");			
			echo "<pre> ============================================================================== </pre>";
			echo "<pre> DB Access Error - debug_print_backtrace() OUTPUT START </pre>";
			echo "<pre> ============================================================================== </pre>";
			echo "<pre>"; debug_print_backtrace(); echo "</pre>";
			echo "<pre> ============================================================================== </pre>";
			$t_result = false;
		} 
		return $t_result;
		
	}


	function fetch_array( &$p_result ) 
	{
		if ( $p_result->EOF ) {
			return false;
		}		
		
		// 20101212 - BUGID 4093: MSSQL - Problems on fetch_array()
		// mysql obeys FETCH_MODE_BOTH, hence ->fields works, other drivers do not support this
		// 20090713 - pmo - add oci8po
		switch ($this->db->databaseType) 
		{
			case "mysql":
			case "oci8po":
			case "mssql":
				$t_array = $p_result->fields;
				break;
				
			default:
				$t_array = $p_result->GetRowAssoc(false);
		}
		
		$p_result->MoveNext();
		return $t_array;
	}


    // 20080315 - franciscom - Got new code from Mantis, that manages FETCH_MODE_ASSOC
	function db_result( $p_result, $p_index1=0, $p_index2=0 ) {
		if ( $p_result && ( $this->num_rows( $p_result ) > 0 ) ) 
		{
			$p_result->Move( $p_index1 );
			$t_result = $p_result->GetArray();
        
			if ( isset( $t_result[0][$p_index2] ) ) {
				return $t_result[0][$p_index2];
			}
        
			// The numeric index doesn't exist. FETCH_MODE_ASSOC may have been used.
			// Get 2nd dimension and make it numerically indexed
			$t_result = array_values( $t_result[0] );
			return $t_result[$p_index2];
		}
		return false;
	}


	/** @return integer the last inserted id */
	function insert_id($p_table = null) 
	{
		if ( isset($p_table) && ($this->db_is_pgsql() || $this->db_is_oracle()))
		{
			if ( $this->db_is_pgsql() ) 
			{
				$sql = "SELECT currval('".$p_table."_id_seq')";
			}
			elseif ($this->db_is_oracle())
			{
				$sql = "SELECT ".$p_table."_id_seq.currval from dual";
			}
			$result = $this->exec_query( $sql );
			return $this->db_result($result);
		}
		return $this->db->Insert_ID( );
	}


	/** Check is the database is PostgreSQL */
	function db_is_pgsql() 
	{
		$status_ok = false;
		switch( $this->dbType ) 
		{
			case 'postgres':
			case 'postgres7':
			case 'pgsql':
				$status_ok = true;
			break;	
		}
		return $status_ok;
	}


	/** 
	 * Check is the database is ORACLE 
	 * @return boolean TRUE = Oracle type
	 **/
	function db_is_oracle() 
	{
		$status_ok = false;
		switch( $this->dbType ) 
		{
			case 'oci8':
			case 'oci8po':
				$status_ok = true;
			break;	
		}		
		return $status_ok;
	}


	function db_table_exists( $p_table_name ) {
		return in_array ( $p_table_name , $this->db->MetaTables( "TABLE" ) ) ;
	}


	function db_field_exists( $p_field_name, $p_table_name ) {
		return in_array ( $p_field_name , $this->db->MetaColumnNames( $p_table_name ) ) ;
	}


	/** 
	 * Check if there is an index defined on the specified table/field and with
	 * the specified type.
	 * Warning: only works with MySQL
	 * 
	 * @param string $p_table Name of table to check
	 * @param string $p_field Name of field to check
	 * @param string $p_key key type to check for (eg: PRI, MUL, ...etc)
	 * 
	 * @return boolean 
	 */
	function key_exists_on_field( $p_table, $p_field, $p_key ) {
		$c_table = $this->db->prepare_string( $p_table );
		$c_field = $this->db->prepare_string( $p_field );
		$c_key   = $this->db->prepare_string( $p_key );

		$sql = "DESCRIBE $c_table";
		$result = $this->exec_query( $sql );
		
		$count = $this->num_rows( $result );
		for ( $i=0 ; $i < $count ; $i++ ) {
			$row = $this->db->fetch_array( $result );

			if ( $row['Field'] == $c_field ) {
				return ( $row['Key'] == $c_key );
			}
		}
		return false;
	}


	# prepare a string before DB insertion
	# 20051226 - fm
	function prepare_string( $p_string )
	{
		if (is_null($p_string))
			return '';
			
		$t_escaped = $this->db->qstr( $p_string, false );
		// from second char(1) to one before last(-1)
		return(substr($t_escaped,1,-1));
	}


	# prepare an integer before DB insertion
	function prepare_int( $p_int ) {
		return (int)$p_int;
	}


	# prepare a boolean before DB insertion
	function prepare_bool( $p_bool ) {
		return (int)(bool)$p_bool;
	}

	# return current timestamp for DB
	function db_now()
	{
	    switch($this->db->databaseType)
    	{
			/* @todo: maybe we should use this?
      		case 'odbc_mssql':
				return "GETDATE()";
			*/
			default:
				return $this->db->DBTimeStamp(time());
		}
	}


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


	/** @return integer count queries */
	function count_queries () {
		return count( $this->queries_array );
		}


	/** @return integer count unique queries */
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


	/** get total time for queries */
	function time_queries () {
		$t_count = count( $this->queries_array );
		$t_total = 0;
		for ( $i = 0; $i < $t_count; $i++ ) {
			$t_total += $this->queries_array[$i][1];
		}
		return $t_total;
	}


	/** 
	 * close the connection.
	 * Not really necessary most of the time since a connection is
	 * automatically closed when a page finishes loading.
	 */
	function close() {
		$t_result = $this->db->Close();
	}


	function error_num() {
		return $this->db->ErrorNo();
	}


	function error_msg() {
		return $this->db->ErrorMsg();
	}


	/** 
	 * returns a message string with: error num, error msg and query.
	 * 
	 * @return string the message
	 */
	function error( $p_query=null ) {
		$msg= $this->error_num() . " - " . $this->error_msg();
		
		if ( null !== $p_query ) 
		{
			$msg .= " - " . $p_query ;
		} 
		return $msg;
	}


	function num_rows( $p_result ) {
		return $p_result->RecordCount( );
	}


	function affected_rows() {
		return $this->db->Affected_Rows( );
	}


	/**
	 * Fetches the first column first row 
	 *
	 * @param string $sql the query to be executed
	 * @param string $column the name of the column which shall be returned
	 * 
	 * @return mixed the value of the column
	 **/
	function fetchFirstRowSingleColumn($sql,$column)
	{
		$value = null;
		$row = $this->fetchFirstRow($sql);
		
		// BUGID 1318
		if ($row && array_key_exists($column, $row))
		{
		    $value = $row[$column];
		}
		return $value;
	}

	/**
	 * Fetches the first row (in a assoc-array)
	 *
	 * @param string $sql the query to be executed
	 * @return array the first row
	 **/
	function fetchFirstRow($sql)
	{
		$result = $this->exec_query($sql);
		$row = null;
		if ($result)
			$row = $this->fetch_array($result);
		
		return $row;
	}


	/**
	 * Get one value (no array)
	 * for example: SELECT COUNT(*) FROM table 
	 *
	 * @param string $sql the query to be executed
	 * @return string of one value || null
	 **/
	public function fetchOneValue($sql)
	{
	  	$row = $this->fetchFirstRow($sql);
		if ($row)
    	{
			$fieldName = array_keys($row);   
			return $row[$fieldName[0]];
		}
		return null;
	}
	
	
	/**
	 * Fetches all values for a given column of all returned rows
	 *
	 * @param string $sql the query to be executed
	 * @param string $column the name of the column
	 * @param integer $limit (optional) number of rows
     *
	 * @return array an enumerated array, which contains all the values
	 **/
	function fetchColumnsIntoArray($sql,$column,$limit = -1)
	{
		$items = null;
		$result = $this->exec_query($sql,$limit);
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
	 * @param string $sql the query to be executed
	 * @param string $column the name of the column
	 * @param booleam $cumulative default 0
	 *                useful in situations with results set with multiple
	 *                rows with same value on key column like this:
	 *
	 *                col1   col2  col3 ...
	 *                 X      A     C
	 *                 X      B     Z
	 *                 Y      B     0
	 *
	 *        cumulative=0 -> return items= array('X' => array('A','C'), 'Y' => array('B','0') )
	 *
	 *        cumulative=1 -> return items= 
	 *                        array('X' => array( 0 => array('A','C'), 1 => array('B','Z')),
	 *                              'Y' => array( 0 => array('B','0')I )
	 *
	 * @param integer $limit (optional) number of rows
	 *
	 * @return array an assoc array whose keys are the values from the columns
	 * 				 of the rows
	 **/
	function fetchRowsIntoMap($sql,$column,$cumulative = 0,$limit = -1)
	{
		$items = null;
		$result = $this->exec_query($sql,$limit);
		if ($result)
		{
			while($row = $this->fetch_array($result))
			{
				// -----------------------------------------------
                // Error management Code 				 
				$empty_column = (trim($column)=='');
				$missing_column = false;
				if( !$empty_column )
				{
					$missing_column = !isset($row[$column]);
				}
		        $shoot=$missing_column || $empty_column;
				$errorMsg=__CLASS__ . '/' . __FUNCTION__ . ' - ';
                if( $missing_column )
                {
	        	    $errorMsg .= 'missing column:' . $column;
                }
                else if( $empty_column )
                {
                	$errorMsg .= 'empty column';
                }
                if($shoot)
                {
    			    $errorMsg .= ' - SQL:' . $sql;
    				trigger_error($errorMsg,E_USER_NOTICE);
    				return null;
    			}	
                // -----------------------------------------------
                
				if ($cumulative)
				{
					$items[$row[$column]][] = $row;
				}

				else
				{
					$items[$row[$column]] = $row;
				}	
			}
		}
		
		return $items;
	}
	
	
	/**
	 * Fetches the values of two columns from all rows into a map
	 *
	 * @param string $sql the query to be executed
	 * @param string $column1 the name of the column (keys for the map)
	 * @param string $column2 the name of the second column (values of the map)
	 * @param boolean $cumulative
	 *                useful in situations with results set like
	 *                col1   col2
	 *                 X      A
	 *                 X      B
	 *                 Y      B
	 *
	 *        cumulative=0 -> return items= array('X' => 'B', 'Y' => 'B')
	 *
	 *        cumulative=1 -> return items= array('X' => array('A','B'), 'Y' => array('B') )
	 *               
	 * @param integer $limit (optional) number of rows
	 *               
	 * @return assoc array whose keys are the values of column1 and the values are:
	 *
	 *         cumulative=0  => the values of column2 
	 *         cumulative=1  => array with the values of column2 
	 *
	 **/
	function fetchColumnsIntoMap($sql,$column1,$column2,$cumulative=0,$limit = -1)
	{
		$result = $this->exec_query($sql,$limit);
		$items = null;
		if ($result)
		{
			while ($myrow = $this->fetch_array($result))
			{
			  if($cumulative)
			  {
				  $items[$myrow[$column1]][] = $myrow[$column2];
				}
				else
				{
				  $items[$myrow[$column1]] = $myrow[$column2];
				}  
			}	
		}
		
		return $items;
	}


	/**
	 * database server information
	 * wrapper for adodb method ServerInfo
	 *
	 * @return assoc array members 'version' and 'description'
	 **/
	function get_version_info()
	{
		$version = $this->db->ServerInfo();
		return $version;
	}


	/**
	 **/
	function get_recordset($sql,$fetch_mode = null,$limit = -1)
	{
		$output = null;

		$result = $this->exec_query($sql,$limit);
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
	 * @param string $sql the query to be executed
	 * @param string $column the name of the column
	 * @param integer $limit (optional) number of rows
     *
	 * @return array an assoc array whose keys are the values from the columns
	 * 				 of the rows
	 **/
	function fetchArrayRowsIntoMap($sql,$column,$limit = -1)
	{
		$items = null;
		$result = $this->exec_query($sql,$limit);
		if ($result)
		{
			while($row = $this->fetch_array($result))
			{
				$items[$row[$column]][] = $row;
			}
		}
		
		return $items;
	}


	/**
	 * Fetches all rows into a map whose keys are the values of columns
	 *
	 * @param string $sql the query to be executed
	 * @param string $column_main_key the name of the column
	 * @param string $column_sec_key the name of the column
	 * @param boolean $cumulative
	 * @param integer $limit (optional) number of rows
	 * 
	 * @return array $items[$row[$column_main_key]][$row[$column_sec_key]]
	 * 
	 **/
	function fetchMapRowsIntoMap($sql,$column_main_key,$column_sec_key,$cumulative = 0,$limit = -1)
	{
		$items = null;
		$result = $this->exec_query($sql,$limit);
		if ($result)
		{
			while($row = $this->fetch_array($result))
			{
				if($cumulative)
				{
					$items[$row[$column_main_key]][$row[$column_sec_key]][] = $row;
				}
				else
				{
					$items[$row[$column_main_key]][$row[$column_sec_key]] = $row;
				}	
			}
		}
		return $items;
	}

	/** 
	 * 
	 **/
	function build_sql_create_db($db_name)
	{
		$db_type = $this->db->databaseType;
		$sql='';
		
		switch($db_type)
		{
			case 'postgres7':
				$sql = 'CREATE DATABASE "' . $this->prepare_string($db_name) . '" ' . "WITH ENCODING='UNICODE' "; 
				break;
				
			case 'mssql':
				$sql = 'CREATE DATABASE [' . $this->prepare_string($db_name) . '] '; 
				break;
				
			case 'mysql':
			default:
				$sql = "CREATE DATABASE `" . $this->prepare_string($db_name) . "` CHARACTER SET utf8 "; 
			break;
		}
		return ($sql);
	}


	function db_null_timestamp()
	{
		$db_type = $this->db->databaseType;
		$nullValue = NULL;
		
		switch($db_type)
		{
			case 'mysql':
				// is not an error i put single quote on value			
				$nullValue = " '0000-00-00 00:00:00' ";
			break;
		}
		return $nullValue;
	}


} // end of database class
?>