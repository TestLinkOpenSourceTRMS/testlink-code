<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  issueTrackerView.php
 * @author   francisco.mancardi@gmail.com
 *
**/
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once("common.php");

testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();

$gui = initEnv($db);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/**
 * @return object returns the arguments for the page
 */
function init_args($context) {
  $args = new stdClass();
  $args->currentUser = $_SESSION['currentUser']; 
  $args->user_feedback = array('type' => '', 'message' => '');
  $args->id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
  $args->tproject_id = $context->tproject_id;
  $args->tplan_id = $context->tplan_id;
  
  return $args;
}

/**
 *
 */
function initEnv(&$dbH) {

  $issueTrackerMgr = new tlIssueTracker($dbH);
  list($context,$env) = initContext();

  $args = init_args($context);

  list($add2args,$gui) = initUserEnv($dbH,$context);
  $gui->activeMenu['system'] = 'active';
  
  $gui->items = $issueTrackerMgr->getAll(array('output' => 'add_link_count', 'checkEnv' => true));

  $gui->canManage = $args->currentUser->hasRight($dbH,"issuetracker_management");

  $gui->user_feedback = $args->user_feedback;

  if($args->id > 0) {
    $gui->items[$args->id]['connection_status'] = $issueTrackerMgr->checkConnection($args->id) ? 'ok' : 'ko'; 
  }
  return $gui;
}



/**
 *
 */
function checkRights(&$db,&$user) {
  return $user->hasRight($db,"issuetracker_view") || $user->hasRight($db,"issuetracker_management");
}