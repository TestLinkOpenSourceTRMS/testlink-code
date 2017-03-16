<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  buildEdit.php
 *
 *
 */
require('../../config.inc.php');
require_once("common.php");
require_once("web_editor.php");
$editorCfg = getWebEditorCfg('build');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();

$date_format_cfg = config_get('date_format');

$op = new stdClass();
$op->user_feedback = '';
$op->buttonCfg = new stdClass();
$op->buttonCfg->name = "";
$op->buttonCfg->value = "";

$smarty = new TLSmarty();
$tplan_mgr = new testplan($db);
$build_mgr = new build_mgr($db);

$args = init_args($_REQUEST,$_SESSION,$date_format_cfg,$tplan_mgr);
$gui = initializeGui($args,$build_mgr);


$of = web_editor('notes',$_SESSION['basehref'],$editorCfg);
$of->Value = getItemTemplateContents('build_template', $of->InstanceName, $args->notes);


$op = new stdClass();
$op->operation_descr = '';
$op->user_feedback = '';
$op->buttonCfg = '';
$op->status_ok = 1;

switch($args->do_action)
{
  case 'edit':
    $op = edit($args,$build_mgr,$date_format_cfg);
    $gui->closed_on_date = $args->closed_on_date;
    $of->Value = $op->notes;
  break;

  case 'create':
    $op = create($args);
    $gui->closed_on_date = $args->closed_on_date;
  break;

  case 'do_delete':
    $op = doDelete($db,$args,$build_mgr,$tplan_mgr);
  break;

  case 'do_update':
    $op = doUpdate($args,$build_mgr,$tplan_mgr,$date_format_cfg);
    $of->Value = $op->notes;
    $templateCfg->template = $op->template;
  break;

  case 'do_create':
    $op = doCreate($args,$build_mgr,$tplan_mgr,$date_format_cfg);
    $of->Value = $op->notes;
    $templateCfg->template = $op->template;
  break;

  case 'setActive':
    $build_mgr->setActive($args->build_id);
  break;

  case 'setInactive':
    $build_mgr->setInactive($args->build_id);
  break;

  case 'open':
    $build_mgr->setOpen($args->build_id);
  break;

  case 'close':
    $build_mgr->setClosed($args->build_id);
  break;

}

$dummy = null;
$gui->release_date = (isset($op->status_ok) && $op->status_ok && $args->release_date != "") ? 
                      localize_dateOrTimeStamp(null, $dummy, 'date_format',$args->release_date) : 
                      $args->release_date_original;
$gui->closed_on_date = $args->closed_on_date;
$gui->operation_descr = $op->operation_descr;
$gui->user_feedback = $op->user_feedback;
$gui->buttonCfg = $op->buttonCfg;

$gui->mgt_view_events = $args->user->hasRight($db,"mgt_view_events");
$gui->editorType = $editorCfg['type'];

renderGui($smarty,$args,$tplan_mgr,$templateCfg,$of,$gui);

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
 * @internal revisions
 *
 */
function init_args($request_hash, $session_hash,$date_format,&$tplanMgr)
{
  $args = new stdClass();
  $request_hash = strings_stripSlashes($request_hash);

  $nullable_keys = array('notes','do_action','build_name');
  foreach($nullable_keys as $value)
  {
    $args->$value = isset($request_hash[$value]) ? $request_hash[$value] : null;
  }

  $intval_keys = array('build_id' => 0, 'source_build_id' => 0);
  foreach($intval_keys as $key => $value)
  {
    $args->$key = isset($request_hash[$key]) ? intval($request_hash[$key]) : $value;
  }

  $bool_keys = array('is_active' => 0, 'is_open' => 0, 'copy_to_all_tplans' => 0,
                     'copy_tester_assignments' => 0);
  foreach($bool_keys as $key => $value)
  {
    $args->$key = isset($request_hash[$key]) ? 1 : $value;
  }

  // convert start date to iso format to write to db
  $args->release_date = null;
  if (isset($request_hash['release_date']) && $request_hash['release_date'] != '') 
  {
    $date_array = split_localized_date($request_hash['release_date'], $date_format);
    if ($date_array != null) 
    {
      // set date in iso format
      $args->release_date = $date_array['year'] . "-" . $date_array['month'] . "-" . $date_array['day'];
    }
  }
  
  $args->release_date_original = isset($request_hash['release_date']) && $request_hash['release_date'] != '' ?
                                   $request_hash['release_date'] : null;
    
  $args->closed_on_date = isset($request_hash['closed_on_date']) ? $request_hash['closed_on_date'] : null;
    

  if(isset($request_hash['tplan_id']) && intval($request_hash['tplan_id']) > 0)
  {
    $args->tplan_id = intval($_REQUEST['tplan_id']);
    $dp = $tplanMgr->get_by_id($args->tplan_id);
    $args->tplan_name = $dp['name'];
  } 
  else
  {
    $args->tplan_id = isset($session_hash['testplanID']) ? intval($session_hash['testplanID']) : 0;
    $args->tplan_name = isset($session_hash['testplanName']) ? $session_hash['testplanName']: '';
  }  

  $args->testprojectID = intval($session_hash['testprojectID']);
  $args->testprojectName = $session_hash['testprojectName'];
  $args->userID = intval($session_hash['userID']);

  $args->exec_status_filter = isset($request_hash['exec_status_filter']) ?
                                    $request_hash['exec_status_filter'] : null;

  $args->user = $_SESSION['currentUser'];
  return $args;
}

