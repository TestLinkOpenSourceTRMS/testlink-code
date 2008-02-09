<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: seapine.cfg.php,v 1.2 2008/02/09 16:24:40 franciscom Exp $ 
* 
* 20080209 - franciscom - added http links
*                         comments corrected
*
* 20080207 - needles - Created for Seapine's TestTrackPro. 
* TestLink uses the database to check if displayed bugs resolved, verified, 
* and closed bugs. If they are it will strike through them
* 
*/

//Set the bug tracking system Interface
/** The DB host to use when connecting to the Seapine db */
define('BUG_TRACK_DB_HOST', '[seapinedbhost]');

//Set the bug tracking system Interface
/** Seapine Project ID */
define('BUG_TRACK_PROJECT_ID', '[seapineprojectid]');

/** database that contains the Seapine tables */
define('BUG_TRACK_DB_NAME', '[seapinedbname]');

/** User to connect to Seapine */
define('BUG_TRACK_DB_USER', '[seapinedbuser]');

/** DB password to use for connecting to the Seapine db */
define('BUG_TRACK_DB_PASS', '[seapinedbpass]');

/** The DB type to use for connecting to the bugtracking db */
define('BUG_TRACK_DB_TYPE', 'mysql');

//define('BUG_TRACK_DB_CHARSET',"windows-1250");
//define('BUG_TRACK_DB_CHARSET',"gb2312");
define('BUG_TRACK_DB_CHARSET',"UTF-8");


/** link of the web server */
// if you have installed tturlredirector.exe
define('BUG_TRACK_HREF', "ttstudio://[seapineserver]//[projectname]/dfct?recordID="); 

//
// Examples:
// define('BUG_TRACK_HREF', "ttstudio://localhost//TESTLINK_INTEGRATION/dfct?recordID="); 
// define('BUG_TRACK_HREF', 
//        'http://localhost/scripts/ttcgi.exe?command=hyperlink&project=TESTLINK_INTEGRATION&table=dfct&recordID=');

/** link to the bugtracking system, for entering new bugs */
// if you have installed tturlredirector.exe
define('BUG_TRACK_ENTER_BUG_HREF',"ttstudio://[seapineserver]:99//[projectname]/dfct");

// Examples:
// define('BUG_TRACK_ENTER_BUG_HREF',"ttstudio://localhost:99//TESTLINK_INTEGRATION/dfct");
// define('BUG_TRACK_ENTER_BUG_HREF',
//        'http://localhost/scripts/ttcgi.exe?command=hyperlink&project=TESTLINK_INTEGRATION&table=dfct');

?>