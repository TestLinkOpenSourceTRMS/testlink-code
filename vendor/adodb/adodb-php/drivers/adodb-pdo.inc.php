<?php
/**
 * ADOdb base PDO driver
 *
 * This file is part of ADOdb, a Database Abstraction Layer library for PHP.
 *
 * @package ADOdb
 * @link https://adodb.org Project's web site and documentation
 * @link https://github.com/ADOdb/ADOdb Source code and issue tracker
 *
 * The ADOdb Library is dual-licensed, released under both the BSD 3-Clause
 * and the GNU Lesser General Public Licence (LGPL) v2.1 or, at your option,
 * any later version. This means you can use it in proprietary products.
 * See the LICENSE.md file distributed with this source code for details.
 * @license BSD-3-Clause
 * @license LGPL-2.1-or-later
 *
 * @copyright 2000-2013 John Lim
 * @copyright 2014 Damien Regad, Mark Newnham and the ADOdb community
 */

// security - hide paths
if (!defined('ADODB_DIR')) die();


/*
enum pdo_param_type {
PDO::PARAM_NULL, 0

/* int as in long (the php native int type).
 * If you mark a column as an int, PDO expects get_col to return
 * a pointer to a long
PDO::PARAM_INT, 1

/* get_col ptr should point to start of the string buffer
PDO::PARAM_STR, 2

/* get_col: when len is 0 ptr should point to a php_stream *,
 * otherwise it should behave like a string. Indicate a NULL field
 * value by setting the ptr to NULL
PDO::PARAM_LOB, 3

/* get_col: will expect the ptr to point to a new PDOStatement object handle,
 * but this isn't wired up yet
PDO::PARAM_STMT, 4 /* hierarchical result set

/* get_col ptr should point to a zend_bool
PDO::PARAM_BOOL, 5


/* magic flag to denote a parameter as being input/output
PDO::PARAM_INPUT_OUTPUT = 0x80000000
};
*/

function adodb_pdo_type($t)
{
	switch($t) {
	case 2: return 'VARCHAR';
	case 3: return 'BLOB';
	default: return 'NUMERIC';
	}
}

/*----------------------------------------------------------------------------*/


class ADODB_pdo extends ADOConnection {
	var $databaseType = "pdo";
	var $dataProvider = "pdo";
	var $fmtDate = "'Y-m-d'";
	var $fmtTimeStamp = "'Y-m-d, h:i:sA'";
	var $replaceQuote = "''"; // string to use to replace quotes
	var $hasAffectedRows = true;
	var $_bindInputArray = true;
	var $_genIDSQL;
	var $_genSeqSQL = "create table %s (id integer)";
	var $_dropSeqSQL;
	var $_autocommit = true;
	var $_lastAffectedRows = 0;

	var $_errormsg = false;
	var $_errorno = false;

	var $stmt = false;
	var $_driver;

	/*
	* Describe parameters passed directly to the PDO driver
	*
	* @example $db->pdoOptions = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];
	*/
	public $pdoParameters = array();

	function _UpdatePDO()
	{
		$d = $this->_driver;
		$this->fmtDate = $d->fmtDate;
		$this->fmtTimeStamp = $d->fmtTimeStamp;
		$this->replaceQuote = $d->replaceQuote;
		$this->sysDate = $d->sysDate;
		$this->sysTimeStamp = $d->sysTimeStamp;
		$this->random = $d->random;
		$this->concat_operator = $d->concat_operator;
		$this->nameQuote = $d->nameQuote;
		$this->arrayClass = $d->arrayClass;

		$this->hasGenID = $d->hasGenID;
		$this->_genIDSQL = $d->_genIDSQL;
		$this->_genSeqSQL = $d->_genSeqSQL;
		$this->_dropSeqSQL = $d->_dropSeqSQL;

		$d->_init($this);
	}

	function Time()
	{
		if (!empty($this->_driver->_hasdual)) {
			$sql = "select $this->sysTimeStamp from dual";
		}
		else {
			$sql = "select $this->sysTimeStamp";
		}

		$rs = $this->_Execute($sql);
		if ($rs && !$rs->EOF) {
			return $this->UnixTimeStamp(reset($rs->fields));
		}

		return false;
	}

