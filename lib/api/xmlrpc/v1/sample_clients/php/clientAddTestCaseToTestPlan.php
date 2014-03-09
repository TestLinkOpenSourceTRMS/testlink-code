<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientAddTestCaseToTestPlan.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2010/07/11 17:30:25 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method="addTestCaseToTestPlan";

$unitTestDescription="Test - {$method} - Test Plan WITHOUT Platforms";

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testprojectid"] = 521; //188;
$args["testcaseexternalid"]='SPP-3';
$args["version"]=1;
$args["testplanid"]=522;


$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
$answer = runTest($client,$method,$args);
// ---------------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------------
$unitTestDescription="Test - {$method} - Test Plan WITH Platforms";

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : DEV_KEY;
$args["testprojectid"] = 521; // 188;
$args["testcaseexternalid"]='SPP-1';
$args["version"]=1;
$args["testplanid"]=523;

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
$answer = runTest($client,$method,$args,2);


// [ID: 1 ] MAC OS		
// [ID: 2 ] Solaris 10		
// [ID: 3 ] Solaris 8		
// [ID: 4 ] Solaris 9		
// [ID: 5 ] Windows 2008		
// [ID: 6 ] Windows 7

// [ID: 213 ] <xml project>		 XML			
// [ID: 214 ] Italian chars é à ò		 ITA			
// [ID: 1 ] P1	  sasas	 P1			
// [ID: 206 ] PROJECT_FOR_U1