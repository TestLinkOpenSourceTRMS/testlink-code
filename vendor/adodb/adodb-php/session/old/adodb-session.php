<?php
/**
 * ADOdb Session Management
 *
 * This file provides PHP4 session management using the ADODB database
 * wrapper library.
 *
 * @deprecated
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

/*
 Example
 =======

	include('adodb.inc.php');
	include('adodb-session.php');
	session_start();
	session_register('AVAR');
	$_SESSION['AVAR'] += 1;
	print "
-- \$_SESSION['AVAR']={$_SESSION['AVAR']}</p>";

To force non-persistent connections, call adodb_session_open first before session_start():

	include('adodb.inc.php');
	include('adodb-session.php');
	adodb_sess_open(false,false,false);
	session_start();
	session_register('AVAR');
	$_SESSION['AVAR'] += 1;
	print "
-- \$_SESSION['AVAR']={$_SESSION['AVAR']}</p>";


 Installation
 ============
 1. Create this table in your database (syntax might vary depending on your db):

  create table sessions (
	   SESSKEY char(32) not null,
	   EXPIRY int(11) unsigned not null,
	   EXPIREREF varchar(64),
	   DATA text not null,
	  primary key (sesskey)
  );

  For oracle:
    create table sessions (
	   SESSKEY char(32) not null,
	   EXPIRY DECIMAL(16)  not null,
	   EXPIREREF varchar(64),
	   DATA varchar(4000) not null,
	  primary key (sesskey)
  );


  2. Then define the following parameters. You can either modify
     this file, or define them before this file is included:

  	$ADODB_SESSION_DRIVER='database driver, eg. mysql or ibase';
	$ADODB_SESSION_CONNECT='server to connect to';
	$ADODB_SESSION_USER ='user';
	$ADODB_SESSION_PWD ='password';
	$ADODB_SESSION_DB ='database';
	$ADODB_SESSION_TBL = 'sessions'

  3. Recommended is PHP 4.1.0 or later. There are documented
	 session bugs in earlier versions of PHP.

  4. If you want to receive notifications when a session expires, then
  	 you can tag a session with an EXPIREREF, and before the session
	 record is deleted, we can call a function that will pass the EXPIREREF
	 as the first parameter, and the session key as the second parameter.

	 To do this, define a notification function, say NotifyFn:

	 	function NotifyFn($expireref, $sesskey)
	 	{
	 	}

	 Then you need to define a global variable $ADODB_SESSION_EXPIRE_NOTIFY.
	 This is an array with 2 elements, the first being the name of the variable
	 you would like to store in the EXPIREREF field, and the 2nd is the
	 notification function's name.

	 In this example, we want to be notified when a user's session
	 has expired, so we store the user id in the global variable $USERID,
	 store this value in the EXPIREREF field:

	 	$ADODB_SESSION_EXPIRE_NOTIFY = array('USERID','NotifyFn');

	Then when the NotifyFn is called, we are passed the $USERID as the first
	parameter, eg. NotifyFn($userid, $sesskey).
*/

if (!defined('_ADODB_LAYER')) {
	include (dirname(__FILE__).'/adodb.inc.php');
}

