<?php
/**
 * Library for basic performance monitoring and tuning
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

// Simple guide to configuring db2: so-so http://www.devx.com/gethelpon/10MinuteSolution/16575

// SELECT * FROM TABLE(SNAPSHOT_APPL('SAMPLE', -1)) as t
class perf_db2 extends adodb_perf{
	var $createTableSQL = "CREATE TABLE adodb_logsql (
		  created TIMESTAMP NOT NULL,
		  sql0 varchar(250) NOT NULL,
		  sql1 varchar(4000) NOT NULL,
		  params varchar(3000) NOT NULL,
		  tracer varchar(500) NOT NULL,
		  timer decimal(16,6) NOT NULL
		)";

	var $settings = array(
	'Ratios',
		'data cache hit ratio' => array('RATIO',
			"SELECT
				case when sum(POOL_DATA_L_READS+POOL_INDEX_L_READS)=0 then 0
				else 100*(1-sum(POOL_DATA_P_READS+POOL_INDEX_P_READS)/sum(POOL_DATA_L_READS+POOL_INDEX_L_READS)) end
				FROM TABLE(SNAPSHOT_APPL('',-2)) as t",
			'=WarnCacheRatio'),

	'Data Cache',
		'data cache buffers' => array('DATAC',
		'select sum(npages) from SYSCAT.BUFFERPOOLS',
			'See <a href=http://www7b.boulder.ibm.com/dmdd/library/techarticle/anshum/0107anshum.html#bufferpoolsize>tuning reference</a>.' ),
		'cache blocksize' => array('DATAC',
		'select avg(pagesize) from SYSCAT.BUFFERPOOLS',
			'' ),
		'data cache size' => array('DATAC',
		'select sum(npages*pagesize) from SYSCAT.BUFFERPOOLS',
			'' ),
	'Connections',
		'current connections' => array('SESS',
			"SELECT count(*) FROM TABLE(SNAPSHOT_APPL_INFO('',-2)) as t",
			''),

		false
	);


	function __construct(&$conn)
	{
		$this->conn = $conn;
	}

	function Explain($sql,$partial=false)
	{
		$save = $this->conn->LogSQL(false);
		if ($partial) {
			$sqlq = $this->conn->qstr($sql.'%');
			$arr = $this->conn->GetArray("select distinct sql1 from adodb_logsql where sql1 like $sqlq");
			if ($arr) {
				foreach($arr as $row) {
					$sql = reset($row);
					if (crc32($sql) == $partial) break;
				}
			}
		}
		$qno = rand();
		$ok = $this->conn->Execute("EXPLAIN PLAN SET QUERYNO=$qno FOR $sql");
		ob_start();
		if (!$ok) echo "<p>Have EXPLAIN tables been created?</p>";
		else {
			$rs = $this->conn->Execute("select * from explain_statement where queryno=$qno");
			if ($rs) rs2html($rs);
		}
		$s = ob_get_contents();
		ob_end_clean();
		$this->conn->LogSQL($save);

		$s .= $this->Tracer($sql);
		return $s;
	}

	/**
	 *  Gets a list of tables
	 *
	 * @param int $throwaway discarded variable to match the parent method
	 * @return string The formatted table list
	 */
	function Tables($throwaway=0)
	{
		$rs = $this->conn->Execute("select tabschema,tabname,card as rows,
			npages pages_used,fpages pages_allocated, tbspace tablespace
			from syscat.tables where tabschema not in ('SYSCAT','SYSIBM','SYSSTAT') order by 1,2");
		return rs2html($rs,false,false,false,false);
	}
}
