<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqView.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/11/19 21:02:56 $ by $Author: franciscom $
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

$req_mgr=new requirement_mgr($db);
$req_id = isset($_REQUEST['requirement_id']) ? strings_stripSlashes($_REQUEST['requirement_id']) : null;

$template_dir="requirements/";

$req = $req_mgr->get_by_id($req_id);
$req['author'] = getUserName($db,$req['author_id']);
$req['modifier'] = getUserName($db,$req['modifier_id']);
$req['coverage'] = $req_mgr->get_coverage($req_id);

$cf_smarty=$req_mgr->html_table_of_custom_field_values($req_id);

$smarty = new TLSmarty();
$smarty->assign('cf',$cf_smarty);
$smarty->assign('req_id',$req_id);
$smarty->assign('title', $req['title']);
$smarty->assign('req', $req);
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 
$smarty->assign('selectReqStatus', $arrReqStatus);
$smarty->display($template_dir . 'reqView.tpl');
?>
