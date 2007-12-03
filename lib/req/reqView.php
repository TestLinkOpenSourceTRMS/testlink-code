<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqView.php,v $
 * @version $Revision: 1.11 $
 * @modified $Date: 2007/12/03 20:42:27 $ by $Author: schlundus $
 * @author Martin Havlat
 * 
 * Screen to view content of requirement.
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('requirements.inc.php');
require_once('requirement_mgr.class.php');
require_once('users.inc.php');
testlinkInitPage($db);

$req_mgr = new requirement_mgr($db);
$idReq = isset($_GET['idReq']) ? intval($_GET['idReq']) : null;

$arrReq = $req_mgr->get_by_id($idReq);
$arrReq['author'] = getUserName($db,$arrReq['author_id']);
$arrReq['modifier'] = getUserName($db,$arrReq['modifier_id']);
$arrReq['coverage'] = $req_mgr->get_coverage($idReq);

$cf_smarty=$req_mgr->html_table_of_custom_field_values($idReq);

$smarty = new TLSmarty();
$smarty->assign('cf',$cf_smarty);
$smarty->assign('title', $arrReq['title']);
$smarty->assign('arrReq', $arrReq);
$smarty->assign('internalTemplate', 'inc_reqView.tpl');
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 
$smarty->assign('selectReqStatus', $arrReqStatus);
$smarty->display('infoWindow.tpl');
?>
