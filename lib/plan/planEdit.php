<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2
 *
 * Filename $RCSfile: planEdit.php,v $
 *
 * @version $Revision: 1.46 $
 * @modified $Date: 2009/02/07 19:44:03 $ by $Author: schlundus $
 *
 * Purpose:  ability to edit and delete test plans
 *-------------------------------------------------------------------------
 * rev : 20080827 - franciscom - BUGID 1692
 *
 */
require_once('../../config.inc.php');
require_once("common.php");
require_once("web_editor.php");
$editorCfg = getWebEditorCfg('testplan');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$smarty = new TLSmarty();
$smarty->assign('editorType',$editorCfg['type']);

$user_feedback = '';
$template = null;
$args = init_args($_REQUEST,$_SESSION);

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);
$tplans = $tproject_mgr->get_all_testplans($args->tproject_id);

$tpName = null;
$bActive = 0;
$cf_smarty = '';

$of = web_editor('notes',$_SESSION['basehref'],$editorCfg);
$of->Value = null;

$main_descr=lang_get('testplan_title_tp_management'). " - " .
            lang_get('testproject') . ' ' . $args->tproject_name;

// Checks on testplan name, and testplan name<=>testplan id
if($args->do_action == "do_create" || $args->do_action == "do_update")
{
	$tpName = $args->testplan_name;
	$name_exists = $tproject_mgr->check_tplan_name_existence($args->tproject_id,$tpName);
	$name_id_rel_ok = (isset($tplans[$args->tplan_id]) && $tplans[$args->tplan_id]['name'] == $tpName);
}

$cf_smarty = $tplan_mgr->html_table_of_custom_field_inputs($args->tplan_id,$args->tproject_id);

switch($args->do_action)
{
	case 'edit':
		$tpInfo = $tplan_mgr->get_by_id($args->tplan_id);
		if (sizeof($tpInfo))
		{
			$notes = $tpInfo['notes'];
			$of->Value = $notes;
			$tpName = $tpInfo['name'];
			$bActive = $tpInfo['active'];
		}
		break;

	case 'do_delete':
		$tpInfo = $tplan_mgr->get_by_id($args->tplan_id);
		if ($tpInfo)
		{
			$tplan_mgr->delete($args->tplan_id);
			logAuditEvent(TLS("audit_testplan_deleted",$args->tproject_name,$tpInfo['name']),"DELETE",$args->tplan_id,"testplan");
		}
		//unset the session tp if its deleted
		if (isset($_SESSION['testPlanId']) && ($_SESSION['testPlanId'] = $args->tplan_id))
		{
			$_SESSION['testPlanId'] = 0;
			$_SESSION['testPlanName'] = null;
		}
		break;

	case 'do_update':
		$of->Value = $args->notes;
		$tpName = $args->testplan_name;
		$bActive = ($args->active == 'on') ? 1 :0 ;

		$template = 'planEdit.tpl';
		$status_ok = false;

		if(!$name_exists || $name_id_rel_ok)
		{
			if(!$tplan_mgr->update($args->tplan_id,$args->testplan_name,$args->notes,$bActive))
			{
				$user_feedback = lang_get('update_tp_failed1'). $tpName . lang_get('update_tp_failed2').": " .
				                 $db->error_msg() . "<br />";
			}
			else
			{
				logAuditEvent(TLS("audit_testplan_saved",$args->tproject_name,$args->testplan_name),"SAVE",$args->tplan_id,"testplans");
				$cf_map = $tplan_mgr->get_linked_cfields_at_design($args->tplan_id);
				$tplan_mgr->cfield_mgr->design_values_to_db($_REQUEST,$args->tplan_id,$cf_map);

				if(isset($_SESSION['testPlanId']) && ($args->tplan_id == $_SESSION['testPlanId']))
					$_SESSION['testPlanName'] = $args->testplan_name;

				$status_ok = true;
				$template = null;
			}
		}
		else
		{
			$user_feedback = lang_get("warning_duplicate_tplan_name");
    }
    
		if(!$status_ok)
		{
			$smarty->assign('tplan_id',$args->tplan_id);
			$smarty->assign('tpName', $tpName);
			$smarty->assign('tpActive', $bActive);
			$smarty->assign('tproject_name', $args->tproject_name);
			$smarty->assign('notes', $of->CreateHTML());
		}
		break;

  case 'do_create':
		$template = 'planEdit.tpl';
		$status_ok = false;

		$of->Value = $args->notes;
		$tpName = $args->testplan_name;
		$bActive = ($args->active == 'on') ? 1 :0 ;
		if(!$name_exists)
		{
			$tplan_id = $tplan_mgr->create($args->testplan_name,$args->notes,$args->tproject_id);

			if ($tplan_id == 0)
			{
				$user_feedback = $db->error_msg();
			}
			else
			{
				logAuditEvent(TLS("audit_testplan_created",$args->tproject_name,$args->testplan_name),"CREATED",$tplan_id,"testplans");
				$cf_map = $tplan_mgr->get_linked_cfields_at_design($tplan_id);
				$tplan_mgr->cfield_mgr->design_values_to_db($_REQUEST,$tplan_id,$cf_map);

				$status_ok = true;
				$template = null;

				$user_feedback ='';

				if($args->rights == 'on')
					$result = insertTestPlanUserRight($db, $tplan_id,$args->user_id);

				if($args->copy)
				{
					$tplan_mgr->copy_as($args->source_tpid, $tplan_id,
					                    $args->testplan_name,$args->tproject_id,
					                    $args->copy_options,$args->tcversion_type);
				}
			}
		}
		else
		{
			$user_feedback = lang_get("warning_duplicate_tplan_name");
    }
    
		if(!$status_ok)
		{
			$smarty->assign('tplan_id',$args->tplan_id);
			$smarty->assign('tpName', $tpName);
			$smarty->assign('tpActive', $bActive);
			$smarty->assign('tproject_name', $args->tproject_name);
			$smarty->assign('notes', $of->CreateHTML());
		}
		break;
}

