{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource execTCaseHeader.inc.tpl
*}

    {$theClass = "exec_tc_title"}
    {$hasNewestVersionMsg = ''}
    {if $gui->hasNewestVersion}
      {$theClass = "exec_tc_title_alert"}
      {$hasNewestVersionMsg = $labels.hasNewestVersionMsg}
    {/if}
    <div class="{$theClass}">
      {if '' !== $hasNewestVersionMsg}  
        <div style="text-align: center;">{$hasNewestVersionMsg}</div>
        {if $gui->hasNewestVersion} 
          <div style="text-align: center;">
            <input type="hidden" id="TCVToUpdate" name="TCVToUpdate"
              value="{$gui->tcversionSet}">
            <input type="submit" id="linkLatestVersion" name="linkLatestVersion"
                     value="{$labels.updateLinkToLatestTCVersion}"/>
          </div>     
          <br>    
        {/if}
      {/if}
      {if $gui->grants->edit_testcase}
        {$tplan=$gui->tplan_id}
        {$metaMode="editOnExec&tplan_id=$tplan"}                    
        <a href="javascript:openTCaseWindow({$tc_exec.testcase_id},{$tc_exec.id},'{$metaMode}')">
        <img src="{$tlImages.note_edit}"  title="{$labels.show_tcase_spec}">
        </a>
      {/if}
    
      {$labels.title_test_case}&nbsp;{$gui->tcasePrefix|escape}{$cfg->testcase_cfg->glue_character}{$tc_exec.tc_external_id|escape} :: {$labels.version}: {$tc_exec.version} :: {$tc_exec.name|escape}
      <br />

      {$cfdtime = $gui->design_time_cfields}
      {$cfdtime = $cfdtime[$tc_exec.testcase_id]}
      {if $cfdtime.after_title neq ''}
        <div style="padding: 15px 3px 4px 5px;">
          <div class="custom_field_container">
            {$cfdtime.after_title}
          </div>
        </div>  
      {/if}

      <div style="padding-left: 5px;">
        <br>
        <b>{$labels.estimated_execution_duration}{$smarty.const.TITLE_SEP}</b>
          {$tc_exec.estimated_exec_duration}
      </div>

      <div style="padding-left: 5px;">
        <b>{$labels.execution_type}{$smarty.const.TITLE_SEP}</b>
           {$gui->execution_types[$tc_exec.execution_type]}
      </div>


      <div>
        <br>
        {if $tc_exec.assigned_user == ''}
          <img src="{$tlImages.warning}" style="border:none" />&nbsp;{$labels.has_no_assignment}
        {else}
            <img src="{$tlImages.user}" style="border:none" />&nbsp;
            {$labels.assigned_to}{$title_sep}{$tc_exec.assigned_user|escape}
        {/if}
      </div>



    </div>
