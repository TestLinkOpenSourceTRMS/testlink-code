{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource tcView_viewer.tpl
viewer for test case in test specification

@internal revisions
*}
{lang_get var="tcView_viewer_labels"
          s="requirement_spec,Requirements,tcversion_is_inactive_msg,
             btn_edit,btn_delete,btn_mv_cp,btn_del_this_version,btn_new_version,
             btn_export,btn_execute_automatic_testcase,version,testplan_usage,
             testproject,testsuite,title_test_case,summary,steps,btn_add_to_testplans,
             title_last_mod,title_created,by,expected_results,keywords,goto_execute,
             btn_create_step,step_number,btn_reorder_steps,step_actions,hint_new_sibling,
             execution_type_short_descr,delete_step,show_hide_reorder,btn_new_sibling,
             test_plan,platform,insert_step,btn_print,btn_print_view,hint_new_version,
             execution_type,test_importance,none,preconditions,btn_compare_versions,
             requirement,btn_show_exec_history,btn_resequence_steps,link_unlink_requirements"}

{lang_get s='warning_delete_step' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{* will be useful in future to semplify changes *}
{assign var="tableColspan" value=$gui->tableColspan} 
{assign var="addInfoDivStyle" value='style="padding: 5px 3px 4px 10px;"'}


{assign var="module" value='lib/testcases/'}
{assign var="tcase_id" value=$args_testcase.testcase_id}
{assign var="tcversion_id" value=$args_testcase.id}
{assign var="showMode" value=$gui->show_mode} 

{* Used on several operations to implement goback *}
{assign var="tcViewAction" value="lib/testcases/archiveData.php?tcase_id=$tcase_id&show_mode=$showMode"}
             
{assign var="hrefReqSpecMgmt" value="lib/general/frmWorkArea.php?feature=reqSpecMgmt"}
{assign var="hrefReqSpecMgmt" value="$basehref$hrefReqSpecMgmt"}

{assign var="hrefReqMgmt" value="lib/requirements/reqView.php?showReqSpecTitle=1&requirement_id="}
{assign var="hrefReqMgmt" value="$basehref$hrefReqMgmt"}

{assign var="url_args" value="tcAssign2Tplan.php?tcase_id=$tcase_id&tcversion_id=$tcversion_id"}
{assign var="hrefAddTc2Tplan"  value="$basehref$module$url_args"}


{assign var="url_args" value="tcEdit.php?doAction=editStep&testcase_id=$tcase_id&tcversion_id=$tcversion_id"}
{assign var="goBackAction" value="$basehref$tcViewAction"}
{assign var="goBackActionURLencoded" value=$goBackAction|escape:'url'}
{assign var="url_args" value="$url_args&goback_url=$goBackActionURLencoded&show_mode=$showMode&step_id="}
{assign var="hrefEditStep"  value="$basehref$module$url_args"}


{assign var="tcExportAction" value="lib/testcases/tcExport.php?goback_url=$goBackActionURLencoded&show_mode=$showMode"}
{assign var="exportTestCaseAction" value="$basehref$tcExportAction"}

{assign var="printTestCaseAction" value="lib/testcases/tcPrint.php?show_mode=$showMode"}

{assign var="execFeatureAction" value="lib/general/frmWorkArea.php?feature=executeTest"}



{assign var="author_userinfo" value=$args_users[$args_testcase.author_id]}
{assign var="updater_userinfo" value=""}

{if $args_testcase.updater_id != ''}
  {assign var="updater_userinfo" value=$args_users[$args_testcase.updater_id]}
{/if}

{if $args_show_title == "yes"}
    {if $args_tproject_name != ''}
     <h2>{$tcView_viewer_labels.testproject} {$args_tproject_name|escape} </h2>
    {/if}
    {if $args_tsuite_name != ''}
     <h2>{$tcView_viewer_labels.testsuite} {$args_tsuite_name|escape} </h2>
    {/if}
	  <h2>{$tcView_viewer_labels.title_test_case} {$args_testcase.name|escape} </h2>
{/if}
{assign var="warning_edit_msg" value=""}
{assign var="warning_delete_msg" value=""}

<div style="display: inline;" class="groupBtn">

  

{if $args_can_do->edit == "yes"}

  {assign var="edit_enabled" value=0}
  {assign var="delete_enabled" value=0}

  {* Seems logical you can disable some you have executed before *}
  {assign var="active_status_op_enabled" value=1}
  {assign var="has_been_executed" value=0}
  {lang_get s='can_not_edit_tc' var="warning_edit_msg"}
  {lang_get s='system_blocks_delete_executed_tc' var="warning_delete_msg"}

  {if $args_status_quo == null || $args_status_quo[$args_testcase.id].executed == null}
      {assign var="edit_enabled" value=1}
      {assign var="delete_enabled" value=1}
      {assign var="warning_edit_msg" value=""}
      {assign var="warning_delete_msg" value=""}
  {else} 
    {if isset($args_tcase_cfg) && $args_tcase_cfg->can_edit_executed == 1}
      {assign var="edit_enabled" value=1} 
      {assign var="has_been_executed"  value=1} 
      {lang_get s='warning_editing_executed_tc' var="warning_edit_msg"}
    {/if} 
    
    {if isset($args_tcase_cfg)}
      {if $args_tcase_cfg->can_delete_executed == 1}
        {assign var="delete_enabled" value=1} 
        {assign var="has_been_executed"  value=1} 
        {assign var="warning_delete_msg" value=""}
      {else}
        {if ($args_can_do->delete_testcase == "yes" &&  
            $args_can_delete_testcase == "yes") ||
            ($args_can_do->delete_version == "yes" && 
            $args_can_delete_version == "yes")}
          {lang_get s='system_blocks_delete_executed_tc' var="warning_delete_msg"}
        {/if}  
      {/if}  
    {/if} 
    
  {/if}

    <form style="display: inline;" id="topControls" name="topControls" method="post" action="lib/testcases/tcEdit.php">
    <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
    <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
    <input type="hidden" name="has_been_executed" value="{$has_been_executed}" />
    <input type="hidden" name="doAction" value="" />
    <input type="hidden" name="show_mode" value="{$gui->show_mode}" />


    {if $edit_enabled}
         <input type="submit" name="edit_tc" 
                onclick="doAction.value='edit';{$gui->submitCode}" value="{$tcView_viewer_labels.btn_edit}" />
    {/if}
  
    {* Double condition because for test case versions WE DO NOT DISPLAY this  button, using $args_can_delete_testcase='no'
      *}
    {if $delete_enabled && $args_can_do->delete_testcase == "yes" &&  $args_can_delete_testcase == "yes"}
      <input type="submit" name="delete_tc" value="{$tcView_viewer_labels.btn_delete}" />
    {/if}
  
    {* Double condition because for test case versions WE DO NOT DISPLAY this  button, using $args_can_move_copy='no'
    *}
    {if $args_can_do->copy == "yes" && $args_can_move_copy == "yes"}
         <input type="submit" name="move_copy_tc"   value="{$tcView_viewer_labels.btn_mv_cp}" />
    {/if}

    {if $edit_enabled}
        <input type="hidden" name="containerID" value="{$args_testcase.testsuite_id}" />
        <input type="submit" name="new_tc" title="{$tcView_viewer_labels.hint_new_sibling}"
               onclick="doAction.value='create';{$gui->submitCode}" value="{$tcView_viewer_labels.btn_new_sibling}" />
    {/if}

    </form>

  <span>
  <form style="display: inline;" id="tcexport" name="tcexport" method="post" action="{$exportTestCaseAction}" >
    <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
    <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
    <input type="submit" name="export_tc" value="{$tcView_viewer_labels.btn_export}" />
  </form>
  </span>

  <span>
  <form style="display: inline;" id="tcprint" name="tcprint" method="post" action="" >
    <input type="button" name="tcPrinterFriendly" value="{$tcView_viewer_labels.btn_print_view}" 
           onclick="javascript:openPrintPreview('tc',{$args_testcase.testcase_id},{$args_testcase.id},null,
                                                '{$printTestCaseAction}');"/>
  </form>
  </span>

    <form style="display: inline;" id="versionControls" name="versionControls" method="post" action="lib/testcases/tcEdit.php">
    <input type="hidden" name="testcase_id" id="versionControls_testcase_id" value="{$args_testcase.testcase_id}" />
    <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
    <input type="hidden" name="has_been_executed" value="{$has_been_executed}" />
    <input type="hidden" name="doAction" value="" />
    <input type="hidden" name="show_mode" value="{$gui->show_mode}" />


    {if $args_can_do->create_new_version == "yes"}
      <input type="submit" name="do_create_new_version" title="{$tcView_viewer_labels.hint_new_version}" 
             value="{$tcView_viewer_labels.btn_new_version}" />
    {/if}

    {if $delete_enabled && $args_can_do->delete_version == "yes" && $args_can_delete_version == "yes"}
       <input type="submit" name="delete_tc_version" value="{$tcView_viewer_labels.btn_del_this_version}" />
    {/if}


  
    {* --------------------------------------------------------------------------------------- *}
    {if $active_status_op_enabled eq 1 && $args_can_do->deactivate=='yes'}
          {if $args_testcase.active eq 0}
              {assign var="act_deact_btn" value="activate_this_tcversion"}
              {assign var="act_deact_value" value="activate_this_tcversion"}
              {assign var="version_title_class" value="inactivate_version"}
          {else}
              {assign var="act_deact_btn" value="deactivate_this_tcversion"}
              {assign var="act_deact_value" value="deactivate_this_tcversion"}
              {assign var="version_title_class" value="activate_version"}
          {/if}
          <input type="submit" name="{$act_deact_btn}"
                             value="{lang_get s=$act_deact_value}" />
    {/if}

  </form>
{/if} {* user can edit *}

  {if $args_can_do->add2tplan == "yes" && $args_has_testplans}
  <span>
  <form style="display: inline;" id="addToTestPlans" name="addToTestPlans" method="post" action="">
    <input type="hidden" name="testcase_id" id="versionControls_testcase_id" value="{$args_testcase.testcase_id}" />
    <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
    <input type="button" id="addTc2Tplan_{$args_testcase.id}"  name="addTc2Tplan_{$args_testcase.id}" 
           value="{$tcView_viewer_labels.btn_add_to_testplans}" onclick="location='{$hrefAddTc2Tplan}'" />
  </form>         
  </span>

  {/if}

  <span>
  {* compare versions *}
  {if $args_testcase.version > 1}
    <form style="display: inline;" id="version_compare" name="version_compare" method="post" action="lib/testcases/tcCompareVersions.php">
      <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
      <input type="submit" name="compare_versions" value="{$tcView_viewer_labels.btn_compare_versions}" />
    </form>
  {/if}
  </span>
  <span>
    <input type="button" onclick="javascript:openExecHistoryWindow({$args_testcase.testcase_id});"
           value="{$tcView_viewer_labels.btn_show_exec_history}" />
  </span>

  </div> {* class="groupBtn" *}
<br/><br/>



{* --------------------------------------------------------------------------------------- *}
  {if $args_testcase.active eq 0}
    <div class="messages" align="center">{$tcView_viewer_labels.tcversion_is_inactive_msg}</div>
  {/if}
   {if $warning_edit_msg != ""}
       <div class="messages" align="center">
         {$warning_edit_msg|escape}<br>
       </div>
   {/if}
   {if $warning_delete_msg != ""}
       <div class="messages" align="center">
         {$warning_delete_msg|escape}<br>
       </div>
   {/if}
   

{literal}
<script type="text/javascript">
/**
 * used instead of window.open().
 *
 */
function launchEditStep(step_id)
{
  document.getElementById('stepsControls_step_id').value=step_id;
  document.getElementById('stepsControls_doAction').value='editStep';
  document.getElementById('stepsControls').submit();
}

/**
 * used instead of window.open().
 *
 */
function launchInsertStep(step_id)
{
  document.getElementById('stepsControls_step_id').value=step_id;
  document.getElementById('stepsControls_doAction').value='doInsertStep';
  document.getElementById('stepsControls').submit();
}


</script>
{/literal}

<form id="stepsControls" name="stepsControls" method="post" action="lib/testcases/tcEdit.php">
  <input type="hidden" name="goback_url" value="{$goBackAction}" />
  <input type="hidden" id="stepsControls_doAction" name="doAction" value="" />
  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
  <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
  <input type="hidden" name="has_been_executed" value="{$has_been_executed}" />
  <input type="hidden" id="stepsControls_step_id" name="step_id" value="0" />
  <input type="hidden" id="stepsControls_show_mode" name="show_mode" value="{$gui->show_mode}" />


    {include file="testcases/inc_tcbody.tpl" 
             inc_tcbody_close_table=false
             inc_tcbody_testcase=$args_testcase
             inc_tcbody_show_title=$args_show_title
             inc_tcbody_tableColspan=$tableColspan
             inc_tcbody_labels=$tcView_viewer_labels
             inc_tcbody_author_userinfo=$author_userinfo
             inc_tcbody_updater_userinfo=$updater_userinfo
             inc_tcbody_cf=$args_cf}
    
  {if $args_testcase.steps != ''}
  {include file="testcases/inc_steps.tpl"
           layout=$gui->steps_results_layout
           edit_enabled=$edit_enabled
           steps=$args_testcase.steps}
  {/if}
</table>

<div {$addInfoDivStyle}>
  {if $edit_enabled}
  <input type="submit" name="create_step" 
          onclick="doAction.value='createStep';{$gui->submitCode}" value="{$tcView_viewer_labels.btn_create_step}" />

  {if $args_testcase.steps != ''}
    <input type="submit" name="resequence_steps" id="resequence_steps" 
            onclick="doAction.value='doResequenceSteps';{$gui->submitCode}" 
            value="{$tcView_viewer_labels.btn_resequence_steps}" />
  {/if}

  <span class="order_info" style='display:none'>
  <input type="submit" name="renumber_step" 
          onclick="doAction.value='doReorderSteps';{$gui->submitCode};validateStepsReorder('stepsControls');" 
          value="{$tcView_viewer_labels.btn_reorder_steps}" />
  </span>
  {/if}
</div>
</form>

{if $session['testprojectOptions']->automationEnabled}
  <div {$addInfoDivStyle}>
    <span class="labelHolder">{$tcView_viewer_labels.execution_type} {$smarty.const.TITLE_SEP}</span>
    {if isset($gui->execution_types[$args_testcase.execution_type])}
      {$gui->execution_types[$args_testcase.execution_type]}
    {else}
      Unknown execution type code: {$args_testcase.execution_type}
    {/if}  
  </div>
{/if}

{if $session['testprojectOptions']->testPriorityEnabled}
   <div {$addInfoDivStyle}>
   <form id="importanceForm" name="importanceForm" method="post" action="lib/testcases/tcEdit.php">
    <input type="hidden" name="doAction" id="doAction" value="setImportance">
    <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
    <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
    
    <span class="labelHolder">{$tcView_viewer_labels.test_importance} {$smarty.const.TITLE_SEP}</span>
    
    {if $edit_enabled}
    <select name="importance" onchange="importanceForm.submit()">
          {html_options options=$gsmarty_option_importance selected=$args_testcase.importance}
    </select>
    {else}
      {$gsmarty_option_importance[$args_testcase.importance]}
    {/if}
   </form>
   </div>
{/if}

  {* 20090718 - franciscom *}
  {if $args_cf.standard_location neq ''}
  <div {$addInfoDivStyle}>
        <div id="cfields_design_time" class="custom_field_container">{$args_cf.standard_location}</div>
  </div>
  {/if}

  <div {$addInfoDivStyle}>
    <table cellpadding="0" cellspacing="0" style="font-size:100%;">
          <tr>
             <td width="35%" style="vertical-align:top;"><a href={$gsmarty_href_keywordsView}>{$tcView_viewer_labels.keywords}</a>: &nbsp;
          </td>
           <td style="vertical-align:top;">
               {foreach item=keyword_item from=$args_keywords_map}
                {$keyword_item.keyword|escape}
                <br />
                {foreachelse}
                  {$tcView_viewer_labels.none}
            {/foreach}
          </td>
        </tr>
        </table>
  </div>

  {if $gui->requirementsEnabled == TRUE && ($gui->view_req_rights == "yes" || $gui->requirement_mgmt) }
  <div {$addInfoDivStyle}>
    <table cellpadding="0" cellspacing="0" style="font-size:100%;">
             <tr>
               <td colspan="{$tableColspan}" style="vertical-align:text-top;"><span><a title="{$tcView_viewer_labels.requirement_spec}" href="{$hrefReqSpecMgmt}"
               target="mainframe" class="bold">{$tcView_viewer_labels.Requirements}</a>
              {if $gui->requirement_mgmt}
                <img class="clickable" src="{$tlImages.item_link}"
                     onclick="javascript:openReqWindow({$args_testcase.testcase_id});"
                     title="{$tcView_viewer_labels.link_unlink_requirements}" />
              {/if}
              : &nbsp;</span>
             </td>
              <td>
              {section name=item loop=$args_reqs}
                <img class="clickable" src="{$tlImages.edit}"
                     onclick="javascript:openLinkedReqWindow({$args_reqs[item].id});"
                     title="{$tcView_viewer_labels.requirement}" />
                {$gsmarty_gui->role_separator_open}{$args_reqs[item].req_spec_title|escape}{$gsmarty_gui->role_separator_close}
                {$args_reqs[item].req_doc_id|escape}{$gsmarty_gui->title_separator_1}{$args_reqs[item].title|escape}
                {if !$smarty.section.item.last}<br />{/if}
              {sectionelse}
                {$tcView_viewer_labels.none}
              {/section}
              </td>
            </tr>
    </table>
  </div>
  {/if}
  
{if $args_linked_versions != null}
  {* Test Case version Test Plan Assignment *}
  <br />
  <div {$addInfoDivStyle}>
    <span class="bold"> {$tcView_viewer_labels.testplan_usage} </span>
    <table class="simple sortable">
    <th>{$tcView_viewer_labels.version}</th>
    <th>{$tlImages.sort_hint}{$tcView_viewer_labels.test_plan}</th>
    {if $gui->platforms != null}
      <th>{$tlImages.sort_hint}{$tcView_viewer_labels.platform}</th>
    {/if}
    {foreach from=$args_linked_versions item=link2tplan_platform}
      {foreach from=$link2tplan_platform item=link2platform key=tplan_id}
        {foreach from=$link2platform item=version_info}
          <tr>
          <td style="width:10%;text-align:center;">{$version_info.version}</td>
          <td>{$version_info.tplan_name|escape}
              <a href="{$execFeatureAction}" target="_parent" ><img class="clickable" src="{$tlImages.execute}" 
                             title="{$tcView_viewer_labels.goto_execute}" /></a>
          </td>
          {if $gui->platforms != null}
            <td>
            {if $version_info.platform_id > 0}
              {$gui->platforms[$version_info.platform_id]|escape}
            {/if}          
            </td>
          {/if}
          </tr>
        {/foreach}
      {/foreach}
    {/foreach}
    </table>
  </div>
{/if}
