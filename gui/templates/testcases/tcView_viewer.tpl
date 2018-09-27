{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource tcView_viewer.tpl
viewer for test case in test specification

*}
{lang_get var="tcView_viewer_labels"
          s="requirement_spec,Requirements,tcversion_is_inactive_msg,
             btn_edit,btn_delete,btn_mv_cp,btn_del_this_version,btn_new_version,
             btn_export,btn_execute_automatic_testcase,version,
             testplan_usage,version_short,
             testproject,testsuite,title_test_case,summary,steps,btn_add_to_testplans,applyExecTypeChangeToAllSteps,
             title_last_mod,title_created,by,expected_results,keywords,goto_execute,
             btn_create_step,step_number,btn_reorder_steps,step_actions,hint_new_sibling,
             execution_type_short_descr,delete_step,show_hide_reorder,btn_new_sibling,
             test_plan,platform,insert_step,btn_print,btn_print_view,hint_new_version,
             execution_type,test_importance,importance,none,preconditions,btn_compare_versions,btn_bulk,
             show_ghost_string,display_author_updater,onchange_save,
             estimated_execution_duration,status,btn_save,estimated_execution_duration_short,
             requirement,btn_show_exec_history,btn_resequence_steps,link_unlink_requirements,
             code_mgmt,code_link_tl_to_cts,can_not_edit_frozen_tc,testcase_operations,
			 testcase_version_operations"}

{lang_get s='warning_delete_step' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{* will be useful in future to semplify changes *}
{$tableColspan=$gui->tableColspan} 
{$addInfoDivStyle='style="padding: 5px 3px 4px 10px;"'}


{$module='lib/testcases/'}
{$tcase_id=$args_testcase.testcase_id}
{$tcversion_id=$args_testcase.id}
{$showMode=$gui->show_mode} 

{$openC = $gsmarty_gui->role_separator_open}
{$closeC = $gsmarty_gui->role_separator_close}
{$sepC = $gsmarty_gui->title_separator_1}


{* Used on several operations to implement goback *}
{$tcViewAction="lib/testcases/archiveData.php?tcase_id=$tcase_id&show_mode=$showMode"}
             
{$hrefReqSpecMgmt="lib/general/frmWorkArea.php?feature=reqSpecMgmt"}
{$hrefReqSpecMgmt="$basehref$hrefReqSpecMgmt"}

{$hrefReqMgmt="lib/requirements/reqView.php?showReqSpecTitle=1&requirement_id="}
{$hrefReqMgmt="$basehref$hrefReqMgmt"}

{$url_args="tcAssign2Tplan.php?tcase_id=$tcase_id&tcversion_id=$tcversion_id"}
{$hrefAddTc2Tplan="$basehref$module$url_args"}


{$url_args="tcEdit.php?doAction=editStep&testcase_id=$tcase_id&tcversion_id=$tcversion_id"}
{$goBackAction="$basehref$tcViewAction"}
{$goBackActionURLencoded=$goBackAction|escape:'url'}
{$url_args="$url_args&goback_url=$goBackActionURLencoded&show_mode=$showMode&step_id="}
{$hrefEditStep="$basehref$module$url_args"}

{$tproject_id = $gui->tproject_id}
{$tcExportAction="lib/testcases/tcExport.php?tproject_id=$tproject_id&goback_url=$goBackActionURLencoded&show_mode=$showMode"}
{$exportTestCaseAction="$basehref$tcExportAction"}

{$printTestCaseAction="lib/testcases/tcPrint.php?show_mode=$showMode"}

{$execFeatureAction="lib/general/frmWorkArea.php?feature=executeTest"}

{$bulkOpAction="lib/testcases/tcBulkOp.php?goback_url=$goBackActionURLencoded&show_mode=$showMode"}
{$bulkOpAction="$basehref$bulkOpAction"}


{$author_userinfo=$args_users[$args_testcase.author_id]}
{$updater_userinfo=""}

{if $args_testcase.updater_id != ''}
  {$updater_userinfo=$args_users[$args_testcase.updater_id]}
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


  
{$warning_edit_msg=""}
{$warning_delete_msg=""}
{$edit_enabled=0}
{$delete_enabled=0}
{$has_been_executed=0}
{$show_relations=1}
{if $args_can_do->edit == "yes"}

  {$has_been_executed=0}
  {lang_get s='can_not_edit_tc' var="warning_edit_msg"}
  {lang_get s='system_blocks_delete_executed_tc' var="warning_delete_msg"}

  {if $args_status_quo == null || $args_status_quo[$args_testcase.id].executed == null}
      {$edit_enabled=1}
      {$delete_enabled=1}
      {$warning_edit_msg=""}
      {$warning_delete_msg=""}
  {else} 
    {if isset($args_tcase_cfg) && $args_tcase_cfg->can_edit_executed == 1}
      {$edit_enabled=1} 
      {$has_been_executed=1} 
      {lang_get s='warning_editing_executed_tc' var="warning_edit_msg"}
    {/if} 
    
    {if isset($args_tcase_cfg)}
      {if $args_tcase_cfg->can_delete_executed == 1}
        {$delete_enabled=1} 
        {$has_been_executed=1} 
        {$warning_delete_msg=""}
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

{if $args_read_only == "yes"}
  {$edit_enabled=0} 
  {$delete_enabled=0} 
{/if}
{if $args_hide_relations == "yes"}
	{$show_relations=0}
{/if}

<div style="display:{$tlCfg->gui->op_area_display->test_case};" 
    id="tcView_viewer_tcase_control_panel">
  <fieldset class="groupBtn">
	 <b>{$tcView_viewer_labels.testcase_operations}</b>

    <form style="display: inline;" id="topControls" name="topControls" method="post" action="{$basehref}lib/testcases/tcEdit.php">
		<input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
		<input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
		<input type="hidden" name="has_been_executed" value="{$has_been_executed}" />
		<input type="hidden" name="doAction" value="" />
		<input type="hidden" name="show_mode" value="{$gui->show_mode}" />

		{* New TC sibling *}
		{if $args_read_only != "yes" }
			<input type="hidden" name="containerID" value="{$args_testcase.testsuite_id}" />
			<input type="submit" name="new_tc" title="{$tcView_viewer_labels.hint_new_sibling}"
				   onclick="doAction.value='create';{$gui->submitCode}" value="{$tcView_viewer_labels.btn_new_sibling}" />
		{/if}

		{* Move Copy *}
		{if $args_can_do->copy == "yes" && $args_can_move_copy == "yes"}
			 <input type="submit" name="move_copy_tc"   value="{$tcView_viewer_labels.btn_mv_cp}" />
		{/if}
	  
		{* Delete TC *}
		{if $delete_enabled && $args_can_do->delete_testcase == "yes" &&  $args_can_delete_testcase == "yes"}
		  <input type="submit" name="delete_tc" value="{$tcView_viewer_labels.btn_delete}" />
		{/if}
    </form>
  
	{* bulk action *}
	{if $edit_enabled}
	  <form style="display: inline;" id="tcbulkact" name="tcbulkact" 
			method="post" action="{$bulkOpAction}" >
		<input type="hidden" name="tcase_id" id="tcase_id" value="{$args_testcase.testcase_id}" />
		<input type="submit" name="bulk_op" value="{$tcView_viewer_labels.btn_bulk}" />
	  </form>
	{/if}
	
	{* compare versions *}
	<span>
	  {if $args_testcase.version > 1}
		<form style="display: inline;" id="version_compare" name="version_compare" method="post" action="{$basehref}lib/testcases/tcCompareVersions.php">
		  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
		  <input type="submit" name="compare_versions" value="{$tcView_viewer_labels.btn_compare_versions}" />
		</form>
	  {/if}
	</span>
    {* execution history *}
	<span>
      <input type="button" onclick="javascript:openExecHistoryWindow({$args_testcase.testcase_id},1);"
           value="{$tcView_viewer_labels.btn_show_exec_history}" />
    </span>
  </fieldset>
  {* End of TC Section *}

  <fieldset class="groupBtn">
  	 <b>{$tcView_viewer_labels.testcase_version_operations}</b>

  <form style="display: inline;" id="versionControls" name="versionControls" method="post" action="{$basehref}lib/testcases/tcEdit.php">
	<input type="hidden" name="testcase_id" id="versionControls_testcase_id" value="{$args_testcase.testcase_id}" />
	<input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
	<input type="hidden" name="has_been_executed" value="{$has_been_executed}" />
	<input type="hidden" name="doAction" value="" />
	<input type="hidden" name="show_mode" value="{$gui->show_mode}" />

		
	{* Edit TC *}
	{if $edit_enabled && $args_frozen_version=="no"}
		 <input type="submit" name="edit_tc" 
				onclick="doAction.value='edit';{$gui->submitCode}" value="{$tcView_viewer_labels.btn_edit}" />
	{/if}

	{* new TC version *}
	{if $args_can_do->create_new_version == "yes" && $args_read_only != "yes"}
	  <input type="submit" name="do_create_new_version" title="{$tcView_viewer_labels.hint_new_version}" 
			 value="{$tcView_viewer_labels.btn_new_version}" />
	{/if}

	{* activate/desactivate TC version *}
	{if $args_can_do->edit == "yes" && $args_can_do->deactivate=='yes' && $args_frozen_version=="no"}
		  {if $args_testcase.active eq 0}
			  {$act_deact_btn="activate_this_tcversion"}
			  {$act_deact_value="activate_this_tcversion"}
			  {$version_title_class="inactivate_version"}
		  {else}
			  {$act_deact_btn="deactivate_this_tcversion"}
			  {$act_deact_value="deactivate_this_tcversion"}
			  {$version_title_class="activate_version"}
		  {/if}
		  <input type="submit" name="{$act_deact_btn}"
							 value="{lang_get s=$act_deact_value}" />
	{/if}

	{* freeze/unfreeze TC version *}
	{if $args_read_only != "yes" && 
		$args_can_do->freeze=='yes'}
		  {if $args_frozen_version=="yes"}
			  {$freeze_btn="unfreeze"}
			  {$freeze_value="unfreeze_this_tcversion"}
			  {$version_title_class="unfreeze_version"}
		  {else}
			  {$freeze_btn="freeze"}
			  {$freeze_value="freeze_this_tcversion"}
			  {$version_title_class="freeze_version"}
		  {/if}

		 <input type="submit" name="{$freeze_btn}" 
				onclick="doAction.value='{$freeze_btn}';{$gui->submitCode}" value="{lang_get s=$freeze_value}" />
	{/if}

	{* delete TC version *}
	{if $args_frozen_version=="no" && $args_can_do->delete_version == "yes" && $args_can_delete_version == "yes"}
	   <input type="submit" name="delete_tc_version" value="{$tcView_viewer_labels.btn_del_this_version}" />
	{/if}

  </form>
  {* add TC version to testplan *}
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
  {* Export TC version *}
	<span>
	  <form style="display: inline;" id="tcexport" name="tcexport" method="post" action="{$exportTestCaseAction}" >
		<input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
		<input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
		<input type="submit" name="export_tc" value="{$tcView_viewer_labels.btn_export}" />
	  </form>
	</span>
{/if} {* user can edit *}

{* Print TC version *}
<span>
  <form style="display: inline;" id="tcprint" name="tcprint" method="post" action="" >
    <input type="button" name="tcPrinterFriendly" value="{$tcView_viewer_labels.btn_print_view}" 
           onclick="javascript:openPrintPreview('tc',{$args_testcase.testcase_id},{$args_testcase.id},null,
                                                '{$printTestCaseAction}');"/>
  </form>
</span>

</fieldset>
{* End of TC version Section *}


</div>



{* --------------------------------------------------------------------------------------- *}
  {if $args_testcase.active eq 0}
    <div class="messages" align="center">{$tcView_viewer_labels.tcversion_is_inactive_msg}</div>
  {/if}
  
  {* warning message when tc version is frozen *}
{if $args_frozen_version=="yes"}
  <div class="messages" align="center">{$tcView_viewer_labels.can_not_edit_frozen_tc}</div>
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

<form id="stepsControls" name="stepsControls" method="post" action="{$basehref}lib/testcases/tcEdit.php">
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
             inc_tcbody_editor_type=$gui->designEditorType
             inc_tcbody_cf=$args_cf}
    
  {if $args_testcase.steps != ''}
  {include file="testcases/inc_steps.tpl"
           layout=$gui->steps_results_layout
           edit_enabled=$edit_enabled
		       args_frozen_version=$args_frozen_version
           ghost_control=true
           steps=$args_testcase.steps}
  {/if}
