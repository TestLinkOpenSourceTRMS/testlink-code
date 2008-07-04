<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * $Id: trac.cfg.php,v 1.3 2008/07/04 02:42:30 tosikawa Exp $ 
 * 
 * Constants used throughout TestLink are defined within this file
 * they should be changed for your environment
 * 
 * [Trac Settings]
 * The XmlRpcPlugin plugin should be installed in your Trac.
 * @link http://trac-hacks.swapoff.org/wiki/XmlRpcPlugin/ "Trac XmlRpcPlugin"
 *
 * In addition, you should add the permission of 'TICKET_VIEW' and 'XML_RPC'
 * to the user 'anonymous' in Trac.
 **/

//Set the bug tracking system Interface to Trac 0.10.x
//also tested with Trac 0.10.4

// Note: Please save this file in the character set same as PHP.

/** Trac Project Root */
define('BUG_TRACK_DB_HOST', 'http://<YourTracServer>/trac/');

/** Mapping TL test project name vs trac project url */
$g_interface_bugs_project_name_mapping = array(
    '<YourTLTestProjectName1>' => '<YourTracProject1>',
    '<YourTLTestProjectName2>' => '<YourTracProject2>',
);

/*--- Don't change the following parameters. ---*/
/** Link to the bugtracking system, for entering new bugs. */
define('BUG_TRACK_ENTER_BUG_HREF', '/newticket');
/** Link to the bugtracking system, for show bugs. */
define('BUG_TRACK_HREF', '/ticket');

/* The following parameters are not in use. */
define('BUG_TRACK_DB_TYPE', '[Not in Use]');
define('BUG_TRACK_DB_NAME', '[Not in Use]');
define('BUG_TRACK_DB_CHARSET', '[Not in Use]');
define('BUG_TRACK_DB_USER', '[Not in Use]');
define('BUG_TRACK_DB_PASS', '[Not in Use]');
?>