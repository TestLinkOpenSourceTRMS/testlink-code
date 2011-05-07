<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * edit/delete test projetcs.
 *
 * @filesource	projectEdit.php
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2007-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * @todo Verify dependency before delete testplan
 *
 * @internal revision
 * 20110506 - franciscom - Refactoring for TABBED Browsing
 * 20101207 - franciscom - BUGID 3999: Test Project list does not refresh after deleted
 * 20100313 - franciscom - reduced interface 'width' with smarty
 * 20100217 - franciscom - fixed errors showed on event viewer due to missing properties
 * 20100119 - franciscom - BUGID 3048
 *
 */

require_once('../../config.inc.php');
require_once('common.php');
require_once("web_editor.php");
$editorCfg = getWebEditorCfg('testproject');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);
$args = init_args($tproject_mgr);
checkRights($db,$_SESSION['currentUser'],$args);

$gui = new stdClass();
$gui = $args;
$gui->tproject_id = $args->tprojectID;
$gui->canManage = $_SESSION['currentUser']->hasRight($db,"mgt_modify_product",$gui->tproject_id);
$gui->found = 'yes';

$ui = new stdClass();
$ui->doActionValue = '';
$ui->buttonValue = '';
$ui->caption = '';
$ui->main_descr = lang_get('title_testproject_management');
$user_feedback = '';

$of = web_editor('notes',$_SESSION['basehref'],$editorCfg) ;
$status_ok = 1;
$template = null;
switch($args->doAction)
{
    case 'create':
    	$template = $templateCfg->default_template;
      	$ui = create($args,$gui,$tproject_mgr,$gui);
    	break;

    case 'edit':
    	$template = $templateCfg->default_template;
    	$ui = edit($args,$gui,$tproject_mgr);
    	break;

    case 'doCreate':
    	$op = doCreate($args,$tproject_mgr);
    	$template= $op->status_ok ?  null : $templateCfg->default_template;
    	$ui = $op->ui;
    	$status_ok = $op->status_ok;
    	$user_feedback = $op->msg;
    	$gui->reloadType = $op->reloadType;
    	
    	if( $status_ok && $gui->contextTprojectID == 0)
    	{
    		// before this action there were ZERO test project on system 
    		// need to update context
    		$gui->contextTprojectID = $op->id;
    	}
    	break;

    case 'doUpdate':
    	$op = doUpdate($args,$tproject_mgr,$args->contextTprojectID);
    	$template= $op->status_ok ?  null : $templateCfg->default_template;
    	$ui = $op->ui;

    	$status_ok = $op->status_ok;
    	$user_feedback = $op->msg;
    	$gui->reloadType = $op->reloadType;
      break;

    case 'doDelete':
        $op = doDelete($args,$tproject_mgr,$args->contextTprojectID);
    	$status_ok = $op->status_ok;
    	$user_feedback = $op->msg;
    	$gui->reloadType = $op->reloadType;
    	$gui->contextTprojectID = $op->contextTprojectID;
      break;
}

$ui->main_descr = lang_get('title_testproject_management');
$smarty = new TLSmarty();
$smarty->assign('gui_cfg',config_get('gui'));
$smarty->assign('editorType',$editorCfg['type']);
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
        $gui->tprojects = getTprojectSet($tproject_mgr,$args->userID);

        // Context Need to be updated using first test project on set
        $gui->contextTprojectID = $gui->tprojects[0]['id'];
        $template= is_null($template) ? 'projectView.tpl' : $template;
        $smarty->assign('gui',$gui);
        $smarty->display($templateCfg->template_dir . $template);
    break;


    case "ErrorOnAction":
    default:
        if( $args->doAction != "edit")
        {
    		$of->Value = getItemTemplateContents('project_template', $of->InstanceName, $args->notes);
        }
        else
        {
        	$of->Value = $args->notes;
        }
        foreach($ui as $prop => $value)
        {
            $smarty->assign($prop,$value);
        }
        $smarty->assign('gui', $args);
        $smarty->assign('notes', $of->CreateHTML());
        $smarty->assign('user_feedback', $user_feedback);
        $smarty->assign('feedback_type', 'ultrasoft');
        $smarty->display($templateCfg->template_dir . $template);
    break;

}

