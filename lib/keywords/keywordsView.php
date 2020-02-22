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
require_once("keywordsEnv.php");

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$gui = initScript($db);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * @return object returns the arguments for the page
 */
function initArgs(&$dbHandler) {
  $args = new stdClass();
  list($context,$env) = initContext();

  if( $context->tproject_id <= 0 ) {
    throw new Exception("Error Invalid Test Project ID", 1);
  }

  // Check rights before doing anything else
  // Abort if rights are not enough 
  $user = $_SESSION['currentUser'];

  // check only at test project level
  $environment = array('tproject_id' => $context->tproject_id);
  
  $check = new stdClass();
  $check->items = array('mgt_view_key');
  $check->mode = 'and';
  checkAccess($dbHandler,$user,$environment,$check);
  
  // OK, go ahead
  $args = getKeywordsEnv($dbHandler,$user,$context->tproject_id);
  foreach ($context as $prop => $val) {
    $args->$prop = $val;  
  }

  setOpenByAnotherEnv($args);

  return $args;
}

/**
 * 
 */
function initScript(&$dbH) {

  $argsObj = initArgs($dbH);

  list($add2args,$gui) = initUserEnv($dbH,$argsObj);

  $k2l = get_object_vars($argsObj);
  foreach ($k2l as $pp => $value) {
    $gui->$pp = $value;
  }

  $gui->activeMenu['projects'] = 'active';
  return $gui;
}