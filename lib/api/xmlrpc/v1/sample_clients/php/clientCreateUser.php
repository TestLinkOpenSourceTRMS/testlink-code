<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource clientGetProjectPlatforms
 * @Author: francisco.mancardi@gmail.com
 *
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();


$method='createUser';
$test_num=0;
$tlDevKey = 'dev01';
$tlDevKey = isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $tlDevKey;


// ------------------------------------------------
$test_num++;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=$tlDevKey;
$args["login"] = 'ZTZ';
$args["firstname"] = 'first name';
$args["lastname"] = 'last name';
$args["email"] = 'email@email.com';
$args["password"] = 'OPTIONAL';

$additionalInfo = '';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args,$test_num);

