<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqEdit.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/11/19 21:02:56 $ by $Author: franciscom $
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
require_once("csv.inc.php");
require_once("xml.inc.php");
require_once('requirement_spec_mgr.class.php');
require_once('requirement_mgr.class.php');

require_once("../../third_party/fckeditor/fckeditor.php");
require_once("configCheck.php");
testlinkInitPage($db);

$req_spec_mgr=new requirement_spec_mgr($db);
$req_mgr=new requirement_mgr($db);

$get_cfield_values=array();
$get_cfield_values['req_spec']=0;
$get_cfield_values['req']=0;

$user_feedback='';
$sqlResult = null;
$action = null;
$sqlItem = 'SRS';
$arrReq = array();
$template_dir="requirements/";
$template='reqSpecView.tpl';

$_REQUEST = strings_stripSlashes($_REQUEST);

$req_id = isset($_REQUEST['requirement_id']) ? $_REQUEST['requirement_id'] : null;
$req_spec_id = isset($_REQUEST['req_spec_id']) ? $_REQUEST['req_spec_id'] : null;


$reqDocId = isset($_REQUEST['reqDocId']) ? trim($_REQUEST['reqDocId']) : null;
$title = isset($_REQUEST['title']) ? trim($_REQUEST['title']) : null;

$scope = isset($_REQUEST['scope']) ? $_REQUEST['scope'] : null;
$reqStatus = isset($_REQUEST['reqStatus']) ? $_REQUEST['reqStatus'] : TL_REQ_STATUS_VALID;
$reqType = isset($_REQUEST['reqType']) ? $_REQUEST['reqType'] : TL_REQ_TYPE_1;
$countReq = isset($_REQUEST['countReq']) ? intval($_REQUEST['countReq']) : 0;
$bCreate = isset($_REQUEST['create']) ? intval($_REQUEST['create']) : 0;

$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
$login_name = isset($_SESSION['user']) ? $_SESSION['user'] : null;

$do_export = isset($_REQUEST['exportAll']) ? 1 : 0;
$exportType = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : null;

$do_create_tc_from_req = isset($_REQUEST['create_tc_from_req']) ? 1 : 0;
$do_delete_req = isset($_REQUEST['req_select_delete']) ? 1 : 0;

$reorder = isset($_REQUEST['req_reorder']) ? 1 : 0;
$do_req_reorder = isset($_REQUEST['do_req_reorder']) ? 1 : 0;

$tproject = new testproject($db);
$smarty = new TLSmarty();

$of = new fckeditor('scope') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet = $g_fckeditor_toolbar;;

$attach['status_ok']=true;
$attach['msg']='';

$do_action = isset($_REQUEST['do_action']) ? $_REQUEST['do_action']:null;
$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : "";
$user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;


switch($do_action)
{
  case "create":
  $template = 'reqEdit.tpl';
	
	// get custom fields
	$cf_smarty = $req_mgr->html_table_of_custom_field_inputs(null,$tproject_id);
  $smarty->assign('cf', $cf_smarty);
  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_create');
  break;


  case "edit":
  $template = 'reqEdit.tpl';
  $req = $req_mgr->get_by_id($req_id);

	// get custom fields
	$cf_smarty = $req_mgr->html_table_of_custom_field_inputs(null,$tproject_id);
  $smarty->assign('cf', $cf_smarty);
  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_update');
  $smarty->assign('req', $req); 
  break;


  case "do_create":
	$template = 'reqEdit.tpl';
	$cf_smarty = $req_mgr->html_table_of_custom_field_inputs(null,$tproject_id);
	$smarty->assign('cf',$cf_smarty);
	$ret = $req_mgr->create($req_spec_id,$reqDocId,$title, $scope,$user_id,$reqStatus,$reqType);
	$user_feedback = $ret['msg'];	                                 
	if($ret['status_ok'])
	{
		$user_feedback = sprintf(lang_get('req_created'), $reqDocId);  
	  
	  $cf_map = $req_mgr->get_linked_cfields(null,$tproject_id) ;
    //$req_mgr->values_to_db($_REQUEST,$ret['id'],$cf_map);
	}
  $scope = '';
  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_create');
  break;


  case "do_update":
  $template = 'reqView.tpl';
  $msg = $req_mgr->update($req_id,trim($reqDocId),$title,$scope,$user_id,$reqStatus,$reqType);
	                              
  $cf_map = $req_mgr->get_linked_cfields(null,$tproject_id) ;
  $req_mgr->values_to_db($_REQUEST,$req_id,$cf_map);
  $req = $req_mgr->get_by_id($req_id);
  $smarty->assign('req', $req); 
  break;
    
}


$smarty->assign('req_id', $req_id);
$smarty->assign('req_spec_id', $req_spec_id);
$smarty->assign('user_feedback', $user_feedback);
$smarty->assign('attach', $attach);
$smarty->assign('action', $action);
$smarty->assign('name',$title);
$smarty->assign('selectReqStatus', $arrReqStatus);
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 

$of->Value="";
if (!is_null($scope))
{
	$of->Value=$scope;
}

$smarty->assign('scope',$of->CreateHTML());
$smarty->display($template_dir.$template);
?>