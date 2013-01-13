<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  reqMgrSystemView.php
 *
 * @author  francisco.mancardi@gmail.com
 * @internal revisions
 * 
 * @since 1.9.6
 *
**/
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once("common.php");

testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();

$mgr = new tlReqMgrSystem($db);

$gui = new stdClass();
$args = init_args();
$gui->items = $mgr->getAll(array('output' => 'add_link_count', 'checkEnv' => true));
$gui->canManage = $args->currentUser->hasRight($db,"reqmgrsystem_management");
$gui->user_feedback = $args->user_feedback;

if($args->id > 0)
{
  $gui->items[$args->id]['connection_status'] = $mgr->checkConnection($args->id) ? 'ok' : 'ko'; 
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
<cfg>
<user></user>
<password></password>
<project></project>
<uriwsdl></uriwsdl>
</cfg>
*/


/**
 * @return object returns the arguments for the page
 */
function init_args()
{
  $args = new stdClass();
  $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;

  if( $args->tproject_id == 0 )
  {
    $args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_SESSION['tproject_id']) : 0;
  }
  $args->currentUser = $_SESSION['currentUser']; 
  
  $args->user_feedback = array('type' => '', 'message' => '');
  $args->id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
  return $args;
}


function checkRights(&$db,&$user)
{
  return $user->hasRight($db,"reqmgrsystem_view") || $user->hasRight($db,"reqmgrsystem_management");
}
?>