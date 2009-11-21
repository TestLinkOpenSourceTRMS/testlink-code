<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: projectEdit.php,v $
 *
 * @version $Revision: 1.37 $
 * @modified $Date: 2009/11/21 19:16:27 $ $Author: uid189872 $
 *
 * @author Martin Havlat
 *
 * Allows users to edit/delete test projetcs.
 *
 * @todo Verify dependency before delete testplan
 *
 * @internal revision
 * 20091121 - franciscom - BUGID - Julian Contribution
 * 20080827 - franciscom - BUGID 1692
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once("web_editor.php");
$editorCfg = getWebEditorCfg('testproject');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db,true,false,"checkRights");

$gui_cfg = config_get('gui');
$templateCfg = templateConfiguration();

$session_tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

$template = null;
$ui = new stdClass();

$ui->doActionValue = '';
$ui->buttonValue = '';
$ui->caption = '';
$ui->main_descr = lang_get('title_testproject_management');

$user_feedback = '';
$reloadType = 'none';

$tproject_mgr = new testproject($db);
$args = init_args($tproject_mgr, $_REQUEST, $session_tproject_id);
$of = web_editor('notes',$_SESSION['basehref'],$editorCfg) ;
// $of->Value = getItemTemplateContents('project_template', $of->name, $args->notes);

$found = 'yes';
$status_ok = 1;

switch($args->doAction)
{
    case 'create':
    	$template = $templateCfg->default_template;
      	$ui = create($args);
    	break;

    case 'edit':
    	$template = $templateCfg->default_template;
    	$ui=edit($args,$tproject_mgr);
    	break;

    case 'doCreate':
    	$op = doCreate($args,$tproject_mgr);
    	$template= $op->status_ok ?  null : $templateCfg->default_template;
    	$ui = $op->ui;
    	$status_ok = $op->status_ok;
    	$user_feedback = $op->msg;
    	break;

    case 'doUpdate':
    	$op = doUpdate($args,$tproject_mgr,$session_tproject_id);
    	$template= $op->status_ok ?  null : $templateCfg->default_template;
    	$ui = $op->ui;

    	$status_ok = $op->status_ok;
    	$user_feedback = $op->msg;
    	$reloadType = $op->reloadType;
      break;

    case 'doDelete':
      $op = doDelete($args,$tproject_mgr,$session_tproject_id);
    	$status_ok = $op->status_ok;
    	$user_feedback = $op->msg;
    	$reloadType = $op->reloadType;
      break;
}

$ui->main_descr = lang_get('title_testproject_management');
$smarty = new TLSmarty();
$smarty->assign('gui_cfg',$gui_cfg);
$smarty->assign('editorType',$editorCfg['type']);
$smarty->assign('canManage', has_rights($db,"mgt_modify_product"));
$smarty->assign('mgt_view_events',$_SESSION['currentUser']->hasRight($db,"mgt_view_events"));

if(!$status_ok)
{
   $args->doAction = "ErrorOnAction";
}

