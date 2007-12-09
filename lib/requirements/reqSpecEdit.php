<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: reqSpecEdit.php,v $
 * @version $Revision: 1.9 $
 * @modified $Date: 2007/12/09 17:27:38 $ $Author: franciscom $ 
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
require_once("web_editor.php");

testlinkInitPage($db);

$sqlResult = null;
$action = null;
$main_descr=null;
$action_descr=null;
$cf_smarty=null;
$user_feedback=null;

$_REQUEST = strings_stripSlashes($_REQUEST);
$args=init_args();

$req_spec_mgr = new requirement_spec_mgr($db);
$smarty = new TLSmarty();

$template_dir="requirements/";

switch($args->do_action)
{
  case "create":
  $main_descr=lang_get('testproject') . TITLE_SEP . $args->tproject_name;
  $action_descr=lang_get('create_req_spec');
  $template = $template_dir . 'reqSpecEdit.tpl';
	
	// get custom fields
	$cf_smarty = $req_spec_mgr->html_table_of_custom_field_inputs(null,$args->tproject_id);
  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_create');
  break;


  case "edit":
  $req_spec = $req_spec_mgr->get_by_id($args->req_spec_id);
  $main_descr=lang_get('req_spec') . TITLE_SEP . $req_spec['title'];
  $action_descr=lang_get('edit_req_spec');

  $template = $template_dir . 'reqSpecEdit.tpl';
  
  $args->scope=$req_spec['scope'];			
	
	$smarty->assign('req_spec_id',$args->req_spec_id);	
	$smarty->assign('req_spec_title',$req_spec['title']);	
  	
  $smarty->assign('total_req_counter',$req_spec['total_req']);	
  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_update');
  
	// get custom fields
	$cf_smarty = $req_spec_mgr->html_table_of_custom_field_inputs($args->req_spec_id,$args->tproject_id);
  break;


  case "do_create":
  $main_descr=lang_get('testproject') . TITLE_SEP . $args->tproject_name;
  $action_descr=lang_get('create_req_spec');
  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_create');

  $template = $template_dir . 'reqSpecEdit.tpl';
  $cf_smarty = $req_spec_mgr->html_table_of_custom_field_inputs(null,$args->tproject_id);
	$ret = $req_spec_mgr->create($args->tproject_id,$args->title,$args->scope,$args->countReq,$args->user_id);
	
	$user_feedback = $ret['msg'];	                                 
	if( $ret['status_ok'])
	{
    $user_feedback = sprintf(lang_get('req_spec_created'),$args->title);
    $cf_map = $req_spec_mgr->get_linked_cfields(null,$args->tproject_id) ;
    $req_spec_mgr->values_to_db($_REQUEST,$ret['id'],$cf_map);
	}
  $args->scope="";
  break;


  case "do_update":
  $smarty->assign('req_spec_id', $args->req_spec_id);
  $template = $template_dir . 'reqSpecView.tpl';
	$ret=$req_spec_mgr->update($args->req_spec_id,$args->title,$args->scope,$args->countReq,$args->user_id);
	$sqlResult=$ret['msg'];

	if( $ret['status_ok'] )
	{
    $cf_map = $req_spec_mgr->get_linked_cfields($args->req_spec_id);
    $req_spec_mgr->values_to_db($_REQUEST,$args->req_spec_id,$cf_map);
	} 

  $cf_smarty = $req_spec_mgr->html_table_of_custom_field_values($args->req_spec_id,$args->tproject_id);
  $req_spec = $req_spec_mgr->get_by_id($args->req_spec_id);
  $req_spec['author'] = trim(getUserName($db,$req_spec['author_id']));
  $req_spec['modifier'] = trim(getUserName($db,$req_spec['modifier_id']));
  $smarty->assign('req_spec_id', $args->req_spec_id);
  $smarty->assign('req_spec', $req_spec);
  break;


  case "do_delete":
  $req_spec = $req_spec_mgr->get_by_id($args->req_spec_id);
  $req_spec_mgr->delete($args->req_spec_id);

  $template = 'show_message.tpl';
  $user_feedback = sprintf(lang_get('req_spec_deleted'),$req_spec['title']);
  $smarty->assign('title', lang_get('delete_req_spec'));
  $smarty->assign('item_type', lang_get('requirement_spec'));
  $smarty->assign('item_name', $req_spec['title']);
  $smarty->assign('user_feedback',$user_feedback );
  $smarty->assign('refresh_tree','yes');
  $smarty->assign('result','ok');
  break;


  case "reorder":
  $template = $template_dir .  'reqSpecReorder.tpl';
  $order_by=' ORDER BY NH.node_order,REQ_SPEC.id ';
  $all_req_spec=$req_spec_mgr->get_all_in_testproject($args->tproject_id,$order_by);
  $smarty->assign('tproject_id', $args->tproject_id);
  $smarty->assign('tproject_name', $args->tproject_name);
  $smarty->assign('arrReqSpecs', $all_req_spec);
  break;

  case "do_reorder":
  $nodes_in_order = transform_nodes_order($args->nodes_order);

  // need to remove first element, is testproject
  array_shift($nodes_in_order);
	$req_spec_mgr->set_order($nodes_in_order);
  $template = $template_dir .  'project_req_spec_mgmt.tpl';
  $smarty->assign('refresh_tree', 'yes');
  break;
}

$of=web_editor('scope',$_SESSION['basehref']) ;
$of->Value="";
if($args->scope)
{
  $of->Value = $args->scope;
}

$smarty->assign('cf', $cf_smarty);
$smarty->assign('action_descr',$action_descr);
$smarty->assign('main_descr',$main_descr);
$smarty->assign('name',$args->title);
$smarty->assign('user_feedback',$user_feedback );
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 
$smarty->assign('scope',$of->CreateHTML());
$smarty->display($template);
?>


<?php
function init_args()
{
  $args->title = isset($_REQUEST['req_spec_title']) ? $_REQUEST['req_spec_title'] : null;
  $args->scope = isset($_REQUEST['scope']) ? $_REQUEST['scope'] : null;
  $args->countReq = isset($_REQUEST['countReq']) ? intval($_REQUEST['countReq']) : 0;
  $args->req_spec_id = isset($_REQUEST['req_spec_id']) ? intval($_REQUEST['req_spec_id']) : null;

  $args->do_action = isset($_REQUEST['do_action']) ? $_REQUEST['do_action']:null;
  $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
  $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : "";
  $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
  $args->nodes_order = isset($_REQUEST['nodes_order']) ? $_REQUEST['nodes_order'] : null;

  return $args;
}



?>