/**
 * INITialize page ARGuments, using the $_REQUEST and $_SESSION
 * super-global hashes.
 * Important: changes in HTML input elements on the Smarty template
 *            must be reflected here.
 *
 * @return singleton object with html values tranformed and other
 *                   generated variables.
 * @internal revisions
 */
function init_args(&$tprojectMgr)
{
    $args = new stdClass();
	$_REQUEST = strings_stripSlashes($_REQUEST);
	$nullable_keys = array('tprojectName','color','notes','doAction','tcasePrefix');
	foreach ($nullable_keys as $value)
	{
		$args->$value = isset($_REQUEST[$value]) ? trim($_REQUEST[$value]) : null;
	}

	$intval_keys = array('tprojectID' => 0, 'contextTprojectID' => 0, 'copy_from_tproject_id' => 0);
	foreach ($intval_keys as $key => $value)
	{
		$args->$key = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : $value;
	}

	// get input from the project edit/create page
	$checkbox_keys = array(	'is_public' => 0,'active' => 0,'optReq' => 0,
							'optPriority' => 0,'optAutomation' => 0,'optInventory' => 0);
	foreach ($checkbox_keys as $key => $value)
	{
		$args->$key = isset($_REQUEST[$key]) ? 1 : $value;
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
			{
				$args->tprojectName = $the_data['name'];
			}	
		}
		else
		{
			$args->notes = '';
		}
	}

	$args->userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
	$args->testprojects = null;
	$args->projectOptions = prepareOptions($args);
	return $args;
}

/**
 * Collect a test project options (input from form) to a singleton
 * 
 * @param array $argsObj the page input
 * @return singleton data to be stored
 */
function prepareOptions($argsObj)
{
	  	$options = new stdClass();
	  	$options->requirementsEnabled = $argsObj->optReq;
	  	$options->testPriorityEnabled = $argsObj->optPriority;
	  	$options->automationEnabled = $argsObj->optAutomation;
	  	$options->inventoryEnabled = $argsObj->optInventory;

	  	return $options;
}

