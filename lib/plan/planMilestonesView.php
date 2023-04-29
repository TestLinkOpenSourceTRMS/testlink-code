<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 *
 * @package 	 TestLink
 * @author     Francisco Mancardi
 * @copyright  2003-2023, TestLink community 
 * @filesoruce planMilestonesView.php
 * @link 		   http://www.testlink.org
 * 
 **/

require_once("../../config.inc.php");
require_once("common.php");
require_once("testplan.class.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
list($args,$gui) = initScript($db);


$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/**
 *
 */
function initScript(&$dbH) 
{
  $args = init_args($dbH);
  $gui = initialize_gui($dbH,$args);  

 return array($args,$gui);
}

/**
 *
 */
function init_args(&$dbH) {
	list($args,$env) = initContext();

  $args->user = $_SESSION['currentUser'];
  // ----------------------------------------------------------------
  // Feature Access Check
  $env = [
    'script' => basename(__FILE__),
    'tproject_id' => $args->tproject_id,
    'tplan_id' => $args->tplan_id
  ];
  $args->user->checkGUISecurityClearance($dbHandler,$env,
                    array('testplan_planning'),'and');
  // ----------------------------------------------------------------

	return $args;
}

/**
 *
 *
 */
function initialize_gui(&$dbHandler,&$argsObj) {
 
  list($argsObj,$gui) = initUserEnv($dbHandler,$argsObj); 

  if ($gui->tproject_id == 0) {
    throw new Exception("Bad Test Project ID", 1);
  }
  if ($gui->tplan_id == 0) {
    throw new Exception("Bad Test Plan ID", 1);
  }

  $gui->activeMenu['execution'] = 'active';    
  $gui->user_feedback = null;
  $gui->action_descr = null;

  $gui->tplan_name = testplan::getName($dbHandler,$gui->tplan_id);
  $gui->main_descr = lang_get('title_milestones') . " " . $gui->tplan_name;

  $manager = new milestone($dbHandler);
	$gui->items = $manager->get_all_by_testplan($gui->tplan_id);
  $gui->itemsLive = null;

  if(!is_null($gui->items)) {
    $metrics = new tlTestPlanMetrics($dbHandler);
    $gui->itemsLive = $metrics->getMilestonesMetrics($gui->tplan_id,$gui->items);
  }  

  $gui->managerURL = "lib/plan/planMilestonesEdit.php" .
                     "?tproject_id=$gui->tproject&tplan_id=$gui->tplan_id";

	return $gui;
}