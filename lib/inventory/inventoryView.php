<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * View project inventory 
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 2009,2019 TestLink community 
 *
 **/

require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$gui = new stdClass();
$gui->rightEdit = has_rights($db,"project_inventory_management");
$gui->rightView = has_rights($db,"project_inventory_view");

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);