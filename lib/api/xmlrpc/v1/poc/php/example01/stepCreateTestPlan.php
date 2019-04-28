<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  stepCreateTestPlan.php
 * @Author      francisco.mancardi@gmail.com
 *
 */

$method='createTestPlan';
$tlIdx++;
if( !isset($tlTestCasePrefix) || is_null($tlTestCasePrefix) )
{
  throw new Exception("This is intended to be used with $tlTestCasePrefix provided", 1);      
}

$args=array();
$args["devKey"] =isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $tlDevKey;
$args["prefix"] = $tlTestCasePrefix;
$args["testplanname"]="TPLAN BY API";
$args["notes"]="test plan created using XML-RPC-API";

$client = new IXR_Client($server_url);
$client->debug = $tlDebug;
$ret = runTest($client,$method,$args,$tlIdx);

if( isset($ret[0]['id']) )
{
  $env->$tlIDName = $ret[0]['id'];
}
else
{
  $msg = 'Warning! ' . ' :: ' . $ret[0]['message'] . ' (file: ' . basename(__FILE__) . ")";
  throw new Exception($msg, 1);
}