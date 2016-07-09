<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientSetTestCaseExecutionType.php
 *
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal revisions 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();



$method='setTestCaseExecutionType';


$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=32989;
$args["testcaseexternalid"]='APF-1';
$args["version"]=1;
$args["executiontype"]=1;
$client = new IXR_Client($server_url);
$client->debug=true;
runTest($client,$method,$args);

/*
$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=32989;
$args["testcaseexternalid"]='APF-1';
$args["version"]=1;
$args["executiontype"]=2;
$client = new IXR_Client($server_url);
$client->debug=true;
runTest($client,$method,$args);
*/

/*
$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=32989;
$args["testcaseexternalid"]='APF-1';
$args["version"]=1;
$args["executiontype"]=10;
$client = new IXR_Client($server_url);
$client->debug=true;
runTest($client,$method,$args);
*/
?>