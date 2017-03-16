<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource getTestCaseCustomFieldExecutionValue.php
 * @Author: francisco.mancardi@gmail.com
 *
 *
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getTestCaseCustomFieldExecutionValue';

$test_num=0;

// -----------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]='admin';
$args["testprojectid"]=279311;
$args["testplanid"]=279324;
$args["version"]=1;
$args["executionid"]=9229;
$args["customfieldname"]='STRING4EXEC';
$args["details"]='simple';
$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);
// -----------------------------------------------------

// -----------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]='admin';
$args["devKey"]='admin';
$args["testprojectid"]=279311;
$args["testplanid"]=279324;
$args["version"]=2;
$args["executionid"]=9230;
$args["customfieldname"]='STRING4EXEC';
$args["details"]='simple';
$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);
// -----------------------------------------------------

// -----------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]='admin';
$args["testprojectid"]=279311;
// $args["testplanid"]=279324;
$args["testplanid"]=173854;

$args["customfieldname"]='STRING4EXEC';
$args["details"]='simple';
$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);
// -----------------------------------------------------

// -----------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]='admin';
$args["testprojectid"]=279311;
$args["testplanid"]=279324;
$args["version"]=17;
$args["executionid"]=17;

$args["customfieldname"]='STRING4EXEC';
$args["details"]='simple';
$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);
// -----------------------------------------------------