<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: bugzilla.cfg.php,v 1.4 2005/12/29 20:59:00 schlundus Exp $ 
* 
* Currently the only bug tracking system is bugzilla. 
* TestLink uses bugzilla to check if displayed bugs resolved, verified, 
* and closed bugs. If they are it will strike through them
* 
* 20051229 - scs - added DEFINE for the DB-Type
*/

//Set the bug tracking system Interface
/** The DB host to use when connecting to the Bugzilla db */
define('BUG_TRACK_DB_HOST', '<bugzilladbhost>');
/** The name of the database that contains the Bugzilla tables */
define('BUG_TRACK_DB_NAME', '<bugzilladbname>');
/** The DB type being used by Bugzilla */
define('BUG_TRACK_DB_USER', '<bugzilladbuser>');
/** The DB password to use for connecting to the Bugzilla db */
define('BUG_TRACK_DB_PASS', '<bugzilladbpass>');
/** The DB type to use for connecting to the bugtracking db */
define('BUG_TRACK_DB_TYPE', 'mysql');
/** link of the web server */
define('BUG_TRACK_HREF', "http://<bugzillaserver>/bugzilla/show_bug.cgi?id="); 
/** link to the bugtracking system, for entering new bugs */
define('BUG_TRACK_ENTER_BUG_HREF',"http://<bugzillaserver>/bugzilla/");
?>