</table>

{if $edit_enabled && $args_frozen_version=="no"}
<div {$addInfoDivStyle}>
  <input type="submit" name="create_step" 
          onclick="doAction.value='createStep';{$gui->submitCode}" value="{$tcView_viewer_labels.btn_create_step}" />

  {if $args_testcase.steps != ''}
    <input type="submit" name="resequence_steps" id="resequence_steps" 
            onclick="doAction.value='doResequenceSteps';{$gui->submitCode}" 
            value="{$tcView_viewer_labels.btn_resequence_steps}" />
  {/if}

  <span class="order_info" style='display:none'>
  <input type="submit" name="renumber_step" 
          onclick="doAction.value='doReorderSteps';{$gui->submitCode};javascript: return validateStepsReorder('step_number{$args_testcase.id}');"
          value="{$tcView_viewer_labels.btn_reorder_steps}" />
  </span>
</div>
{/if}
</form>

{include file="testcases/attributesLinearForViewer.inc.tpl"} 


{if $args_cf.standard_location neq ''}
  <div {$addInfoDivStyle}>
        <div id="cfields_design_time" class="custom_field_container">{$args_cf.standard_location}</div>
  </div>
  {/if}

  <p>
  <div {$addInfoDivStyle}>
   {$kwRW = ($edit_enabled == 1) && ($has_been_executed == 0)} 

   {include file="testcases/keywords.inc.tpl" 
            args_edit_enabled=$kwRW} 
  </div>
  
  {if $gui->requirementsEnabled == TRUE && 
     ($gui->view_req_rights == "yes" || $gui->req_tcase_link_management) }

     {$reqLinkingEnabled = 0}
     {if $gui->req_tcase_link_management && $args_frozen_version=="no" &&
         $edit_enabled == 1 }
        {$reqLinkingEnabled = 1}
     {/if}    

     {if $tlCfg->testcase_cfg->reqLinkingDisabledAfterExec == 1 && 
         $has_been_executed == 1}
        {$reqLinkingEnabled = 0}
     {/if}
     

  <div {$addInfoDivStyle}>
    <table cellpadding="0" cellspacing="0" style="font-size:100%;">
             <tr>
               <td colspan="{$tableColspan}" style="vertical-align:text-top;"><span><a title="{$tcView_viewer_labels.requirement_spec}" href="{$hrefReqSpecMgmt}"
               target="mainframe" class="bold">{$tcView_viewer_labels.Requirements}</a>
              {if $reqLinkingEnabled }
                <img class="clickable" src="{$tlImages.item_link}"
                     onclick="javascript:openReqWindow({$args_testcase.testcase_id},'a');"
                     title="{$tcView_viewer_labels.link_unlink_requirements}" />
              {/if}
              : &nbsp;</span>
             </td>
              <td>
              {section name=item loop=$args_reqs}
                {$reqID=$args_reqs[item].id}
                {$reqVersionID=$args_reqs[item].req_version_id}
                {$reqVersionNum=$args_reqs[item].version}
                
                
                <img class="clickable" src="{$tlImages.edit}"
                     onclick="javascript:openLinkedReqVersionWindow({$reqID},{$reqVersionID});"
                     title="{$tcView_viewer_labels.requirement}" />
                {$openC}{$args_reqs[item].req_spec_title|escape}{$closeC}
                {$args_reqs[item].req_doc_id|escape}&nbsp{$openC}{$tcView_viewer_labels.version_short}{$reqVersionNum}{$closeC}{$sepC}{$args_reqs[item].title|escape}
                {if !$smarty.section.item.last}<br />{/if}
              {sectionelse}
                {$tcView_viewer_labels.none}
              {/section}
              </td>
            </tr>
    </table>
  </div>
  {/if}


  {if $gui->codeTrackerEnabled}
  <br>
  <div {$addInfoDivStyle}>
    <table cellpadding="0" cellspacing="0" style="font-size:100%;">
      <tr>
        <td colspan="{$tableColspan}" style="vertical-align:text-top;">
          <span><a title="{$tcView_viewer_labels.code_mgmt}" href="{$gui->cts->cfg->uriview}"
               target="_blank" class="bold">{$tcView_viewer_labels.code_mgmt}</a><b>: &nbsp;</b>
            <a href="javascript:open_script_add_window({$gui->tproject_id},null,{$tcversion_id},'link')">
            <img src="{$tlImages.new_f2_16}" title="{$tcView_viewer_labels.code_link_tl_to_cts}" style="border:none" /></a>
              &nbsp;
          </span>
        </td>
      </tr>
      {* TestScript Links (if any) *}
      {if isset($gui->scripts[$tcversion_id]) && !is_null($gui->scripts[$tcversion_id])}
        <tr style="background-color: #d0d0d0">
          {include file="inc_show_scripts_table.tpl"
           scripts_map=$gui->scripts[$tcversion_id]
           can_delete=true
           tcase_id=$tcversion_id
           tproject_id=$tproject_id
          }
        </tr>
      {/if}
    </table>
  </div>
  {/if}
  
{if $show_relations}
  <br />
  {include file="testcases/relations.inc.tpl"
           args_relations = $args_relations
           args_frozen_version = $args_frozen_version
           args_edit_enabled = $edit_enabled} 
{/if}

{if $args_linked_versions != null && $tlCfg->spec_cfg->show_tplan_usage}
  {* Test Case version Test Plan Assignment *}
  <br />
  {include file="testcases/quickexec.inc.tpl" args_edit_enabled=$edit_enabled} 
{/if}

