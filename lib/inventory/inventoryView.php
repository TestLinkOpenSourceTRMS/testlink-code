<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * View project inventory 
 * 
 * @filesource	inventoryView.php
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 *
 *	@todo redirect if no right
 *
 * @internal Revisions:
 *
 **/

require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);

$args = init_args();
$templateCfg = templateConfiguration();
$gui = new stdClass();
$gui->rightEdit = $_SESSION['currentUser']->hasRights($db,"project_inventory_management",$args->tproject_id);
$gui->rightView = $_SESSION['currentUser']->hasRights($db,"project_inventory_view",$args->tproject_id);
$gui->tproject_id = $args->tproject_id;

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



function init_args()
{
	$argsObj = new stdClass();
	$argsObj->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
	
	return $argsObj;

}
?>