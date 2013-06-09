{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource attributes.inc.tpl
*}

{if $session['testprojectOptions']->automationEnabled}
  <div class="labelHolder">{$labels.execution_type}
  <select name="exec_type" onchange="content_modified = true">
      {html_options options=$gui->execution_types selected=$gui->tc.execution_type}
    </select>
  </div>
  {/if}

  {if $session['testprojectOptions']->testPriorityEnabled}
    <div>
  <span class="labelHolder">{$labels.importance}</span>
  <select name="importance" onchange="content_modified = true">
      {html_options options=$gsmarty_option_importance selected=$gui->tc.importance}
    </select>
  </div>
{/if}

  <div>
<span class="labelHolder">{$labels.status}</span>
<select name="tc_status" id="tc_status" 
    onchange="content_modified = true">
{html_options options=$gui->domainTCStatus selected=$gui->tc.status}
</select>
</div>
<div>
<span class="labelHolder">{$labels.estimated_execution_duration}</span>
<input type="text" name="estimated_execution_duration" id="estimated_execution_duration"
     size="{#EXEC_DURATION_SIZE#}" maxlength="{#EXEC_DURATION_MAXLEN#}"
     title="{$labels.estimated_execution_duration}" 
     value={$gui->tc.estimated_exec_duration}>
</div>
