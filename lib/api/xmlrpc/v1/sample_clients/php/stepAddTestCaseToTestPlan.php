<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  stepAddTestCaseToTestPlan.php
 * @Author      francisco.mancardi@gmail.com
 *
 * @internal revisions
 *
 */
require_once 'util.php';
require_once 'sample.inc.php';
$method="addTestCaseToTestPlan";

/*
 args['testprojectid']
 args['testplanid']
 args['testcaseexternalid']
 args['version']
 args['platformid'] - OPTIONAL Only if  test plan has no platforms
 args['executionorder'] - OPTIONAL
 args['urgency'] - OPTIONAL
 args['overwrite'] - OPTIONAL
*/  

global $env;
global $tlTestCasePrefix;

$unitTestDescription = "";
$args=array();
$args["devKey"] = isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $tlDevKey;
$args["testprojectid"] = $env->tlProjectID;
$args["testplanid"] = $env->tlPlanID;

$args["testcaseexternalid"] = $tlTestCasePrefix . '-1';
$args["version"] = $env->tlTestCaseVersion;
$args["overwrite"] = $tlOverWriteOnAdd;

$tlIdx++;
$client = new IXR_Client($server_url);
$client->debug = $tlDebug;
$answer = runTest($client,$method,$args,$tlIdx);

