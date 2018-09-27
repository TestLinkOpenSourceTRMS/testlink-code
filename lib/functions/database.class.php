<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * @filesource  database.class.php
 * @package     TestLink
 * @author      Francisco Mancardi
 * @author      Mantis Team
 * @copyright   2006-2016 TestLink community 
 * @copyright   2002-2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 *             (Parts of code has been adapted from Mantis BT)
 * @link       http://www.testlink.org
 *
 * @internal revisions
 * @since 1.9.15 
 *
 */
 
/**
 * IMPORTANT NOTICE
 * As stated on ADODB documentation:
 *
 * ----------------------------------------------------------------------------------------------------------------------
 * $ADODB_COUNTRECS
 * If the database driver API does not support counting the number of records returned in a SELECT statement, 
 * the function RecordCount() is emulated when the global variable $ADODB_COUNTRECS is set to true, which is the default.
 * We emulate this by buffering the records, WHICH CAN TAKE UP LARGE AMOUNTS OF MEMORY FOR BIG RECORDSETS. 
 * Set this variable to false for the best performance. 
 * THIS VARIABLE IS CHECKED EVERY TIME A QUERY IS EXECUTED, so you can selectively choose which recordsets to count. 
 * ----------------------------------------------------------------------------------------------------------------------
 *
 * this set will improve performance but have a side
 * effect, for DBMS like POSTGRES method num_rows() will return ALWAYS -1, causing problems
 *
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
 * @package   TestLink
 */
class database 
{
  const CUMULATIVE=1;
  const ONERROREXIT=1;
  
  var $db;
  var $queries_array = array();
  var $is_connected=false;
  var $nQuery = 0;
  var $overallDuration = 0;
  var $dbType;
  
  private $logEnabled=0;
  private $logQueries=0;
  
  // timer analysis
  function microtime_float() 
  {
    list( $usec, $sec ) = explode( " ", microtime() );
    return ( (float)$usec + (float)$sec );
  }
  
  function setLogEnabled($value)
  {
      $this->logEnabled=$value ? 1 : 0;
  }
  
  function getLogEnabled($value)
  {
      return $this->logEnabled;
  }
  
  function setLogQueries($value)
  {
      $this->logQueries = $value ? 1 : 0;
  }
  
  function getLogQueries($value)
  {
      return $this->logQueries;
  }

  // TICKET 4898: MSSQL - Add support for SQLSRV drivers needed for PHP on WINDOWS version 5.3 and higher
  function __construct($db_type)
  {
    $fetch_mode = ADODB_FETCH_ASSOC;

    $this->dbType = $db_type;
    if( $this->dbType == 'mysql' && version_compare(phpversion(), "5.5.0", ">=") )
    {
      $this->dbType = 'mysqli';
    }
    $adodb_driver = $this->dbType;
  
    // added to reduce memory usage (before this setting we used ADODB_FETCH_BOTH)
    if($this->dbType == 'mssql')
    {
      $fetch_mode = ADODB_FETCH_BOTH;
      if(PHP_OS == 'WINNT')
      {
        // Faced this problem when testing XAMPP 1.7.7 on Windows 7 with MSSQL 2008 Express
        // From PHP MANUAL - reganding mssql_* functions
        // These functions allow you to access MS SQL Server database.
        // This extension is not available anymore on Windows with PHP 5.3 or later.
        // SQLSRV, an alternative driver for MS SQL is available from Microsoft:
        // http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx.       
        //
        // PHP_VERSION_ID is available as of PHP 5.2.7
        if ( defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 50300)  
        {
          $adodb_driver = 'mssqlnative';
        }     
      } 
    }
    $this->db = NewADOConnection($adodb_driver);
    $this->db->SetFetchMode($fetch_mode);
  }


  // access to the ADODB object
  function get_dbmgr_object()
  {
    return($this->db);
  }

  
  
  /** Make a connection to the database */
  # changed Connect() to NConnect() see ADODB Manuals
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
    $ec = 0;
    $emsg = null;
    $logLevel = 'DEBUG';
    $message = '';

    if($this->logQueries)
    {
      $this->nQuery++;
      $t_start = $this->microtime_float();
    }
      
    if ( ( $p_limit != -1 ) || ( $p_offset != -1 ) ) {
      $t_result = $this->db->SelectLimit( $p_query, $p_limit, $p_offset );
    } else {
      $t_result = $this->db->Execute( $p_query );
    }
    
    if($this->logQueries)
    {
      $t_elapsed = number_format( $this->microtime_float() - $t_start, 4);
      $this->overallDuration += $t_elapsed;
      $message =  "SQL [". $this->nQuery . "] executed [took {$t_elapsed} secs]" .
                  "[all took {$this->overallDuration} secs]:\n\t\t";
    }
    $message .= $p_query;

