<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Full example of API use, created to setup environment to test
 * http://mantis.testlink.org/view.php?id=5451
 *
 * Create a Test Project 
 * Create a Test Plan
 * Create a Build 
 * Create 3 Platforms
 * Assign ALL Platforms to Test Plan.
 *
 *
 * @filesource ./lib/api/sample_clients/php/clientSetupTicket-5451.php
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal revisions
 * 
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

// common
$cfg = new stdClass();
$cfg->debug = false;
$cfg->devKey =  'Spock (The Tholian Web)';
$cfg->prefix = 'AXE';

$args4call = array();

// $server_url is GLOBAL created on some previous include
$op = createTestProject($server_url,$cfg,$args4call);
$op = createTestPlan($server_url,$cfg,$args4call);
if( isset($op[0]['status']) &&  $op[0]['status'])
{
  $tplan_id = $op[0]['id'];
  $op = createBuild($server_url,$cfg,$args4call,$tplan_id);
}


$platformSet = array();
$dummy = new stdClass();
$dummy->name = 'Ferrari';
$dummy->notes = 'Italy';
$platformSet[$dummy->name] = $dummy;

$dummy = new stdClass();
$dummy->name = 'Porsche';
$dummy->notes = 'Germany';
$platformSet[$dummy->name] = $dummy;

$dummy = new stdClass();
$dummy->name = 'Renault';
$dummy->notes = 'France';
$platformSet[$dummy->name] = $dummy;

foreach($platformSet as $name => &$item)
{
  $op = createPlatform($server_url,$cfg,$args4call,$item);
  $item->id = $op['id'];  
}




// ------------------------------------------------------------------------------------------------
// Support functions
// ------------------------------------------------------------------------------------------------
function createTestProject($server_url,$cfg,&$args4call)
{
  $method = 'createTestProject';
  $args4call[$method] = array("devKey" => $cfg->devKey,
                                "testcaseprefix" => $cfg->prefix,
                                "testprojectname" => "TICKET 5451",
                                "notes" => 
                                "To test TICKET 5451: Test Plan WITH 2 or more PLATFORMS - " . 
                                "Test Cases Without Tester Assignment provide wrong result");
  
  $client = new IXR_Client($server_url);
  $client->debug = $cfg->debug;
  return runTest($client,$method,$args4call[$method]);
}


function createTestPlan($server_url,$cfg,&$args4call)
{
  $method = 'createTestPlan';
  $args4call[$method] = array("devKey" => $cfg->devKey,
                              "testprojectname" => $args4call['createTestProject']["testprojectname"], 
                              "testplanname" => "TPLAN A - 3 Platforms",
                              "notes" => "Test plan used to test report 'Test cases without tester assignment' ");
  
  $client = new IXR_Client($server_url);
  $client->debug = $cfg->debug;
  return runTest($client,$method,$args4call[$method]);
}


function createBuild($server_url,$cfg,&$args4call,$tplan_id)
{

  $method = 'createBuild';
  $args4call[$method] = array("devKey" => $cfg->devKey,
                              "buildname" => '1.0', 
                              "testplanid" => $tplan_id,
                              "buildnote" => "Build used to test issue 5451");
  
  $client = new IXR_Client($server_url);
  $client->debug = $cfg->debug;
  return runTest($client,$method,$args4call[$method]);
}

function createPlatform($server_url,$cfg,&$args4call,$item)
{
  $method = 'createPlatform';
  $args4call[$method] = array("devKey" => $cfg->devKey,
                              "platformname" => $item->name, 
                              "notes" => $item->notes, 
                              "testprojectname" => $args4call['createTestProject']["testprojectname"]);
  
  $client = new IXR_Client($server_url);
  $client->debug = $cfg->debug;
  return runTest($client,$method,$args4call[$method]);
}


?>