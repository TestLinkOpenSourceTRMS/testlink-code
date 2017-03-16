<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientUpdateTestSuiteCustomFieldDesignValue.php
 *
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal revisions 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';

$method='updateTestSuiteCustomFieldDesignValue';

echo '<h2>Testing Method: ' . $method . '</h2>';

$args=array();
$args["devKey"] = 'admin';
$args["testprojectid"]=279340;
$args["testsuiteid"]=279341;
$args["customfields"] = array('TCSTRING' => 'From DUCATI to YAMAHA');

$client = new IXR_Client($server_url);
$client->debug=true;

runTest($client,$method,$args);

// 
$args=array();
$args["devKey"] = 'admin';
$args["testprojectid"]=279340;
$args["testsuiteid"]=279341;
$args["customfields"] = array('CF_MOTO' => 'From DUCATI to YAMAHA');

$client = new IXR_Client($server_url);
$client->debug=true;

runTest($client,$method,$args);

// 
$args=array();
$args["devKey"] = 'admin';
$args["testprojectid"]=279340;
$args["testsuiteid"]=279341;
$args["customfields"] = array('CF_MOTO' => 'From DUCATI to YAMAHA');

$client = new IXR_Client($server_url);
$client->debug=true;

runTest($client,$method,$args);
