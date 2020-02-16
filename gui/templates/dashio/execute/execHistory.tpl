{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filsesource execHistory.tpl
*}  
{lang_get var='labels' 
          s='title_test_case,th_test_case_id,version,date_time_run,platform,test_exec_by,
             exec_status,testcaseversion,attachment_mgmt,deleted_user,build,testplan,
             execution_type_manual,execution_type_auto,run_mode,exec_notes,
             edit_execution,display_only_active_test_plans,access_test_steps_exec'}


{include file="inc_head.tpl" openHead='yes'}
{include file="inc_ext_js.tpl"}

{*  Initialize note panels. 
  The array panel_init_functions is filled with init functions (see below)
  or inc_exec_show_tc_exec.tpl and executed from onReady below *}
<script>
panel_init_functions = new Array();
Ext.onReady(function() {
  for(var gdx=0; gdx<panel_init_functions.length;gdx++) {
    panel_init_functions[gdx]();
  }
});

function load_notes(panel,exec_id)
{
  var url2load=fRoot+'lib/execute/getExecNotes.php?readonly=1&exec_id=' + exec_id;
  panel.load({ url:url2load });
}
</script>
</head>

{$attachment_model=$gui->exec_cfg->att_model}
{$my_colspan=$attachment_model->num_cols+2}
{$printExecutionAction="lib/execute/execPrint.php"}

<body onUnload="storeWindowSize('execHistoryPopup')">
{if $gui->main_descr != ''}
<h1>{$gui->main_descr|escape}<br></h1>
<h1>{$gui->detailed_descr|escape}<br></h1>
{/if}
<div class="workBack">
  {if $gui->warning_msg == ''}

    <form name="execHistory" id="execHistory" action="lib/execute/execHistory.php">
      <input type="hidden" name="tcase_id" id="tcase_id" value="{$gui->tcase_id}">
      {$labels.display_only_active_test_plans}
      <input type="checkbox"
             id="onlyActiveTestPlans" name="onlyActiveTestPlans"
             {if $gui->onlyActiveTestPlans} checked {/if}
             style="font-size: 90%;" onclick="this.form.submit()"/>
    </form>

    <table cellspacing="0" class="exec_history">
      <tr>
        <th style="text-align:left">{$labels.date_time_run}</th>
        <th style="text-align:left">{$labels.testplan}</th>
        <th style="text-align:left">{$labels.build}</th>
        {if $gui->displayPlatformCol}
          {assign var="my_colspan" value=$my_colspan+1}
          <th style="text-align:left">{$labels.platform}</th>
        {/if}
        <th style="text-align:left">{$labels.test_exec_by}</th>
        <th style="text-align:center">{$labels.exec_status}</th>
        <th style="text-align:center">{$labels.testcaseversion}</th>
        <th style="text-align:left"><nobr>{$labels.run_mode}</nobr></th>
        {if $tlCfg->exec_cfg->steps_exec }
        <th style="text-align:left"><nobr>&nbsp;</nobr></th>
        {/if}
      </tr>
    
      {* Table data *}
      {foreach item=tcv_exec_set from=$gui->execSet}
        {foreach item=tcv_exec from=$tcv_exec_set}
          {cycle values='#eeeeee,#d0d0d0' assign="bg_color"}
          <tr style="border-top:1px solid black; background-color: {$bg_color}">
            <td>
              {if $gui->grants->exec_edit_notes[$tcv_exec.testplan_id]}
                <img src="{$smarty.const.TL_THEME_IMG_DIR}/note_edit.png" style="vertical-align:middle" 
                     title="{$labels.edit_execution}" onclick="javascript: openExecEditWindow(
                     {$tcv_exec.execution_id},{$tcv_exec.id},{$tcv_exec.testplan_id},{$gui->tproject_id});">
              {/if}
              {localize_timestamp ts=$tcv_exec.execution_ts}
            </td>
            <td>{$tcv_exec.testplan_name|escape}</td>
            <td>
            {*
            {if !$tcv_exec.build_is_open}
              <img src="{$smarty.const.TL_THEME_IMG_DIR}/lock.png" title="{$labels.closed_build}">
            {/if}
            *}
            {$tcv_exec.build_name|escape}
            </td>
            {if $gui->displayPlatformCol}<td>{$tcv_exec.platform_name}</td>{/if}
            <td title="{$tcv_exec.tester_first_name|escape} {$tcv_exec.tester_last_name|escape}">
            {$tcv_exec.tester_login|escape}
            </td>
            {assign var="tc_status_code" value=$tcv_exec.status}
            <td class="{$tlCfg->results.code_status.$tc_status_code}" style="text-align:center">
                {localize_tc_status s=$tcv_exec.status}
            </td>
            <td  style="text-align:center">{$tcv_exec.tcversion_number}</td>
    
            <td class="icon_cell" align="center">
            {if $tcv_exec.execution_run_type == $smarty.const.TESTCASE_EXECUTION_TYPE_MANUAL}
            <img src="{$smarty.const.TL_THEME_IMG_DIR}/user.png" title="{$labels.execution_type_manual}"
                 style="border:none" />
            {else}
            <img src="{$smarty.const.TL_THEME_IMG_DIR}/bullet_wrench.png" title="{$labels.execution_type_auto}"
                 style="border:none" />
            {/if}
            </td>
          {if $tlCfg->exec_cfg->steps_exec }
            <td class="icon_cell" align="center">
              <img src="{$tlImages.steps}" title="{$labels.access_test_steps_exec}"  
                   onclick="javascript:openPrintPreview('exec',{$tcv_exec.execution_id},
                                                        null,null,'{$printExecutionAction}');"/>
            </td>
          {/if}


          </tr>
    
    
          {if $tcv_exec.execution_notes != ""}
            <script>
          {* -------------------------------------------------------------------------- 
            Initialize panel if notes exists. 
            There might be multiple note panels
            visible at the same time, so we need to collect those init functions in
            an array and execute them from Ext.onReady(). 
            See execSetResults.tpl 
            --------------------------------------------------------------------------
          *}
          {literal}
              var panel_init = function(){
                var p = new Ext.Panel({
                    title: {/literal}'{$labels.exec_notes}'{literal},
                    collapsible:true, collapsed: true, baseCls: 'x-tl-panel',
                    renderTo: {/literal}'exec_notes_container_{$tcv_exec.execution_id}'{literal},
                    width:'100%',html:''
                });
                p.on({'expand' : function(){load_notes(this,{/literal}{$tcv_exec.execution_id}{literal});}});
              };
              panel_init_functions.push(panel_init);
              {/literal}
            </script>
          <tr style="background-color: {$bg_color}">
              <td colspan="{$my_colspan}" id="exec_notes_container_{$tcv_exec.execution_id}"
                  style="padding:5px 5px 5px 5px;">
              </td>
            </tr>
          {/if}
    
          <tr style="background-color: {$bg_color}">
          <td colspan="{$my_colspan}">
            {if isset($gui->cfexec[$tcv_exec.execution_id])}
              {assign var="cf_value_info" value=$gui->cfexec[$tcv_exec.execution_id]}
              {$cf_value_info}
            {/if} 
          </td>
          </tr>
    
          {* Attachments *}
          <tr style="background-color: {$bg_color}">
            <td colspan="{$my_colspan}">
            {if isset($gui->attachments[$tcv_exec.execution_id])}
              {assign var="attach_info" value=$gui->attachments[$tcv_exec.execution_id]}
              {include file="inc_attachments.tpl"
                       attach_attachmentInfos=$attach_info
                       attach_id=$tcv_exec.execution_id
                       attach_tableName="executions"
                       attach_show_upload_btn=$attachment_model->show_upload_btn
                       attach_show_title=$attachment_model->show_title
                       attach_downloadOnly=1 
                       attach_tableClassName=null
                       attach_inheritStyle=0
                       attach_tableStyles=null}
            {/if}
          </td>
          </tr>
    
    
          
          {if isset($gui->bugs[$tcv_exec.execution_id])}
            <tr style="background-color: {$bg_color}">
            <td colspan="{$my_colspan}">
              {include file="inc_show_bug_table.tpl"
              bugs_map=$gui->bugs[$tcv_exec.execution_id] can_delete=0 exec_id=$tcv_exec.execution_id}
            </td>
          </tr>
          {/if}
        {/foreach}
      {/foreach}
    
    
    </table>
  {else}
    <br />
    <div class="user_feedback">
    {$gui->warning_msg}
    </div>
  {/if}
</div>
</body>
</html>
