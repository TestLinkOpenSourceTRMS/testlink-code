{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_steps.tpl
            Shows the steps for a testcase in horizontal layout

@used-by inc_steps.tpl
@use issue_inputs_on_step.inc.tpl

@param $steps Array of the steps
@param $edit_enabled Steps links to edit page if true

*}
  {$inExec = 0}
  {if isset($add_exec_info) && $add_exec_info}
    {$inExec = 1}
  {/if}  

{* 
<script src="https://cdnjs.cloudflare.com/ajax/libs/TableDnD/0.9.1/jquery.tablednd.js" integrity="sha256-d3rtug+Hg1GZPB7Y/yTcRixO/wlI78+2m08tosoRn7A=" crossorigin="anonymous"></script>
*}
<script type="text/javascript" language="javascript" 
  src="{$basehref}node_modules/tablednd/js/jquery.tablednd.js">
</script>


<div class="workBack">
  <table class="simple" id="stepsOnTable">
  <tr class="nodrag">
    <th width="40px"><nobr>
    {if $edit_enabled && $steps != '' && !is_null($steps) && $args_frozen_version=="no"}
      <img class="clickable" src="{$tlImages.reorder}" align="left"
           title="{$inc_steps_labels.show_hide_reorder}"
           onclick="showHideByClass('span','order_info');">
      <img class="clickable" src="{$tlImages.ghost_item}" align="left"
           title="{$inc_steps_labels.show_ghost_string}"
           onclick="showHideByClass('span','ghost');">
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
  <tr id="step_row_{$step_info.id}" style="border: 1px solid white;">
    <td style="text-align:center;">
      <span class="order_info" style='display:none'>
      {if $edit_enabled && $args_frozen_version=="no"}
        <input type="text" class="step_number{$args_testcase.id}" name="step_set[{$step_info.id}]" id="step_set_{$step_info.id}"
          value="{$step_info.step_number}"
          size="{#STEP_NUMBER_SIZE#}"
          maxlength="{#STEP_NUMBER_MAXLEN#}">
        {include file="error_icon.tpl" field="step_number"}
      {/if}
      </span>
      <span id="tcstep_{$step_info.id}">{$step_info.step_number}</span>
      {$spanid="tcstep_ghost_{$step_info.id}"}
      {if $ghost_control}
        <span id="{$spanid}" 
              class="ghost" 
              style="display:none"
              title="{$inc_steps_labels.click_to_copy_ghost_to_clipboard}"
              onclick="copyGhostString('{$spanid}')">{$step_info.ghost_action}</span>    
      {/if}
    </td>
    <td title="{$inc_steps_labels.doubleclick_to_edit}" {if $edit_enabled && $args_frozen_version=="no"} style="cursor:pointer;" ondblclick="launchEditStep({$step_info.id})" {/if}>{if $gui->stepDesignEditorType == 'none'}{$step_info.actions|nl2br}{else}{$step_info.actions}{/if}
    </td>
    <td title="{$inc_steps_labels.doubleclick_to_edit}" {if $edit_enabled && $args_frozen_version=="no"} style="cursor:pointer;" ondblclick="launchEditStep({$step_info.id})" {/if}>{if $gui->stepDesignEditorType == 'none'}{$step_info.expected_results|nl2br}{else}{$step_info.expected_results}{/if}</td>
    {if $session['testprojectOptions']->automationEnabled}
    <td {if $edit_enabled && $args_frozen_version=="no"} style="cursor:pointer;" ondblclick="launchEditStep({$step_info.id})" {/if}>{$gui->execution_types[$step_info.execution_type]}</td>
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
        <textarea class="step_note_textarea" 
          name="step_notes[{$step_info.id}]" id="step_notes_{$step_info.id}" 
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
  {/foreach}
 </table>
</div>

<input type="hidden" name="stepSeq" id="stepSeq" value="">
<script type="text/javascript">
$(document).ready(function() {
    // Initialise the table
    $("#stepsOnTable").tableDnD({
      onDrop: function(table, row) {
          var xx = $.tableDnD.serialize()
                    .replace(/stepsOnTable/g,'')
                    .replace(/%5D/g,'')
                    .replace(/%5B/g,'')
                    .replace(/=/g,'')
                    .replace(/step_row_/g,'');
          $('#stepSeq').val(xx);

          // alert('Before jQuery AJAX');    
          url2call = fRoot+'lib/ajax/stepReorder.php';
          // alert(url2call);

          // -------------------------------------
          jQuery.ajax({
                  url: url2call,
                  data: {
                      'stepSeq': xx,
                  },
                  success:function(data) {
                    /* 
                     update screen
                    */
                    var parsec = JSON.parse(data);
                    for(var prop in parsec) {
                      jQuery("span#tcstep_" + prop).html(parsec[prop]);
                    } 
                    alert('Steps numbers have been re-sequenced'); 
                    // console.log(data);
                    // console.log('done');
                  },
                  error: function(){
                    console.log('FAILURE AJAX CALL -> ' + url2call);
                  }
              });  

          // alert('Use the Resequence Steps Button To Save');    
      }
    });
});
</script>