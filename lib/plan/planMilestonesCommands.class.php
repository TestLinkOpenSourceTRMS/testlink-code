<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource planMilestonesCommands.class.php
 * @author Francisco Mancardi
 * 
 * @internal revisions
 */
require_once("testplan.class.php");  // needed because milestone_mgr is inside
class planMilestonesCommands
{
  private $db;
  private $milestone_mgr;
  private $defaultTemplate='planMilestonesEdit.tpl';
  private $submit_button_label;
  private $auditContext;
  private $viewAction = 'lib/plan/planMilestonesView.php';
  
  function __construct(&$db)
  {
      $this->db = $db;
      $this->milestone_mgr = new milestone_mgr($db);
    $this->submit_button_label = lang_get('btn_save');
  }

  function setAuditContext($auditContext)
  {
      $this->auditContext = $auditContext;
  }

  /*
    function: create

    args:
    
    returns: 

  */
  function create(&$argsObj)
  {
      $guiObj = new stdClass();
    $guiObj->main_descr = lang_get('testplan') . TITLE_SEP;
    $guiObj->action_descr = lang_get('create_milestone');
    $guiObj->template = $this->defaultTemplate;
    $guiObj->submit_button_label = $this->submit_button_label;
    $guiObj->milestone = array('id' => 0, 'name' => '', 'target_date' => '', 
                               'start_date' => '',
                               'high_percentage' => '', 'medium_percentage' => '', 
                               'low_percentage' => '', 
                               'testplan_id' => $argsObj->tplan_id,
                               'testplan_name' => $argsObj->tplan_name,);
    return $guiObj; 
  }

  /*
    function: edit

    args:
    
    returns: 

  */
  function edit(&$argsObj)
  {
    $guiObj = new stdClass();
    $dummy = $this->milestone_mgr->get_by_id($argsObj->id);
    $guiObj->milestone = $dummy[$argsObj->id];
      
    // $dummyy necessary because localize_dateOrTimeStamp wants second parameter to be passed by reference
    $dummy = null;
    
    // localize target date (is always set on edit)
    $guiObj->milestone['target_date'] = localize_dateOrTimeStamp(null, $dummy, 'date_format',$guiObj->milestone['target_date']);
      
    // as start date is optional it can be "0000-00-00" (default timestamp)
    if ($guiObj->milestone['start_date'] != "0000-00-00") 
    {
      $guiObj->milestone['start_date'] = localize_dateOrTimeStamp(null, $dummy, 'date_format',$guiObj->milestone['start_date']);
    } 
    else 
    {
      $guiObj->milestone['start_date'] = "";
    }
      
    $guiObj->main_descr = lang_get('testplan') . TITLE_SEP;
    $guiObj->action_descr = sprintf(lang_get('edit_milestone'),$guiObj->milestone['name']);
    $guiObj->template = $this->defaultTemplate;
    $guiObj->submit_button_label = $this->submit_button_label;
    return $guiObj; 
  }


  /*
    function: doCreate

    args:
    
    returns: 

  */
  function doCreate(&$argsObj,$basehref)
  {
    $date_format_cfg = config_get('date_format');
    $guiObj = new stdClass();
    $guiObj->main_descr = lang_get('Milestone') . TITLE_SEP;
    $guiObj->action_descr = lang_get('create_milestone');
    $guiObj->submit_button_label=$this->submit_button_label;
    $guiObj->template = null;
        $op_ok = 1;

        // Check name do not exists
        $name_exists = $this->milestone_mgr->check_name_existence($argsObj->tplan_id,$argsObj->name);
    if($name_exists)
    {
      $guiObj->user_feedback = sprintf(lang_get('milestone_name_already_exists'),$argsObj->name);
            $op_ok=0;
        }

        // BUGID 3716
        // are the dates valid?
        if ($op_ok) {
          // start date is optional
          $op_ok = is_valid_date($argsObj->target_date_original, $date_format_cfg) && 
                   ($argsObj->start_date_original == '' || is_valid_date($argsObj->start_date_original, $date_format_cfg));
          if (!$op_ok) {
            $guiObj->user_feedback = sprintf(lang_get('warning_invalid_date'));
          }
        }
        
        // check target date 
    if($op_ok)
    {
          $timestamp=array();
      $timestamp['target'] = strtotime($argsObj->target_date . " 23:59:59");
      $timestamp['now'] = strtotime("now");
          
      if( $timestamp['target'] < $timestamp['now'] )
      {
        $op_ok=0;
        $guiObj->user_feedback = lang_get('warning_milestone_date');
            }
        }
        
        // BUGID 3829 - check target date > start date
        if($op_ok && isset($argsObj->start_date)) {
          $timestamp['target'] = strtotime($argsObj->target_date . " 23:59:59");
          $timestamp['start'] = strtotime($argsObj->start_date . " 23:59:59");
          
          // target must be chronologically after start
          if( $timestamp['target'] < $timestamp['start'] )
      {
        $op_ok=0;
        $guiObj->user_feedback = lang_get('warning_target_before_start');
            }
        }

    if($op_ok)
    {
      // avoid warning on event viewer
      if (!isset($argsObj->start_date)) {
        $argsObj->start_date = "";
      }
      /*
          $argsObj->id = $this->milestone_mgr->create($argsObj->tplan_id,$argsObj->name,
                                                      $argsObj->target_date,$argsObj->start_date,
                                                      $argsObj->low_priority_tcases,
                                                      $argsObj->medium_priority_tcases,
                                                      $argsObj->high_priority_tcases);
          */
            $argsObj->low_priority = $argsObj->low_priority_tcases;
          $argsObj->medium_priority = $argsObj->medium_priority_tcases;
          $argsObj->high_priority = $argsObj->high_priority_tcases;
         
          $argsObj->id = $this->milestone_mgr->create($argsObj);

          $guiObj->user_feedback = 'ok';
          if($argsObj->id > 0)
          {
            logAuditEvent(TLS("audit_milestone_created",$argsObj->tplan_name,$argsObj->name),
                          "CREATE",$argsObj->id,"milestones");
            $guiObj->user_feedback = sprintf(lang_get('milestone_created'), $argsObj->name);
              $guiObj->template = $basehref . $this->viewAction;
          }
    }    
    return $guiObj; 
  }


