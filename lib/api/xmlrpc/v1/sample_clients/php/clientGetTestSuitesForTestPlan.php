<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetTestSuitesForTestPlan.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2009/05/01 20:36:56 $ by $Author: franciscom $
 *
 *
 * A sample client implementation in php
 * 
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net>
 * @package 	TestlinkAPI
 * @link      http://testlink.org/api/
 *
 *
 *
 * rev: 20081013 - franciscom - minor improvements to avoid reconfigure server url
 *                              added test of getTestSuitesForTestPlan()
 *      20080818 - franciscom - start work on custom field tests
 *      20080306 - franciscom - added dBug to improve diagnostic info.
 *      20080305 - franciscom - refactored
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getTestSuitesForTestPlan';
$test_num=1;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testplanid"]=3;

$debug=true;
echo $unitTestDescription;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args);
?>