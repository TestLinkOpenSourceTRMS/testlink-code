<?php
/**
 * SAPDB data driver
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
if (!defined('ADODB_SAPDB')){
define('ADODB_SAPDB',1);

class ADODB_SAPDB extends ADODB_odbc {
	var $databaseType = "sapdb";
	var $concat_operator = '||';
	var $sysDate = 'DATE';
	var $sysTimeStamp = 'TIMESTAMP';
	var $fmtDate = "'Y-m-d'";	/// used by DBDate() as the default date format used by the database
	var $fmtTimeStamp = "'Y-m-d H:i:s'"; /// used by DBTimeStamp as the default timestamp fmt.
	var $hasInsertId = true;
	var $_bindInputArray = true;

	function ServerInfo()
	{
		$info = ADODB_odbc::ServerInfo();
		if (!$info['version'] && preg_match('/([0-9.]+)/',$info['description'],$matches)) {
			$info['version'] = $matches[1];
		}
		return $info;
	}

	function MetaPrimaryKeys($table, $owner = false)
	{
		$table = $this->Quote(strtoupper($table));

		return $this->GetCol("SELECT columnname FROM COLUMNS WHERE tablename=$table AND mode='KEY' ORDER BY pos");
	}

	function MetaIndexes ($table, $primary = FALSE, $owner = false)
	{
		$table = $this->Quote(strtoupper($table));

		$sql = "SELECT INDEXNAME,TYPE,COLUMNNAME FROM INDEXCOLUMNS ".
			" WHERE TABLENAME=$table".
			" ORDER BY INDEXNAME,COLUMNNO";

		global $ADODB_FETCH_MODE;
		$save = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		if ($this->fetchMode !== FALSE) {
			$savem = $this->SetFetchMode(FALSE);
		}

		$rs = $this->Execute($sql);
		if (isset($savem)) {
			$this->SetFetchMode($savem);
		}
		$ADODB_FETCH_MODE = $save;

		if (!is_object($rs)) {
			return FALSE;
		}

		$indexes = array();
		while ($row = $rs->FetchRow()) {
			$indexes[$row[0]]['unique'] = $row[1] == 'UNIQUE';
			$indexes[$row[0]]['columns'][] = $row[2];
		}
		if ($primary) {
			$indexes['SYSPRIMARYKEYINDEX'] = array(
					'unique' => True,	// by definition
					'columns' => $this->GetCol("SELECT columnname FROM COLUMNS WHERE tablename=$table AND mode='KEY' ORDER BY pos"),
				);
		}
		return $indexes;
	}

	function MetaColumns ($table, $normalize = true)
	{
		global $ADODB_FETCH_MODE;
		$save = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		if ($this->fetchMode !== FALSE) {
			$savem = $this->SetFetchMode(FALSE);
		}
		$table = $this->Quote(strtoupper($table));

		$retarr = array();
		foreach($this->GetAll("SELECT COLUMNNAME,DATATYPE,LEN,DEC,NULLABLE,MODE,\"DEFAULT\",CASE WHEN \"DEFAULT\" IS NULL THEN 0 ELSE 1 END AS HAS_DEFAULT FROM COLUMNS WHERE tablename=$table ORDER BY pos") as $column)
		{
			$fld = new ADOFieldObject();
			$fld->name = $column[0];
			$fld->type = $column[1];
			$fld->max_length = $fld->type == 'LONG' ? 2147483647 : $column[2];
			$fld->scale = $column[3];
			$fld->not_null = $column[4] == 'NO';
			$fld->primary_key = $column[5] == 'KEY';
			if ($fld->has_default = $column[7]) {
				if ($fld->primary_key && $column[6] == 'DEFAULT SERIAL (1)') {
					$fld->auto_increment = true;
					$fld->has_default = false;
				} else {
					$fld->default_value = $column[6];
					switch($fld->type) {
						case 'VARCHAR':
						case 'CHARACTER':
						case 'LONG':
							$fld->default_value = $column[6];
							break;
						default:
							$fld->default_value = trim($column[6]);
							break;
					}
				}
			}
			$retarr[$fld->name] = $fld;
		}
		if (isset($savem)) {
			$this->SetFetchMode($savem);
		}
		$ADODB_FETCH_MODE = $save;

		return $retarr;
	}

	function MetaColumnNames($table, $numIndexes = false, $useattnum = false)
	{
		$table = $this->Quote(strtoupper($table));

		return $this->GetCol("SELECT columnname FROM COLUMNS WHERE tablename=$table ORDER BY pos");
	}

	// unlike it seems, this depends on the db-session and works in a multiuser environment
	protected function _insertID($table = '', $column = '')
	{
		return empty($table) ? False : $this->GetOne("SELECT $table.CURRVAL FROM DUAL");
	}

	/*
		SelectLimit implementation problems:

		The following will return random 10 rows as order by performed after "WHERE rowno<10"
		which is not ideal...

			select * from table where rowno < 10 order by 1

		This means that we have to use the adoconnection base class SelectLimit when
		there is an "order by".

		See http://listserv.sap.com/pipermail/sapdb.general/2002-January/010405.html
	 */

};


class ADORecordSet_sapdb extends ADORecordSet_odbc {

	var $databaseType = "sapdb";

}

} //define
