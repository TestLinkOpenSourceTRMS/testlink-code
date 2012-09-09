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
 * @copyright 	2007-2012, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * @todo Verify dependency before delete testplan
 *
 * @internal revisions
 * @since 2.0
 *
 */

require_once('../../config.inc.php');
require_once('common.php');
require_once("web_editor.php");
require_once("form_api.php");

$editorCfg = getWebEditorCfg('testproject');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db);
$templateCfg = templateConfiguration();
list($tprojectMgr,$args,$gui,$ui) = initializeEnv($db);
$gui->editorType = $editorCfg['type'];

checkRights($db,$_SESSION['currentUser'],$args);  // is failed script execution will be aborted

$cmdMgr = new projectCommands($db,$_SESSION['currentUser']);
$cmdMgr->setTemplateCfg(templateConfiguration());



$of = web_editor('notes',$_SESSION['basehref'],$editorCfg) ;
$status_ok = 1;
$template = null;
$doRender = false;
switch(($pfn = $args->doAction))
{
    case 'create':
        $op = $cmdMgr->$pfn($args,$gui);
        $doRender = true;
    	break;

    case 'edit':
    	$template = $templateCfg->default_template;
    	$ui = edit($args,$gui,$tprojectMgr);
    	break;

    case 'doCreate':
    	$op = doCreate($args,$tprojectMgr);
    	$template= $op->status_ok ?  null : $templateCfg->default_template;
    	$ui = $op->ui;
    	$status_ok = $op->status_ok;
    	$gui->user_feedback = $op->msg;
    	$gui->reloadType = $op->reloadType;
    	
    	if( $status_ok && $gui->contextTprojectID == 0)
    	{
    		// before this action there were ZERO test project on system 
    		// need to update context
    		$gui->contextTprojectID = $op->id;
    	}
    	break;

    case 'doUpdate':
    	$op = doUpdate($args,$tprojectMgr,$args->contextTprojectID);
    	$template= $op->status_ok ?  null : $templateCfg->default_template;
    	$ui = $op->ui;

    	$status_ok = $op->status_ok;
    	$gui->user_feedback = $op->msg;
    	$gui->reloadType = $op->reloadType;
      break;

    case 'doDelete':
        $op = doDelete($args,$tprojectMgr,$args->contextTprojectID);
    	$status_ok = $op->status_ok;
    	$gui->user_feedback = $op->msg;
    	$gui->reloadType = $op->reloadType;
    	$gui->contextTprojectID = $op->contextTprojectID;
      break;
}


if( $doRender )
{
	$cmdMgr->renderGui($args,$gui,$op);
	exit();
}



$smarty = new TLSmarty();

if(!$status_ok)
{
   $args->doAction = "ErrorOnAction";
}

switch($args->doAction)
{
    case "doCreate":
    case "doDelete":
    case "doUpdate":
        $gui->tprojects = getTprojectSet($tprojectMgr,$args->userID);

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

        // HERE WE NEED REWORK - 20120908
        foreach($ui as $prop => $value)
        {
            $gui->$prop = $value;
        }
        $gui->notes = $of->CreateHTML();
        $smarty->assign('gui', $args);
        $smarty->display($templateCfg->template_dir . $template);
    break;

}


/**
 * initialize page ENVironment
 *
 * @return array
 * @internal revisions
 */

function initializeEnv(&$dbHandler)
{
	$tprojectMgr = new testproject($dbHandler);
	$argsObj = init_args($tprojectMgr);

	// Gui
	$guiObj = $argsObj;
	$guiObj->canManage = $guiObj->user->hasRight($dbHandler,"mgt_modify_product");
	$guiObj->mgt_view_events = $guiObj->user->hasRight($dbHandler,"mgt_view_events");
	$guiObj->found = 'yes';
	$guiObj->cfg = config_get('gui');
	$guiObj->user_feedback = '';
	$guiObj->feedback_type = 'ultrasoft';
	$guiObj->main_descr = lang_get('title_testproject_management');

	
	$itMgr = new tlIssueTracker($dbHandler);
	$guiObj->issueTrackers = $itMgr->getAll();
	unset($itMgr);

	// UI
	$uiObj = new stdClass();
	$uiObj->doActionValue = $uiObj->buttonValue = $uiObj->caption = '';


	return array($tprojectMgr,$argsObj,$guiObj,$uiObj);
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
	$args->user = $_SESSION['currentUser'];
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