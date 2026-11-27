<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource clientCreateIssueTrackerSystem.php
 *
 * @author: aurelien.tisne@c-s.fr
 *
 */
require_once 'util.php';
require_once 'sample.inc.php';

$method = lcfirst(str_replace('client','',basename(__FILE__,".php")));
$devKey = 'admin';

// Add an ITS of type bugzilla XMLRPC

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $devKey;
$args["itsname"] = "itsTest";
$args["type"] = 1;
$args["cfg"] = "<issuetracker>
<username>USERNAME</username>
<password>PASSWORD</password>
<uribase>http://bugzilla.mozilla.org/</uribase>
<!-- In order to create issues from TestLink, you need to provide this MANDATORY info -->
<product>BUGZILLA PRODUCT</product>
<component>BUGZILLA PRODUCT</component>
<!-- This can be adjusted according Bugzilla installation. -->
<!-- COMMENTED SECTION 
 There are defaults defined in bugzillaxmlrpcInterface.class.php. 
<version>unspecified</version>
<severity>Trivial</severity>
<op_sys>All</op_sys>
<priority>Normal</priority>
<platform>All</platform> --> 
</issuetracker>";

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
$answer = runTest($client,$method,$args);