    if (!$t_result)
    {
      $ec       = $this->error_num();
      $emsg     = $this->error_msg();
      $message .= "\nQuery failed: errorcode[" . $ec . "]". "\n\terrormsg:".$emsg;
      $logLevel = 'ERROR';


      tLog("ERROR ON exec_query() - database.class.php <br />" . $this->error(htmlspecialchars($p_query)) . 
           "<br />THE MESSAGE : $message ", 'ERROR', "DATABASE");     
      echo "<pre> ============================================================================== </pre>";
      echo "<pre> DB Access Error - debug_print_backtrace() OUTPUT START </pre>";
      echo "<pre> ATTENTION: Enabling more debug info will produce path disclosure weakness (CWE-200) </pre>";
      echo "<pre>            Having this additional Information could be useful for reporting </pre>";
      echo "<pre>            issue to development TEAM. </pre>";
      echo "<pre> ============================================================================== </pre>";
      
      if(defined('DBUG_ON') && DBUG_ON == 1)
      { 
        echo "<pre>"; debug_print_backtrace(); echo "</pre>";
        die();
      }   
      echo "<pre>"; debug_print_backtrace(); echo "</pre>";
        die();
      
      //else
      //{
      //  echo "<pre>"; debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS); echo "</pre>";
      //}  
      echo "<pre> ============================================================================== </pre>";
      $t_result = false;
    }
    
    if($this->logEnabled)
    {
        tLog($message,$logLevel,"DATABASE");
    }
    
    if($this->logQueries)
    {
      array_push ($this->queries_array, array( $p_query, $t_elapsed, $ec, $emsg ) );
    }

