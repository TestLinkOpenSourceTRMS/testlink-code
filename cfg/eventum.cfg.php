<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: eventum.cfg.php,v 1.2 2007/12/12 18:21:22 havlat Exp $ 
* 
* Constants used throughout TestLink are defined within this file
* they should be changed for your environment
* 
* 20071211 - scs - added DEFINE for the DB-Type
*/

//Set the bug tracking system Interface to EVENTUM 2.1

/** The DB host to use when connecting to the eventum db */
define('BUG_TRACK_DB_HOST', 'localhost');

/** The name of the database that contains the eventum tables */
 define('BUG_TRACK_DB_NAME', 'eventum');

/** The DB type being used by eventum
values: mysql
*/
define('BUG_TRACK_DB_TYPE', 'mysql');

/** The DB password to use for connecting to the eventum db */
define('BUG_TRACK_DB_USER', 'root');
define('BUG_TRACK_DB_PASS', 'mysqlroot');


/* link of the web server for eventum*/
/* anonymous login into eventum has to be turned on, and a eventum user has to created with viewer rights to all public projects
/* Change the following in your eventum config_inc.php (replace dummy with your created user)
 	# --- anonymous login -----------
	# Allow anonymous login
	$g_allow_anonymous_login	= ON;
	$g_anonymous_account		= 'dummy';
*/
 define('BUG_TRACK_HREF', "http://localhost/eventum/view.php?id="); 


/** link to the bugtracking system, for entering new bugs */
define('BUG_TRACK_ENTER_BUG_HREF',"http://localhost/eventum/");

?>