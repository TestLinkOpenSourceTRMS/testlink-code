<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: jira.cfg.php,v 1.0 2005/10/25 17:40:56 
* 
*
* 20051229 - scs - added DEFINE for the DB-Type
*/

// Contributed by  jbarchibald@gmail.com

//Set the bug tracking system Interface to JIRA 3.1.1
/** The DB host to use when connecting to the JIRA db */
define('BUG_TRACK_DB_HOST', 'localhost');

/** The name of the database that contains the jira tables */
define('BUG_TRACK_DB_NAME', 'jiradb');

/** The DB type being used by jira */
define('BUG_TRACK_DB_USER', 'root');

/** The DB password to use for connecting to the jira db */
define('BUG_TRACK_DB_PASS', 'mysqlroot');

/** link of the web server for jira */
// define('BUG_TRACK_HREF', "http://localhost:8080/secure/Dashboard.jspa"); 
define('BUG_TRACK_HREF', "http://localhost:8080/browse/"); 

/** The DB type to use for connecting to the bugtracking db */
define('BUG_TRACK_DB_TYPE', 'mysql');

/** link to the bugtracking system, for entering new bugs */
define('BUG_TRACK_ENTER_BUG_HREF',"http://localhost:8080/browse/");
?>