    return $t_result;
    
  }


  // TICKET 4898: MSSQL - Add support for SQLSRV drivers needed for PHP on WINDOWS version 5.3 and higher
  function fetch_array( &$p_result ) 
  {
    if ( $p_result->EOF ) {
      return false;
    }   
    
    // mysql obeys FETCH_MODE_BOTH, hence ->fields works, other drivers do not support this
    switch ($this->db->databaseType) 
    {
      case "mysql":
      case "oci8po":
      case "mssql":
      case "mssqlnative":
        $t_array = $p_result->fields;
        break;
        
      default:
        $t_array = $p_result->GetRowAssoc(false);
        break;
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
  # -> 882226357
  # > SELECT UNIX_TIMESTAMP('1997-10-04 22:23:00');
  # -> 875996580
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
    {
      $row = $this->fetch_array($result);
    }
    unset($result);
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
      {
        $items[] = $row[$column];
      } 
    }
    
    unset($result);
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
   *         of the rows
   **/
  function fetchRowsIntoMap($sql,$column,$cumulative = 0,$limit = -1,$col2implode='')
  {
    $items = null;
    $result = $this->exec_query($sql,$limit);
    if ($result)
    {
      // -----------------------------------------------
      // Error management Code         
      $errorMsg=__CLASS__ . '/' . __FUNCTION__ . ' - ';
      if( ($empty_column = (trim($column)=='') ) )
      {
        $errorMsg .= 'empty column - SQL:' . $sql;
        trigger_error($errorMsg,E_USER_NOTICE);
        return null;
      }

      while($row = $this->fetch_array($result))
      {
        // -----------------------------------------------
                // Error management Code         
                if( !isset($row[$column]) )
                {
                $errorMsg .= 'missing column:' . $column;
              $errorMsg .= ' - SQL:' . $sql;
            trigger_error($errorMsg,E_USER_NOTICE);
            return null;
          } 
                // -----------------------------------------------
                
        if ($cumulative)
        {
          $items[$row[$column]][] = $row;
        }
        else if($col2implode != '')
        {
          if(isset($items[$row[$column]]))
          {
            $items[$row[$column]][$col2implode] .= ',' . $row[$col2implode]; 
          }  
          else
          {
            $items[$row[$column]] = $row;
          }  
        }  
        else 
        {
          $items[$row[$column]] = $row;
        } 
      }
    }
    
    unset($result);
    unset($row);
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

    unset($result);
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
  function get_recordset($sql,$fetch_mode = null,$limit = -1, $start = -1)
  {
    $output = null;

    $result = $this->exec_query($sql,$limit,$start);
    if ($result)
    {
      while($row = $this->fetch_array($result))
      {
        $output[] = $row;
      } 
    }

    unset($result);
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
   *         of the rows
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

    unset($result);
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
  function fetchMapRowsIntoMap($sql,$main_key,$sec_key,
                               $cumulative = 0,$limit = -1, $col2implode ='')
  {
    $items = null;
    $result = $this->exec_query($sql,$limit);
    if ($result)
    {
      while($row = $this->fetch_array($result))
      {
        if($cumulative)
        {
          $items[$row[$main_key]][$row[$sec_key]][] = $row;
        }
        else if($col2implode !='')
        {
          if(isset($items[$row[$main_key]][$row[$sec_key]]))
          {
            $items[$row[$main_key]][$row[$sec_key]][$col2implode] .= 
              ',' . $row[$col2implode];
          } 
          else
          {
            $items[$row[$main_key]][$row[$sec_key]] = $row;   
          } 
        }  
        else
        {
          $items[$row[$main_key]][$row[$sec_key]] = $row;
        } 
      }
    }
    
    unset($result);
    return $items;
  }

  /** 
   *  TICKET 4898: MSSQL - Add support for SQLSRV drivers needed for PHP on WINDOWS version 5.3 and higher
   **/
  function build_sql_create_db($db_name)
  {
    $sql='';
    
    switch($this->db->databaseType)
    {
      case 'postgres7':
      case 'postgres8':
        $sql = 'CREATE DATABASE "' . $this->prepare_string($db_name) . '" ' . "WITH ENCODING='UNICODE' "; 
        break;
        
      case 'mssql':
      case 'mssqlnative':
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


  /**
   * Fetches all rows into a map of 3 levels
   *
   * @param string $sql the query to be executed
   * @param array $keyCols, columns to used as access key
   * @param boolean $cumulative
   * @param integer $limit (optional) number of rows
   * 
   * @return array $items[$row[$column_main_key]][$row[$column_sec_key]]
   * 
   **/
  function fetchRowsIntoMap3l($sql,$keyCols,$cumulative = 0,$limit = -1)
  {
    $items = null;
    $result = $this->exec_query($sql,$limit);
    
    // new dBug($result);
    if ($result)
    {
      while($row = $this->fetch_array($result))
      {
        if($cumulative)
        {
          $items[$row[$keyCols[0]]][$row[$keyCols[1]]][$row[$keyCols[2]]][] = $row;
        }
        else
        {
          $items[$row[$keyCols[0]]][$row[$keyCols[1]]][$row[$keyCols[2]]] = $row;
        } 
      }
    }

    unset($result);
    return $items;
  }


  /**
   * Fetches all rows into a map of 3 levels
   *
   * @param string $sql the query to be executed
   * @param array $keyCols, columns to used as access key
   * @param boolean $cumulative
   * @param integer $limit (optional) number of rows
   * 
   * @return array $items[$row[$column_main_key]][$row[$column_sec_key]]
   * 
   **/
  function fetchRowsIntoMap4l($sql,$keyCols,$cumulative = 0,$limit = -1)
  {
    $items = null;
    $result = $this->exec_query($sql,$limit);
  
    // displayMemUsage(__FUNCTION__);
  
    // new dBug($result);
    if ($result)
    {
      while($row = $this->fetch_array($result))
      {
        if($cumulative)
        {
          $items[$row[$keyCols[0]]][$row[$keyCols[1]]][$row[$keyCols[2]]][$row[$keyCols[3]]][] = $row;
        }
        else
        {
          $items[$row[$keyCols[0]]][$row[$keyCols[1]]][$row[$keyCols[2]]][$row[$keyCols[3]]] = $row;
        } 
      }
    }
    // displayMemUsage(__FUNCTION__);
    unset($result);
    // displayMemUsage(__FUNCTION__);
    return $items;
  }




  /**
   * Fetches all rows into a map whose keys are the values of columns
   *
   * @param string $sql the query to be executed
   * @param string $column the name of the column
   *
   * @return array an assoc array 
   **/
  function fetchRowsIntoMapAddRC($sql,$column,$limit = -1)
  {
    $items = null;
    $result = $this->exec_query($sql,$limit);
    if ($result)
    {
      $errorMsg=__CLASS__ . '/' . __FUNCTION__ . ' - ';
      if( ($empty_column = (trim($column)=='') ) )
      {
        $errorMsg .= 'empty column - SQL:' . $sql;
        trigger_error($errorMsg,E_USER_NOTICE);
        return null;
      }

      while($row = $this->fetch_array($result))
      {
        if( !isset($row[$column]) )
        {
          $errorMsg .= 'missing column:' . $column;
          $errorMsg .= ' - SQL:' . $sql;
          trigger_error($errorMsg,E_USER_NOTICE);
          return null;
        } 
        if(!isset($items[$row[$column]]) )
        {
          $row['recordcount'] = 0;
        }
        else
        {
          $row['recordcount'] = $items[$row[$column]]['recordcount'];
        }
        $row['recordcount']++;
        $items[$row[$column]] = $row;
      }
    }
    
    unset($result);
    unset($row);
    return $items;
  }

  /**
   * @used-by testplan.class.php
   */
  function fetchMapRowsIntoMapStackOnCol($sql,$column_main_key,$column_sec_key,$stackOnCol)
  {
    $items = null;
    $result = $this->exec_query($sql);
    if ($result)
    {
      while($row = $this->fetch_array($result))
      {
        if( !isset($items[$row[$column_main_key]][$row[$column_sec_key]]) )
        {
          $items[$row[$column_main_key]][$row[$column_sec_key]] = $row;
          $items[$row[$column_main_key]][$row[$column_sec_key]][$stackOnCol] = array();
        }
        $items[$row[$column_main_key]][$row[$column_sec_key]][$stackOnCol][]=$row[$stackOnCol];
      }
    }
    unset($result);
    return $items;
  }


} // end of database class