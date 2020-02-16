<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	project_req_spec_mgmt.php
 *
 */
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db,false,false,"checkRights");


$args = init_args($db);
$gui = new stdClass();
$gui->tproject_name = $args->tproject_name;
$gui->main_descr = lang_get('testproject') .  TITLE_SEP . 
                    $gui->tproject_name . TITLE_SEP . 
                    lang_get('title_req_spec');
$gui->tproject_id = $args->tproject_id;
$gui->refresh_tree = 'no';

$gui->userGrants = new stdClass();
$gui->userGrants->modify = $args->user->hasRight($db,'mgt_modify_req');
$gui->userGrants->ro = $args->user->hasRight($db,'mgt_view_req');

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display('requirements/project_req_spec_mgmt.tpl');

/**
 *
 */
function init_args(&$dbH)
{
  list($args,$env) = initContext();
  $args->id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

  if ($args->tproject_id==0) {
    $args->tproject_id = $args->id;
  }

  if ($args->tproject_id==0) {
    throw new Exception("Bad Test Project ID", 1);
  }
  
  $args->tproject_name = testproject::getName($dbH,$args->tproject_id);
  $args->basehref = $_SESSION['basehref'];

  return $args;
}



/**
 *
 */
function checkRights(&$db,&$user)
{
  return ($user->hasRight($db,'mgt_view_req') || 
		  $user->hasRight($db,'mgt_modify_req'));
}