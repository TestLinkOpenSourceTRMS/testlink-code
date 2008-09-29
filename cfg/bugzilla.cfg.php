<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: bugzilla.cfg.php,v 1.9 2008/09/29 19:47:57 schlundus Exp $ 
* 
* Currently the only bug tracking system is bugzilla. 
* TestLink uses bugzilla to check if displayed bugs resolved, verified, 
* and closed bugs. If they are it will strike through them
* 
*/

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