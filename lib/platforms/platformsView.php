<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource: platformsView.php
 *
 * allows users to manage platforms. 
 */
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$tplCfg = templateConfiguration();
$args = init_args();

$platform_mgr = new tlPlatform($db, $args->tproject_id);
$gui = $platform_mgr->initViewGui($args->currentUser,$args);	  

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($tplCfg->tpl);


/**
 * 
 *
 */
function init_args() {
	$args = new stdClass();
	$args->currentUser = $_SESSION['currentUser']; 

  list($context,$env) = initContext();
  $args->tproject_id = $context->tproject_id;
  $args->tplan_id = $context->tplan_id;
  
  if( 0 == $args->tproject_id ) {
    throw new Exception("Unable Get Test Project ID => Can Not Proceed", 1);
  }

	return $args;
}



/**
 * 
 *
 */
function checkRights(&$db,&$user) {
	return ($user->hasRightOnProj($db,'platform_management') || 
          $user->hasRightOnProj($db,'platform_view'));
}