switch($args->doAction)
{
    case "doCreate":
    case "doDelete":
    case "doUpdate":
        $tprojects = $tproject_mgr->get_accessible_for_user($args->userID,'array_of_map',
                                                            " ORDER BY nodes_hierarchy.name ");

        $template= is_null($template) ? 'projectView.tpl' : $template;
        $smarty->assign('tprojects',$tprojects);
        $smarty->assign('doAction',$reloadType);
        $smarty->display($templateCfg->template_dir . $template);
    break;

    case "ErrorOnAction":
    default:
    	$of->Value = getItemTemplateContents('project_template', $of->InstanceName, $args->notes);

        foreach($ui as $prop => $value)
        {
            $smarty->assign($prop,$value);
        }
        $smarty->assign('user_feedback', $user_feedback);
        $smarty->assign('feedback_type', 'ultrasoft');

        $smarty->assign('gui', $args);
        $smarty->assign('id', $args->tprojectID);
        $smarty->assign('name', $args->tprojectName);
        $smarty->assign('active', $args->active);
        $smarty->assign('optReq', $args->optReq);
        $smarty->assign('optPriority', $args->optPriority);
        $smarty->assign('optAutomation', $args->optAutomation);
        $smarty->assign('tcasePrefix', $args->tcasePrefix);
        $smarty->assign('notes', $of->CreateHTML());
        $smarty->assign('found', $found);
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
 * rev:20080112 - franciscom -
 *     20070206 - franciscom - BUGID 617
*/
function init_args($tprojectMgr,$request_hash, $session_tproject_id)
{
    $args = new stdClass();
	$request_hash = strings_stripSlashes($request_hash);
	$nullable_keys = array('tprojectName','color','notes','doAction','tcasePrefix');
	foreach ($nullable_keys as $value)
	{
		$args->$value = isset($request_hash[$value]) ? trim($request_hash[$value]) : null;
	}

	// $intval_keys = array('optReq' => 0, 'tprojectID' => 0);
	$intval_keys = array('tprojectID' => 0);
	foreach ($intval_keys as $key => $value)
	{
		$args->$key = isset($request_hash[$key]) ? intval($request_hash[$key]) : $value;
	}

	$checkbox_keys = array('is_public' => 0,'active' => 0,'optReq' => 0,'optPriority' => 0,'optAutomation' => 0);
	foreach ($checkbox_keys as $key => $value)
	{
		$args->$key = isset($request_hash[$key]) ? 1 : $value;
	}

	// Special algorithm for notes
	// 20070206 - BUGID 617
	if($args->doAction != 'doUpdate' && $args->doAction != 'doCreate')
	{
		if ($args->tprojectID > 0)
		{
			$the_data = $tprojectMgr->get_by_id($args->tprojectID);
			$args->notes = $the_data['notes'];
			if ($args->doAction == 'doDelete')
				$args->tprojectName = $the_data['name'];
		}
		else
		{
			$args->notes = '';
		}
	}

	$args->userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;

	return $args;
}

/*
  function: doCreate

  args:

  returns:

*/
function doCreate($argsObj,&$tprojectMgr)
{
    $key2get=array('status_ok','msg');

    $op = new stdClass();
	$op->ui = new stdClass();

    $op->status_ok = 0;
    $op->template = null;
    $op->msg = '';
	$op->id = 0;

    $check_op = crossChecks($argsObj,$tprojectMgr);
    foreach($key2get as $key)
    {
        $op->$key=$check_op[$key];
    }

	  if($op->status_ok)
	  {
	    $options = new stdClass();
	    
	    $options->requirement_mgmt = $argsObj->optReq;
	    $options->priority_mgmt = $argsObj->optPriority;
	    $options->automated_execution = $argsObj->optAutomation;
	    	    
	  	$new_id = $tprojectMgr->create($argsObj->tprojectName,$argsObj->color,$options,
	  	                               $argsObj->notes,$argsObj->active,$argsObj->tcasePrefix,$argsObj->is_public);
	  								                 
	  	if (!$new_id)
	  	{
	  		$op->msg = lang_get('refer_to_log');
	  	}
	  	else
	  	{
	  		$op->template = 'projectView.tpl';
	  		$op->id = $new_id;
	  	}
	  }

	  if( $op->status_ok )
	  {
	      logAuditEvent(TLS("audit_testproject_created",$argsObj->tprojectName),"CREATE",$op->id,"testprojects");
	  }
		else
		{
			$op->ui->doActionValue = 'doCreate';
			$op->ui->buttonValue = lang_get('btn_create');
			$op->ui->caption = lang_get('caption_new_tproject');
		}

    return $op;
}

/*
  function: doUpdate

  args:

  returns:

*/
function doUpdate($argsObj,&$tprojectMgr,$sessionTprojectID)
{
    $key2get = array('status_ok','msg');

    $op = new stdClass();
    $op->ui = new stdClass();

    $op->status_ok = 0;
    $op->msg = '';
    $op->template = null;
    $op->reloadType = 'none';

    $oldObjData=$tprojectMgr->get_by_id($argsObj->tprojectID);
    $op->oldName=$oldObjData['name'];

    $check_op = crossChecks($argsObj,$tprojectMgr);
    foreach($key2get as $key)
    {
        $op->$key=$check_op[$key];
    }

	 if($op->status_ok)
	 {
        if( $tprojectMgr->update($argsObj->tprojectID,trim($argsObj->tprojectName),$argsObj->color,
        						 $argsObj->optReq, $argsObj->optPriority, $argsObj->optAutomation,
        						 $argsObj->notes, $argsObj->active,$argsObj->tcasePrefix,
        						 $argsObj->is_public) )
        {
        	$op->msg = '';
        	$tprojectMgr->activateTestProject($argsObj->tprojectID,$argsObj->active);
        	logAuditEvent(TLS("audit_testproject_saved",$argsObj->tprojectName),"UPDATE",$argsObj->tprojectID,"testprojects");
        }
        else
        {
        	$op->status_ok=0;
        }	
	}
    if($op->status_ok)
	{
		if($sessionTprojectID == $argsObj->tprojectID)
			$op->reloadType = 'reloadNavBar';
	}
	else
	{
    	$op->ui->doActionValue = 'doUpdate';
    	$op->ui->buttonValue = lang_get('btn_save');
    	$op->ui->caption = sprintf(lang_get('caption_edit_tproject'),$op->oldName);
	}

	  return $op;
}


/*
  function: edit
            initialize variables to launch user interface (smarty template)
            to get information to accomplish edit task.

  args:

  returns: -

*/
function edit(&$argsObj,&$tprojectMgr)
{
	$tprojectInfo = $tprojectMgr->get_by_id($argsObj->tprojectID);

	$argsObj->tprojectName = $tprojectInfo['name'];
	$argsObj->color = $tprojectInfo['color'];
	$argsObj->notes = $tprojectInfo['notes'];
	$argsObj->optReq = $tprojectInfo['option_reqs'];
	$argsObj->optPriority = $tprojectInfo['option_priority'];
	$argsObj->optAutomation = $tprojectInfo['option_automation'];
	$argsObj->active = $tprojectInfo['active'];
	$argsObj->tcasePrefix = $tprojectInfo['prefix'];
	$argsObj->is_public = $tprojectInfo['is_public'];


	$ui = new stdClass();
	$ui->main_descr=lang_get('title_testproject_management');
	$ui->doActionValue = 'doUpdate';
	$ui->buttonValue = lang_get('btn_save');
	$ui->caption = sprintf(lang_get('caption_edit_tproject'),$argsObj->tprojectName);
	return $ui;
}

/*
  function: crossChecks
            do checks that are common to create and update operations
            - name is valid ?
            - name already exists ?
            - prefix already exits ?
  args:

  returns: -

  rev: 20090606 - franciscom - minor refactoring
*/
function crossChecks($argsObj,&$tprojectMgr)
{
    $op = new stdClass();
    $updateAdditionalSQLFilter = null ;
    $op = $tprojectMgr->checkName($argsObj->tprojectName);

    $check_op = array();
    $check_op['msg'] = array();
    $check_op['status_ok'] = $op['status_ok'];

    if($argsObj->doAction == 'doUpdate')
    {
        $updateAdditionalSQLFilter = " testprojects.id <> {$argsObj->tprojectID}";
    }
    
    if($check_op['status_ok'])
    {
		    if($tprojectMgr->get_by_name($argsObj->tprojectName,$updateAdditionalSQLFilter))
		    {
		    	$check_op['msg'][] = sprintf(lang_get('error_product_name_duplicate'),$argsObj->tprojectName);
		    	$check_op['status_ok'] = 0;
		    }
            
            // Check prefix no matter what has happen with previous check
		    $rs = $tprojectMgr->get_by_prefix($argsObj->tcasePrefix,$updateAdditionalSQLFilter);
		    if(!is_null($rs))
		    {
		    	$check_op['msg'][] = sprintf(lang_get('error_tcase_prefix_exists'),$argsObj->tcasePrefix);
		    	$check_op['status_ok'] = 0;
		    }
    }
    else
    {
         $check_op['msg'][] = $op['msg'];
    }
    return $check_op;
}

/*
  function: create

  args :

  returns:

*/
function create(&$argsObj)
{
    $argsObj->active = 1;
    $argsObj->is_public = 1;

	$gui = new stdClass();
	$gui->doActionValue = 'doCreate';
	$gui->buttonValue = lang_get('btn_create');
	$gui->caption = lang_get('caption_new_tproject');

    return $gui;
}


/*
  function: doDelete

  args :

  returns:

*/
function doDelete($argsObj,&$tprojectMgr,$sessionTprojectID)
{

  	$ope_status = $tprojectMgr->delete($argsObj->tprojectID);
    $op = new stdClass();
	$op->status_ok = $ope_status['status_ok'];
	$op->reloadType = 'none';

	if ($ope_status['status_ok'])
	{
		if($sessionTprojectID == $argsObj->tprojectID)
			$op->reloadType = 'reloadNavBar';

		$op->msg = sprintf(lang_get('test_project_deleted'),$argsObj->tprojectName);
		logAuditEvent(TLS("audit_testproject_deleted",$argsObj->tprojectName),"DELETE",$argsObj->tprojectID,"testprojects");
	}
	else
	{
		$op->msg = lang_get('info_product_not_deleted_check_log') . ' ' . $ope_status['msg'];
	}

    return $op;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_modify_product');
}
?>