{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource attributesLinearForViewer.inc.tpl
*}

<p>
<fieldset>
<legend></legend>
<form style="display:inline;" id="statusForm" name="statusForm" id="statusForm" 
      method="post" action="lib/testcases/tcEdit.php">
  <input type="hidden" name="doAction" id="doAction" value="setStatus">
  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
  <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />

  <span class="labelHolder">{$tcView_viewer_labels.status}{$smarty.const.TITLE_SEP}</span>
  {if $edit_enabled}
  <select name="status" id="status" onchange="document.getElementById('statusForm').submit();">
    {html_options options=$gui->domainTCStatus selected=$args_testcase.status}
  </select>
  {else}
    {$gui->domainTCStatus[$args_testcase.status]}
  {/if}
</form>



{if $session['testprojectOptions']->testPriorityEnabled}
   <form style="display:inline;" id="importanceForm" name="importanceForm" method="post" 
         action="lib/testcases/tcEdit.php">

    <input type="hidden" name="doAction" id="doAction" value="setImportance">
    <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
    <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
    
  <span class="labelHolder" style="margin-left:20px;">{$tcView_viewer_labels.importance}{$smarty.const.TITLE_SEP}</span>
    {if $edit_enabled}
    <select name="importance" onchange="document.getElementById('importanceForm').submit();" >
          {html_options options=$gsmarty_option_importance selected=$args_testcase.importance}
    </select>
    {else}
      {$gsmarty_option_importance[$args_testcase.importance]}
    {/if}
   </form>
{/if}


{if $session['testprojectOptions']->automationEnabled}
<form style="display:inline;" id="execTypeForm" name="execTypeForm" method="post" 
      action="lib/testcases/tcEdit.php">
    <input type="hidden" name="doAction" id="doAction" value="setExecutionType">
    <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
    <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
  <span class="labelHolder" style="margin-left:20px;">{$tcView_viewer_labels.execution_type}{$smarty.const.TITLE_SEP}</span>
  {if $edit_enabled}
    <select name="exec_type" onchange="document.getElementById('execTypeForm').submit();" >
      {html_options options=$gui->execution_types selected=$args_testcase.execution_type}
    </select>
  {else}
    {$gui->execution_types[$args_testcase.execution_type]}
  {/if}
</form>
{/if}

<form style="display:inline;" id="estimatedExecDurationForm" name="estimatedExecDurationForm" method="post"
      action="lib/testcases/tcEdit.php">
  <input type="hidden" name="doAction" id="doAction" value="setEstimatedExecDuration">
  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
  <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />

  <span class="labelHolder" title="{$tcView_viewer_labels.estimated_execution_duration}"
        style="margin-left:20px;">{$tcView_viewer_labels.estimated_execution_duration_short}{$smarty.const.TITLE_SEP}</span>

  {if $edit_enabled}
  <span>
  <input type="text" name="estimated_execution_duration" id="estimated_execution_duration"
       size="{#EXEC_DURATION_SIZE#}" maxlength="{#EXEC_DURATION_MAXLEN#}"
       title="{$tcView_viewer_labels.estimated_execution_duration}" 
       value={$args_testcase.estimated_exec_duration}>
  <input type="submit" name="setEstimated" value="{$tcView_viewer_labels.btn_save}" />
  </span>
  {else}
    {$args_testcase.estimated_exec_duration}
  {/if}

</form>
</fieldset>
