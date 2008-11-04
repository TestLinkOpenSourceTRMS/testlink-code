<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: trackplus.cfg.php, v 1.0 2006/06/09 12:40:56  
* 
* Configuration for trackplus BTS integration
*
* Constants used throughout TestLink are defined within this file
* they should be changed for your environment
*/

//Set the bug tracking system Interface to TRACKPLUS 3.2.0
/** The DB host to use when connecting to the trackplus db */
define('BUG_TRACK_DB_HOST', '[to be configured]');

/** The name of the database that contains the trackplus tables */
define('BUG_TRACK_DB_NAME', '[to be configured]');

/** The DB type being used by trackplus */
define('BUG_TRACK_DB_USER', '[to be configured]');

/** The DB password to use for connecting to the trackplus db */
define('BUG_TRACK_DB_PASS', '[to be configured]');

/** The DB type to use for connecting to the bugtracking db */
define('BUG_TRACK_DB_TYPE', 'mysql');

/* link of the web server for trackplus*/
define('BUG_TRACK_HREF', "http://myweb/tracksf/printItem.do?key="); 

/** link to the bugtracking system, for entering new bugs */
define('BUG_TRACK_ENTER_BUG_HREF',"http://myweb/tracksf/");
?>