	// returns true or false
	function _connect($argDSN, $argUsername, $argPassword, $argDatabasename, $persist=false)
	{
		$at = strpos($argDSN,':');
		$this->dsnType = substr($argDSN,0,$at);

		if ($argDatabasename) {
			switch($this->dsnType){
				case 'sqlsrv':
					$argDSN .= ';database='.$argDatabasename;
					break;
				case 'mssql':
				case 'mysql':
				case 'oci':
				case 'pgsql':
				case 'sqlite':
				case 'firebird':
				default:
					$argDSN .= ';dbname='.$argDatabasename;
			}
		}
		/*
		* Configure for persistent connection if required,
		* by adding the the pdo parameter into any provided
		* ones
		*/
		if ($persist) {
			$this->pdoParameters[\PDO::ATTR_PERSISTENT] = true;
		}

		try {
			$this->_connectionID = new \PDO($argDSN, $argUsername, $argPassword, $this->pdoParameters);
		} catch (Exception $e) {
			$this->_connectionID = false;
			$this->_errorno = -1;
			//var_dump($e);
			$this->_errormsg = 'Connection attempt failed: '.$e->getMessage();
			return false;
		}

		if ($this->_connectionID) {
			switch(ADODB_ASSOC_CASE){
				case ADODB_ASSOC_CASE_LOWER:
					$m = PDO::CASE_LOWER;
					break;
				case ADODB_ASSOC_CASE_UPPER:
					$m = PDO::CASE_UPPER;
					break;
				default:
				case ADODB_ASSOC_CASE_NATIVE:
					$m = PDO::CASE_NATURAL;
					break;
			}

			//$this->_connectionID->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_SILENT );
			$this->_connectionID->setAttribute(PDO::ATTR_CASE,$m);

			// Now merge in any provided attributes for PDO
			foreach ($this->connectionParameters as $options) {
				foreach($options as $k=>$v) {
					if ($this->debug) {
						ADOconnection::outp('Setting attribute: ' . $k . ' to ' . $v);
					}
					$this->_connectionID->setAttribute($k,$v);
				}
			}

			$class = 'ADODB_pdo_'.$this->dsnType;
			//$this->_connectionID->setAttribute(PDO::ATTR_AUTOCOMMIT,true);
			switch($this->dsnType) {
				case 'mssql':
				case 'mysql':
				case 'oci':
				case 'pgsql':
				case 'sqlite':
				case 'sqlsrv':
				case 'firebird':
				case 'dblib':
					include_once(ADODB_DIR.'/drivers/adodb-pdo_'.$this->dsnType.'.inc.php');
					break;
			}
			if (class_exists($class)) {
				$this->_driver = new $class();
			}
			else {
				$this->_driver = new ADODB_pdo_base();
			}

			$this->_driver->_connectionID = $this->_connectionID;
			$this->_UpdatePDO();
			$this->_driver->database = $this->database;
			return true;
		}
		$this->_driver = new ADODB_pdo_base();
		return false;
	}

	function Concat()
	{
		$args = func_get_args();
		if(method_exists($this->_driver, 'Concat')) {
			return call_user_func_array(array($this->_driver, 'Concat'), $args);
		}

		return call_user_func_array('parent::Concat', $args);
	}

	/**
	 * Triggers a driver-specific request for a bind parameter
	 *
	 * @param string $name
	 * @param string $type
	 *
	 * @return string
	 */
	public function param($name,$type='C') {

		$args = func_get_args();
		if(method_exists($this->_driver, 'param')) {
			// Return the driver specific entry, that mimics the native driver
			return call_user_func_array(array($this->_driver, 'param'), $args);
		}

		// No driver specific method defined, use mysql format '?'
		return call_user_func_array('parent::param', $args);
	}

	// returns true or false
	function _pconnect($argDSN, $argUsername, $argPassword, $argDatabasename)
	{
		return $this->_connect($argDSN, $argUsername, $argPassword, $argDatabasename, true);
	}

	/*------------------------------------------------------------------------------*/