if (!defined('ADODB_SESSION')) {

 define('ADODB_SESSION',1);

 /* if database time and system time is difference is greater than this, then give warning */
 define('ADODB_SESSION_SYNCH_SECS',60);

 /*
	Thanks Joe Li. See PHPLens Issue No: 11487&x=1
*/
function adodb_session_regenerate_id()
{
	$conn = ADODB_Session::_conn();
	if (!$conn) return false;

	$old_id = session_id();
	if (function_exists('session_regenerate_id')) {
		session_regenerate_id();
	} else {
		session_id(md5(uniqid(rand(), true)));
		$ck = session_get_cookie_params();
		setcookie(session_name(), session_id(), false, $ck['path'], $ck['domain'], $ck['secure']);
		//@session_start();
	}
	$new_id = session_id();
	$ok = $conn->Execute('UPDATE '. ADODB_Session::table(). ' SET sesskey='. $conn->qstr($new_id). ' WHERE sesskey='.$conn->qstr($old_id));

	/* it is possible that the update statement fails due to a collision */
	if (!$ok) {
		session_id($old_id);
		if (empty($ck)) $ck = session_get_cookie_params();
		setcookie(session_name(), session_id(), false, $ck['path'], $ck['domain'], $ck['secure']);
		return false;
	}

	return true;
}

/****************************************************************************************\
	Global definitions
\****************************************************************************************/
GLOBAL 	$ADODB_SESSION_CONNECT,
	$ADODB_SESSION_DRIVER,
	$ADODB_SESSION_USER,
	$ADODB_SESSION_PWD,
	$ADODB_SESSION_DB,
	$ADODB_SESS_CONN,
	$ADODB_SESS_LIFE,
	$ADODB_SESS_DEBUG,
	$ADODB_SESSION_EXPIRE_NOTIFY,
	$ADODB_SESSION_CRC,
	$ADODB_SESSION_TBL;


	$ADODB_SESS_LIFE = ini_get('session.gc_maxlifetime');
	if ($ADODB_SESS_LIFE <= 1) {
	 // bug in PHP 4.0.3 pl 1  -- how about other versions?
	 //print "<h3>Session Error: PHP.INI setting <i>session.gc_maxlifetime</i>not set: $ADODB_SESS_LIFE</h3>";
	 	$ADODB_SESS_LIFE=1440;
	}
	$ADODB_SESSION_CRC = false;
	//$ADODB_SESS_DEBUG = true;

	//////////////////////////////////
	/* SET THE FOLLOWING PARAMETERS */
	//////////////////////////////////

	if (empty($ADODB_SESSION_DRIVER)) {
		$ADODB_SESSION_DRIVER='mysql';
		$ADODB_SESSION_CONNECT='localhost';
		$ADODB_SESSION_USER ='root';
		$ADODB_SESSION_PWD ='';
		$ADODB_SESSION_DB ='xphplens_2';
	}

	if (empty($ADODB_SESSION_EXPIRE_NOTIFY)) {
		$ADODB_SESSION_EXPIRE_NOTIFY = false;
	}
	//  Made table name configurable - by David Johnson djohnson@inpro.net
	if (empty($ADODB_SESSION_TBL)){
		$ADODB_SESSION_TBL = 'sessions';
	}

	/*
	$ADODB_SESS['driver'] = $ADODB_SESSION_DRIVER;
	$ADODB_SESS['connect'] = $ADODB_SESSION_CONNECT;
	$ADODB_SESS['user'] = $ADODB_SESSION_USER;
	$ADODB_SESS['pwd'] = $ADODB_SESSION_PWD;
	$ADODB_SESS['db'] = $ADODB_SESSION_DB;
	$ADODB_SESS['life'] = $ADODB_SESS_LIFE;
	$ADODB_SESS['debug'] = $ADODB_SESS_DEBUG;

	$ADODB_SESS['debug'] = $ADODB_SESS_DEBUG;
	$ADODB_SESS['table'] = $ADODB_SESS_TBL;
	*/

/****************************************************************************************\
	Create the connection to the database.

	If $ADODB_SESS_CONN already exists, reuse that connection
\****************************************************************************************/
function adodb_sess_open($save_path, $session_name,$persist=true)
{
GLOBAL $ADODB_SESS_CONN;
	if (isset($ADODB_SESS_CONN)) return true;

GLOBAL 	$ADODB_SESSION_CONNECT,
	$ADODB_SESSION_DRIVER,
	$ADODB_SESSION_USER,
	$ADODB_SESSION_PWD,
	$ADODB_SESSION_DB,
	$ADODB_SESS_DEBUG;

	// cannot use & below - do not know why...
	$ADODB_SESS_CONN = ADONewConnection($ADODB_SESSION_DRIVER);
	if (!empty($ADODB_SESS_DEBUG)) {
		$ADODB_SESS_CONN->debug = true;
		ADOConnection::outp( " conn=$ADODB_SESSION_CONNECT user=$ADODB_SESSION_USER pwd=$ADODB_SESSION_PWD db=$ADODB_SESSION_DB ");
	}
	if ($persist) $ok = $ADODB_SESS_CONN->PConnect($ADODB_SESSION_CONNECT,
			$ADODB_SESSION_USER,$ADODB_SESSION_PWD,$ADODB_SESSION_DB);
	else $ok = $ADODB_SESS_CONN->Connect($ADODB_SESSION_CONNECT,
			$ADODB_SESSION_USER,$ADODB_SESSION_PWD,$ADODB_SESSION_DB);

	if (!$ok) ADOConnection::outp( "
-- Session: connection failed</p>",false);
}

/****************************************************************************************\
	Close the connection
\****************************************************************************************/
function adodb_sess_close()
{
global $ADODB_SESS_CONN;

	if ($ADODB_SESS_CONN) $ADODB_SESS_CONN->Close();
	return true;
}

/****************************************************************************************\
	Slurp in the session variables and return the serialized string
\****************************************************************************************/
function adodb_sess_read($key)
{
global $ADODB_SESS_CONN,$ADODB_SESSION_TBL,$ADODB_SESSION_CRC;

	$rs = $ADODB_SESS_CONN->Execute("SELECT data FROM $ADODB_SESSION_TBL WHERE sesskey = '$key' AND expiry >= " . time());
	if ($rs) {
		if ($rs->EOF) {
			$v = '';
		} else
			$v = rawurldecode(reset($rs->fields));

		$rs->Close();

		// new optimization adodb 2.1
		$ADODB_SESSION_CRC = strlen($v).crc32($v);

		return $v;
	}

	return ''; // thx to Jorma Tuomainen, webmaster#wizactive.com
}

/****************************************************************************************\
	Write the serialized data to a database.

	If the data has not been modified since adodb_sess_read(), we do not write.
\****************************************************************************************/
function adodb_sess_write($key, $val)
{
	global
		$ADODB_SESS_CONN,
		$ADODB_SESS_LIFE,
		$ADODB_SESSION_TBL,
		$ADODB_SESS_DEBUG,
		$ADODB_SESSION_CRC,
		$ADODB_SESSION_EXPIRE_NOTIFY;

	$expiry = time() + $ADODB_SESS_LIFE;

	// crc32 optimization since adodb 2.1
	// now we only update expiry date, thx to sebastian thom in adodb 2.32
	if ($ADODB_SESSION_CRC !== false && $ADODB_SESSION_CRC == strlen($val).crc32($val)) {
		if ($ADODB_SESS_DEBUG) echo "
-- Session: Only updating date - crc32 not changed</p>";
		$qry = "UPDATE $ADODB_SESSION_TBL SET expiry=$expiry WHERE sesskey='$key' AND expiry >= " . time();
		$rs = $ADODB_SESS_CONN->Execute($qry);
		return true;
	}
	$val = rawurlencode($val);

	$arr = array('sesskey' => $key, 'expiry' => $expiry, 'data' => $val);
	if ($ADODB_SESSION_EXPIRE_NOTIFY) {
		$var = reset($ADODB_SESSION_EXPIRE_NOTIFY);
		global $$var;
		$arr['expireref'] = $$var;
	}
	$rs = $ADODB_SESS_CONN->Replace($ADODB_SESSION_TBL,$arr,
    	'sesskey',$autoQuote = true);

	if (!$rs) {
		ADOConnection::outp( '
-- Session Replace: '.$ADODB_SESS_CONN->ErrorMsg().'</p>',false);
	}  else {
		// bug in access driver (could be odbc?) means that info is not committed
		// properly unless select statement executed in Win2000
		if ($ADODB_SESS_CONN->databaseType == 'access')
			$rs = $ADODB_SESS_CONN->Execute("select sesskey from $ADODB_SESSION_TBL WHERE sesskey='$key'");
	}
	return !empty($rs);
}

function adodb_sess_destroy($key)
{
	global $ADODB_SESS_CONN, $ADODB_SESSION_TBL,$ADODB_SESSION_EXPIRE_NOTIFY;

	if ($ADODB_SESSION_EXPIRE_NOTIFY) {
		reset($ADODB_SESSION_EXPIRE_NOTIFY);
		$fn = next($ADODB_SESSION_EXPIRE_NOTIFY);
		$savem = $ADODB_SESS_CONN->SetFetchMode(ADODB_FETCH_NUM);
		$rs = $ADODB_SESS_CONN->Execute("SELECT expireref,sesskey FROM $ADODB_SESSION_TBL WHERE sesskey='$key'");
		$ADODB_SESS_CONN->SetFetchMode($savem);
		if ($rs) {
			$ADODB_SESS_CONN->BeginTrans();
			while (!$rs->EOF) {
				$ref = $rs->fields[0];
				$key = $rs->fields[1];
				$fn($ref,$key);
				$del = $ADODB_SESS_CONN->Execute("DELETE FROM $ADODB_SESSION_TBL WHERE sesskey='$key'");
				$rs->MoveNext();
			}
			$ADODB_SESS_CONN->CommitTrans();
		}
	} else {
		$qry = "DELETE FROM $ADODB_SESSION_TBL WHERE sesskey = '$key'";
		$rs = $ADODB_SESS_CONN->Execute($qry);
	}
	return $rs ? true : false;
}

function adodb_sess_gc($maxlifetime)
{
	global $ADODB_SESS_DEBUG, $ADODB_SESS_CONN, $ADODB_SESSION_TBL,$ADODB_SESSION_EXPIRE_NOTIFY;

	if ($ADODB_SESSION_EXPIRE_NOTIFY) {
		reset($ADODB_SESSION_EXPIRE_NOTIFY);
		$fn = next($ADODB_SESSION_EXPIRE_NOTIFY);
		$savem = $ADODB_SESS_CONN->SetFetchMode(ADODB_FETCH_NUM);
		$t = time();
		$rs = $ADODB_SESS_CONN->Execute("SELECT expireref,sesskey FROM $ADODB_SESSION_TBL WHERE expiry < $t");
		$ADODB_SESS_CONN->SetFetchMode($savem);
		if ($rs) {
			$ADODB_SESS_CONN->BeginTrans();
			while (!$rs->EOF) {
				$ref = $rs->fields[0];
				$key = $rs->fields[1];
				$fn($ref,$key);
				$del = $ADODB_SESS_CONN->Execute("DELETE FROM $ADODB_SESSION_TBL WHERE sesskey='$key'");
				$rs->MoveNext();
			}
			$rs->Close();

			$ADODB_SESS_CONN->CommitTrans();

		}
	} else {
		$qry = "DELETE FROM $ADODB_SESSION_TBL WHERE expiry < " . time();
		$ADODB_SESS_CONN->Execute($qry);

		if ($ADODB_SESS_DEBUG) ADOConnection::outp("
-- <b>Garbage Collection</b>: $qry</p>");
	}
	// suggested by Cameron, "GaM3R" <gamr@outworld.cx>
	if (defined('ADODB_SESSION_OPTIMIZE')) {
	global $ADODB_SESSION_DRIVER;

		switch( $ADODB_SESSION_DRIVER ) {
			case 'mysql':
			case 'mysqlt':
				$opt_qry = 'OPTIMIZE TABLE '.$ADODB_SESSION_TBL;
				break;
			case 'postgresql':
			case 'postgresql7':
				$opt_qry = 'VACUUM '.$ADODB_SESSION_TBL;
				break;
		}
		if (!empty($opt_qry)) {
			$ADODB_SESS_CONN->Execute($opt_qry);
		}
	}
	if ($ADODB_SESS_CONN->dataProvider === 'oci8') $sql = 'select  TO_CHAR('.($ADODB_SESS_CONN->sysTimeStamp).', \'RRRR-MM-DD HH24:MI:SS\') from '. $ADODB_SESSION_TBL;
	else $sql = 'select '.$ADODB_SESS_CONN->sysTimeStamp.' from '. $ADODB_SESSION_TBL;

	$rs = $ADODB_SESS_CONN->SelectLimit($sql,1);
	if ($rs && !$rs->EOF) {

		$dbts = reset($rs->fields);
		$rs->Close();
		$dbt = $ADODB_SESS_CONN->UnixTimeStamp($dbts);
		$t = time();

		if (abs($dbt - $t) >= ADODB_SESSION_SYNCH_SECS) {

			$msg =
			__FILE__.": Server time for webserver {$_SERVER['HTTP_HOST']} not in synch with database: database=$dbt ($dbts), webserver=$t (diff=".(abs($dbt-$t)/3600)." hrs)";
			error_log($msg);
			if ($ADODB_SESS_DEBUG) ADOConnection::outp("
-- $msg</p>");
		}
	}

	return true;
}

session_set_save_handler(
	"adodb_sess_open",
	"adodb_sess_close",
	"adodb_sess_read",
	"adodb_sess_write",
	"adodb_sess_destroy",
	"adodb_sess_gc");
}

/*  TEST SCRIPT -- UNCOMMENT */

if (0) {

	session_start();
	session_register('AVAR');
	$_SESSION['AVAR'] += 1;
	ADOConnection::outp( "
-- \$_SESSION['AVAR']={$_SESSION['AVAR']}</p>",false);
}
