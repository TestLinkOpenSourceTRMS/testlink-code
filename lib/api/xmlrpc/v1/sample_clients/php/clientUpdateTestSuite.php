<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  clientUpdateTestSuite.php
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

$method='updateTestSuite';


$unitTestDescription="Test - $method";
$test_num = 0;
$tlDevKey = '985978c915f50e47a4b1a54a943d1b76';
$tlDevKey = isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $tlDevKey;


// -------------------------------------------------------------
$test_num++;
$additionalInfo = 'Using Test Project PREFIX - Will Change ORDER';
$args=array();
$args["devKey"]=$tlDevKey;

$args["prefix"]='ZTZ';
$args["testsuiteid"]=1072;
$args["order"]=12000;
// $args["testsuitename"]='TS API 200.0';
// $args["details"]='MOMO This has been created by XMLRPC API Call';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);


// -------------------------------------------------------------
$test_num++;
$additionalInfo = 'Using Test Project PREFIX - Will UPDATE Name CREATING DUP';
$args=array();
$args["devKey"]=$tlDevKey;

$args["prefix"]='ZTZ';
$args["testsuiteid"]=1081;
$args["testsuitename"]='TS API 200.0';
$args["details"]='MOMO This has been created by XMLRPC API Call';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);


// -------------------------------------------------------------
$test_num++;
$additionalInfo = 'Using Test Project PREFIX - Will Update Name QUIET';
$args=array();
$args["devKey"]=$tlDevKey;

$args["prefix"]='ZTZ';
$args["testsuiteid"]=1080;
$args["testsuitename"]='TS API SUPER 200.0';

// $args["details"]='MOMO This has been created by XMLRPC API Call';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);



// -------------------------------------------------------------
$test_num++;
$additionalInfo = 'Using Test Project PREFIX - Will Update Details';
$args=array();
$args["devKey"]=$tlDevKey;

$args["prefix"]='ZTZ';
$args["testsuiteid"]=1080;

// $args["testsuitename"]='TS API 200.0';

$args["details"]='MOMO This has been created by XMLRPC API Call';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);

