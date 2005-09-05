<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqView.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2005/09/05 11:33:32 $ by $Author: havlat $
 * @author Martin Havlat
 * 
 * Screen to view content of requirement.
 * 
 */
 
////////////////////////////////////////////////////////////////////////////////

require_once('../../config.inc.php');
require_once('common.php');
require_once('requirements.inc.php');
require_once('users.inc.php');


// init page 
testlinkInitPage();

$idReq = isset($_GET['idReq']) ? strings_stripSlashes($_GET['idReq']) : null;

$arrReq = getReqData($idReq);
$arrReq['author'] = getUserName($arrReq['id_author']);
$arrReq['modifier'] = getUserName($arrReq['id_modifier']);
$arrReq['coverage'] = getTc4Req($idReq);


// smarty
$smarty = new TLSmarty;
$smarty->assign('title', $arrReq['title']);
$smarty->assign('arrReq', $arrReq);
$smarty->assign('internalTemplate', 'inc_reqView.tpl');
$smarty->assign('modify_req_rights', has_rights("mgt_modify_req")); 
$smarty->assign('selectReqStatus', $arrReqStatus);

$smarty->display('infoWindow.tpl');

?>
