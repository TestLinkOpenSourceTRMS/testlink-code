{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource tcView_viewer.tpl
viewer for test case in test specification
*}
{include file="testcases/tcView_viewer.labels.tpl"}
{lang_get s='warning_delete_step' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{* will be useful in future to semplify changes *}
{$tableColspan=$gui->tableColspan} 
{$addInfoDivStyle='style="padding: 5px 3px 4px 10px;"'}


{$module='lib/testcases/'}
{$tcase_id=$args_testcase.testcase_id}
{$tcversion_id=$args_testcase.id}
{$showMode=$gui->show_mode} 
{$tplan_id=$gui->tplan_id} 
{$tproject_id=$gui->tproject_id} 


{$openC = $gsmarty_gui->role_separator_open}
{$closeC = $gsmarty_gui->role_separator_close}
{$sepC = $gsmarty_gui->title_separator_1}


{* Used on several operations to implement goback *}
{$tcViewAction="lib/testcases/archiveData.php?tcase_id=$tcase_id&show_mode=$showMode&tproject_id=$tproject_id&tplan_id=$tplan_id"}
             
{$hrefReqSpecMgmt="lib/general/frmWorkArea.php?feature=reqSpecMgmt"}
{$hrefReqSpecMgmt="$basehref$hrefReqSpecMgmt"}

{$hrefReqMgmt="lib/requirements/reqView.php?showReqSpecTitle=1&requirement_id="}
{$hrefReqMgmt="$basehref$hrefReqMgmt"}

{$url_args="tcAssign2Tplan.php?tcase_id=$tcase_id&tcversion_id=$tcversion_id&tproject_id=$tproject_id&tplan_id=$tplan_id"}
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

{$managerURL="lib/testcases/tcEdit.php?tproject_id=$tproject_id"}
{$managerURL="$basehref$managerURL"}




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
{$show_relations=1}
{lang_get s='can_not_edit_tc' var="warning_edit_msg"}
{lang_get s='system_blocks_delete_executed_tc' var="warning_delete_msg"}

{$has_been_executed=0}
{if $args_status_quo != null 
  || $args_status_quo[$args_testcase.id].executed}
  {$has_been_executed=1}  
{/if}

{if $args_can_do->edit == "yes"}
    {if $args_status_quo == null 
        || $args_status_quo[$args_testcase.id].executed == null}
        {$edit_enabled=1}
        {$delete_enabled=1}
        {$warning_edit_msg=""}
        {$warning_delete_msg=""}
    {else} 
      {if isset($args_tcase_cfg) 
        && $args_tcase_cfg->can_edit_executed == 1}
        {$edit_enabled=1} 
        {lang_get s='warning_editing_executed_tc' var="warning_edit_msg"}
      {/if} 
      
      {if isset($args_tcase_cfg)}
        {if $args_tcase_cfg->can_delete_executed == 1}
          {$delete_enabled=1} 
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

    {if 'editOnExec' != $gui->show_mode && $args_hide_relations == "yes"}
    	{$show_relations=0}
    {/if}

    <div style="display:{$tlCfg->gui->op_area_display->test_case};" 
         id="tcView_viewer_tcase_control_panel_{$tcversion_id}">

    {$allOpOnTCV = false}
    {if 'editOnExec' != $gui->show_mode 
        && isset($args_tcase_operations_enabled) 
        && $args_tcase_operations_enabled == "yes"}
      {$allOpOnTCV = true}
    {/if} 

    {if $allOpOnTCV}
      <fieldset class="groupBtn">
    	  <b>{$tcView_viewer_labels.testcase_operations}</b>
        <form style="display: inline;" id="topControls" name="topControls"
          method="post" action="{$basehref}lib/testcases/tcEdit.php">
      		<input type="hidden" name="testcase_id"
                 value="{$args_testcase.testcase_id}" />
      		<input type="hidden" name="tcversion_id" 
                 value="{$args_testcase.id}" />
      		<input type="hidden" name="has_been_executed" 
                 value="{$has_been_executed}" />
      		<input type="hidden" name="doAction" value="" />
      		<input type="hidden" name="show_mode" value="{$gui->show_mode}" />
          <input type="hidden" name="tplan_id" value="{$gui->tplan_id}" />

      		{* New TC sibling *}
      		{if $args_new_sibling == "yes" }
      			<input type="hidden" name="containerID" value="{$args_testcase.testsuite_id}" />
      			<input class="{#BUTTON_CLASS#}" 
              type="submit" name="new_tc" id="new_tc"
              title="{$tcView_viewer_labels.hint_new_sibling}"
      				onclick="doAction.value='create';{$gui->submitCode}" value="{$tcView_viewer_labels.btn_new_sibling}" />
      		{/if}

      		{* Move Copy *}
      		{if $args_can_do->copy == "yes" 
              && $args_can_move_copy == "yes"}
      			<input class="{#BUTTON_CLASS#}" type="submit" 
                   name="move_copy_tc" id="move_copy_tc"
                   value="{$tcView_viewer_labels.btn_mv_cp}" />
      		{/if}
      	  
      		{* Delete TC *}
      		{if $delete_enabled 
              && $args_can_do->delete_testcase == "yes" 
              && $args_can_delete_testcase == "yes"}
      		   <input class="{#BUTTON_CLASS#}" type="submit" 
                    name="delete_tc" id="delete_tc"
                    value="{$tcView_viewer_labels.btn_delete}" />
      		{/if}
        </form> <!-- id="topControls" -->
      
      	{* bulk action *}
      	{if $edit_enabled && $args_bulk_action=="yes"}
      	  <form style="display: inline;" id="tcbulkact" name="tcbulkact" 
      			method="post" action="{$bulkOpAction}" >
      		<input type="hidden" name="tcase_id" id="tcase_id" value="{$args_testcase.testcase_id}" />
      		<input class="{#BUTTON_CLASS#}" type="submit" name="bulk_op" value="{$tcView_viewer_labels.btn_bulk}" />
      	  </form>
      	{/if}
      	
      	{* compare versions *}
      	<span>
      	  {if $args_testcase.version > 1}
        		<form style="display: inline;" id="version_compare" 
                  name="version_compare" method="post" 
                  action="{$basehref}lib/testcases/tcCompareVersions.php">
        		  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
        		  <input class="{#BUTTON_CLASS#}" type="submit" name="compare_versions" value="{$tcView_viewer_labels.btn_compare_versions}" />
        		</form>
      	  {/if}
      	</span>

        {* execution history *}
      	<span>
            <input class="{#BUTTON_CLASS#}" type="button" 
                   onclick="javascript:openExecHistoryWindow({$args_testcase.testcase_id},1);"
                 value="{$tcView_viewer_labels.btn_show_exec_history}" />
        </span>
      </fieldset>
    {/if}
    {* End of TC Section *}

    {* START TCV SECTION *}
    <fieldset class="groupBtn">
    	<b>{$tcView_viewer_labels.testcase_version_operations}</b>
      <form style="display: inline;" id="versionControls" 
        name="versionControls" method="post" 
        action="{$basehref}lib/testcases/tcEdit.php">
      	<input type="hidden" name="testcase_id" 
               id="versionControls_testcase_id" value="{$args_testcase.testcase_id}" />
      	<input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />

        <input type="hidden" name="tplan_id" value="{$gui->tplan_id}" />

      	<input type="hidden" name="has_been_executed"
          value="{$has_been_executed}" />
      	<input type="hidden" name="doAction" value="" />
      	<input type="hidden" name="show_mode" value="{$gui->show_mode}" />

      	{* Edit TC *}
      	{if $edit_enabled && $args_frozen_version=="no"}
      		 <input class="{#BUTTON_CLASS#}" type="submit" name="edit_tc" 
      				onclick="doAction.value='edit';{$gui->submitCode}" value="{$tcView_viewer_labels.btn_edit}" />
      	{/if}

        {if ( isset($args_tcversion_operation_only_edit_button) 
          && $args_tcversion_operation_only_edit_button == "no") 
          || ($args_can_do->delete_frozen_tcversion == "yes")
          }

          {* new TC version *}
          {if $args_can_do->create_new_version == "yes" 
             && $args_read_only != "yes"}
             {if $gui->new_version_source == 'this'}
               <input class="{#BUTTON_CLASS#}" type="submit" 
                 name="do_create_new_version"
                 id="do_create_new_version" 
                 title="{$tcView_viewer_labels.hint_new_version}" 
                 value="{$tcView_viewer_labels.btn_new_version}" />
             {/if}
             {if $gui->new_version_source == 'latest'}
               <input class="{#BUTTON_CLASS#}" type="submit" 
                 name="do_create_new_version_from_latest" 
                 title="{$tcView_viewer_labels.btn_new_version_from_latest}" 
                 value="{$tcView_viewer_labels.btn_new_version_from_latest}" />      
             {/if}
          {/if}

        	{* freeze/unfreeze TC version *}
        	{if 'editOnExec' != $gui->show_mode && $args_read_only != "yes" 
              && $args_can_do->freeze=='yes'}
        		  {if $args_frozen_version=="yes"}
        			  {$freeze_btn="unfreeze"}
        			  {$freeze_value="unfreeze_this_tcversion"}
        			  {$version_title_class="unfreeze_version"}
        		  {else}
        			  {$freeze_btn="freeze"}
        			  {$freeze_value="freeze_this_tcversion"}
        			  {$version_title_class="freeze_version"}
        		  {/if}

        		 <input class="{#BUTTON_CLASS#}" type="submit" name="{$freeze_btn}" 
        				onclick="doAction.value='{$freeze_btn}';{$gui->submitCode}" value="{lang_get s=$freeze_value}" />
        	{/if}

        	{* delete TC version *}
        	{if ( $args_frozen_version=="no" 
              || $args_can_do->delete_frozen_tcversion == "yes") 
              && $args_can_do->delete_version == "yes" 
              && $args_can_delete_version == "yes"}
        	   <input class="{#BUTTON_CLASS#}" type="submit" name="delete_tc_version" value="{$tcView_viewer_labels.btn_del_this_version}" />
        	{/if}
        {/if}
      </form>

      {if 'editOnExec' != $gui->show_mode && 
        isset($args_tcversion_operation_only_edit_button) &&
        $args_tcversion_operation_only_edit_button == "no"}
        
        {* add TC version to testplan *}
        {if $args_can_do->add2tplan == "yes" && $args_has_testplans}
        	<span>
        	  <form style="display: inline;" id="addToTestPlans" name="addToTestPlans" method="post" action="">
        		<input type="hidden" name="testcase_id" id="versionControls_testcase_id" value="{$args_testcase.testcase_id}" />
        		<input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
        		<input class="{#BUTTON_CLASS#}" type="button" id="addTc2Tplan_{$args_testcase.id}"  name="addTc2Tplan_{$args_testcase.id}" 
        		   value="{$tcView_viewer_labels.btn_add_to_testplans}" onclick="location='{$hrefAddTc2Tplan}'" />
        	  </form>
        	</span>
        {/if}
        {* Export TC version *}
      	<span>
      	  <form style="display: inline;" id="tcexport" name="tcexport" method="post" action="{$exportTestCaseAction}" >
      		<input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
      		<input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
      		<input class="{#BUTTON_CLASS#}" type="submit" name="export_tc" value="{$tcView_viewer_labels.btn_export}" />
      	  </form>
      	</span>
      {/if}
    </fieldset>
    {* End TCV SECTION *}

{/if} {* $args_can_do->edit -> user can edit *}

{* Print TC version *}
<fieldset class="groupBtn">
<span>
  <form style="display: inline;" id="tcprint" 
        name="tcprint" method="post" action="" >
    <input class="{#BUTTON_CLASS#}" type="button"
           name="tcPrinterFriendly" id="tcPrinterFriendly"
           value="{$tcView_viewer_labels.btn_print_view}" 
           onclick="javascript:openPrintPreview('tc',{$args_testcase.testcase_id},{$args_testcase.id},null,
           '{$printTestCaseAction}');"/>
  </form>
