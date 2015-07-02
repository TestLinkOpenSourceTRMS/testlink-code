<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 *
 * @filesource  planUrgency.php
 * @package     TestLink
 * @author      Martin Havlat
 * @copyright   2003-2014, TestLink community 
 * @link        http://www.testlink.org
 * 
 * @internal revisions
 * @since 1.9.13
 **/
 
require('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db,false,false,"checkRights");
$args = init_args();

if($args->show_help)
{
  show_instructions('test_urgency');
  exit();  
}
$templateCfg = templateConfiguration();
$tplan_mgr = new testPlanUrgency($db);
$gui = initializeGui($args,$tplan_mgr->tree_manager);

if( $args->urgency != OFF || isset($args->urgency_tc) )
{
  $gui->user_feedback = doProcess($args,$tplan_mgr);
}  


// get the current urgency for child test cases
$context = new stdClass();
$context->tplan_id = $args->tplan_id;
$context->tsuite_id = $args->node_id;
$context->tproject_id = $args->tproject_id;
$context->platform_id = $args->platform_id;

$gui->listTestCases = $tplan_mgr->getSuiteUrgency($context,array('build4testers' => $args->build4testers),
                                                  array('testcases' => $args->testCaseSet));

foreach($gui->listTestCases as $tcversion_id => $tcaseSet) 
{
  foreach($tcaseSet as $idx => $tcase)
  {
    $gui->listTestCases[$tcversion_id][$idx]['priority'] = priority_to_level($tcase['priority']);
  }  
}

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: init_args()

  args: -
  
  returns: object with user input.

*/
function init_args()
{
  $_REQUEST = strings_stripSlashes($_REQUEST);
    
  $args = new stdClass();
  $args->show_help = (isset($_REQUEST['level']) && $_REQUEST['level']=='testproject');
    
  $args->tproject_id = intval(isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : $_SESSION['testprojectID']);
  $args->tplan_id = intval(isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID']);
  $args->tplan_name = $_SESSION['testplanName'];
  $args->node_type = isset($_REQUEST['level']) ? $_REQUEST['level'] : OFF;
  $args->node_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ERROR;

  // Sets urgency for suite
 
  if (isset($_REQUEST['high_urgency']))
  {  
    $args->urgency = HIGH;
  }
  elseif (isset($_REQUEST['medium_urgency']))
  {  
    $args->urgency = MEDIUM;
  }
  elseif (isset($_REQUEST['low_urgency']))
  {  
    $args->urgency = LOW;
  }
  else
  {
    $args->urgency = OFF;
  }  

  // Sets urgency for every single tc
  if (isset($_REQUEST['urgency'])) 
  {
    $args->urgency_tc = $_REQUEST['urgency'];
  }

  // For more information about the data accessed in session here, see the comment
  // in the file header of lib/functions/tlTestCaseFilterControl.class.php.
  $args->treeFormToken = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0;
  $mode = 'plan_mode';
  $session_data = isset($_SESSION[$mode]) && isset($_SESSION[$mode][$args->treeFormToken]) ? 
                  $_SESSION[$mode][$args->treeFormToken] : null;


  $args->testCaseSet = $session_data['testcases_to_show'];
  $args->build4testers = intval($session_data['setting_build']);
  $args->platform_id = intval($session_data['setting_platform']);
      
  return $args;
}

/**
 *
 */
function initializeGui(&$argsObj,&$treeMgr)
{
  $guiObj = new stdClass();

  $ni = $treeMgr->get_node_hierarchy_info($argsObj->node_id);
  $guiObj->node_name = $ni['name'];
  $guiObj->user_feedback = null;
  $guiObj->node_id = $argsObj->node_id;
  $guiObj->tplan_id = $argsObj->tplan_id;
  $guiObj->tplan_name = $argsObj->tplan_name;
  $guiObj->formToken = $argsObj->treeFormToken;
  return $guiObj;
} 


/**
 *
 */
function doProcess(&$argsObj,&$tplanMgr)
{
  $userFeedback = null;

  // Set urgency for test suite
  if($argsObj->urgency != OFF)
  {
    $userFeedback['type'] = $tplanMgr->setSuiteUrgency($argsObj->tplan_id, $argsObj->node_id, $argsObj->urgency);
    $userFeedback['message'] = lang_get(($userFeedback['type'] == OK) ? "feedback_urgency_ok" : "feedback_urgency_fail");
  }

  // Set urgency for individual testcases
  if(isset($argsObj->urgency_tc))
  {
    foreach ($argsObj->urgency_tc as $id => $urgency) 
    {
      $tplanMgr->setTestUrgency($argsObj->tplan_id, $id, $urgency);
    }
  }

  return $userFeedback;
}



function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'testplan_planning');
}