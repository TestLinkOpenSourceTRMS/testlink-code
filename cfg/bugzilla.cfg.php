<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: bugzilla.cfg.php,v 1.3 2005/08/25 17:40:56 schlundus Exp $ 
* 
* Currently the only bug tracking system is bugzilla. 
* TestLink uses bugzilla to check if displayed bugs resolved, verified, 
* and closed bugs. If they are it will strike through them
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
/** link of the web server */
define('BUG_TRACK_HREF', "http://<bugzillaserver>/bugzilla/show_bug.cgi?id="); 
/** link to the bugtracking system, for entering new bugs */
define('BUG_TRACK_ENTER_BUG_HREF',"http://<bugzillaserver>/bugzilla/");
?>