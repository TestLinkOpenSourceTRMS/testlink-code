<?php
/**
 * ADOdb Proxy Server.
 *
 * @deprecated 5.21.0
 *
 * Security warning - use with extreme caution !
 * Depending on how it is setup, this feature can potentially expose the
 * database to attacks, particularly if used with a privileged user account.
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

/* Documentation on usage is at https://adodb.org/dokuwiki/doku.php?id=v5:proxy:proxy_index
 *
 * Legal query string parameters:
 *
 * sql = holds sql string
 * nrows = number of rows to return
 * offset = skip offset rows of data
 * fetch = $ADODB_FETCH_MODE
 *
 * example:
 *
 * http://localhost/php/server.php?sql=select+*+from+table&nrows=10&offset=2
 */


/*
 * Define the IP address you want to accept requests from
 * as a security measure. If blank we accept anyone promisciously!
 */
$ACCEPTIP = '127.0.0.1';

/*
 * Connection parameters
 */
$driver = 'mysqli';
$host = 'localhost'; // DSN for odbc
$uid = 'root';
$pwd = 'garbase-it-is';
$database = 'test';

/*============================ DO NOT MODIFY BELOW HERE =================================*/
// $sep must match csv2rs() in adodb.inc.php
$sep = ' :::: ';

include('./adodb.inc.php');
include_once(ADODB_DIR.'/adodb-csvlib.inc.php');

function err($s)
{
	die('**** '.$s.' ');
}

///////////////////////////////////////// DEFINITIONS


$remote = $_SERVER["REMOTE_ADDR"];


if (!empty($ACCEPTIP))
 if ($remote != '127.0.0.1' && $remote != $ACCEPTIP)
 	err("Unauthorised client: '$remote'");


if (empty($_REQUEST['sql'])) err('No SQL');


$conn = ADONewConnection($driver);

if (!$conn->connect($host,$uid,$pwd,$database)) err($conn->errorNo(). $sep . $conn->errorMsg());
$sql = $_REQUEST['sql'];

if (isset($_REQUEST['fetch']))
	$ADODB_FETCH_MODE = $_REQUEST['fetch'];

if (isset($_REQUEST['nrows'])) {
	$nrows = $_REQUEST['nrows'];
	$offset = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : -1;
	$rs = $conn->selectLimit($sql,$nrows,$offset);
} else
	$rs = $conn->execute($sql);
if ($rs){
	//$rs->timeToLive = 1;
	echo _rs2serialize($rs,$conn,$sql);
	$rs->close();
} else
	err($conn->errorNo(). $sep .$conn->errorMsg());
