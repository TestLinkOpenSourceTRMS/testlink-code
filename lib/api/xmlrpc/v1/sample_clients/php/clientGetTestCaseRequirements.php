<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource clientGetTestCaseRequirements.php
 *
 * @author: aurelien.tisne@c-s.fr
 *
 */
require_once 'util.php';
require_once 'sample.inc.php';

$method = lcfirst(str_replace('client','',basename(__FILE__,".php")));
$devKey = 'admin';

// Get all requirements linked with a Test Case

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $devKey;
$args["testcaseid"] = 1;

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
$answer = runTest($client,$method,$args);
