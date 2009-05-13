<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: jira.cfg.php,v 1.0 2009/04/08 17:40:56
 *
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2009/05/13 09:35:01 $ $Author: amkhullar $
 *
 * @author amkhullar@gmail.com
 *
 * Integration with JIRA Using SOAP Interface.
**/

//Set the bug tracking system Interface to JIRA 3.11.1 (tested with this version)
//-----------------------------------------------------------------------------------------
/* The following parameters are not in use. */
define('BUG_TRACK_DB_TYPE', '[Not in Use]');
define('BUG_TRACK_DB_NAME', '[Not in Use]');
define('BUG_TRACK_DB_CHARSET', '[Not in Use]');
define('BUG_TRACK_DB_USER', '[Not in Use]');
define('BUG_TRACK_DB_PASS', '[Not in Use]');
//-----------------------------------------------------------------------------------------

/** The Username being used by JIRA logon */
define('BUG_TRACK_USERNAME', 'test');

/** The Password being used by JIRA logon*/
define('BUG_TRACK_PASSWORD', 'test');
/** link of the web server for JIRA*/
define('BUG_TRACK_HREF',"http://localhost:8080/");
/** path of JIRA WSDL */
define('BUG_TRACK_SOAP_HREF', "rpc/soap/jirasoapservice-v2?wsdl");

/** link of the web server for jira ticket*/
define('BUG_TRACK_SHOW_BUG_HREF', "browse/");
/** link of the web server for creating new jira ticket*/
define('BUG_TRACK_ENTER_BUG_HREF',"secure/Dashboard.jspa?os_destination=%2Fsecure%2FCreateIssue%21default.jspa");

?>