	function SelectLimit($sql,$nrows=-1,$offset=-1,$inputarr=false,$secs2cache=0)
	{
		$save = $this->_driver->fetchMode;
		$this->_driver->fetchMode = $this->fetchMode;
		$this->_driver->debug = $this->debug;
		$ret = $this->_driver->SelectLimit($sql,$nrows,$offset,$inputarr,$secs2cache);
		$this->_driver->fetchMode = $save;
		return $ret;
	}


	function ServerInfo()
	{
		return $this->_driver->ServerInfo();
	}

	function MetaTables($ttype=false,$showSchema=false,$mask=false)
	{
		return $this->_driver->MetaTables($ttype,$showSchema,$mask);
	}

	function MetaColumns($table,$normalize=true)
	{
		return $this->_driver->MetaColumns($table,$normalize);
	}

	public function metaIndexes($table,$normalize=true,$owner=false)
	{
		if (method_exists($this->_driver,'metaIndexes'))
			return $this->_driver->metaIndexes($table,$normalize,$owner);
	}

	/**
	 * Return a list of Primary Keys for a specified table.
	 *
	 * @param string   $table
	 * @param bool     $owner      (optional) not used in this driver
	 *
	 * @return string[]    Array of indexes
	 */
	public function metaPrimaryKeys($table,$owner=false)
	{
		if (method_exists($this->_driver,'metaPrimaryKeys'))
			return $this->_driver->metaPrimaryKeys($table,$owner);
	}

	/**
	 * Returns a list of Foreign Keys associated with a specific table.
	 *
	 * @param string   $table
	 * @param string   $owner      (optional) not used in this driver
	 * @param bool     $upper
	 * @param bool     $associative
	 *
	 * @return string[]|false An array where keys are tables, and values are foreign keys;
	 *                        false if no foreign keys could be found.
	 */
	public function metaForeignKeys($table, $owner = '', $upper = false, $associative = false) {
		if (method_exists($this->_driver,'metaForeignKeys'))
			return $this->_driver->metaForeignKeys($table, $owner, $upper, $associative);
	}

	/**
	 * List procedures or functions in an array.
	 *
	 * @param $procedureNamePattern A procedure name pattern; must match the procedure name as it is stored in the database.
	 * @param $catalog              A catalog name; must match the catalog name as it is stored in the database.
	 * @param $schemaPattern        A schema name pattern.
	 *
	 * @return false|array false if not supported, or array of procedures on current database with structure below
	 *         Array(
	 *           [name_of_procedure] => Array(
	 *             [type] => PROCEDURE or FUNCTION
	 *             [catalog] => Catalog_name
	 *             [schema] => Schema_name
	 *             [remarks] => explanatory comment on the procedure
	 *           )
	 *         )
	 */
	public function metaProcedures($procedureNamePattern = null, $catalog  = null, $schemaPattern  = null) {
		if (method_exists($this->_driver,'metaProcedures'))
			return $this->_driver->metaProcedures($procedureNamePattern,$catalog,$schemaPattern);
		return false;
	}

	function InParameter(&$stmt,&$var,$name,$maxLen=4000,$type=false)
	{
		$obj = $stmt[1];
		if ($type) {
			$obj->bindParam($name, $var, $type, $maxLen);
		}
		else {
			$obj->bindParam($name, $var);
		}
	}

	function OffsetDate($dayFraction,$date=false)
	{
		return $this->_driver->OffsetDate($dayFraction,$date);
	}

	function SelectDB($dbName)
	{
		return $this->_driver->SelectDB($dbName);
	}

	function SQLDate($fmt, $col=false)
	{
		return $this->_driver->SQLDate($fmt, $col);
	}

	function ErrorMsg()
	{
		if ($this->_errormsg !== false) {
			return $this->_errormsg;
		}
		if (!empty($this->_stmt)) {
			$arr = $this->_stmt->errorInfo();
		}
		else if (!empty($this->_connectionID)) {
			$arr = $this->_connectionID->errorInfo();
		}
		else {
			return 'No Connection Established';
		}

		if ($arr) {
			if (sizeof($arr)<2) {
				return '';
			}
			if ((integer)$arr[0]) {
				return $arr[2];
			}
			else {
				return '';
			}
		}
		else {
			return '-1';
		}
	}


