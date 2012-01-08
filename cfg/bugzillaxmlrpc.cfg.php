<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @filesource	bugzillaxmlrpc.cfg.php
* 
* TestLink uses bugzilla to check if displayed bugs resolved, verified, 
* and closed bugs. 
* 
* @internal revisions
*/

/** Set the bug tracking system Interface */
define('BUG_TRACK_USERNAME', 'testlink.helpme@gmail.com');
define('BUG_TRACK_PASSWORD', 'testlink.helpme');


/** link of the web server */
define('BUG_TRACK_HREF', "http://bugzilla.mozilla.org/"); 

/** link to access the bugtracking system, via XMLRPC API */
define('BUG_TRACK_XMLRPC_HREF', BUG_TRACK_HREF . "xmlrpc.cgi"); 

/** link of the bugtracking system, for display/show issue */
define('BUG_TRACK_SHOW_ISSUE_HREF', BUG_TRACK_HREF . "show_bug.cgi?id="); 

/** link to the bugtracking system, for entering new bugs */
define('BUG_TRACK_ENTER_ISSUE_HREF', BUG_TRACK_HREF . "");
?>