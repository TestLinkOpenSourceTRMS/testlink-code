<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: cfieldsView.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2008/02/14 21:26:20 $ by $Author: schlundus $
**/
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);

$template_dir = 'cfields/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$cfield_mgr = new cfield_mgr($db);
$cfield_map = $cfield_mgr->get_all();

$smarty = new TLSmarty();
$smarty->assign('cf_map',$cfield_map);
$smarty->assign('cf_types',$cfield_mgr->get_available_types());
$smarty->display($template_dir . $default_template);
?>