/**
 *
 *
 */
function initializeGui(&$argsObj,&$buildMgr)
{
  $guiObj = new stdClass();
  $guiObj->main_descr = lang_get('title_build_2') . config_get('gui_title_separator_2') . 
                        lang_get('test_plan') . config_get('gui_title_separator_1') . 
                        $argsObj->tplan_name;
  $guiObj->cfields = $buildMgr->html_custom_field_inputs($argsObj->build_id,$argsObj->testprojectID,
                                                         'design','',$_REQUEST);
  $dummy = config_get('results');
  foreach($dummy['status_label_for_exec_ui'] as $kv => $vl)
  {
    $guiObj->exec_status_filter['items'][$dummy['status_code'][$kv]] = lang_get($vl);  
  }  

  $guiObj->tplan_id = $argsObj->tplan_id;
  return $guiObj;
}


/*
  function: edit
            edit action
            
  args :

  returns:

*/
function edit(&$argsObj,&$buildMgr,$dateFormat)
{
  $binfo = $buildMgr->get_by_id($argsObj->build_id);
  $op = new stdClass();
  $op->buttonCfg = new stdClass();
  $op->buttonCfg->name = "do_update";
  $op->buttonCfg->value = lang_get('btn_save');
  $op->notes = $binfo['notes'];
  $op->user_feedback = '';
  $op->status_ok = 1;

  $argsObj->build_name = $binfo['name'];
  $argsObj->is_active = $binfo['active'];
  $argsObj->is_open = $binfo['is_open'];
  $argsObj->release_date = $binfo['release_date'];

  if( $binfo['closed_on_date'] == '')
  {
    $argsObj->closed_on_date = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
  }    
  else
  {    
    $datePieces = explode("-",$binfo['closed_on_date']);
    $argsObj->closed_on_date = mktime(0,0,0,$datePieces[1],$datePieces[2],$datePieces[0]);
  }
  
  $op->operation_descr=lang_get('title_build_edit') . TITLE_SEP_TYPE3 . $argsObj->build_name;

  return $op;
}

/*
  function: create
            prepares environment to manage user interaction on a create operations
 
  args: $argsObj: reference to input values received by page.

  returns: object with part of gui configuration

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
  $argsObj->closed_on_date = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));

  return $op;
}

/*
  function: doDelete

  args :

  returns:

*/
function doDelete(&$dbHandler,&$argsObj,&$buildMgr,&$tplanMgr)
{
  $op = new stdClass();
  $op->user_feedback = '';
  $op->operation_descr = '';
  $op->buttonCfg = null;

  $build = $buildMgr->get_by_id($argsObj->build_id);
  
  $qty = $tplanMgr->getExecCountOnBuild($argsObj->tplan_id,$argsObj->build_id);
  if($qty > 0 && !$argsObj->user->hasRight($dbHandler,'exec_delete'))
  {
    // Need to check if user has rigth to delete executions
    $op->user_feedback = sprintf(lang_get("cannot_delete_build_no_exec_delete"),$build['name']);
    return $op;
  }  

 
  if (!$buildMgr->delete($argsObj->build_id))
  {
    $op->user_feedback = lang_get("cannot_delete_build");
  }
  else
  {
    logAuditEvent(TLS("audit_build_deleted",$argsObj->testprojectName,$argsObj->tplan_name,$build['name']),
                  "DELETE",$argsObj->build_id,"builds");
  }
  return $op;
}

