<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * View project inventory 
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 * @version    	CVS: $Id: inventoryView.php,v 1.2 2010/02/20 09:27:29 franciscom Exp $
 *
 *	@todo redirect if no right
 *
 * @internal Revisions:
 * None
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

?>