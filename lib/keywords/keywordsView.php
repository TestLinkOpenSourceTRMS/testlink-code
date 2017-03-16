<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource: keywordsView.php
 *
 * Display list of available keywords. 
 */
require_once("../../config.inc.php");
require_once("common.php");

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$args = init_args($db);

$gui = $args;
$gui->editUrl = $_SESSION['basehref'] . "lib/keywords/keywordsEdit.php?" .
                "tproject_id={$gui->tproject_id}"; 

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * @return object returns the arguments for the page
 */
function init_args(&$dbHandler)
{
  $args = new stdClass();
  $args->tproject_id = isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : 0;
  $args->tproject_id = intval($args->tproject_id);

  if( $args->tproject_id <= 0 )
  {
    throw new Exception("Error Invalid Test Project ID", 1);
  }

  // Check rights before doing anything else
  // Abort if rights are not enough 
  $user = $_SESSION['currentUser'];
  $env['tproject_id'] = $args->tproject_id;
  $env['tplan_id'] = 0;
  
  $check = new stdClass();
  $check->items = array('mgt_view_key');
  $check->mode = 'and';
  checkAccess($dbHandler,$user,$env,$check);
  
  // OK, go ahead
  $tproject = new testproject($dbHandler);
  $args->keywords = $tproject->getKeywords($args->tproject_id);

  $args->canManage = $user->hasRight($dbHandler,"mgt_modify_key",$args->tproject_id);

  return $args;
}