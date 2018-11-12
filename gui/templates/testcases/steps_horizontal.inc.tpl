{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_steps.tpl
            Shows the steps for a testcase in horizontal layout

@used-by inc_steps.tpl

@param $steps Array of the steps
@param $edit_enabled Steps links to edit page if true

@internal revisions
*}
  {if isset($add_exec_info) && $add_exec_info}
    {$inExec = 1}
  {else}
    {$inExec = 0}
  {/if}  

  <tr>
    <th width="40px"><nobr>
    {if $edit_enabled && $steps != '' && !is_null($steps) && $args_frozen_version=="no"}
      <img class="clickable" src="{$tlImages.reorder}" align="left"
           title="{$inc_steps_labels.show_hide_reorder}"
           onclick="showHideByClass('span','order_info');">
      <img class="clickable" src="{$tlImages.ghost_item}" align="left"
           title="{$inc_steps_labels.show_ghost_string}"
           onclick="showHideByClass('tr','ghost');">
    {/if}
    {$inc_steps_labels.step_number}
    </th>
    <th>{$inc_steps_labels.step_actions}
    </th>
    <th>{$inc_steps_labels.expected_results}</th>
    {if $session['testprojectOptions']->automationEnabled}
    <th width="25">{$inc_steps_labels.execution_type_short_descr}</th>
    {/if}
    {if $edit_enabled}
    <th>&nbsp;</th>
    <th>&nbsp;</th>
    {/if}

    {if $inExec}
      <th>{if $tlCfg->exec_cfg->steps_exec_notes_default == 'latest'}{$inc_steps_labels.latest_exec_notes}
          {else}{$inc_steps_labels.step_exec_notes}{/if}
          <img class="clickable" src="{$tlImages.clear_notes}" 
          onclick="javascript:clearTextAreaByClassName('step_note_textarea');" title="{$inc_steps_labels.clear_all_notes}"></th>

      <th>{$inc_steps_labels.step_exec_status}
       <img class="clickable" src="{$tlImages.reset}" 
          onclick="javascript:clearSelectByClassName('step_status');" title="{$inc_steps_labels.clear_all_status}"></th>
    {/if}    


  </tr>
  
  {$rowCount=$steps|@count} 
  {$row=0}

  {$att_ena = $inExec && 
              $tlCfg->exec_cfg->steps_exec_attachments}

  {foreach from=$steps item=step_info}
  <tr id="step_row_{$step_info.step_number}">
    <td style="text-align:left;">
      <span class="order_info" style='display:none'>
      {if $edit_enabled && $args_frozen_version=="no"}
        <input type="text" class="step_number{$args_testcase.id}" name="step_set[{$step_info.id}]" id="step_set_{$step_info.id}"
          value="{$step_info.step_number}"
          size="{#STEP_NUMBER_SIZE#}"
          maxlength="{#STEP_NUMBER_MAXLEN#}">
        {include file="error_icon.tpl" field="step_number"}
      {/if}
      </span>
      {$step_info.step_number}
    </td>
    <td {if $edit_enabled && $args_frozen_version=="no"} style="cursor:pointer;" onclick="launchEditStep({$step_info.id})" {/if}>{if $gui->stepDesignEditorType == 'none'}{$step_info.actions|nl2br}{else}{$step_info.actions}{/if}
    </td>
    <td {if $edit_enabled && $args_frozen_version=="no"} style="cursor:pointer;" onclick="launchEditStep({$step_info.id})" {/if}>{if $gui->stepDesignEditorType == 'none'}{$step_info.expected_results|nl2br}{else}{$step_info.expected_results}{/if}</td>
    {if $session['testprojectOptions']->automationEnabled}
    <td {if $edit_enabled && $args_frozen_version=="no"} style="cursor:pointer;" onclick="launchEditStep({$step_info.id})" {/if}>{$gui->execution_types[$step_info.execution_type]}</td>
    {/if}

    {if $edit_enabled && $args_frozen_version=="no"}
    <td class="clickable_icon">
      <img style="border:none;cursor: pointer;"
           title="{$inc_steps_labels.delete_step}"
           alt="{$inc_steps_labels.delete_step}"
           onclick="delete_confirmation({$step_info.id},'{$step_info.step_number|escape:'javascript'|escape}',
                                         '{$del_msgbox_title}','{$warning_msg}');"
           src="{$tlImages.delete}"/>
    </td>
    
    <td class="clickable_icon">
      <img style="border:none;cursor: pointer;"  title="{$inc_steps_labels.insert_step}"    
           alt="{$inc_steps_labels.insert_step}"
           onclick="launchInsertStep({$step_info.id});"    src="{$tlImages.insert_step}"/>
    </td>
    
    {/if}

    {if $inExec}
      <td class="exec_tcstep_note">
        <textarea class="step_note_textarea" name="step_notes[{$step_info.id}]" id="step_notes_{$step_info.id}" 
                  cols="40" rows="5">{$step_info.execution_notes|escape}</textarea>
      </td>

      <td>
        <select class="step_status" name="step_status[{$step_info.id}]" id="step_status_{$step_info.id}">
          {html_options options=$gui->execStepStatusValues selected=$step_info.execution_status}

        </select> <br>
        
        {if $gui->tlCanCreateIssue}
          {include file="execute/add_issue_on_step.inc.tpl" 
                   args_labels=$labels
                   args_step_id=$step_info.id}
        {/if}
      </td>

    {/if}
   
  </tr>
  {if $inExec && $gui->tlCanCreateIssue} 
    <tr>
      <td colspan=6>
      {include file="execute/issue_inputs_on_step.inc.tpl"
               args_labels=$labels
               args_step_id=$step_info.id}
      </td>
    </tr> 
  {/if}

  {if $gui->allowStepAttachments && $att_ena}
    <tr>
      <td colspan=6>
      {include file="attachments_simple.inc.tpl" attach_id=$step_info.id}
      </td>
    </tr> 
  {/if} 

  {if $ghost_control}
    <tr class='ghost' style='display:none'><td></td><td>{$step_info.ghost_action}</td><td>{$step_info.ghost_result}</td></tr>    
  {/if}

    {$rCount=$row+$step_info.step_number}
    {if ($rCount < $rowCount) && ($rowCount>=1)}
      <tr width="100%">
        {if $session['testprojectOptions']->automationEnabled}
        <td colspan=6>
        {else}
        <td colspan=5>
        {/if}
        <hr align="center" width="100%" color="grey" size="1">
        </td>
      </tr>
    {/if}

  {/foreach}