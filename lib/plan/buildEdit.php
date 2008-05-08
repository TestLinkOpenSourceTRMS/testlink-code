<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: buildEdit.php,v $
 *
 * @version $Revision: 1.13 $
 * @modified $Date: 2008/05/08 21:06:42 $ $Author: schlundus $
 *
 * rev :
 *      20080217 - franciscom - refactoring
 *      20080213 - franciscom - fixed bad template names
 *
*/
require('../../config.inc.php');
require_once("common.php");
require_once("builds.inc.php");
require_once("web_editor.php");

testlinkInitPage($db);

$templateCfg = new stdClass();
$templateCfg->template_dir = 'plan/';
$templateCfg->default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));
$templateCfg->template = null;

$op = new stdClass();
$op->user_feedback = '';
$op->buttonCfg = new stdClass();
$op->buttonCfg->name = "";
$op->buttonCfg->value = "";

$smarty = new TLSmarty();
$tplan_mgr = new testplan($db);
$build_mgr = new build_mgr($db);

$args = init_args($_REQUEST,$_SESSION);
$of = web_editor('notes',$_SESSION['basehref']);
$of->Value = null;

$main_descr = lang_get('title_build_2') . TITLE_SEP_TYPE3 . lang_get('test_plan') . TITLE_SEP . $args->tplan_name;

switch($args->do_action)
{
	case 'edit':
	  	$op = edit($args,$build_mgr);
		$of->Value = $op->notes;
		break;

	case 'create':
	  	$op = create($args);
		break;

	case 'do_delete':
	  	$op = doDelete($args,$build_mgr);
		break;

	case 'do_update':
	  	$op = doUpdate($args,$build_mgr,$tplan_mgr);
		$of->Value = $op->notes;
		$templateCfg->template = $op->template;
		break;

	case 'do_create':
	  	$op = doCreate($args,$build_mgr,$tplan_mgr);
		$of->Value = $op->notes;
		$templateCfg->template = $op->template;
		break;

}


$smarty->assign('main_descr',$main_descr);
$smarty->assign('operation_descr',$op->operation_descr);
$smarty->assign('user_feedback',$op->user_feedback);
$smarty->assign('buttonCfg',$op->buttonCfg);
$smarty->assign('testplan_create', has_rights($db,"mgt_testplan_create"));
$smarty->assign('mgt_view_events',$_SESSION['currentUser']->hasRight($db,"mgt_view_events"));
renderGui($smarty,$args,$tplan_mgr,$templateCfg,$of);


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
	$args = new stdClass();
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

	$bool_keys = array('is_active' => 0,'is_open' => 0, 'copy_to_all_tplans' => 0);
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


/*
  function:

  args :

  returns:

*/
function edit(&$argsObj,&$buildMgr)
{
	$binfo = $buildMgr->get_by_id($argsObj->build_id);
	$op = new stdClass();
	$op->buttonCfg = new stdClass();
	$op->buttonCfg->name = "do_update";
	$op->buttonCfg->value = lang_get('btn_save');
	$op->notes = $binfo['notes'];
	$op->user_feedback = '';

	$argsObj->build_name = $binfo['name'];
	$argsObj->is_active = $binfo['active'];
	$argsObj->is_open = $binfo['is_open'];
	$op->operation_descr=lang_get('title_build_edit') . TITLE_SEP_TYPE3 . $argsObj->build_name;

    return $op;
}

/*
  function:

  args :

  returns:

*/
function create(&$argsObj)
{
	$op = new stdClass();
    $op->operation_descr = lang_get('title_build_create');
	$op->buttonCfg = new stdClass();
	$op->buttonCfg->name = "do_create";
	$op->buttonCfg->value = lang_get('btn_create');
	$op->user_feedback = '';
	$argsObj->is_active = 1;
	$argsObj->is_open = 1;

    return $op;
}

/*
  function: doDelete

  args :

  returns:

*/
function doDelete(&$argsObj,&$buildMgr)
{
	$op = new stdClass();
    $op->user_feedback = '';
    $op->operation_descr = '';
    $op->buttonCfg = null;

	$build = $buildMgr->get_by_id($argsObj->build_id);
	if (!$buildMgr->delete($argsObj->build_id))
		$op->user_feedback = lang_get("cannot_delete_build");
	else
		logAuditEvent(TLS("audit_build_deleted",$argsObj->testprojectName,$argsObj->tplan_name,$build['name']),
		              "DELETE",$argsObj->build_id,"builds");

    return $op;
}

/*
  function:

  args :

  returns:

*/
function renderGui(&$smartyObj,&$argsObj,&$tplanMgr,$templateCfg,$owebeditor)
{
    $doRender = false;
    switch($argsObj->do_action)
    {
    	case "do_create":
    	case "do_delete":
    	case "do_update":
       	$doRender = true;
    		$tpl = is_null($templateCfg->template) ? 'buildView.tpl' : $templateCfg->template;
    		break;

    	case "edit":
    	case "create":
        $doRender = true;
    		$tpl = is_null($templateCfg->template) ? $templateCfg->default_template : $templateCfg->template;
    		break;
    }

    if($doRender)
    {
      	$enable_copy = ($argsObj->do_action == 'create' || $argsObj->do_action == 'do_create') ? 1 : 0;

		$my_builds = $tplanMgr->get_builds($argsObj->tplan_id);
   		$smartyObj->assign('the_builds',$my_builds);
    	$smartyObj->assign('build_id',$argsObj->build_id);
    	$smartyObj->assign('build_name', $argsObj->build_name);
    	$smartyObj->assign('is_active', $argsObj->is_active);
    	$smartyObj->assign('is_open', $argsObj->is_open);
    	$smartyObj->assign('notes', $owebeditor->CreateHTML());
    	$smartyObj->assign('enable_copy', $enable_copy);

  		$smartyObj->display($templateCfg->template_dir . $tpl);
    }

}


