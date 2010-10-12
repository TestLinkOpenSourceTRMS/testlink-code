<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Manages test plans
 *
 * @package 	TestLink
 * @author 		
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: planEdit.php,v 1.56 2010/10/12 19:58:59 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 *
 * @internal Revisions:
 * 20101012 - franciscom - html_table_of_custom_field_inputs() interface changes
 *						   BUGID 3891: Do not lose Custom Field values if test plan can not be created due to duplicated name	
 * 20100602 - franciscom - BUGID 3485: "Create from existing Test Plan" always copies builds
 *
 **/

require_once('../../config.inc.php');
require_once("common.php");
require_once("web_editor.php");
$editorCfg = getWebEditorCfg('testplan');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$smarty = new TLSmarty();
$do_display=false;
$template = null;
$args = init_args($_REQUEST);
if (!$args->tproject_id)
{
	$smarty->assign('title', lang_get('fatal_page_title'));
	$smarty->assign('content', lang_get('error_no_testprojects_present'));
	$smarty->display('workAreaSimple.tpl');
	exit();
}

$gui = initializeGui($db,$args,$editorCfg,$tproject_mgr);
$of = web_editor('notes',$_SESSION['basehref'],$editorCfg);
$of->Value = getItemTemplateContents('testplan_template', $of->InstanceName, $args->notes);


// Checks on testplan name, and testplan name<=>testplan id
if($args->do_action == "do_create" || $args->do_action == "do_update")
{
	$gui->testplan_name = $args->testplan_name;
	$name_exists = $tproject_mgr->check_tplan_name_existence($args->tproject_id,$args->testplan_name);
	$name_id_rel_ok = (isset($gui->tplans[$args->tplan_id]) && 
	                   $gui->tplans[$args->tplan_id]['name'] == $args->testplan_name);
}

// 20101012 - franciscom
// interface changes to be able to do not loose CF values if some problem arise on User Interface
$gui->cfields = $tplan_mgr->html_table_of_custom_field_inputs($args->tplan_id,$args->tproject_id,'design','',$_REQUEST);
switch($args->do_action)
{
	case 'edit':
		$tplanInfo = $tplan_mgr->get_by_id($args->tplan_id);
		if (sizeof($tplanInfo))
		{
			$of->Value = $tplanInfo['notes'];
			$gui->testplan_name = $tplanInfo['name'];
			$gui->is_active = $tplanInfo['active'];
			$gui->is_public = $tplanInfo['is_public'];
			$gui->tplan_id = $args->tplan_id;
		}
		break;

	case 'do_delete':
		$tplanInfo = $tplan_mgr->get_by_id($args->tplan_id);
		if ($tplanInfo)
		{
			$tplan_mgr->delete($args->tplan_id);
			logAuditEvent(TLS("audit_testplan_deleted",$args->tproject_name,$tplanInfo['name']),
			              "DELETE",$args->tplan_id,"testplan");
		}
		//unset the session test plan if it is deleted
		if (isset($_SESSION['testplanID']) && ($_SESSION['testplanID'] = $args->tplan_id))
		{
			$_SESSION['testplanID'] = 0;
			$_SESSION['testplanName'] = null;
		}
		break;

	case 'do_update':
		$of->Value = $args->notes;
		$gui->testplan_name = $args->testplan_name;
		$gui->is_active = ($args->active == 'on') ? 1 :0 ;
		$gui->is_public = ($args->is_public == 'on') ? 1 :0 ;

		$template = 'planEdit.tpl';
		$status_ok = false;

		if(!$name_exists || $name_id_rel_ok)
		{
			if(!$tplan_mgr->update($args->tplan_id,$args->testplan_name,$args->notes,
			                       $args->active,$args->is_public))
			{
				$gui->user_feedback = lang_get('update_tp_failed1'). $gui->testplan_name . 
				                      lang_get('update_tp_failed2').": " . $db->error_msg() . "<br />";
			}
			else
			{
				logAuditEvent(TLS("audit_testplan_saved",$args->tproject_name,$args->testplan_name),"SAVE",
				                  $args->tplan_id,"testplans");
				$cf_map = $tplan_mgr->get_linked_cfields_at_design($args->tplan_id);
				$tplan_mgr->cfield_mgr->design_values_to_db($_REQUEST,$args->tplan_id,$cf_map);

				if(isset($_SESSION['testplanID']) && ($args->tplan_id == $_SESSION['testplanID']))
				{
					$_SESSION['testplanName'] = $args->testplan_name;
                }
				$status_ok = true;
				$template = null;
			}
		}
		else
		{
			$gui->user_feedback = lang_get("warning_duplicate_tplan_name");
        }
    
		if(!$status_ok)
		{
			$gui->tplan_id=$args->tplan_id;
			$gui->tproject_name=$args->tproject_name;
			$gui->notes=$of->CreateHTML();
		}
		break;

  case 'do_create':
		$template = 'planEdit.tpl';
		$status_ok = false;

		$of->Value = $args->notes;
		$gui->testplan_name = $args->testplan_name;
		$gui->is_active = ($args->active == 'on') ? 1 :0 ;
		$gui->is_public = ($args->is_public == 'on') ? 1 :0 ;
		if(!$name_exists)
		{
			$new_tplan_id = $tplan_mgr->create($args->testplan_name,$args->notes,
			                                   $args->tproject_id,$args->active,$args->is_public);
			if ($new_tplan_id == 0)
			{
				$gui->user_feedback = $db->error_msg();
			}
			else
			{
				logAuditEvent(TLS("audit_testplan_created",$args->tproject_name,$args->testplan_name),
				              "CREATED",$new_tplan_id,"testplans");
				$cf_map = $tplan_mgr->get_linked_cfields_at_design($new_tplan_id,$args->tproject_id);
				$tplan_mgr->cfield_mgr->design_values_to_db($_REQUEST,$new_tplan_id,$cf_map);

				$status_ok = true;
				$template = null;
				$gui->user_feedback ='';

				if($args->rights == 'on')
				{
					$result = insertTestPlanUserRight($db,$new_tplan_id,$args->user_id);
                }
                
				if($args->copy)
				{
					// BUGID 3485: "Create from existing Test Plan" always copies builds
					$options = array('items2copy' => $args->copy_options,'copy_assigned_to' => $args->copy_assigned_to,
									 'tcversion_type' => $args->tcversion_type);
					$tplan_mgr->copy_as($args->source_tplanid, $new_tplan_id,$args->testplan_name,
										$args->tproject_id,$args->user_id,$options);
				}
			}
		}
		else
		{
			$gui->user_feedback = lang_get("warning_duplicate_tplan_name");
        }
    
		if(!$status_ok)
		{
			// $gui->tplan_id=$new_tplan_id;
			$gui->tproject_name=$args->tproject_name;
			$gui->notes=$of->CreateHTML();
		}
		break;
}

