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
// $f_list used as suffix to create test case function name
// if function does not exist => will not be executed
// then to disable a test just add any letter before single letter on this list
// example na => tc_a() will not be found => not executed
// 
$f_list = 'na,nb,nc,nd,ne,nf,ng,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z';
$f_list = explode(',',$f_list);
foreach($f_list as $sfx)
{
	$fname = 'tc_' . $sfx;
	if( function_exists($fname) )
	{
		$fname($client,$tcCounter);
	}
}
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
	$args["summary"] = "Changing SUMMARY via API with special chars '&#><";

	$debug=true;
	echo $unitTestDescription;
	$client->debug=$debug;
	runTest($client,$method,$args,$tcCounter);
}
// ----------------------------------------------------------------------------------------------------
// ..................................................................................
function tc_b(&$client,&$tcCounter)
{
	$method='updateTestCase';
	$unitTestDescription = "Test #{$tcCounter}- {$method} - " .
					       "TRY TO Update Summary WRONG VERSION NUMBER";
	$tcCounter++;
	
	$args=array();
	$args["devKey"]=DEV_KEY;
	$args["testcaseexternalid"] = 'TPAFR-1';
	$args["version"] = 9;
	$args["summary"] = "Changing SUMMARY via API with special chars '&#><";

	$debug=true;
	echo $unitTestDescription;
	$client->debug=$debug;
	runTest($client,$method,$args,$tcCounter);
}
// ----------------------------------------------------------------------------------------------------
// ..................................................................................
function tc_c(&$client,&$tcCounter)
{
	$method='updateTestCase';
	$unitTestDescription = "Test #{$tcCounter}- {$method} - " .
					       "TRY TO Update Summary WRONG TYPE FOR VERSION NUMBER";
	$tcCounter++;
	
	$args=array();
	$args["devKey"]=DEV_KEY;
	$args["testcaseexternalid"] = 'TPAFR-1';
	$args["version"] = 'A';
	$args["summary"] = "Changing SUMMARY via API with special chars '&#><";

	$debug=true;
	echo $unitTestDescription;
	$client->debug=$debug;
	runTest($client,$method,$args,$tcCounter);
}
// ----------------------------------------------------------------------------------------------------
// ..................................................................................
function tc_d(&$client,&$tcCounter)
{
	$method='updateTestCase';
	$unitTestDescription = "Test #{$tcCounter}- {$method} - " .
					       "TRY TO Update Summary WITH VERSION NUMBER";
	$tcCounter++;
	
	$args=array();
	$args["devKey"]=DEV_KEY;
	$args["testcaseexternalid"] = 'TPAFR-1';
	$args["version"] = 2;
	$args["summary"] = "XXXWS - Changing SUMMARY via API with For version= " . $args["version"];

	$unitTestDescription .= ' ' . $args["version"];
	$debug=true;
	echo $unitTestDescription;
	$client->debug=$debug;
	runTest($client,$method,$args,$tcCounter);
}
// ----------------------------------------------------------------------------------------------------
// ..................................................................................
function tc_e(&$client,&$tcCounter)
{
	$method='updateTestCase';
	$unitTestDescription = "Test #{$tcCounter}- {$method} - " .
					       "TRY TO Update Summary,status WITH VERSION NUMBER";
	$tcCounter++;
	
	$args=array();
	$args["devKey"]=DEV_KEY;
	$args["testcaseexternalid"] = 'TPAFR-1';
	$args["version"] = 2;
	$args["summary"] = "XXXWS - Changing SUMMARY via API with For version= " . $args["version"];

	$args["status"] = 2;
	
	$unitTestDescription .= ' ' . $args["version"];
	$debug=true;
	echo $unitTestDescription;
	$client->debug=$debug;
	runTest($client,$method,$args,$tcCounter);
}
// ----------------------------------------------------------------------------------------------------
// ..................................................................................
function tc_f(&$client,&$tcCounter)
{
	$method='updateTestCase';
	$unitTestDescription = "Test #{$tcCounter}- {$method} - " .
					       "TRY TO Update preconditions,importance WITH VERSION NUMBER";
	$tcCounter++;
	
	$args=array();
	$args["devKey"]=DEV_KEY;
	$args["testcaseexternalid"] = 'TPAFR-1';
	$args["version"] = 4;
	$args["preconditions"] = "Yahhoo PRECOND For version= " . $args["version"];
	$args["importance"] = 3;
	
	$unitTestDescription .= ' ' . $args["version"];
	$debug=true;
	echo $unitTestDescription;
	$client->debug=$debug;
	runTest($client,$method,$args,$tcCounter);
}
// ----------------------------------------------------------------------------------------------------
// ..................................................................................
function tc_g(&$client,&$tcCounter)
{
	$method='updateTestCase';
	$unitTestDescription = "Test #{$tcCounter}- {$method} - " .
					       "TRY TO Update execution type WITH VERSION NUMBER";
	$tcCounter++;
	
	$args=array();
	$args["devKey"]=DEV_KEY;
	$args["testcaseexternalid"] = 'TPAFR-1';
	$args["version"] = 4;
	$args["execution_type"] = 2;
	
	$unitTestDescription .= ' ' . $args["version"];
	$debug=true;
	echo $unitTestDescription;
	$client->debug=$debug;
	runTest($client,$method,$args,$tcCounter);
}
// ----------------------------------------------------------------------------------------------------
// ..................................................................................
function tc_h(&$client,&$tcCounter)
{
	$method='updateTestCase';
	$unitTestDescription = "Test #{$tcCounter}- {$method} - " .
					       "TRY TO Update estimated execution duration WITH VERSION NUMBER";
	$tcCounter++;
	
	$args=array();
	$args["devKey"]=DEV_KEY;
	$args["testcaseexternalid"] = 'TPAFR-1';
	$args["version"] = 4;
	$args["estimated_execution_duration"] = 2.78;
	
	$unitTestDescription .= ' ' . $args["version"];
	$debug=true;
	echo $unitTestDescription;
	$client->debug=$debug;
	runTest($client,$method,$args,$tcCounter);
}
// ----------------------------------------------------------------------------------------------------
?>