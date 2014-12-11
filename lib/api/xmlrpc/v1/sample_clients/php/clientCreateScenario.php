<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientCreateTestProject.php
 *
 * @version 
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
$cfg->devKey =  'admin';
$cfg->prefix = 'AX';

// $server_url is GLOBAL created on some previous include
$args4call = array();

$ret['createTestProject'] = createTestProject($server_url,$cfg,$args4call);
// $ret = createTestSuite($server_url$cfg,$args4call);
$ret['createTestPlan'] = createTestPlan($server_url,$cfg,$args4call);
$ret['createBuild'] = createBuild($server_url,$cfg,$args4call,$ret['createTestPlan'][0]['id']);
$ret['createPlatform'] = createPlatform($server_url,$cfg,$args4call);



/**
 *
 */
function createTestProject($server_url,$cfg,&$args4call)
{
  $method = __FUNCTION__;

  $args4call[$method] = array("devKey" => $cfg->devKey,
                              "testcaseprefix" => $cfg->prefix,
                              "testprojectname" => 'TPROJ-01',
                              "notes" => "test project created using XML-RPC-API");
  
  $client = new IXR_Client($server_url);
  $client->debug = $cfg->debug;
  return runTest($client,$method,$args4call[$method]);
}


/**
 *
 */
function createTestPlan($server_url,$cfg,&$args4call)
{
  $method = __FUNCTION__;
  $args4call[$method] = array("devKey" => $cfg->devKey,
                              "testprojectname" => $args4call['createTestProject']["testprojectname"], 
                              "testplanname" => "TPLAN A",
                              "notes" => "Test plan used to test report 'Test cases without tester assignment' ");
  
  $client = new IXR_Client($server_url);
  $client->debug = $cfg->debug;
  return runTest($client,$method,$args4call[$method]);
}


/**
 *
 */
function createBuild($server_url,$cfg,&$args4call,$tplan_id)
{

  $method = __FUNCTION__;
  $args4call[$method] = array("devKey" => $cfg->devKey,
                              "buildname" => '1.0', 
                              "testplanid" => $tplan_id,
                              "buildnote" => "Build used to test issue 5451");
  
  $client = new IXR_Client($server_url);
  $client->debug = $cfg->debug;
  return runTest($client,$method,$args4call[$method]);
}

/**
 *
 */
function createPlatform($server_url,$cfg,&$args4call)
{
  $method = __FUNCTION__;

  $platforms = array();

  $item = new stdClass();
  $item->name = 'PLAT-01';
  $item->notes = 'Notes for' . $item->name;
  $platforms[] = $item;

  $item = new stdClass();
  $item->name = 'PLAT-02';
  $item->notes = 'Notes for' . $item->name;
  $platforms[] = $item;

  $item = new stdClass();
  $item->name = 'PLAT-03';
  $item->notes = 'Notes for' . $item->name;
  $platforms[] = $item;

  $res = array();
  $client = new IXR_Client($server_url);
  foreach($platforms as $item)
  {
    $args4call[$method] = array("devKey" => $cfg->devKey,
                                "platformname" => $item->name, 
                                "notes" => $item->notes, 
                                "testprojectname" => $args4call['createTestProject']["testprojectname"]);
    $client->debug = $cfg->debug;
    $res[] = runTest($client,$method,$args4call[$method]);
  }  
  return $res;
}
