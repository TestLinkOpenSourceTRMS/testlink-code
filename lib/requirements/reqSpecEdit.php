<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: reqSpecEdit.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2007/11/22 07:34:37 $
 * 
 * @author Martin Havlat
 * 
 * View existing and create a new req. specification.
 * 
 * rev : 20071106 - franciscom - custom field management
 * 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("req_tree_menu.php");
require_once('requirements.inc.php');
require_once('requirement_spec_mgr.class.php');

require_once("../../third_party/fckeditor/fckeditor.php");
testlinkInitPage($db);

$sqlResult = null;
$action = null;
$title = null;
$scope = null;

$_REQUEST = strings_stripSlashes($_REQUEST);
$title = isset($_REQUEST['req_spec_title']) ? $_REQUEST['req_spec_title'] : null;
$scope = isset($_REQUEST['scope']) ? $_REQUEST['scope'] : null;
$countReq = isset($_REQUEST['countReq']) ? intval($_REQUEST['countReq']) : 0;
$req_spec_id = isset($_REQUEST['req_spec_id']) ? intval($_REQUEST['req_spec_id']) : null;

$do_action = isset($_REQUEST['do_action']) ? $_REQUEST['do_action']:null;
$tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tprojectName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : "";
$user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
// ----------------------------------------------------

$req_spec_mgr = new requirement_spec_mgr($db);
$smarty = new TLSmarty();
$smarty->assign('page_descr',lang_get('req_spec'));

$template_dir="requirements/";

switch($do_action)
{
  case "create":
  $template = $template_dir . 'reqSpecEdit.tpl';
	
	// get custom fields
	$cf_smarty = $req_spec_mgr->html_table_of_custom_field_inputs(null,$tprojectID);
  $smarty->assign('cf', $cf_smarty);
  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_create');
  break;


  case "edit":
  $template = $template_dir . 'reqSpecEdit.tpl';
  $req_spec = $req_spec_mgr->get_by_id($req_spec_id);
  
  $scope=$req_spec['scope'];			
	
	$smarty->assign('req_spec_id',$req_spec_id);	
	$smarty->assign('req_spec_title',$req_spec['title']);	
  	
  $smarty->assign('total_req_counter',$req_spec['total_req']);	
  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_update');
  
	// get custom fields
	$cf_smarty = $req_spec_mgr->html_table_of_custom_field_inputs($req_spec_id,$tprojectID);
  $smarty->assign('cf', $cf_smarty);
  break;


  case "do_create":
  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_create');

  $template = $template_dir . 'reqSpecEdit.tpl';
	$ret = $req_spec_mgr->create($tprojectID,$title,$scope,$countReq,$user_id);
	
	$sqlResult=$ret['msg'];
	if( $ret['status_ok'])
	{
    $cf_map = $req_spec_mgr->get_linked_cfields(null,$tprojectID) ;
    $req_spec_mgr->values_to_db($_REQUEST,$ret['id'],$cf_map);
	}
  $scope="";
  break;


  case "do_update":
  $smarty->assign('req_spec_id', $req_spec_id);
  $template = $template_dir . 'reqSpecView.tpl';
	$ret=$req_spec_mgr->update($req_spec_id,$title,$scope,$countReq,$user_id);
	$sqlResult=$ret['msg'];

	if( $ret['status_ok'] )
	{
    $cf_map = $req_spec_mgr->get_linked_cfields($req_spec_id);
    $req_spec_mgr->values_to_db($_REQUEST,$req_spec_id,$cf_map);
	} 

  $req_spec = $req_spec_mgr->get_by_id($req_spec_id);
  $req_spec['author'] = getUserName($db,$req_spec['author_id']);
  $req_spec['modifier'] = getUserName($db,$req_spec['modifier_id']);
  $smarty->assign('req_spec_id', $req_spec_id);
  $smarty->assign('req_spec', $req_spec);
  break;


  case "do_delete":
  $req_spec = $req_spec_mgr->get_by_id($req_spec_id);
  $req_spec_mgr->delete($req_spec_id);

  $template = 'show_message.tpl';
  $user_feedback = sprintf(lang_get('req_spec_deleted'),$req_spec['title']);
  $smarty->assign('title', lang_get('delete_req_spec'));
  $smarty->assign('item_type', lang_get('requirement_spec'));
  $smarty->assign('item_name', $req_spec['title']);
  $smarty->assign('user_feedback',$user_feedback );
  $smarty->assign('refresh_tree','yes');
  $smarty->assign('result','ok');
  break;

    
}

$of = new fckeditor('scope') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet=$g_fckeditor_toolbar;;

$of->Value="";
if($scope)
{
  $of->Value = $scope;
}

$smarty->assign('name',$title);
$smarty->assign('productName', $tprojectName);
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 
$smarty->assign('scope',$of->CreateHTML());
$smarty->display($template);
?>





  	
