<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: cfieldsView.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/12/18 18:02:13 $ by $Author: franciscom $
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
