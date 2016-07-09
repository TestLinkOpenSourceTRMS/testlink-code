<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Filename $RCSfile: xmlrpc.php,v $
 *
 * @version $Revision: 1.91 $
 * @modified $Date: 2010/05/14 19:58:53 $ by $Author: franciscom $
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net>
 * @package 	TestlinkAPI
 * 
 * Testlink API makes it possible to interact with Testlink  
 * using external applications and services. This makes it possible to report test results 
 * directly from automation frameworks as well as other features.
 * 
 * 
 *
 * @internal revisions
 *	
 */

require_once("xmlrpc.class.php");

$XMLRPCServer = new TestlinkXMLRPCServer();
?>