function doCreate($argsObj,&$tprojectMgr)
{
	$key2get=array('status_ok','msg');
	
	$op = new stdClass();
	$op->ui = new stdClass();
	
	$op->status_ok = 0;
	$op->template = null;
	$op->msg = '';
	$op->id = 0;
    $op->reloadType = 'none';
	
	$check_op = crossChecks($argsObj,$tprojectMgr);
	foreach($key2get as $key)
	{
	    $op->$key=$check_op[$key];
	}

	if($op->status_ok)
	{
	  	$options = prepareOptions($argsObj);
	  	    
		$new_id = $tprojectMgr->create($argsObj->tprojectName, $argsObj->color,
									   $options, $argsObj->notes, $argsObj->active, $argsObj->tcasePrefix,
									   $argsObj->is_public);
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
    	$op->reloadType = 'reloadNavBar';
    	
		if($argsObj->copy_from_tproject_id > 0)
		{
			$options = array('copy_requirements' => $argsObj->optReq);
			$tprojectMgr->copy_as($argsObj->copy_from_tproject_id,$new_id,
			                      $argsObj->userID,trim($argsObj->tprojectName),$options);
		}
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
function doUpdate($argsObj,&$tprojectMgr,$contextTprojectID)
{
	
    $key2get = array('status_ok','msg');

    $op = new stdClass();
    $op->ui = new stdClass();

    $op->status_ok = 0;
    $op->msg = '';
    $op->template = null;
    $op->reloadType = 'none';

    $oldObjData = $tprojectMgr->get_by_id($argsObj->tprojectID);
    $op->oldName = $oldObjData['name'];

    $check_op = crossChecks($argsObj,$tprojectMgr);
    foreach($key2get as $key)
    {
        $op->$key=$check_op[$key];
    }

	 if($op->status_ok)
	 {
	  	$options = prepareOptions($argsObj);
        if( $tprojectMgr->update($argsObj->tprojectID,trim($argsObj->tprojectName),
        						 $argsObj->color, $argsObj->notes, $options, $argsObj->active,
        						 $argsObj->tcasePrefix, $argsObj->is_public) )
        {
        	$op->msg = '';
        	$tprojectMgr->activate($argsObj->tprojectID,$argsObj->active);
        	logAuditEvent(TLS("audit_testproject_saved",$argsObj->tprojectName),"UPDATE",$argsObj->tprojectID,"testprojects");
        }
        else
        {
        	$op->status_ok=0;
        }	
	}
    if($op->status_ok)
	{
		if($contextTprojectID == $argsObj->tprojectID)
		{
			$op->reloadType = 'reloadNavBar';
		}	
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
function edit(&$argsObj,&$guiObj,&$tprojectMgr)
{
	$tprojectInfo = $tprojectMgr->get_by_id($argsObj->tprojectID);
   
	$argsObj->tprojectName = $tprojectInfo['name'];
	$argsObj->color = $tprojectInfo['color'];
	$argsObj->notes = $tprojectInfo['notes'];
	$argsObj->projectOptions = $tprojectInfo['opt'];
	$argsObj->active = $tprojectInfo['active'];
	$argsObj->tcasePrefix = $tprojectInfo['prefix'];
	$argsObj->is_public = $tprojectInfo['is_public'];

	$guiObj->reloadType = 'none';


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
function create(&$argsObj,&$guiObj,&$tprojectMgr)
{
	$uiObj = new stdClass();
	$uiObj->doActionValue = 'doCreate';
	$uiObj->buttonValue = lang_get('btn_create');
	$uiObj->caption = lang_get('caption_new_tproject');
	$uiObj->testprojects = $tprojectMgr->get_all(null,array('access_key' => 'id'));

	// update by refence
    $argsObj->active = 1;
    $argsObj->is_public = 1;
	$guiObj->testprojects = $uiObj->testprojects;
	$guiObj->reloadType = 'none';

    return $uiObj;
}


/*
  function: doDelete

  args :

  returns:

*/
function doDelete($argsObj,&$tprojectMgr,$contextTprojectID)
{

  	$ope_status = $tprojectMgr->delete($argsObj->tprojectID);
    $op = new stdClass();
	$op->status_ok = $ope_status['status_ok'];
	$op->reloadType = 'none';
	$op->contextTprojectID = $contextTprojectID;
	$op->runUpdateLogic = 0;
	
	if ($ope_status['status_ok'])
	{
		$op->reloadType = 'reloadNavBar';

		// if we have deleted test project currently selected on test project Combo (Context)  
		// on NavBar we need:
		// a) reload NavBar in order to update the Combo
		// b) need to update Project View template memory of Context, this info is present
		//    on each URL on listing
		if( intval($contextTprojectID) == intval($argsObj->tprojectID) )
		{
			$op->runUpdateLogic = 1;
			// need to get test project set available AFTER delete
        	$tprojectSet = getTprojectSet($tprojectMgr,$argsObj->userID);
        	if( !is_null($tprojectSet) )
        	{
        		$op->contextTprojectID = key($tprojectSet);
        	}
		}	
		
		$op->msg = sprintf(lang_get('test_project_deleted'),$argsObj->tprojectName);
		logAuditEvent(TLS("audit_testproject_deleted",$argsObj->tprojectName),"DELETE",$argsObj->tprojectID,"testprojects");
	}
	else
	{
		$op->msg = lang_get('info_product_not_deleted_check_log') . ' ' . $ope_status['msg'];
	}

    return $op;
}


/**
 * helper
 *
 */
function getTprojectSet(&$tprojectMgr,$userID)
{
	$items = $tprojectMgr->get_accessible_for_user($userID,'array_of_map');
	return $items;
}



/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	// Test Projects are System Wide items, for this reason for this feature
	// check must be done on Global Rights => those that belong to role assigned to user 
	// when user was created (Global/Default Role) => enviroment is ignored.
	// To instruct method to ignore enviromente, we need to set enviroment but with INEXISTENT ID 
	// (best option is negative value)
	$env['tproject_id'] = -1;
	$env['tplan_id'] = -1;
	checkSecurityClearance($db,$userObj,$env,array('mgt_modify_product'),'and');
}
?>