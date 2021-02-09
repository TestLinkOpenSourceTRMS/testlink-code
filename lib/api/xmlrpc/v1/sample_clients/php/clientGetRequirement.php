<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource clientGetRequirement.php
 *
 * @author: aurelien.tisne@c-s.fr
 *
 */
require_once 'util.php';
require_once 'sample.inc.php';

$method = lcfirst(str_replace('client','',basename(__FILE__,".php")));
$devKey = 'to be changed';
$devKey = isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $devKey;

// Get a requirement from reqID
// with no version -> latest is returned

$args=array();
$args["devKey"]= $devKey;
$args["testprojectid"] = 10;
$args["requirementid"] = 26;

$client = new IXR_Client($server_url);
$client->debug=true;
$answer = runTest($client, $method, $args);

// Get a requirement from reqID
// with a version number

$args=array();
$args["devKey"]= $devKey;
$args["testprojectid"] = 10;
$args["requirementid"] = 26;
$args["version"] = 2;

$client = new IXR_Client($server_url);
$client->debug=true;
$answer = runTest($client, $method, $args);

// Get a requirement from reqID
// with a version ID

$args=array();
$args["devKey"]= $devKey;
$args["testprojectid"] = 10;
$args["requirementid"] = 26;
$args["requirementversionid"] = 123;

$client = new IXR_Client($server_url);
$client->debug=true;
$answer = runTest($client, $method, $args);

// Get a requirement from reqDocID

$args=array();
$args["devKey"]= $devKey;
$args["testprojectid"] = 10;
$args["requirementdocid"] = "R1";

$client = new IXR_Client($server_url);
$client->debug=true;
$answer = runTest($client, $method, $args);