/*
  function:

  args :

  returns:

*/
function renderGui(&$smartyObj,&$argsObj,&$tplanMgr,$templateCfg,$owebeditor,&$guiObj)
{
    $doRender = false;
    switch($argsObj->do_action)
    {
      case "do_create":
      case "do_delete":
      case "do_update":
      case "setActive":
      case "setInactive":
      case "open":
      case "close":
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
      
      // Attention this is affected by changes in templates
      $guiObj->buildSet=$tplanMgr->get_builds($argsObj->tplan_id);

      $guiObj->enable_copy = ($argsObj->do_action == 'create' || $argsObj->do_action == 'do_create') ? 1 : 0;
      $guiObj->notes = $owebeditor->CreateHTML();
      $guiObj->source_build = init_source_build_selector($tplanMgr, $argsObj);

      $guiObj->tplan_name=$argsObj->tplan_name;
      $guiObj->build_id = $argsObj->build_id;
      $guiObj->build_name = $argsObj->build_name;
      $guiObj->is_active = $argsObj->is_active;
      $guiObj->is_open = $argsObj->is_open;
      $guiObj->copy_tester_assignments = $argsObj->copy_tester_assignments;

      
      $smartyObj->assign('gui',$guiObj);
      $smartyObj->display($templateCfg->template_dir . $tpl);
    }

}


