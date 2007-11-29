<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: cfields_view.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2007/11/29 07:59:14 $ by $Author: franciscom $
**/
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);

$template_dir='cfields/';

$sqlResult = null;
$cfield_mgr = new cfield_mgr($db);
$cfield_map = $cfield_mgr->get_all();
$smarty = new TLSmarty();
$smarty->assign('cf_map',$cfield_map);
$smarty->assign('cf_types',$cfield_mgr->get_available_types());
$smarty->display($template_dir . 'cfields_view.tpl');
?>
