<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: fogbugz.cfg.php,v 1.1 2008/11/04 19:58:04 franciscom Exp $ 
* 
* Contributed by Sjoerd Dirk Meijer
*
* Constants used throughout TestLink are defined within this file
* they should be changed for your environment
* 
* 
*/
/** TESTED with FogBugz 3.1.9 (DB328) */

/** The DB host to use when connecting to the fogbugz db */
define('BUG_TRACK_DB_HOST', '[CONFIGURE_BUG_TRACK_DB_HOST]');

/** The name of the database that contains the fogbugz tables */
define('BUG_TRACK_DB_NAME', '[CONFIGURE_BUG_TRACK_DB_NAME]');

/** The DB type being used by fogbugz
values: mysql,mssql,postgres
*/
define('BUG_TRACK_DB_TYPE', '[CONFIGURE_BUG_TRACK_DB_TYPE]');

/** The DB password to use for connecting to the fogbugz db */
define('BUG_TRACK_DB_USER', '[CONFIGURE_BUG_TRACK_DB_USER]');
define('BUG_TRACK_DB_PASS', '[CONFIGURE_BUG_TRACK_DB_PASS]');


//define('BUG_TRACK_DB_CHARSET',"windows-1250");
// define('BUG_TRACK_DB_CHARSET',"gb2312");
define('BUG_TRACK_DB_CHARSET',"UTF-8");

/* link of the web server for fogbugz*/
define('BUG_TRACK_HREF', "http://[ip_of_webserver]/fogbugz/default.asp?pg=pgEditBug&command=view&ixbug="); 

/** link to the bugtracking system, for entering new bugs */
define('BUG_TRACK_ENTER_BUG_HREF',"http://[ip_of_webserver]/fogbugz/default.asp?command=new&pg=pgEditBug");
?>