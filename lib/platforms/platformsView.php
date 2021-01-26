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

$templateCfg = templateConfiguration();
$args = init_args();

$platform_mgr = new tlPlatform($db, $args->tproject_id);
$gui = $platform_mgr->initViewGui($args->currentUser);	  

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * 
 *
 */
function init_args() {
	$args = new stdClass();
  $args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;

  if( 0 == $args->tproject_id ) {
    throw new Exception("Unable Get Test Project ID => Can Not Proceed", 1);
  }

	$args->currentUser = $_SESSION['currentUser']; 

	return $args;
}

/**
 * 
 *
 */
function checkRights(&$db,&$user) {
	return ($user->hasRightOnProj($db,'platform_management') 
          || $user->hasRightOnProj($db,'platform_view'));
}