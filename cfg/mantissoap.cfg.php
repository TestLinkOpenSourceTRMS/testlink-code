<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @filesource	mantissoap.cfg.php
* 
* Constants used throughout TestLink are defined within this file
* they should be changed for your environment
* 
* @internal revisions
* @since 1.9.4 
* 20120106 - franciscom - TICKET 4857 - SOAP access to mantis
*/

define('BUG_TRACK_USERNAME', 'testlink.helpme');
define('BUG_TRACK_PASSWORD', 'testlink.helpme');

define('BUG_TRACK_HREF', "http://www.mantisbt.org/");
define('BUG_TRACK_SOAP_HREF', BUG_TRACK_HREF . "bugs/api/soap/mantisconnect.php?wsdl");
define('BUG_TRACK_SHOW_ISSUE_HREF', BUG_TRACK_HREF . "bugs/view.php?id="); 
define('BUG_TRACK_ENTER_ISSUE_HREF', BUG_TRACK_HREF . "bugs/");
?>