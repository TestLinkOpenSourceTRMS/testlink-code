<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @filesource polarion.cfg.php
* 
* @author Gregor Bonney
*
* @internal revisions
*
*/

# About the path to the polarion svn server
define('BUG_TRACK_SVN_PROTO', 'https://');
define('BUG_TRACK_SVN_USED_PROJECT_GRP', 'TT2');
define('BUG_TRACK_SVN_REPO', 'svn.polarion-server.org/repo/');
define('BUG_TRACK_SVN_WIDIR', '/.polarion/tracker/workitems/');
define('BUG_TRACK_SVN_USER', 'polarionsvnuser');
define('BUG_TRACK_SVN_PASS', 'my-password');

# Will Result in: 
# https://svn.polarion-server.org/polarion/#/project/MYPROJECT/workitem?id=MYPROJECT-4711
define('BUG_TRACK_HREF', "https://svn.polarion-server.org/polarion/#/project/");
define('BUG_TRACK_HREF_END', "/workitem?id=");

# Will Result in: 
# https://svn.polarion-server.org/polarion/#/project/MYPROJECT/?shortcut=Create Work Item
define('BUG_TRACK_ENTER_BUG_HREF',"https://svn.polarion-server.org/polarion/#/project/");
define('BUG_TRACK_ENTER_BUG_HREF_END',"/?shortcut=Create Work Item");
?>