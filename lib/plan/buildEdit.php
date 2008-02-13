<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: buildEdit.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2008/02/13 01:22:51 $ $Author: franciscom $
 *
 * rev :
 *      20080213 - franciscom - fixed bad template names
 *
*/
require('../../config.inc.php');
require_once("common.php");
require_once("builds.inc.php");
require_once("web_editor.php");

testlinkInitPage($db);

$template_dir = 'plan/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$user_feedback = '';
$template = null;
$button_name = "";
$button_value = "";  

$smarty = new TLSmarty();
$tplan_mgr = new testplan($db);
$build_mgr = new build_mgr($db);

$args = init_args($_REQUEST,$_SESSION);

$of = web_editor('notes',$_SESSION['basehref']);
$of->Value = null;

$the_builds = $tplan_mgr->get_builds($args->tplan_id);

// Checks on build name, and build name<=>build id 
if($args->do_action == "do_create" || $args->do_action == "do_update")
{
	$user_feedback = lang_get("warning_duplicate_build") . TITLE_SEP_TYPE3 . $args->build_name;
	$name_exists = $tplan_mgr->check_build_name_existence($args->tplan_id,$args->build_name);
	$name_id_rel_ok = (isset($the_builds[$args->build_id]) && $the_builds[$args->build_id]['name'] == $args->build_name);
	$can_insert_or_update = (!$name_exists || $name_id_rel_ok) ? 1 : 0;
}
switch($args->do_action)
{
	case 'edit':
		$button_name = "do_update";
		$button_value = lang_get('btn_save');  
		$my_b_info = $build_mgr->get_by_id($args->build_id);
		$args->build_name = $my_b_info['name'];
		$of->Value = $my_b_info['notes'];
		$args->is_active = $my_b_info['active'];
		$args->is_open = $my_b_info['is_open'];
		break;
		
	case 'create':
		$button_name = "do_create";
		$button_value = lang_get('btn_create');  
		$args->is_active = 1;
		$args->is_open = 1;
		break;
		
	case 'do_delete':
		$build = $build_mgr->get_by_id($args->build_id);
		if (!$build_mgr->delete($args->build_id))
			$user_feedback = lang_get("cannot_delete_build");
		else
			logAuditEvent(TLS("audit_build_deleted",$build['name']),"DELETE",$args->build_id,"builds");
		break;

	case 'do_update':
		$of->Value = $args->notes;
		$template = "buildEdit.tpl";
		$status_ok = false;
		if ($can_insert_or_update)
		{
			$user_feedback = lang_get("cannot_update_build");
			$template = "buildEdit.tpl";
			if ($build_mgr->update($args->build_id,$args->build_name,$args->notes,$args->is_active,$args->is_open))
			{
				$user_feedback = '';
				$of->Value = '';
				$template = null;
				$status_ok = true;
				logAuditEvent(TLS("audit_build_saved",$args->build_name),"SAVE",$args->build_id,"builds");
			}
		}
		if(!$status_ok)
		{
			$button_name = "do_update";
			$button_value = lang_get('btn_save');  

			$smarty->assign('build_id',$args->build_id);
			$smarty->assign('build_name',$the_builds[$args->build_id]['name']);
			$smarty->assign('notes', $of->CreateHTML());
			$smarty->assign('is_active', $args->is_active);
			$smarty->assign('is_open', $args->is_open);
		}
		break;

	case 'do_create':
		$of->Value = $args->notes;
		$template = "buildEdit.tpl";
		$status_ok = false;
		if($can_insert_or_update)
		{
			$user_feedback = lang_get("cannot_add_build");
			$template = "buildEdit.tpl";
			$buildID = $build_mgr->create($args->tplan_id,$args->build_name,$args->notes,$args->is_active,$args->is_open);
			if ($buildID)
			{
				$user_feedback = '';
				$of->Value = '';
				$template = null;
				$status_ok = true;
				logAuditEvent(TLS("audit_build_created",$args->build_name),"CREATE",$buildID,"builds");
			} 	
		}
		if(!$status_ok)
		{
			$button_name = "do_create";
			$button_value = lang_get('btn_create');  
			$smarty->assign('build_id',$args->build_id);
   	
			if($args->build_id > 0)
				$smarty->assign('build_name',$the_builds[$args->build_id]['name']);
		    
			$smarty->assign('notes', $of->CreateHTML());
			$smarty->assign('is_active', $args->is_active);
			$smarty->assign('is_open', $args->is_open);
		}
		break;
}  

$smarty->assign('user_feedback',$user_feedback);
$smarty->assign('button_name',$button_name);
$smarty->assign('button_value',$button_value);
$smarty->assign('tplan_name', $args->tplan_name);
$smarty->assign('testplan_create', has_rights($db,"mgt_testplan_create"));

switch($args->do_action)
{
	case "do_create":
	case "do_delete":
	case "do_update":
		$the_builds = $tplan_mgr->get_builds($args->tplan_id);
		$template = is_null($template) ? 'buildView.tpl' : $template;
		$smarty->assign('the_builds',$the_builds);
		$smarty->display($template_dir . $template);
		break; 
		
	case "edit":
	case "create":
		$template = is_null($template) ? $default_template : $template;
		$smarty->assign('the_builds',$the_builds);
		$smarty->assign('build_id',$args->build_id);
		$smarty->assign('build_name', $args->build_name);
		$smarty->assign('is_active', $args->is_active);
		$smarty->assign('is_open', $args->is_open);
		$smarty->assign('notes', $of->CreateHTML());
		$smarty->display($template_dir . $template);
		break;
}

/*
 * INITialize page ARGuments, using the $_REQUEST and $_SESSION
 * super-global hashes.
 * Important: changes in HTML input elements on the Smarty template
 *            must be reflected here.
 *
 *  
 * @parameter hash request_hash the $_REQUEST
 * @parameter hash session_hash the $_SESSION
 * @return    object with html values tranformed and other
 *                   generated variables.
*/
function init_args($request_hash, $session_hash)
{
	$args = null;
	$request_hash = strings_stripSlashes($request_hash);

	$nullable_keys = array('notes','do_action','build_name');
	foreach($nullable_keys as $value)
	{
		$args->$value = isset($request_hash[$value]) ? $request_hash[$value] : null;
	}

	$intval_keys = array('build_id' => 0);
	foreach($intval_keys as $key => $value)
	{
		$args->$key = isset($request_hash[$key]) ? intval($request_hash[$key]) : $value;
	}

	$bool_keys = array('is_active' => 0,'is_open' => 0);
	foreach($bool_keys as $key => $value)
	{
		$args->$key = isset($request_hash[$key]) ? 1 : $value;
	}

	$args->tplan_id	       = isset($session_hash['testPlanId']) ? $session_hash['testPlanId']: 0;
	$args->tplan_name      = isset($session_hash['testPlanName']) ? $session_hash['testPlanName']: '';
	$args->testprojectID   = $session_hash['testprojectID'];
	$args->testprojectName = $session_hash['testprojectName'];
	$args->userID          = $session_hash['userID'];
	
	return $args;
}
?>
