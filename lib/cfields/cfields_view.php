<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: cfields_view.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2007/01/29 08:13:32 $ by $Author: franciscom $
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
testlinkInitPage($db);

$sqlResult = null;
$cfield_mgr = new cfield_mgr($db);
$cfield_map = $cfield_mgr->get_all();
$smarty = new TLSmarty();
$smarty->assign('cf_map',$cfield_map);
$smarty->assign('cf_types',$cfield_mgr->get_available_types());
$smarty->display('cfields_view.tpl');
?>
