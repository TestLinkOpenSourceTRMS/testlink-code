<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: gforge.cfg.php,v 1.0.0.0 2008/08/21 17:12:00 john.wanke
* $Id: gforge.cfg.php,v 1.2 2008/11/04 19:58:22 franciscom Exp $ 
*
* Contributed by john.wanke
* 
* Constants used throughout TestLink are defined within this file
* they should be changed for your environment
* 
*/

//Set the bug tracking system Interface to GFORGE x.x.x.x

/** The DB host to use when connecting to the gforge db */
define('BUG_TRACK_DB_HOST', '192.168.255.128');

/** The name of the database that contains the gforge tables */
define('BUG_TRACK_DB_NAME', 'gforge5');

/** The DB type being used by GForge 
values: mysql,mssql,postgres
*/
define('BUG_TRACK_DB_TYPE', 'postgres');

/** The DB password to use for connecting to the gforge db */
define('BUG_TRACK_DB_USER', 'gforge');
define('BUG_TRACK_DB_PASS', '');


//define('BUG_TRACK_DB_CHARSET',"windows-1250");
//define('BUG_TRACK_DB_CHARSET',"gb2312");
define('BUG_TRACK_DB_CHARSET',"UTF-8");


/** name of project in the GForge bugtracking system */
/** this value is a placeholder that is replaced by the actual project name for the specified tracker */
define('BUG_TRACK_PROJECT', "support"); 

/** link of the web server url to VIEW an individual bug tracker record */
//define('BUG_TRACK_HREF', "http://gforge.texmemsys.com/gf/project/GFPROJECT/tracker/?action=TrackerItemBrowse&amp;tracker_item_id="); 
define('BUG_TRACK_HREF', "http://192.168.255.128/gf/project/". BUG_TRACK_PROJECT ."/tracker/?action=TrackerItemEdit&amp;tracker_item_id="); 

/** link to the bugtracking system, for entering new bugs */
define('BUG_TRACK_ENTER_BUG_HREF', "http://192.168.255.128/gf"); 
?>
