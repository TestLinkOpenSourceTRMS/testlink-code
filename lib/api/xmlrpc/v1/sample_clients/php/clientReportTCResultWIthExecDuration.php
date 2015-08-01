<?php
 /**
  * A sample client implementation in php
  * 
  * @author    francisco.mancardi@gmail.com
  * @package   TestlinkAPI
  * @link      http://www.testlink.org/
  *
  */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='reportTCResult';

$tcaseStatusCode['passed']='p';
$tcaseStatusCode['blocked']='b';
$tcaseStatusCode['failed']='f';
$tcaseStatusCode['wrong']='w';
$tcaseStatusCode['departed']='d';

$test_num=0;

// -----------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]='admin';
$args['testcaseexternalid'] = 'IU7206-1';
$args["testplanid"]=279324;
$args["buildname"]='1.0';
$args["execduration"]=2.7;
$args['status'] = $tcaseStatusCode['failed'];
$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);
// -----------------------------------------------------
