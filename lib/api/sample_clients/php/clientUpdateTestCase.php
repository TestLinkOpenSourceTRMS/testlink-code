<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource clientUpdateTestCase.php
 * @author: francisco.mancardi@gmail.com
 *
 * @internal revisions
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$client = new IXR_Client($server_url);

// Get user input
$run = isset($_REQUEST['run']) ? $_REQUEST['run'] : 'all';


$tcCounter = 1;
$dummy = explode(',',$run);
$tc2run = array_flip($dummy);

// ..................................................................................
//if( isset($tc2run['all'] )
//{
//	$tc2
//}
tc_a($client,$tcCounter);

// ..................................................................................

// ..................................................................................
function tc_a(&$client,&$tcCounter)
{
	$method='updateTestCase';
	$unitTestDescription = "Test #{$tcCounter}- {$method} - " .
					       "Update Summary";
	$tcCounter++;
	
	$args=array();
	$args["devKey"]=DEV_KEY;
	$args["testcaseexternalid"] = 'TPAFR-1';
	$args["summary"] = 'Changing SUMMARY via API';

	$debug=true;
	echo $unitTestDescription;
	$client->debug=$debug;
	runTest($client,$method,$args);
}
// ----------------------------------------------------------------------------------------------------
?>