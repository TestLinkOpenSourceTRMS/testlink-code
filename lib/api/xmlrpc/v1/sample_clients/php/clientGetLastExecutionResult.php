<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetLastExecutionResult.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2009/05/01 20:36:56 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getLastExecutionResult';

// --------------------------------------------------------------------
$unitTestDescription="Test - {$method} - NO BUILD NO PLATFORM Filters";

/*
$args=array();
$args["devKey"]='eb6fa75e125944e68739514937d63659';
// $args["testplanid"]=335;
// $args["testplanid"]=1635;
// $args["testcaseexternalid"]='API-2';
// $args["testcaseid"]='1631';

// $args["testplanid"]=3;
$args["testplanid"]=190;
$args["testcaseexternalid"]='AF-76';

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);
*/
// --------------------------------------------------------------------

// --------------------------------------------------------------------
$args=array();
$args["devKey"]='eb6fa75e125944e68739514937d63659';
$args["testplanid"]=189;
$args["testcaseexternalid"]='AF-1';
// $args["buildid"]=4;
$unitTestDescription="Test - {$method} - ONLY BUILD ID Filter => " . $args["buildid"];

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);
die();
// --------------------------------------------------------------------

// --------------------------------------------------------------------
$args=array();
$args["devKey"]='DEV_KEY';
$args["testplanid"]=3;
$args["testcaseexternalid"]='PJH-1';
$args["buildname"]='1';
$unitTestDescription="Test - {$method} - ONLY BUILD NAME Filter => " . $args["buildname"];

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);
// --------------------------------------------------------------------

// --------------------------------------------------------------------
$args=array();
$args["devKey"]='DEV_KEY';
$args["testplanid"]=10;
$args["testcaseexternalid"]='PJH-1';
// $args["buildname"]='1';
$args["platformname"]='Ferrari';
$unitTestDescription="Test - {$method} - ONLY PLATFORM NAME Filter => " . $args["platformname"];

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);
// --------------------------------------------------------------------


