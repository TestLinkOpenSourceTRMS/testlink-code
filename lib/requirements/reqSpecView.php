<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource $RCSfile: reqSpecView.php,v $
 * @version $Revision: 1.11 $
 * @modified $Date: 2008/02/26 22:33:45 $ by $Author: franciscom $
 * @author Martin Havlat
 *
 * Screen to view existing requirements within a req. specification.
 *
 * rev: 20070415 - franciscom - custom field manager
 *      20070415 - franciscom - added reorder feature
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once('requirements.inc.php');
require_once('attachments.inc.php');
require_once('requirement_spec_mgr.class.php');
require_once('requirement_mgr.class.php');
require_once("configCheck.php");

testlinkInitPage($db);

$req_spec_mgr = new requirement_spec_mgr($db);
$req_mgr = new requirement_mgr($db);

$user_feedback='';
$template_dir="requirements/";
$template = 'reqSpecView.tpl';

$args=init_args();

$req_spec = $req_spec_mgr->get_by_id($args->req_spec_id);

//SCHLUNDUS: refactoring, moving to class needed, identical code to reqEdit.php, reqSpecEdit.php, reqSpecView.php
$user = tlUser::getByID($db,$req_spec['author_id']);
$req_spec['author'] = null;
if ($user)
	$req_spec['author'] = $user->getDisplayName();
$req_spec['modifier'] = null;
$user = tlUser::getByID($db,$req_spec['modifier_id']);
if ($user)
	$req_spec['modifier'] = $user->getDisplayName();

$cf_smarty = $req_spec_mgr->html_table_of_custom_field_values($args->req_spec_id,$args->tproject_id);
$attachments = getAttachmentInfosFrom($req_spec_mgr,$args->req_spec_id);

$smarty = new TLSmarty();
$smarty->assign('tproject_name',$args->tproject_name);
$smarty->assign('cf',$cf_smarty);
$smarty->assign('attachments',$attachments);
$smarty->assign('req_spec_id', $args->req_spec_id);
$smarty->assign('req_spec', $req_spec);
$smarty->assign('name',$args->title);
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req"));
$smarty->display($template_dir . $template);


/*
  function: 

  args:
  
  returns: 

*/
function init_args()
{
   $_REQUEST = strings_stripSlashes($_REQUEST);
   $args->req_spec_id = isset($_REQUEST['req_spec_id']) ? $_REQUEST['req_spec_id'] : null;
   $args->title = isset($_REQUEST['title']) ? trim($_REQUEST['title']) : null;
   
   $args->scope = isset($_REQUEST['scope']) ? $_REQUEST['scope'] : null;
   $args->reqStatus = isset($_REQUEST['reqStatus']) ? $_REQUEST['reqStatus'] : TL_REQ_STATUS_VALID;
   $args->reqType = isset($_REQUEST['reqType']) ? $_REQUEST['reqType'] : TL_REQ_TYPE_1;
   $args->countReq = isset($_REQUEST['countReq']) ? intval($_REQUEST['countReq']) : 0;
   
   $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
   $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
  
   return $args;
}


?>