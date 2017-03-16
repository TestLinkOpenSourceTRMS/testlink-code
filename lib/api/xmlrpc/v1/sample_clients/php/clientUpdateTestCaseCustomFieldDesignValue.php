<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientUpdateTestCaseCustomFieldDesignValue.php
 *
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal revisions 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();



$tcaseQty = 100;
$tcCounter = 1;
$method='updateTestCaseCustomFieldDesignValue';


$args=array();
$args["devKey"]='admin';
$args["testprojectid"]=280165;
$args["testcaseexternalid"]='HA-1';
$args["version"]=1;
// $args["customfields"] = array('CF_EXE1' => 'COMODORE64','CF_DT' => mktime(10,10,0,7,29,2009));
// $args["customfields"] = array('TCSTRING' => 'From DUCATI to YAMAHA');

$args["customfields"] = 
  array('L2D' => 
        'http://localhost/development/github/testlink-code/'  . 
        'linkto.php?tprojectPrefix=HA&item=testcase&id=HA-1 ');


$client = new IXR_Client($server_url);
$client->debug=true;

/*
for($idx=1 ; $idx <= $tcaseQty; $idx++)
{
	runTest($client,$method,$args);
}
*/
runTest($client,$method,$args);