  /*
    function: doUpdate

    args:
    
    returns: 

  */
  function doUpdate(&$argsObj,$basehref)
  {
    $date_format_cfg = config_get('date_format');
    $obj=new stdClass();
    $descr_prefix = lang_get('Milestone') . TITLE_SEP;
    $obj=$this->edit($argsObj);
    $obj->user_feedback = 'ok';
    $obj->template = null;
    $dummy = $this->milestone_mgr->get_by_id($argsObj->id);
    $originalMilestone = $dummy[$argsObj->id];

    $op_ok=1;

    // Check name do not exists
    $name_exists = $this->milestone_mgr->check_name_existence($originalMilestone['testplan_id'],
    $argsObj->name,$argsObj->id);
    if($name_exists)
    {
      $obj->user_feedback = sprintf(lang_get('milestone_name_already_exists'),$argsObj->name);
      $op_ok=0;
    }

    // BUGID 3716
    // are the dates valid?
    if ($op_ok) {
      // start date is optional
      $op_ok = is_valid_date($argsObj->target_date_original, $date_format_cfg) &&
      ($argsObj->start_date_original == '' || is_valid_date($argsObj->start_date_original, $date_format_cfg));
      
      if (!$op_ok) {
        $obj->user_feedback = lang_get('warning_invalid_date');
      }
    }
    
    // target date changed ?
    if($op_ok)
    {
      $timestamp=array();
      $timestamp['target'] = strtotime($argsObj->target_date ." 23:59:59");
      $timestamp['original_target'] = strtotime($originalMilestone['target_date'] ." 23:59:59");
      $timestamp['now'] = strtotime("now");

      if( ($timestamp['target'] != $timestamp['original_target']) && $timestamp['target'] < $timestamp['now'] )
      {
        $op_ok=0;
        $obj->user_feedback = lang_get('warning_milestone_date');
      }
    }
    
      // BUGID 3829 - check target date > start date
        if($op_ok && isset($argsObj->start_date)) {
          $timestamp['target'] = strtotime($argsObj->target_date . " 23:59:59");
          $timestamp['start'] = strtotime($argsObj->start_date . " 23:59:59");
          
          // target must be chronologically after start
          if( $timestamp['target'] < $timestamp['start'] )
      {
        $op_ok=0;
        $obj->user_feedback = lang_get('warning_target_before_start');
            }
        }

    if($op_ok)
    {
      // BUGID 3907 - start date is optional -> if empty set to default date
      if (!isset($argsObj->start_date) || $argsObj->start_date == "") {
        $argsObj->start_date = "0000-00-00";
      }
      
      $op_ok = $this->milestone_mgr->update($argsObj->id,$argsObj->name,$argsObj->target_date,
               $argsObj->start_date,$argsObj->low_priority_tcases,$argsObj->medium_priority_tcases,
               $argsObj->high_priority_tcases);
    }
    if($op_ok)
    {
      $obj->main_descr = '';
      $obj->action_descr='';
      $obj->template = "planMilestonesView.php";
      logAuditEvent(TLS("audit_milestone_saved",$argsObj->tplan_name,$argsObj->name),
                          "SAVE",$argsObj->id,"milestones");
    }
    else
    {
      // Action has failed => no change done on DB.
      $obj->main_descr = $descr_prefix . $originalMilestone['name'];
    }
    
    return $obj;
  }


  /*
    function: doDelete

    args:
    
    returns: object with info useful to manage user interface

  */
  function doDelete(&$argsObj,$basehref)
  {
    $dummy = $this->milestone_mgr->get_by_id($argsObj->id);
      $milestone = $dummy[$argsObj->id];

    $this->milestone_mgr->delete($argsObj->id);
    logAuditEvent(TLS("audit_milestone_deleted",$milestone['testplan_name'],$milestone['name']),
                  "DELETE",$argsObj->id,"milestones");
  
    $obj = new stdClass();
    $obj->template = $basehref . $this->viewAction;
    $obj->user_feedback = sprintf(lang_get('milestone_deleted'),$milestone['name']);
    $obj->main_descr = null;
    $obj->title = lang_get('delete_milestone');
    
    return $obj;
  }
}
?>