</span>

{if 1 == $gui->candidateToUpd 
    && '' != $gui->tplan_id 
    && 'editOnExec' == $gui->show_mode 
    && 'yes' == $args_can_do->updTplanTCV } 
  <span>
    <form style="display: inline;" 
          id="updTPlan" name="updTPlan" 
          method="post"
          action="{$basehref}lib/testcases/tcEdit.php">
        <input type="hidden" name="testcase_id" id="updTPlan_testcase_id" value="{$args_testcase.testcase_id}" />
        <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
        <input type="hidden" name="tplan_id" value="{$gui->tplan_id}" />
        <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />

        <input type="hidden" id="updTPlan_show_mode" name="show_mode" 
          value="{$gui->show_mode}" />
        
        <input type="hidden" name="doAction" value="updateTPlanLinkToTCV">
        <input class="{#BUTTON_CLASS#}" 
               type="submit" id="updTPlan" name="updTPlan" 
           style="background:#B22222;color:white;"
           value="{$tcView_viewer_labels.updateLinkToThisTCVersion}">
    </form>
  </span>
{/if}
</fieldset>

{* End of TC version Section *}
</div>

{* -------------------------------------------------------------- *}
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

<form id="stepsControls" name="stepsControls" 
  method="post" action="{$basehref}lib/testcases/tcEdit.php">
  <input type="hidden" name="goback_url" value="{$goBackAction}" />
  <input type="hidden" id="stepsControls_doAction" name="doAction" value="" />
  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
  <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />

  <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
  <input type="hidden" name="has_been_executed" value="{$has_been_executed}" />
  <input type="hidden" id="stepsControls_step_id" name="step_id" value="0" />
  <input type="hidden" id="stepsControls_show_mode" name="show_mode" value="{$gui->show_mode}" />
  <input type="hidden" id="stepsControls_tplan_id" name="tplan_id" 
         value="{$gui->tplan_id}" />

    {include file="{$tplConfig.inc_tcbody}" 
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
    {include file="{$tplConfig.inc_steps}"
             layout=$gui->steps_results_layout
             edit_enabled=$edit_enabled
  		       args_frozen_version=$args_frozen_version
             ghost_control=true
             steps=$args_testcase.steps}
  {/if}
</table>

{if $edit_enabled && $args_frozen_version=="no"}
<div {$addInfoDivStyle}>
  <input class="{#BUTTON_CLASS#}" type="submit" 
         name="create_step" id="create_step"
         onclick="doAction.value='createStep';{$gui->submitCode}"
         value="{$tcView_viewer_labels.btn_create_step}" />

  {if $args_testcase.steps != ''}
    <input class="{#BUTTON_CLASS#}" type="submit" 
           name="resequence_steps" id="resequence_steps" 
           onclick="doAction.value='doResequenceSteps';{$gui->submitCode}" 
           value="{$tcView_viewer_labels.btn_resequence_steps}" />
  {/if}

  <span class="order_info" style='display:none'>
  <input class="{#BUTTON_CLASS#}" type="submit" 
         name="renumber_step" id="renumber_step"
          onclick="doAction.value='doReorderSteps';{$gui->submitCode};javascript: return validateStepsReorder('step_number{$args_testcase.id}');"
          value="{$tcView_viewer_labels.btn_reorder_steps}" />
  </span>
</div>
{/if}
</form>

{include file="{$tplConfig['attributesLinearForViewer.inc']}"} 

{if $args_cf.standard_location neq ''}
  <div {$addInfoDivStyle}>
        <div id="cfields_design_time" class="custom_field_container">{$args_cf.standard_location}</div>
  </div>
  {/if}

  <p>
  <div {$addInfoDivStyle}>
   {$platRW = 0}
   {$kwRW = $args_frozen_version=="no" && $edit_enabled == 1 &&
            $has_been_executed == 0} 
   
   {if $args_frozen_version=="no" && $has_been_executed == 1 }
     {if $args_tcase_cfg->can_edit_executed == 1 || 
         $args_tcase_cfg->can_add_remove_kw_on_executed == 1}
       {$kwRW = 1}
     {/if}
   {/if}
   
   {include file="{$tplConfig['keywords.inc']}" 
            args_edit_enabled=$kwRW
            args_tcase_id=$tcase_id
            args_tcversion_id=$tcversion_id
   } 
</div>
  
<p>
<div {$addInfoDivStyle}>
   {$kwRW = $args_frozen_version=="no" && $edit_enabled == 1 &&
            $has_been_executed == 0} 
   
   {if $args_frozen_version=="no" && $has_been_executed == 1 }
     {if $args_tcase_cfg->can_edit_executed == 1}
       {$platRW = 1}
     {/if}
   {/if}
   
   {include file="{$tplConfig['platforms.inc']}" 
            args_edit_enabled=$platRW
            args_tcase_id=$tcase_id
            args_tcversion_id=$tcversion_id
   } 
</div>


{if $gui->requirementsEnabled == TRUE && 
  ($gui->view_req_rights == "yes" || $gui->req_tcase_link_management) }
  {$reqLinkingEnabled = 0}
  {if $gui->req_tcase_link_management && $args_frozen_version=="no" &&
         $edit_enabled == 1 }
         {$reqLinkingEnabled = 1}
  {/if}    

  {if $tlCfg->testcase_cfg->reqLinkingDisabledAfterExec == 1 && 
       $has_been_executed == 1 && $args_tcase_cfg->can_edit_executed == 0}
       {$reqLinkingEnabled = 0}
  {/if}
  <div {$addInfoDivStyle}>
    <table cellpadding="0" cellspacing="0" style="font-size:100%;">
      <tr>
        <td colspan="{$tableColspan}" style="vertical-align:text-top;"><span><a title="{$tcView_viewer_labels.requirement_spec}" href="{$hrefReqSpecMgmt}"
               target="mainframe" class="bold">{$tcView_viewer_labels.Requirements}</a>

              {if $reqLinkingEnabled && $args_testcase.isTheLatest == 1}
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
          {include file="{$tplConfig.inc_show_scripts_table}"
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
  {include file="{$tplConfig['relations.inc']}"
           args_is_latest_tcv = $args_testcase.isTheLatest
           args_relations = $args_relations
           args_frozen_version = $args_frozen_version
           args_edit_enabled = $edit_enabled} 
{/if}

{if 'editOnExec' != $gui->show_mode && 
  $args_linked_versions != null && $tlCfg->spec_cfg->show_tplan_usage}
  {* Test Case version Test Plan Assignment *}
  <br />
  {include file="{$tplConfig['quickexec.inc']}"
           args_edit_enabled=$edit_enabled} 
{/if}

{if $gui->closeMyWindow }
  <script type="text/javascript">
  window.close();
  </script>
{/if}