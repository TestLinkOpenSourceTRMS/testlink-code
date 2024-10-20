<?php
/**
 * DB2 driver via ODBC
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

if (!defined('_ADODB_ODBC_LAYER')) {
	include_once(ADODB_DIR."/drivers/adodb-odbc.inc.php");
}
if (!defined('ADODB_ODBC_DB2')){
define('ADODB_ODBC_DB2',1);

class ADODB_ODBC_DB2 extends ADODB_odbc {
	var $databaseType = "db2";
	var $concat_operator = '||';
	var $sysTime = 'CURRENT TIME';
	var $sysDate = 'CURRENT DATE';
	var $sysTimeStamp = 'CURRENT TIMESTAMP';
	// The complete string representation of a timestamp has the form
	// yyyy-mm-dd-hh.mm.ss.nnnnnn.
	var $fmtTimeStamp = "'Y-m-d-H.i.s'";
	var $ansiOuter = true;
	var $identitySQL = 'values IDENTITY_VAL_LOCAL()';
	var $_bindInputArray = true;
	 var $hasInsertID = true;
	var $rsPrefix = 'ADORecordset_odbc_';

	function __construct()
	{
		if (strncmp(PHP_OS,'WIN',3) === 0) $this->curmode = SQL_CUR_USE_ODBC;
		parent::__construct();
	}

	function IfNull( $field, $ifNull )
	{
		return " COALESCE($field, $ifNull) "; // if DB2 UDB
	}

	function ServerInfo()
	{
		//odbc_setoption($this->_connectionID,1,101 /*SQL_ATTR_ACCESS_MODE*/, 1 /*SQL_MODE_READ_ONLY*/);
		$vers = $this->GetOne('select versionnumber from sysibm.sysversions');
		//odbc_setoption($this->_connectionID,1,101, 0 /*SQL_MODE_READ_WRITE*/);
		return array('description'=>'DB2 ODBC driver', 'version'=>$vers);
	}

	protected function _insertID($table = '', $column = '')
	{
		return $this->GetOne($this->identitySQL);
	}

	function RowLock($tables,$where,$col='1 as adodbignore')
	{
		if ($this->_autocommit) $this->BeginTrans();
		return $this->GetOne("select $col from $tables where $where for update");
	}

	function MetaTables($ttype=false,$showSchema=false, $qtable="%", $qschema="%")
	{
	global $ADODB_FETCH_MODE;

		$savem = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		$qid = odbc_tables($this->_connectionID, "", $qschema, $qtable, "");

		$rs = new ADORecordSet_odbc($qid);

		$ADODB_FETCH_MODE = $savem;
		if (!$rs) {
			$false = false;
			return $false;
		}

		$arr = $rs->GetArray();
		//print_r($arr);

		$rs->Close();
		$arr2 = array();

		if ($ttype) {
			$isview = strncmp($ttype,'V',1) === 0;
		}
		for ($i=0; $i < sizeof($arr); $i++) {

			if (!$arr[$i][2]) continue;
			if (strncmp($arr[$i][1],'SYS',3) === 0) continue;

			$type = $arr[$i][3];

			if ($showSchema) $arr[$i][2] = $arr[$i][1].'.'.$arr[$i][2];

			if ($ttype) {
				if ($isview) {
					if (strncmp($type,'V',1) === 0) $arr2[] = $arr[$i][2];
				} else if (strncmp($type,'T',1) === 0) $arr2[] = $arr[$i][2];
			} else if (strncmp($type,'S',1) !== 0) $arr2[] = $arr[$i][2];
		}
		return $arr2;
	}

	function MetaIndexes ($table, $primary = FALSE, $owner=false)
	{
        // save old fetch mode
        global $ADODB_FETCH_MODE;
        $save = $ADODB_FETCH_MODE;
        $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
        if ($this->fetchMode !== FALSE) {
               $savem = $this->SetFetchMode(FALSE);
        }
		$false = false;
		// get index details
		$table = strtoupper($table);
		$SQL="SELECT NAME, UNIQUERULE, COLNAMES FROM SYSIBM.SYSINDEXES WHERE TBNAME='$table'";
        if ($primary)
			$SQL.= " AND UNIQUERULE='P'";
		$rs = $this->Execute($SQL);
        if (!is_object($rs)) {
			if (isset($savem))
				$this->SetFetchMode($savem);
			$ADODB_FETCH_MODE = $save;
            return $false;
        }
		$indexes = array ();
        // parse index data into array
        while ($row = $rs->FetchRow()) {
			$indexes[$row[0]] = array(
			   'unique' => ($row[1] == 'U' || $row[1] == 'P'),
			   'columns' => array()
			);
			$cols = ltrim($row[2],'+');
			$indexes[$row[0]]['columns'] = explode('+', $cols);
        }
		if (isset($savem)) {
            $this->SetFetchMode($savem);
			$ADODB_FETCH_MODE = $save;
		}
        return $indexes;
	}

	// Format date column in sql string given an input format that understands Y M D
	function SQLDate($fmt, $col=false)
	{
	// use right() and replace() ?
		if (!$col) $col = $this->sysDate;
		$s = '';

		$len = strlen($fmt);
		for ($i=0; $i < $len; $i++) {
			if ($s) $s .= '||';
			$ch = $fmt[$i];
			switch($ch) {
			case 'Y':
			case 'y':
				$s .= "char(year($col))";
				break;
			case 'M':
				$s .= "substr(monthname($col),1,3)";
				break;
			case 'm':
				$s .= "right(digits(month($col)),2)";
				break;
			case 'D':
			case 'd':
				$s .= "right(digits(day($col)),2)";
				break;
			case 'H':
			case 'h':
				if ($col != $this->sysDate) $s .= "right(digits(hour($col)),2)";
				else $s .= "''";
				break;
			case 'i':
			case 'I':
				if ($col != $this->sysDate)
					$s .= "right(digits(minute($col)),2)";
					else $s .= "''";
				break;
			case 'S':
			case 's':
				if ($col != $this->sysDate)
					$s .= "right(digits(second($col)),2)";
				else $s .= "''";
				break;
			default:
				if ($ch == '\\') {
					$i++;
					$ch = substr($fmt,$i,1);
				}
				$s .= $this->qstr($ch);
			}
		}
		return $s;
	}


	function SelectLimit($sql, $nrows = -1, $offset = -1, $inputArr = false, $secs2cache = 0)
	{
		$nrows = (integer) $nrows;
		if ($offset <= 0) {
		// could also use " OPTIMIZE FOR $nrows ROWS "
			if ($nrows >= 0) $sql .=  " FETCH FIRST $nrows ROWS ONLY ";
			$rs = $this->Execute($sql,$inputArr);
		} else {
			if ($offset > 0 && $nrows < 0);
			else {
				$nrows += $offset;
				$sql .=  " FETCH FIRST $nrows ROWS ONLY ";
			}
			$rs = ADOConnection::SelectLimit($sql,-1,$offset,$inputArr);
		}

		return $rs;
	}

};


class  ADORecordSet_odbc_db2 extends ADORecordSet_odbc {

	var $databaseType = "db2";

	function MetaType($t,$len=-1,$fieldobj=false)
	{
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}

		switch (strtoupper($t)) {
		case 'VARCHAR':
		case 'CHAR':
		case 'CHARACTER':
		case 'C':
			if ($len <= $this->blobSize) return 'C';

		case 'LONGCHAR':
		case 'TEXT':
		case 'CLOB':
		case 'DBCLOB': // double-byte
		case 'X':
			return 'X';

		case 'BLOB':
		case 'GRAPHIC':
		case 'VARGRAPHIC':
			return 'B';

		case 'DATE':
		case 'D':
			return 'D';

		case 'TIME':
		case 'TIMESTAMP':
		case 'T':
			return 'T';

		//case 'BOOLEAN':
		//case 'BIT':
		//	return 'L';

		//case 'COUNTER':
		//	return 'R';

		case 'INT':
		case 'INTEGER':
		case 'BIGINT':
		case 'SMALLINT':
		case 'I':
			return 'I';

		default: return ADODB_DEFAULT_METATYPE;
		}
	}
}

} //define