	function ErrorNo()
	{
		if ($this->_errorno !== false) {
			return $this->_errorno;
		}
		if (!empty($this->_stmt)) {
			$err = $this->_stmt->errorCode();
		}
		else if (!empty($this->_connectionID)) {
			$arr = $this->_connectionID->errorInfo();
			if (isset($arr[0])) {
				$err = $arr[0];
			}
			else {
				$err = -1;
			}
		} else {
			return 0;
		}

		if ($err == '00000') {
			return 0; // allows empty check
		}
		return $err;
	}

	/**
	 * @param bool $auto_commit
	 * @return void
	 */
	function SetAutoCommit($auto_commit)
	{
		if(method_exists($this->_driver, 'SetAutoCommit')) {
			$this->_driver->SetAutoCommit($auto_commit);
		}
	}

	function SetTransactionMode($transaction_mode)
	{
		if(method_exists($this->_driver, 'SetTransactionMode')) {
			return $this->_driver->SetTransactionMode($transaction_mode);
		}

		return parent::SetTransactionMode($transaction_mode);
	}

	function beginTrans()
	{
		if(method_exists($this->_driver, 'beginTrans')) {
			return $this->_driver->beginTrans();
		}

		if (!$this->hasTransactions) {
			return false;
		}
		if ($this->transOff) {
			return true;
		}
		$this->transCnt += 1;
		$this->_autocommit = false;
		$this->SetAutoCommit(false);

		return $this->_connectionID->beginTransaction();
	}

	function commitTrans($ok=true)
	{

		if(method_exists($this->_driver, 'commitTrans')) {
			return $this->_driver->commitTrans($ok);
		}

		if (!$this->hasTransactions) {
			return false;
		}
		if ($this->transOff) {
			return true;
		}
		if (!$ok) {
			return $this->rollbackTrans();
		}
		if ($this->transCnt) {
			$this->transCnt -= 1;
		}
		$this->_autocommit = true;

		$ret = $this->_connectionID->commit();
		$this->SetAutoCommit(true);
		return $ret;
	}

	function RollbackTrans()
	{
		if(method_exists($this->_driver, 'RollbackTrans')) {
			return $this->_driver->RollbackTrans();
		}

		if (!$this->hasTransactions) {
			return false;
		}
		if ($this->transOff) {
			return true;
		}
		if ($this->transCnt) {
			$this->transCnt -= 1;
		}
		$this->_autocommit = true;

		$ret = $this->_connectionID->rollback();
		$this->SetAutoCommit(true);
		return $ret;
	}

	function Prepare($sql)
	{
		$this->_stmt = $this->_connectionID->prepare($sql);
		if ($this->_stmt) {
			return array($sql,$this->_stmt);
		}

		return false;
	}

	function PrepareStmt($sql)
	{
		$stmt = $this->_connectionID->prepare($sql);
		if (!$stmt) {
			return false;
		}
		$obj = new ADOPDOStatement($stmt,$this);
		return $obj;
	}

	public function createSequence($seqname='adodbseq',$startID=1)
	{
		if(method_exists($this->_driver, 'createSequence')) {
			return $this->_driver->createSequence($seqname, $startID);
		}

		return parent::CreateSequence($seqname, $startID);
	}

	function DropSequence($seqname='adodbseq')
	{
		if(method_exists($this->_driver, 'DropSequence')) {
			return $this->_driver->DropSequence($seqname);
		}

		return parent::DropSequence($seqname);
	}

	function GenID($seqname='adodbseq',$startID=1)
	{
		if(method_exists($this->_driver, 'GenID')) {
			return $this->_driver->GenID($seqname, $startID);
		}

		return parent::GenID($seqname, $startID);
	}


