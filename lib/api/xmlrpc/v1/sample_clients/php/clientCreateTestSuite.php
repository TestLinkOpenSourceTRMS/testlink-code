<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientCreateTestSuite.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2009/05/01 20:36:56 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

// Tests: 
// parentid is not a Test Suite ID
// parentid is a Test Suite ID but belongs to other Test Project
// use a new name
// use name of existent Test Suite in parentid => default behaviour BLOCK => will not be created
// use name of existent Test Suite in parentid, request renaming
//

$method='createTestSuite';
$unitTestDescription="Test - $method";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testprojectid"]=1;
$args["testsuitename"]='TS API 2';
$args["details"]='This has been created by XMLRPC API Call';
// $args["parentid"]=16;
$args["checkduplicatedname"]=1;
$args["actiononduplicatedname"]='generate_new';
$args["order"]=1;


$debug=true;
echo $unitTestDescription;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);
?>