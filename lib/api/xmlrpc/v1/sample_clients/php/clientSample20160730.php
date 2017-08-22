<?php
 /**
 * A sample client implementation in php
 * 
 * @author  francisco.mancardi@gmail.com
 * @package TestlinkAPI
 * @link    http://www.testlink.org
 *
 */
 
require_once 'util.php';
require_once 'sample.inc.php';

// Global Coupling Used
$tlTestCasePrefix = 'ZTZ';
$tlDevKey = '985978c915f50e47a4b1a54a943d1b76';
$tlDebug = true;
$tlOverWriteOnAdd = 0;

$tlIdx = 0;

$env = new stdClass();
$env->tlProjectID = -1;
$env->tlSuiteID = -1;
$env->tlPlanID = -1;
$env->tlTestCaseVersion = 1;
// ---------------------------------------------- :)

$doSetUp = false;
$phpSteps = array();

if( $doSetUp )
{
  $phpSteps[] = array('f2i' => 'stepDeleteTestProject.php', 'id' => 'tlProjectID');
  $phpSteps[] = array('f2i' => 'stepCreateTestProject.php', 'id' => 'tlProjectID');
  $phpSteps[] = array('f2i' => 'stepCreateTestSuite.php', 'id' => 'tlSuiteID');
  $phpSteps[] = array('f2i' => 'stepCreateTestCase.php', 'id' => 'tlJolt');
  $phpSteps[] = array('f2i' => 'stepCreateTestPlan.php', 'id' => 'tlPlanID');
}
else
{
  // 
  $env->tlProjectID = 1046;
  $env->tlPlanID = 1051;
  $env->tlTestCaseVersion = 2;
  $tlOverWriteOnAdd = 1;
} 

$phpSteps[] = array('f2i' => 'stepAddTestCaseToTestPlan.php', 'id' => 'tlJolt');

foreach( $phpSteps as $m2i)
{
  try 
  {
    $tlIDName = $m2i['id'];
    require_once $m2i['f2i'];
  } 
  catch (Exception $e) 
  {
    echo $e->getMessage();
  }
}  