	/* returns queryID or false */
	function _query($sql,$inputarr=false)
	{
		$ok = false;
		if (is_array($sql)) {
			$stmt = $sql[1];
		} else {
			$stmt = $this->_connectionID->prepare($sql);
		}

		if ($stmt) {
			if ($this->_driver instanceof ADODB_pdo) {
				$this->_driver->debug = $this->debug;
			}
			if ($inputarr) {

				/*
				* inputarr must be numeric
				*/
				$inputarr = array_values($inputarr);
				$ok = $stmt->execute($inputarr);
			}
			else {
				$ok = $stmt->execute();
			}
		}


		$this->_errormsg = false;
		$this->_errorno = false;

		if ($ok) {
			$this->_stmt = $stmt;
			return $stmt;
		}

		if ($stmt) {

			$arr = $stmt->errorinfo();
			if ((integer)$arr[1]) {
				$this->_errormsg = $arr[2];
				$this->_errorno = $arr[1];
			}

		} else {
			$this->_errormsg = false;
			$this->_errorno = false;
		}
		return false;
	}

	// returns true or false
	function _close()
	{
		$this->_stmt = false;
		return true;
	}

	function _affectedrows()
	{
		return ($this->_stmt) ? $this->_stmt->rowCount() : 0;
	}

	protected function _insertID($table = '', $column = '')
	{
		return ($this->_connectionID) ? $this->_connectionID->lastInsertId() : 0;
	}

	/**
	 * Quotes a string to be sent to the database.
	 *
	 * If we have an active connection, delegates quoting to the underlying
	 * PDO object PDO::quote(). Otherwise, replace "'" by the value of
	 * $replaceQuote (same behavior as mysqli driver).
	 *
	 * @param string  $s           The string to quote
	 * @param bool   $magic_quotes This param is not used since 5.21.0.
	 *                             It remains for backwards compatibility.
	 *
	 * @return string Quoted string
	 */
	function qStr($s, $magic_quotes = false)
	{
		if ($this->_connectionID) {
			return $this->_connectionID->quote($s);
		}
		return "'" . str_replace("'", $this->replaceQuote, $s) . "'";
	}

}

class ADODB_pdo_base extends ADODB_pdo {

	var $sysDate = "'?'";
	var $sysTimeStamp = "'?'";


	function _init($parentDriver)
	{
		$parentDriver->_bindInputArray = true;
		#$parentDriver->_connectionID->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
	}

	function ServerInfo()
	{
		return ADOConnection::ServerInfo();
	}

	function SelectLimit($sql,$nrows=-1,$offset=-1,$inputarr=false,$secs2cache=0)
	{
		$ret = ADOConnection::SelectLimit($sql,$nrows,$offset,$inputarr,$secs2cache);
		return $ret;
	}

	function MetaTables($ttype=false,$showSchema=false,$mask=false)
	{
		return false;
	}

	function MetaColumns($table,$normalize=true)
	{
		return false;
	}
}

class ADOPDOStatement {

	var $databaseType = "pdo";
	var $dataProvider = "pdo";
	var $_stmt;
	var $_connectionID;

	function __construct($stmt,$connection)
	{
		$this->_stmt = $stmt;
		$this->_connectionID = $connection;
	}

	function Execute($inputArr=false)
	{
		$savestmt = $this->_connectionID->_stmt;
		$rs = $this->_connectionID->Execute(array(false,$this->_stmt),$inputArr);
		$this->_connectionID->_stmt = $savestmt;
		return $rs;
	}

	function InParameter(&$var,$name,$maxLen=4000,$type=false)
	{

		if ($type) {
			$this->_stmt->bindParam($name,$var,$type,$maxLen);
		}
		else {
			$this->_stmt->bindParam($name, $var);
		}
	}

	function Affected_Rows()
	{
		return ($this->_stmt) ? $this->_stmt->rowCount() : 0;
	}

	function ErrorMsg()
	{
		if ($this->_stmt) {
			$arr = $this->_stmt->errorInfo();
		}
		else {
			$arr = $this->_connectionID->errorInfo();
		}

		if (is_array($arr)) {
			if ((integer) $arr[0] && isset($arr[2])) {
				return $arr[2];
			}
			else {
				return '';
			}
		} else {
			return '-1';
		}
	}

	function NumCols()
	{
		return ($this->_stmt) ? $this->_stmt->columnCount() : 0;
	}

