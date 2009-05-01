<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientCreateTestCase.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2009/05/01 20:36:56 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='createTestCase';
$unitTestDescription="Test - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=1;
$args["testsuiteid"]=186;
$args["testcasename"]='File System Check';
$args["summary"]='Test Case created via API';
$args["steps"]="These are the steps";
$args["expectedresults"]="All OK";
$args["authorlogin"]='admin';
$args["authorlogin"]='admin';
$args["checkduplicatedname"]=1;
// $args["keywordid"]='1,2,3';
// $args["keywords"]='ALFA,BETA,ZETA';


$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);

// ----------------------------------------------------------------------------------------------------
$method='createTestCase';
$unitTestDescription="Test - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=11260;
$args["testsuiteid"]=11465;
$args["testcasename"]='Network Interface Card (NIC) driver update';
$args["summary"]='Test Case created via API';
$args["steps"]="These are the steps";
$args["expectedresults"]="All OK";
$args["authorlogin"]='admin';
$args["authorlogin"]='admin';
$args["checkduplicatedname"]=1;
$args["keywordid"]='1,2,3,4';


$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);

// ----------------------------------------------------------------------------------------------------
$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=1;
$args["testsuiteid"]=11;
$args["testcasename"]='Volume Manager Increase size';
$args["summary"]='Test Case created via API - Volume Manager Increase size';
$args["steps"]="These are the steps for Volume Manager Increase size";
$args["expectedresults"]="All OK";
$args["authorlogin"]='admin';
$args["authorlogin"]='admin';
$args["checkduplicatedname"]=1;

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);

// ----------------------------------------------------------------------------------------------------
$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=1;
$args["testsuiteid"]=11;
$args["testcasename"]='Volume Manager Increase size';
$args["summary"]='Want to test Action On Duplicate with value create_new_version FOR Volume Manager Increase size';
$args["expectedresults"]="All OK";
$args["authorlogin"]='admin';
$args["authorlogin"]='admin';
$args["checkduplicatedname"]=1;
$args["actiononduplicatedname"]="create_new_version";

$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);
// ----------------------------------------------------------------------------------------------------
?>