<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqView.php,v $
 * @version $Revision: 1.6 $
 * @modified $Date: 2008/01/02 11:35:01 $ by $Author: franciscom $
 * @author Martin Havlat
 * 
 * Screen to view content of requirement.
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('attachments.inc.php');
require_once('requirements.inc.php');
require_once('requirement_mgr.class.php');
require_once('users.inc.php');
testlinkInitPage($db);

$template_dir = "requirements/";
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$req_mgr = new requirement_mgr($db);
$req_id = isset($_REQUEST['requirement_id']) ? intval($_REQUEST['requirement_id']) : null;

$req = $req_mgr->get_by_id($req_id);
$main_descr = lang_get('req') . TITLE_SEP . $req['title'];

//SCHLUNDUS: refactoring, moving to class needed, identical code to reqEdit.php, reqSpecEdit.php, reqSpecView.php
$user = tlUser::getByID($db,$req['author_id']);
$req['author'] = null;
if ($user)
	$req['author'] = $user->getDisplayName();
$req['modifier'] = null;
$user = tlUser::getByID($db,$req['modifier_id']);
if ($user)
	$req['modifier'] = $user->getDisplayName();

$req['coverage'] = $req_mgr->get_coverage($req_id);

$cf_smarty = $req_mgr->html_table_of_custom_field_values($req_id);
$attachments = getAttachmentInfosFrom($req_mgr,$req_id);

$smarty = new TLSmarty();
$smarty->assign('main_descr',$main_descr);
$smarty->assign('cf',$cf_smarty);
$smarty->assign('req_id',$req_id);
$smarty->assign('title', $req['title']);
$smarty->assign('req', $req);
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 
$smarty->assign('selectReqStatus', $arrReqStatus);
$smarty->assign('attachments',$attachments);
$smarty->display($template_dir . $default_template);
?>