/*
  function: doCreate

  args :

  returns:

  @internal revisions
*/
function doCreate(&$argsObj,&$buildMgr,&$tplanMgr,$dateFormat) //,&$smartyObj)
{
  $op = new stdClass();
  $op->operation_descr = '';
  $op->user_feedback = '';
  $op->template = "buildEdit.tpl";
  $op->notes = $argsObj->notes;
  $op->status_ok = 0;
  $op->buttonCfg = null;
  $check = crossChecks($argsObj,$tplanMgr,$dateFormat);
  $targetDate=null;
  if($check->status_ok)
  {
    $user_feedback = lang_get("cannot_add_build");
    $buildID = $buildMgr->create($argsObj->tplan_id,$argsObj->build_name,$argsObj->notes,
                                 $argsObj->is_active,$argsObj->is_open,$argsObj->release_date);
    if ($buildID)
    {
      $cf_map = $buildMgr->get_linked_cfields_at_design($buildID,$argsObj->testprojectID);
      $buildMgr->cfield_mgr->design_values_to_db($_REQUEST,$buildID,$cf_map,null,'build');

      if($argsObj->is_open == 1)
      {
        $targetDate=null;
      } 
      else
      {
        $targetDate=date("Y-m-d",$argsObj->closed_on_date);    
      }
      $buildMgr->setClosedOnDate($buildID,$targetDate);    
      
      if ($argsObj->copy_tester_assignments && $argsObj->source_build_id) 
      {
        if(!is_null($argsObj->exec_status_filter) && is_array($argsObj->exec_status_filter))
        {
          $buildSet[] = $argsObj->source_build_id;

          $execVerboseDomain = config_get('results');
          $execVerboseDomain = array_flip($execVerboseDomain['status_code']);

          // remember that assignment is done at platform + build
          $getOpt = array('outputFormat' => 'mapAccessByID' , 'addIfNull' => true,
                          'outputDetails' => 'name');
          $platformSet = $tplanMgr->getPlatforms($argsObj->tplan_id,$getOpt);
          
          $caOpt['keep_old_assignments'] = true;
          foreach($platformSet as $platform_id => $pname)
          {
            $glf['filters']['platform_id'] = $platform_id; 
            foreach($argsObj->exec_status_filter as $ec)
            {
              switch($execVerboseDomain[$ec])
              {
                case 'not_run':
                  $tcaseSet = $tplanMgr->getHitsNotRunForBuildAndPlatform($argsObj->tplan_id,
                                                                          $platform_id,
                                                                          $argsObj->source_build_id);
                  
                break;

                default:
                  $tcaseSet = $tplanMgr->getHitsSingleStatusFull($argsObj->tplan_id,
                                                                 $platform_id,
                                                                 $ec,$buildSet);
                break;            
              }
              if(!is_null($tcaseSet))
              {
                $targetSet = array_keys($tcaseSet);
                $features = $tplanMgr->getLinkedFeatures($argsObj->tplan_id,$glf['filters']);
                $caOpt['feature_set'] = null;
                foreach($targetSet as $tcase_id)
                {
                  $caOpt['feature_set'][] = 
                         $features[$tcase_id][$glf['filters']['platform_id']]['feature_id'];
                }  
                $tplanMgr->assignment_mgr->copy_assignments($argsObj->source_build_id, 
                                                            $buildID, $argsObj->userID,$caOpt);
              }  
              
            }  
          }  

        }  
        else
        {
          $tplanMgr->assignment_mgr->copy_assignments($argsObj->source_build_id, 
                                                      $buildID, $argsObj->userID);
        }  
      }
          
      $op->user_feedback = '';
      $op->notes = '';
      $op->template = null;
      $op->status_ok = 1;
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
function doUpdate(&$argsObj,&$buildMgr,&$tplanMgr,$dateFormat)
{
  $op = new stdClass();
  $op->operation_descr = '';
  $op->user_feedback = '';
  $op->template = "buildEdit.tpl";
  $op->notes = $argsObj->notes;
  $op->status_ok = 0;
  $op->buttonCfg = null;

  $oldObjData = $buildMgr->get_by_id($argsObj->build_id);
  $oldname = $oldObjData['name'];

  $check = crossChecks($argsObj,$tplanMgr,$dateFormat);
  if($check->status_ok)
  {
    $user_feedback = lang_get("cannot_update_build");
    if ($buildMgr->update($argsObj->build_id,$argsObj->build_name,$argsObj->notes,
                          $argsObj->is_active,$argsObj->is_open,$argsObj->release_date))
    {
      $cf_map = $buildMgr->get_linked_cfields_at_design($argsObj->build_id,$argsObj->testprojectID);
      $buildMgr->cfield_mgr->design_values_to_db($_REQUEST,$argsObj->build_id,$cf_map,null,'build');

      if( $argsObj->closed_on_date == '')
      {
        $argsObj->closed_on_date = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
      }
        
      if($argsObj->is_open == 1)
      {
        $targetDate=null;
      } 
      else
      {
        $targetDate=date("Y-m-d",$argsObj->closed_on_date);    
      }
      $buildMgr->setClosedOnDate($argsObj->build_id,$targetDate);    
 
        
      $op->user_feedback = '';
      $op->notes = '';
      $op->template = null;
      $op->status_ok = 1;
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

  @internal revision
  20100706 - franciscom - BUGID 3581    
*/
function crossChecks($argsObj,&$tplanMgr,$dateFormat)
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
  
  // check is date is valid
  if( $op->status_ok )
  {
    
    // BUGID 3716
    $rdate = trim($argsObj->release_date_original);
    
    // TODO: comment
    $date_array = split_localized_date($rdate,$dateFormat);

      if( $date_array != null )
      {
        $status_ok = checkdate($date_array['month'],$date_array['day'],$date_array['year']);
        $op->status_ok = $status_ok ? 1 : 0;
      } else {
        $op->status_ok = 0;
      }
      
      // release date is optional
      if ( $rdate == "") {
        $op->status_ok = 1;
      }
      
      if( $op->status_ok == 0 )
      {
        $op->user_feedback = lang_get("invalid_release_date");
    }
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

function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'testplan_create_build');
}

/**
 * Initialize the HTML select box for selection of a source build when
 * user wants to copy the user assignments on creation of a new build.
 * 
 * @author Andreas Simon
 * @param testplan $testplan_mgr reference to testplan manager object
 * @param object $argsObj reference to user input object
 * @return array $htmlMenu array structure with all information needed for the menu
 *
 * @internal revisions
 */
function init_source_build_selector(&$testplan_mgr, &$argsObj) 
{

  $htmlMenu = array('items' => null, 'selected' => null, 'build_count' => 0);
  $htmlMenu['items'] = $testplan_mgr->get_builds_for_html_options($argsObj->tplan_id,null,null,
                                                                  array('orderByDir' => 'id:DESC'));
  
  
  // get the number of existing execution assignments with each build
  if( !is_null($htmlMenu['items']) )
  {
    $htmlMenu['build_count'] = count($htmlMenu['items']);
    foreach ($htmlMenu['items'] as $key => $name) 
    {
      $count = $testplan_mgr->assignment_mgr->get_count_of_assignments_for_build_id($key);
      $htmlMenu['items'][$key] = $name . " (" . $count . ")"; 
    }
    
    // if no build has been chosen yet, select the newest build by default
    reset($htmlMenu['items']);
    if( !$argsObj->source_build_id )
    {
      $htmlMenu['selected'] = key($htmlMenu['items']);
    } 
  }   

  return $htmlMenu;
} // end of method
?>