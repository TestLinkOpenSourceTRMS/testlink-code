<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientCreateTestPlan.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2009/06/09 20:23:46 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='createTestPlan';
$unitTestDescription="Test - {$method}";
$idx=1;

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testprojectname"]='API Methods Test Project 2';
$args["testprojectname"]='API Methods Test Project AXECX1';
$args["testplanname"]="TPLAN BY API";
$args["notes"]="test plan created using XML-RPC-API";

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$idx);
$idx++;

// --------------------------------------------------------------------------
$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testprojectname"]='TPROJECT1';
$args["testplanname"]="TPLAN BY API";
$args["notes"]="test plan created using XML-RPC-API";

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$idx);
$idx++;

// --------------------------------------------------------------------------
$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testprojectname"]='TPROJECT1';
$args["testplanname"]="TPLAN BY API-2";
$args["notes"]="test plan 2 created using XML-RPC-API";
$args["active"]=0;
$args["public"]=1;

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$idx);
$idx++;