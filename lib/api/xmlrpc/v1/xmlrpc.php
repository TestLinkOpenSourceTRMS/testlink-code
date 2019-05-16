<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Filename xmlrpc.php
 *
 * 
 * Testlink API makes it possible to interact with Testlink  
 * using external applications and services. This makes it possible to report test results 
 * directly from automation frameworks as well as other features.
 * 
 *	
 */

require_once("xmlrpc.class.php");
$XMLRPCServer = new TestlinkXMLRPCServer();
