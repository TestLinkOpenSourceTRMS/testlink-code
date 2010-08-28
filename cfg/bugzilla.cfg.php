<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: bugzilla.cfg.php,v 1.11 2010/08/28 13:44:05 franciscom Exp $ 
* 
* TestLink uses bugzilla to check if displayed bugs resolved, verified, 
* and closed bugs. 
* 
* @internal revisions
* 20100828 - franciscom - useful information to remember when BUGZILLA DBSERVER
*						  is not same that TestLink DBSERVER
*
*			 Contributed on Forum by TurboTP
*/

// =======================================================================================================
// Important information about Bugzilla on remote mode.
// =======================================================================================================
// 
// The server where bugzilla database run, CAN be remote, but the user specified in the config 
// must be able to access that server 'remotely'.
// Example for MySQL
// On the bugzilla server, if you run this query: 
// [I'm assuming here that the user to bugzilla is 'bugs', and you have permissions to the mysql table]
//
// SELECT user, host FROM mysql.user WHERE user = 'bugs';
//
// I'm betting that only one entry will appear, and it will show bugs and localhost, 
// probably something like this:
//
// +------+-----------+
// | user | host      |
// +------+-----------+
// | bugs | localhost |
// +------+-----------+
// 1 row in set (0.01 sec)
//
// If that happens to be the case, then another entry needs to be created 
// [the user and host are a combined primary key], so that there is a 
// bugs and 'testlink_server.example.com' user/host combination.
// [note that the new entry can have an entirely different password than the localhost entry, if desired]                                                                                        
// =======================================================================================================

//Set the bug tracking system Interface
/** DB host to use when connecting to the Bugzilla db */
define('BUG_TRACK_DB_HOST', '[CONFIGURE_BUG_TRACK_DB_HOST]');

/** name of the database that contains the Bugzilla tables */
define('BUG_TRACK_DB_NAME', '[CONFIGURE_BUG_TRACK_DB_NAME]');

/** charset of the database that contains the Bugzilla tables */
define('BUG_TRACK_DB_CHARSET', '[CONFIGURE_BUG_TRACK_DB_CHARSET]');

/** useful if you have several schemas see BUGID 1444*/
// define('BUG_TRACK_DB_SCHEMA', '[CONFIGURE_BUG_TRACK_DB_SCHEMA]');

/** DB type used for the bugtracking db */
define('BUG_TRACK_DB_TYPE','[CONFIGURE_BUG_TRACK_DB_TYPE]');

/** DB user and password to use for connecting to the Bugzilla db */
define('BUG_TRACK_DB_USER', '[CONFIGURE_BUG_TRACK_DB_USER]');
define('BUG_TRACK_DB_PASS', '[CONFIGURE_BUG_TRACK_DB_USER_PASS]');



/** link of the web server */
define('BUG_TRACK_HREF', "http://[bugzillaserver]/bugzilla/show_bug.cgi?id="); 

/** link to the bugtracking system, for entering new bugs */
define('BUG_TRACK_ENTER_BUG_HREF',"http://[bugzillaserver]/bugzilla/");
?>