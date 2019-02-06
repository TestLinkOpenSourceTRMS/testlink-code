{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource attributesLinearForViewer.inc.tpl
*}

<p>
<fieldset>
<legend></legend>
<form style="display:inline;" 
      id="statusForm_{$args_testcase.id}" name="statusForm_{$args_testcase.id}"  
      method="post" action="{$basehref}lib/testcases/tcEdit.php">
  <input type="hidden" name="doAction" id="doAction" value="setStatus">
  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
  <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />

  <span class="labelHolder" title="{$tcView_viewer_labels.onchange_save}">
  {$tcView_viewer_labels.status}{$smarty.const.TITLE_SEP}</span>
  {if $edit_enabled && $args_testcase.is_open}
  <select name="status" id="status" onchange="document.getElementById('statusForm_{$args_testcase.id}').submit();">
    {html_options options=$gui->domainTCStatus selected=$args_testcase.status}
  </select>
  {else}
    {$gui->domainTCStatus[$args_testcase.status]}
  {/if}
</form>

{if $session['testprojectOptions']->testPriorityEnabled}
   <form style="display:inline;" id="importanceForm_{$args_testcase.id}" 
         name="importanceForm_{$args_testcase.id}" method="post" 
         action="{$basehref}lib/testcases/tcEdit.php">

    <input type="hidden" name="doAction" id="doAction" value="setImportance">
    <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
    <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
    
  <span class="labelHolder" title="{$tcView_viewer_labels.onchange_save}"
        style="margin-left:20px;">{$tcView_viewer_labels.importance}{$smarty.const.TITLE_SEP}</span>
    {if $edit_enabled && $args_testcase.is_open}
    <select name="importance" onchange="document.getElementById('importanceForm_{$args_testcase.id}').submit();" >
          {html_options options=$gsmarty_option_importance selected=$args_testcase.importance}
    </select>
    {else}
      {$gsmarty_option_importance[$args_testcase.importance]}
    {/if}
   </form>
{/if}


{if $session['testprojectOptions']->automationEnabled}
<form style="display:inline;" id="execTypeForm_{$args_testcase.id}" 
      name="execTypeForm_{$args_testcase.id}" method="post" action="{$basehref}lib/testcases/tcEdit.php">
    <input type="hidden" name="doAction" id="doAction" value="setExecutionType">
    <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
    <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
  <span class="labelHolder" title="{$tcView_viewer_labels.onchange_save}" 
        style="margin-left:20px;">{$tcView_viewer_labels.execution_type}{$smarty.const.TITLE_SEP}</span>
  {if $edit_enabled && $args_testcase.is_open}
    <select name="exec_type" onchange="document.getElementById('execTypeForm_{$args_testcase.id}').submit();" >
      {html_options options=$gui->execution_types selected=$args_testcase.execution_type}
    </select>
    <input name="changeExecTypeOnSteps" type="checkbox">{$tcView_viewer_labels.applyExecTypeChangeToAllSteps}
  {else}
    {$gui->execution_types[$args_testcase.execution_type]}
  {/if}
</form>
{/if}

<br>
<form style="display:inline;" id="estimatedExecDurationForm_{$args_testcase.id}" 
      name="estimatedExecDurationForm_{$args_testcase.id}" method="post"
      action="{$basehref}lib/testcases/tcEdit.php">
  <input type="hidden" name="doAction" id="doAction" value="setEstimatedExecDuration">
  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
  <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />

  <span class="labelHolder" title="{$tcView_viewer_labels.estimated_execution_duration}"
        style="margin-left:20px;">{$tcView_viewer_labels.estimated_execution_duration_short}{$smarty.const.TITLE_SEP}</span>

  {if $edit_enabled && $args_testcase.is_open}
  <span>
  <input type="text" name="estimated_execution_duration" id="estimated_execution_duration"
       size="{#EXEC_DURATION_SIZE#}" maxlength="{#EXEC_DURATION_MAXLEN#}"
       title="{$tcView_viewer_labels.estimated_execution_duration}" 
       value="{$args_testcase.estimated_exec_duration}" {$tlCfg->testcase_cfg->estimated_execution_duration->required}>
  <input type="submit" name="setEstimated" value="{$tcView_viewer_labels.btn_save}" />
  </span>
  {else}
    {$args_testcase.estimated_exec_duration}
  {/if}

</form>
</fieldset>