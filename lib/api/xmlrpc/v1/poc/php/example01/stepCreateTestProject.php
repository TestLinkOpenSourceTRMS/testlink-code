<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  stepCreateTestProject.php
 * @Author: francisco.mancardi@gmail.com
 * @internal revisions
 * 
 */
$method='createTestProject';
$unitTestDescription="";
$prefix = is_null($tlTestCasePrefix) ? uniqid() : $tlTestCasePrefix;
$devKey = isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $tlDevKey;

$args=array();
$args["devKey"] = $devKey;
$args["testcaseprefix"] = $prefix;
$args["testprojectname"] = "API Methods Test Project {$args['testcaseprefix']}";

$dummy = '';
$additionalInfo = $dummy;
$args["notes"]="test project created using XML-RPC-API - <br> {$additionalInfo}";

echo $unitTestDescription . ' ' . $additionalInfo;

$tlIdx++; 
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