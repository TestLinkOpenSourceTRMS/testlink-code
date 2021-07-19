<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource clientGetReqCoverage.php
 *
 * @author: aurelien.tisne@c-s.fr
 *
 */
require_once 'util.php';
require_once 'sample.inc.php';

$method = lcfirst(str_replace('client','',basename(__FILE__,".php")));
$devKey = 'to be changed';

// Get all Test Cases linked with a Requirement

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $devKey;
$args["testprojectid"] = 10;
$args["requirementversionid"] = 123;

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
$answer = runTest($client,$method,$args);