/*
  function: doCreate

  args :

  returns:

*/
function doCreate(&$argsObj,&$buildMgr,&$tplanMgr) //,&$smartyObj)
{
	$op = new stdClass();
	$op->operation_descr = '';
	$op->user_feedback = '';
	$op->template = "buildEdit.tpl";
	$op->notes = $argsObj->notes;
	$op->status_ok = false;
	$op->buttonCfg = null;

	$check = crossChecks($argsObj,$tplanMgr);

	if($check->status_ok)
	{
		$user_feedback = lang_get("cannot_add_build");
		$buildID = $buildMgr->create($argsObj->tplan_id,$argsObj->build_name,
	       				$argsObj->notes,$argsObj->is_active,$argsObj->is_open);
		if ($buildID)
		{
			$op->user_feedback = '';
			$op->notes = '';
			$op->template = null;
			$op->status_ok = true;
			logAuditEvent(TLS("audit_build_created",$argsObj->testprojectName,$argsObj->tplan_name,$argsObj->build_name),
							"CREATE",$buildID,"builds");
		}
	}
	if(!$op->status_ok)
	{
		$op->buttonCfg = new stdClass();
		$op->buttonCfg->name = "do_create";
		$op->buttonCfg->value = lang_get('btn_create');
		$op->user_feedback = $check->user_feedback;
	}
	elseif($argsObj->copy_to_all_tplans)
	{
		doCopyToTestPlans($argsObj,$buildMgr,$tplanMgr);
	}
	return $op;
}


/*
  function: doUpdate

  args :

  returns:

*/
function doUpdate(&$argsObj,&$buildMgr,&$tplanMgr)
{
	$op = new stdClass();
	$op->operation_descr = '';
	$op->user_feedback = '';
	$op->template = "buildEdit.tpl";
	$op->notes = $argsObj->notes;
	$op->status_ok = false;
	$op->buttonCfg = null;

    $oldObjData = $buildMgr->get_by_id($argsObj->build_id);
    $oldname = $oldObjData['name'];

	$check = crossChecks($argsObj,$tplanMgr);
	if($check->status_ok)
	{
		$user_feedback = lang_get("cannot_update_build");
		if ($buildMgr->update($argsObj->build_id,$argsObj->build_name,
		                   $argsObj->notes,$argsObj->is_active,$argsObj->is_open))
		{
			$op->user_feedback = '';
			$op->notes = '';
			$op->template = null;
			$op->status_ok = true;
			logAuditEvent(TLS("audit_build_saved",$argsObj->testprojectName,$argsObj->tplan_name,$argsObj->build_name),
			              "SAVE",$argsObj->build_id,"builds");
		}
	}

	if(!$op->status_ok)
	{
		$op->operation_descr = lang_get('title_build_edit') . TITLE_SEP_TYPE3 . $oldname;
		$op->buttonCfg = new stdClass();
		$op->buttonCfg->name = "do_update";
		$op->buttonCfg->value = lang_get('btn_save');
		$op->user_feedback = $check->user_feedback;
	}
	return $op;
}

/*
  function: crossChecks
            do checks that are common to create and update operations
            - name already exists in this testplan?
  args:

  returns: -

*/
function crossChecks($argsObj,&$tplanMgr)
{
	$op = new stdClass();
	$op->user_feedback = '';
	$op->status_ok = 1;
	$buildID = ($argsObj->do_action == 'do_update') ? $argsObj->build_id : null;
	if( $tplanMgr->check_build_name_existence($argsObj->tplan_id,$argsObj->build_name,$buildID) )
	{
	    $op->user_feedback = lang_get("warning_duplicate_build") . TITLE_SEP_TYPE3 . $argsObj->build_name;
	    $op->status_ok = 0;
	}
	return $op;
}

/*
  function: doCopyToTestPlans
            copy do checks that are common to create and update operations
            - name already exists in this testplan?
  args:

  returns: -

*/
function doCopyToTestPlans(&$argsObj,&$buildMgr,&$tplanMgr)
{
    $tprojectMgr = new testproject($tplanMgr->db);

    // exclude this testplan
    $filters = array('tplan2exclude' => $argsObj->tplan_id);
    $tplanset = $tprojectMgr->get_all_testplans($argsObj->testprojectID,$filters);

    if(!is_null($tplanset))
    {
        foreach($tplanset as $id => $info)
        {
            if(!$tplanMgr->check_build_name_existence($id,$argsObj->build_name))
            {
                $buildMgr->create($id,$argsObj->build_name,$argsObj->notes,
                                  $argsObj->is_active,$argsObj->is_open);
            }
        }
    }
}
?>
