<?php
 /**
 * A sample client implementation in php
 * 
 * @author    Francisco Mancardi
 * @package   TestlinkAPI
 * @link      http://testlink.org/api/
 *
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method = 'getTestSuite';
// ----
$UTDescr = "{$method} - Test - Call without parameters";
echo $UTDescr;

$debug = false;
$args=array();
$args["devKey"] = 'devkey';

$client = new IXR_Client($server_url);
$client->debug = $debug;
runTest($client,$method,$args);
// ----

// ----
$UTDescr = "{$method} - Test - Call with just test suite name";
echo $UTDescr;

$debug = false;
$args=array();
$args["devKey"] = 'devkey';
$args["testsuitename"] = 'QAZ-TS';

$client = new IXR_Client($server_url);
$client->debug = $debug;
runTest($client,$method,$args);

// ----

// ----
$UTDescr = "{$method} - Test - Call with just test project prefix";

$debug=false;
echo $UTDescr;
$args=array();
$args["devKey"] = 'devkey';
$args["prefix"] = 'QUANTAS';
$args["details"]='simple';

$client = new IXR_Client($server_url);
$client->debug = $debug;
runTest($client,$method,$args);
// ----

// ----
$UTDescr = "{$method} -  Test ";

$debug=false;
echo $UTDescr;
$args=array();
$args["devKey"] = 'devkey';
$args["testsuitename"] = 'CANNES';
$args["prefix"] = 'SRM';
$args["details"]='simple';

$client = new IXR_Client($server_url);
$client->debug = $debug;
runTest($client,$method,$args);
// ----
