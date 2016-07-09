<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetBuilds.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2009/05/01 20:36:56 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

// -------------------------------------------------------------------------------------
$method='getBuildsForTestPlan';
$unitTestDescription="Test - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testplanid"]=3;

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);

// -------------------------------------------------------------------------------------
$method='getLatestBuildForTestPlan';
$unitTestDescription="Test - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testplanid"]=3;

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);
?>