switch($args->do_action)
{
   case "do_create":
   case "do_delete":
   case "do_update":
   case "list":
        $gui->tplans = $tproject_mgr->get_all_testplans($args->tproject_id);
        $template = is_null($template) ? 'planView.tpl' : $template;
        $do_display=true;
		break;

   case "edit":
   case "create":
        $template = is_null($template) ? 'planEdit.tpl' : $template;
      	$gui->notes=$of->CreateHTML();
        $do_display=true;
		break;
}
if($do_display)
{
    $smarty->assign('gui',$gui);
    $smarty->display($templateCfg->template_dir . $template);
}


/*
 * INITialize page ARGuments, using the $_REQUEST and $_SESSION
 * super-global hashes.
 * Important: changes in HTML input elements on the Smarty template
 *            must be reflected here.
 *
 *
 * @parameter hash request_hash the $_REQUEST
 * @return    object with html values tranformed and other
 *                   generated variables.
 *
 * 20060103 - fm
*/
function init_args($request_hash)
{
	$session_hash = $_SESSION;
	$args = new stdClass();
	$request_hash = strings_stripSlashes($request_hash);

	$nullable_keys = array('testplan_name','notes','rights','active','do_action');
	foreach($nullable_keys as $value)
 	{
		$args->$value = isset($request_hash[$value]) ? trim($request_hash[$value]) : null;
	}

	$checkboxes_keys = array('is_public' => 0,'active' => 0);
	foreach($checkboxes_keys as $key => $value)
	{
		$args->$key = isset($request_hash[$key]) ? 1 : 0;
    }

	$intval_keys = array('copy_from_tplan_id' => 0,'tplan_id' => 0);
	foreach($intval_keys as $key => $value)
	{
	    $args->$key = isset($request_hash[$key]) ? intval($request_hash[$key]) : $value;
	}
	$args->source_tplanid = $args->copy_from_tplan_id;
	$args->copy = ($args->copy_from_tplan_id > 0) ? TRUE : FALSE;

	$args->copy_options=array();
	$boolean_keys = array('copy_tcases' => 0,'copy_priorities' => 0,
                          'copy_milestones' => 0, 'copy_user_roles' => 0, 
                          'copy_builds' => 0, 'copy_platforms_links' => 0);

	foreach($boolean_keys as $key => $value)
	{
	    $args->copy_options[$key]=isset($request_hash[$key]) ? 1 : 0;
	}

	$args->copy_assigned_to = isset($request_hash['copy_assigned_to']) ? 1 : 0;
	$args->tcversion_type = isset($request_hash['tcversion_type']) ? $request_hash['tcversion_type'] : null;
	$args->tproject_id = $session_hash['testprojectID'];
	$args->tproject_name = $session_hash['testprojectName'];
	$args->user_id = $session_hash['userID'];

	return $args;
}

/**
 * checkRights
 *
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_testplan_create');
}

/**
 * initializeGui
 *
 */
function initializeGui(&$dbHandler,&$argsObj,&$editorCfg,&$tprojectMgr)
{
    $guiObj = new stdClass();
    $guiObj->tproject_id = $argsObj->tproject_id; 
    $guiObj->editorType = $editorCfg['type'];
    $guiObj->tplans = $tprojectMgr->get_all_testplans($argsObj->tproject_id);
    $guiObj->tproject_name = $argsObj->tproject_name;
    $guiObj->main_descr = lang_get('testplan_title_tp_management'). " - " .
                         lang_get('testproject') . ' ' . $argsObj->tproject_name;
    $guiObj->testplan_name = null;
    $guiObj->tplan_id = null;
    $guiObj->is_active = 0;
    $guiObj->is_public = 0;
    $guiObj->cfields = '';
    $guiObj->user_feedback = '';               
    
    $guiObj->grants = new stdClass();  
    $guiObj->grants->testplan_create = $_SESSION['currentUser']->hasRight($dbHandler,"mgt_testplan_create");
    $guiObj->grants->mgt_view_events = $_SESSION['currentUser']->hasRight($dbHandler,"mgt_view_events");
    $guiObj->notes = '';
    
    return $guiObj;
}
?>