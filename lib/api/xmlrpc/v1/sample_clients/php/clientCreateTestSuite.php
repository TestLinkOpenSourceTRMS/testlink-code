<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  clientCreateTestSuite.php,v $
 * @Author      francisco.mancardi@gmail.com
 *
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

// Tests: 
// parentid is not a Test Suite ID
// parentid is a Test Suite ID but belongs to other Test Project
// use a new name
// use name of existent Test Suite in parentid => default behaviour BLOCK => will not be created
// use name of existent Test Suite in parentid, request renaming
//

$method='createTestSuite';


$unitTestDescription="Test - $method";
$test_num = 0;
$tlDevKey = '985978c915f50e47a4b1a54a943d1b76';
$tlDevKey = isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $tlDevKey;

// -------------------------------------------------------------
$test_num++;
$additionalInfo = 'Using Test Project PREFIX';
$args=array();
$args["devKey"]=$tlDevKey;

$args["prefix"]='ZTZ';
$args["testsuitename"]='TS API 200.0';
$args["details"]='This has been created by XMLRPC API Call';

// $args["parentid"]=16;
$args["checkduplicatedname"]=1;
$args["actiononduplicatedname"]='generate_new';
$args["order"]=1;


$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);
// -------------------------------------------------------------
$test_num++;
$additionalInfo = 'Using Test Project ID';
$args=array();
$args["devKey"]=$tlDevKey;

$args["testprojectid"]=1046;
$args["testsuitename"]='TS API 2';
$args["details"]='This has been created by XMLRPC API Call';

// $args["parentid"]=16;
$args["checkduplicatedname"]=1;
$args["actiononduplicatedname"]='generate_new';
$args["order"]=1;


$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);