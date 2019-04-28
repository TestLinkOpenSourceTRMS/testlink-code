<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource clientCreateBuild.php
 *
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal revisions 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();


$test_num=0;
$devKey = '985978c915f50e47a4b1a54a943d1b76';

// --------------------------------------------------------
$method='createBuild';
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=$devKey;
$args["testplanid"]=61;
$args["buildname"]='Abril 230';
$args["buildnotes"]='Created via API 3';
$args["copytestersfrombuild"]='3';
$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);

// --------------------------------------------------------

// --------------------------------------------------------
$method='createBuild';
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=$devKey;
$args["testplanid"]=72;
$args["buildname"]='AAASECOND TEST API BUILD';
$args["buildnotes"]='Created via API';
$args["active"]=0;
$args["open"]=0;
$args["releasedate"] = '2016-09-01';
$additionalInfo=' active+open+releasedate attributes';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$test_num);
// --------------------------------------------------------
