<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientUpdateTestCase.php
 *
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal revisions 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$tcaseQty = 100;
$tcCounter = 0;
$method='updateTestCase';

/*
  * @param struct $args
    * @param string $args["devKey"]
    * @param string $args["testcaseexternalid"] format PREFIX-NUMBER
    * @param int    $args["version"] optional version NUMBER (human readable) 
    * @param string $args["name"] - optional
    * @param string $args["summary"] - optional
    * @param string $args["preconditions"] - optional
    * @param array  $args["steps"] - optional
    *               each element is a hash with following keys
    *               step_number,actions,expected_results,execution_type
    *
    * @param int    $args["importance"] - optional - see const.inc.php for domain
    * @param int    $args["executiontype"] - optional - see ... for domain
    * @param int    $args["status'] - optional
    * @param int    $args["estimatedexecduration'] - optional
    * @param string $args["user'] - login name used as updater - optional
    *                               if not provided will be set to user that request update
*/

// Update Only Summary
$args=array();
$args["devKey"]='985978c915f50e47a4b1a54a943d1b76';
$args["testcaseexternalid"]='GG-9';
$args["version"]=1;
$args["summary"]='Updated via XML-RPC API';
$args["status"]=1;

$client = new IXR_Client($server_url);
$client->debug=true;

$tcCounter++;
runTest($client,$method,$args,$tcCounter);



// Update Only Summary + Setting updater
$args=array();
$args["devKey"]='21232f297a57a5a743894a0e4a801fc3';
$args["testcaseexternalid"]='IU-5844-3';
$args["version"]=1;
$args["user"]='Iasmin';
$args["summary"]='Updated via XML-RPC API - by ' . $args["user"];


$client = new IXR_Client($server_url);
$client->debug=true;

$tcCounter++;
runTest($client,$method,$args,$tcCounter);

// Trying to Update AN INEXISTENT Version + Only Summary + Setting updater
$args=array();
$args["devKey"]='21232f297a57a5a743894a0e4a801fc3';
$args["testcaseexternalid"]='IU-5844-3';
$args["version"]=1222;
$args["user"]='Iasmin';
$args["summary"]='Updated via XML-RPC API - by ' . $args["user"];


$client = new IXR_Client($server_url);
$client->debug=true;

$tcCounter++;
runTest($client,$method,$args,$tcCounter);

// Update Summary + duration + Setting updater 
$args=array();
$args["devKey"]='21232f297a57a5a743894a0e4a801fc3';
$args["testcaseexternalid"]='IU-5844-3';
$args["version"]=1;
$args["estimatedexecduration"] = 12.5;
$args["user"]='Iasmin';
$args["summary"]='Updated via XML-RPC API - by ' . $args["user"];


$client = new IXR_Client($server_url);
$client->debug=true;

$tcCounter++;
runTest($client,$method,$args,$tcCounter);


// Update Summary + duration + importance + Setting updater 
$args=array();
$args["devKey"]='21232f297a57a5a743894a0e4a801fc3';
$args["testcaseexternalid"]='IU-5844-3';
$args["version"]=1;
$args["estimatedexecduration"] = 12.5;
$args["user"]='Iasmin';
$args["summary"]='Updated via XML-RPC API - by ' . $args["user"];
$args["importance"] = 3;


$client = new IXR_Client($server_url);
$client->debug=true;

$tcCounter++;
runTest($client,$method,$args,$tcCounter);


// Update creating steps + Setting updater 
$args=array();
$args["devKey"]='21232f297a57a5a743894a0e4a801fc3';
$args["testcaseexternalid"]='IU-5844-5';
$args["version"]=1;
$args["user"]='Iasmin';
$args["summary"]='Updated via XML-RPC API - by ' . $args["user"];
$args["steps"][]=array('step_number' => 1, 'actions' => 'Start Server', 'expected_results' => 'green light');


$client = new IXR_Client($server_url);
$client->debug=true;

$tcCounter++;
runTest($client,$method,$args,$tcCounter);

// Update creating steps + Setting updater 
$args=array();
$args["devKey"]='21232f297a57a5a743894a0e4a801fc3';
$args["testcaseexternalid"]='IU-5844-5';
$args["version"]=1;
$args["user"]='Iasmin';
$args["summary"]='Updated via XML-RPC API - by ' . $args["user"];
$args["steps"][]=array('step_number' => 1, 'actions' => 'Start Server Upd', 'expected_results' => 'green light');
$args["steps"][]=array('step_number' => 10, 'actions' => 'Start Server NEW', 'expected_results' => 'green light');


$client = new IXR_Client($server_url);
$client->debug=true;

$tcCounter++;
runTest($client,$method,$args,$tcCounter);

