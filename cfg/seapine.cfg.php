<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: seapine.cfg.php,v 1.1 2008/02/08 08:24:59 franciscom Exp $ 
* 
* 20080207 - needles - Created for Seapine's TestTrackPro. 
* TestLink uses the database to check if displayed bugs resolved, verified, 
* and closed bugs. If they are it will strike through them
* 
*/

//Set the bug tracking system Interface
/** The DB host to use when connecting to the Seapine db */
define('BUG_TRACK_DB_HOST', '<seapinedbhost>');
//Set the bug tracking system Interface
/** The DB host to use when connecting to the Seapine db */
define('BUG_TRACK_PROJECT_ID', '<seapineprojectid>');
/** The name of the database that contains the Seapine tables */
define('BUG_TRACK_DB_NAME', '<seapinedbname>');
/** The DB type being used by Seapine */
define('BUG_TRACK_DB_USER', '<seapinedbuser>');
/** The DB password to use for connecting to the Seapine db */
define('BUG_TRACK_DB_PASS', '<seapinedbpass>');
/** The DB type to use for connecting to the bugtracking db */
define('BUG_TRACK_DB_TYPE', 'mysql');
//define('BUG_TRACK_DB_CHARSET',"windows-1250");
// define('BUG_TRACK_DB_CHARSET',"gb2312");
define('BUG_TRACK_DB_CHARSET',"UTF-8");


/** link of the web server */
define('BUG_TRACK_HREF', "ttstudio://<seapineserver>//<projectname>/dfct?recordID="); 

/** link to the bugtracking system, for entering new bugs */
define('BUG_TRACK_ENTER_BUG_HREF',"ttstudio://<seapineserver>:99//<projectname>/dfct");
?>