	function ErrorNo()
	{
		if ($this->_stmt) {
			return $this->_stmt->errorCode();
		}
		else {
			return $this->_connectionID->errorInfo();
		}
	}
}

/*--------------------------------------------------------------------------------------
	Class Name: Recordset
--------------------------------------------------------------------------------------*/

class ADORecordSet_pdo extends ADORecordSet {

	var $bind = false;
	var $databaseType = "pdo";
	var $dataProvider = "pdo";

	function __construct($id,$mode=false)
	{
		if ($mode === false) {
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		}
		$this->adodbFetchMode = $mode;
		switch($mode) {
		case ADODB_FETCH_NUM: $mode = PDO::FETCH_NUM; break;
		case ADODB_FETCH_ASSOC:  $mode = PDO::FETCH_ASSOC; break;

		case ADODB_FETCH_BOTH:
		default: $mode = PDO::FETCH_BOTH; break;
		}
		$this->fetchMode = $mode;

		$this->_queryID = $id;
		parent::__construct($id);
	}


	function Init()
	{
		if ($this->_inited) {
			return;
		}
		$this->_inited = true;
		if ($this->_queryID) {
			@$this->_initrs();
		}
		else {
			$this->_numOfRows = 0;
			$this->_numOfFields = 0;
		}
		if ($this->_numOfRows != 0 && $this->_currentRow == -1) {
			$this->_currentRow = 0;
			if ($this->EOF = ($this->_fetch() === false)) {
				$this->_numOfRows = 0; // _numOfRows could be -1
			}
		} else {
			$this->EOF = true;
		}
	}

	function _initrs()
	{
	global $ADODB_COUNTRECS;

		$this->_numOfRows = ($ADODB_COUNTRECS) ? @$this->_queryID->rowCount() : -1;
		if (!$this->_numOfRows) {
			$this->_numOfRows = -1;
		}
		$this->_numOfFields = $this->_queryID->columnCount();
	}

	// returns the field object
	function FetchField($fieldOffset = -1)
	{
		$off=$fieldOffset+1; // offsets begin at 1

		$o= new ADOFieldObject();
		$arr = @$this->_queryID->getColumnMeta($fieldOffset);
		if (!$arr) {
			$o->name = 'bad getColumnMeta()';
			$o->max_length = -1;
			$o->type = 'VARCHAR';
			$o->precision = 0;
	#		$false = false;
			return $o;
		}
		//adodb_pr($arr);
		$o->name = $arr['name'];
		if (isset($arr['sqlsrv:decl_type']) && $arr['sqlsrv:decl_type'] <> "null")
		{
		    /*
		    * If the database is SQL server, use the native built-ins
		    */
		    $o->type = $arr['sqlsrv:decl_type'];
		}
		elseif (isset($arr['native_type']) && $arr['native_type'] <> "null")
		{
		    $o->type = $arr['native_type'];
		}
		else
		{
		     $o->type = adodb_pdo_type($arr['pdo_type']);
		}

		$o->max_length = $arr['len'];
		$o->precision = $arr['precision'];

		switch(ADODB_ASSOC_CASE) {
			case ADODB_ASSOC_CASE_LOWER:
				$o->name = strtolower($o->name);
				break;
			case ADODB_ASSOC_CASE_UPPER:
				$o->name = strtoupper($o->name);
				break;
		}
		return $o;
	}

	function _seek($row)
	{
		return false;
	}

	function _fetch()
	{
		if (!$this->_queryID) {
			return false;
		}

		$this->fields = $this->_queryID->fetch($this->fetchMode);
		return !empty($this->fields);
	}

	function _close()
	{
		$this->_queryID = false;
	}

	function Fields($colname)
	{
		if ($this->adodbFetchMode != ADODB_FETCH_NUM) {
			return @$this->fields[$colname];
		}

		if (!$this->bind) {
			$this->bind = array();
			for ($i=0; $i < $this->_numOfFields; $i++) {
				$o = $this->FetchField($i);
				$this->bind[strtoupper($o->name)] = $i;
			}
		}
		return $this->fields[$this->bind[strtoupper($colname)]];
	}

}

class ADORecordSet_array_pdo extends ADORecordSet_array {}