$smarty->assign('main_descr',$main_descr);
$smarty->assign('cf',$cf_smarty);
$smarty->assign('user_feedback',$user_feedback);
$smarty->assign('testplan_create', has_rights($db,"mgt_testplan_create"));
$smarty->assign('mgt_view_events',$_SESSION['currentUser']->hasRight($db,"mgt_view_events"));

switch($args->do_action)
{
   case "do_create":
   case "do_delete":
   case "do_update":
   case "list":
        $tplans = $tproject_mgr->get_all_testplans($args->tproject_id);

        $template = is_null($template) ? 'planView.tpl' : $template;
        $smarty->assign('tplans',$tplans);
        $smarty->display($templateCfg->template_dir . $template);
		break;

   case "edit":
   case "create":
        $template = is_null($template) ? 'planEdit.tpl' : $template;

        $smarty->assign('tplans',$tplans);
      	$smarty->assign('tplan_id',$args->tplan_id);
      	$smarty->assign('tpName', $tpName);
      	$smarty->assign('tpActive', $bActive);
      	$smarty->assign('tproject_name', $args->tproject_name);
      	$smarty->assign('notes', $of->CreateHTML());
        $smarty->display($templateCfg->template_dir . $template);
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
 *
 * 20060103 - fm
*/
function init_args($request_hash, $session_hash)
{
	$args = new stdClass();
	$request_hash = strings_stripSlashes($request_hash);

	$nullable_keys = array('testplan_name','notes','rights','active','do_action');
	foreach($nullable_keys as $value)
 	{
		$args->$value = isset($request_hash[$value]) ? trim($request_hash[$value]) : null;
	}

	$intval_keys = array('copy_from_tplan_id' => 0,'tplan_id' => 0);
	foreach($intval_keys as $key => $value)
	{
	$args->$key = isset($request_hash[$key]) ? intval($request_hash[$key]) : $value;
	}
	$args->source_tpid = $args->copy_from_tplan_id;
	$args->copy = ($args->copy_from_tplan_id > 0) ? TRUE : FALSE;

	$args->copy_options=array();
	$boolean_keys = array('copy_tcases' => 0,'copy_priorities' => 0,
                        'copy_milestones' => 0, 'copy_user_roles' => 0, 'copy_builds' => 0);

	foreach($boolean_keys as $key => $value)
	{
	$args->copy_options[$key]=isset($request_hash[$key]) ? 1 : 0;
	}

	$args->tcversion_type = isset($request_hash['tcversion_type']) ? $request_hash['tcversion_type'] : null;
	$args->tproject_id = $session_hash['testprojectID'];
	$args->tproject_name = $session_hash['testprojectName'];
	$args->user_id = $session_hash['userID'];

	return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_testplan_create');
}
?>