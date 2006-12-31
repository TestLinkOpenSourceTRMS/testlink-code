<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: cfields_view.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2006/12/31 16:16:20 $ by $Author: franciscom $
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
testlinkInitPage($db);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$bDelete = isset($_GET['delete']) ? 1 : 0;
$bConfirmed = isset($_GET['confirmed']) ? 1 : 0;

$sqlResult = null;

$cfield_mgr=New cfield_mgr($db);

$cfield_map=$cfield_mgr->get_all();

//echo "<pre>debug 20061227 " . __FUNCTION__ . " --- "; print_r($cfield_map); echo "</pre>";

$smarty = new TLSmarty();

$smarty->assign('cf_map',$cfield_map);
$smarty->assign('cf_types',$cfield_mgr->get_available_types());

$smarty->display('cfields_view.tpl');
?>