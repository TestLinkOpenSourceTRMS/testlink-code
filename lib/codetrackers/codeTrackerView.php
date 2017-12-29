<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  codeTrackerView.php
 *
 * @author   uwe_kirst@mentor.com
 * @internal revisions
 * 
 *
**/
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once("common.php");

testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();

$codeTrackerMgr = new tlCodeTracker($db);

$gui = new stdClass();
$args = init_args();
$gui->items = $codeTrackerMgr->getAll(array('output' => 'add_link_count', 'checkEnv' => true));
$gui->canManage = $args->currentUser->hasRight($db,"codetracker_management");
$gui->user_feedback = $args->user_feedback;

if($args->id > 0)
{
  $gui->items[$args->id]['connection_status'] = $codeTrackerMgr->checkConnection($args->id) ? 'ok' : 'ko'; 
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/**
 * @return object returns the arguments for the page
 */
function init_args()
{
  $args = new stdClass();
  $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;

  if( $args->tproject_id == 0 )
  {
    $args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
  }
  $args->currentUser = $_SESSION['currentUser']; 
  
  $args->user_feedback = array('type' => '', 'message' => '');
  
  // only way I've found in order to give feedback for delete
  // need to undertand if we really need/want to do all this mess
  // $args->user_feedback = array('type' => '', 'message' => '');
  // if( isset($_SESSION['codeTrackerView.user_feedback']) )
  // {
  //  $args->user_feedback = array('type' => '', 'message' => $_SESSION['codeTrackerView.user_feedback']);
  //  unset($_SESSION['codeTrackerView.user_feedback']);
  // }

  $args->id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
  return $args;
}


function checkRights(&$db,&$user)
{
  return $user->hasRight($db,"codetracker_view") || $user->hasRight($db,"codetracker_management");
}
