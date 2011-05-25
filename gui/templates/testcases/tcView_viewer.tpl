{*
TestLink Open Source Project - http://testlink.sourceforge.net/

viewer for test case in test specification

@filesource	tcView_viewer.tpl

@internal revisions
20110319 - franciscom - BUGID 4322: New Option to block delete of executed test cases
20110307 - asimon - BUGID 4286: moved print preview to popup to make printing independent from browser easier for the users
                                moved req view button forms and divs around to align buttons in a single row
20110304 - franciscom - BUGID 4286: Option to print single test case
*}
{lang_get var="tcView_viewer_labels"
          s="requirement_spec,Requirements,tcversion_is_inactive_msg,
             btn_edit,btn_delete,btn_mv_cp,btn_del_this_version,btn_new_version,
             btn_export,btn_execute_automatic_testcase,version,testplan_usage,
             testproject,testsuite,title_test_case,summary,steps,btn_add_to_testplans,
             title_last_mod,title_created,by,expected_results,keywords,
             btn_create_step,step_number,btn_reorder_steps,step_actions,
             execution_type_short_descr,delete_step,show_hide_reorder,
             test_plan,platform,insert_step,btn_print,btn_print_view,status,
             execution_type,test_importance,none,preconditions,btn_compare_versions"}

{lang_get s='warning_delete_step' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{* will be useful in future to semplify changes *}
{$tableColspan=$gui->tableColspan} 
{$addInfoDivStyle='style="padding: 5px 3px 4px 10px;"'}


{$module='lib/testcases/'}
{$tcase_id=$args_testcase.testcase_id}
{$tcversion_id=$args_testcase.id}
{$showMode=$gui->show_mode} 

{* Used on several operations to implement goback *}
{$tcViewAction=$gui->tcViewAction}
{$tcViewAction="$tcViewAction$tcase_id"}
{$goBackAction="$basehref$tcViewAction"}
{$goBackActionURLencoded=$goBackAction|escape:'url'}


{$hrefReqSpecMgmt=$gui->reqSpecMgmtHREF}
{$hrefReqSpecMgmt="$basehref$hrefReqSpecMgmt"}

{$hrefReqMgmt=$gui->reqMgmtHREF}
{$hrefReqMgmt="$basehref$hrefReqMgmt"}

{$url_args="&tcase_id=$tcase_id&tcversion_id=$tcversion_id"}
{$hrefAddTc2Tplan=$gui->addTc2TplanHREF}
{$hrefAddTc2Tplan="$basehref$hrefAddTc2Tplan$url_args"}

{$url_args="tcEdit.php?doAction=editStep&testcase_id=$tcase_id&tcversion_id=$tcversion_id"}
{$url_args="$url_args&goback_url=$goBackActionURLencoded&show_mode=$showMode&step_id="}
{$hrefEditStep="$basehref$module$url_args"}


{$tcExportAction=$gui->tcExportAction}
{$tcExportAction="$tcExportAction&goback_url=$goBackActionURLencoded"}
{$exportTestCaseAction="$basehref$tcExportAction"}


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

<div style="display: inline;" class="groupBtn">
{if $args_can_do->edit == "yes"}

  {$edit_enabled=0}
  {$delete_enabled=0}

  {* 20070628 - franciscom - Seems logical you can disable some you have executed before *}
  {$active_status_op_enabled=1}
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
    
    {* 20110319 - BUGID 4322: New Option to block delete of executed test cases *}
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

	<span style="float: left">
	  <form style="display: inline;" id="topControls" name="topControls" 
	  		method="post" action="lib/testcases/tcEdit.php">
	  <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />
	  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
	  <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
	  <input type="hidden" name="has_been_executed" value="{$has_been_executed}" />
	  <input type="hidden" name="doAction" value="" />
	  <input type="hidden" name="show_mode" value="{$gui->show_mode}" />


	  {$go_newline=""}
	  {if $edit_enabled}
	 	    <input type="submit" name="edit_tc" 
	 	           onclick="doAction.value='edit';{$gui->submitCode}" value="{$tcView_viewer_labels.btn_edit}" />
	  {/if}
	
	  {* Double condition because for test case versions WE DO NOT DISPLAY this
	     button, using $args_can_delete_testcase='no'
	  *}
		{if $delete_enabled && $args_can_do->delete_testcase == "yes" &&  $args_can_delete_testcase == "yes"}
			<input type="submit" name="delete_tc" value="{$tcView_viewer_labels.btn_delete}" />
	  {/if}
	
	  {* Double condition because for test case versions WE DO NOT DISPLAY this
	     button, using $args_can_move_copy='no'
	  *}
	  {if $args_can_do->copy == "yes" && $args_can_move_copy == "yes"}
	   		<input type="submit" name="move_copy_tc"   value="{$tcView_viewer_labels.btn_mv_cp}" />
	     	{$go_newline="<br />"}
	  {/if}
	
	  {if $delete_enabled && $args_can_do->delete_version == "yes" && $args_can_delete_version == "yes"}
			 <input type="submit" name="delete_tc_version" value="{$tcView_viewer_labels.btn_del_this_version}" />
	  {/if}

	 	{if $args_can_do->create_new_version == "yes"}
  		<input type="submit" name="do_create_new_version"   value="{$tcView_viewer_labels.btn_new_version}" />
	  {/if}

	
		{* --------------------------------------------------------------------------------------- *}
		{if $active_status_op_enabled eq 1 && $args_can_do->deactivate=='yes'}
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

  {if $args_can_do->add2tplan == "yes" && $args_has_testplans && 
  	  $args_testcase.enabledOnTestPlanDesign}
  <input type="button" id="addTc2Tplan_{$args_testcase.id}"  name="addTc2Tplan_{$args_testcase.id}" 
         value="{$tcView_viewer_labels.btn_add_to_testplans}" onclick="location='{$hrefAddTc2Tplan}'" />

  {/if}
	</form>
	</span>

	<span>
	<form style="display: inline;" id="tcexport" name="tcexport" method="post" action="{$exportTestCaseAction}" >
		<input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />
		<input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
		<input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
		<input type="submit" name="export_tc" style="margin-left: 3px;" value="{$tcView_viewer_labels.btn_export}" />
		{* 20071102 - franciscom *}
		{*
		<input type="button" name="tstButton" value="{$tcView_viewer_labels.btn_execute_automatic_testcase}"
		       onclick="javascript: startExecution({$args_testcase.testcase_id},'testcase');" />
		*}
	</form>
	</span>
{/if} {* user can edit *}

	<span>
	{* compare versions *}
	{if $args_testcase.version > 1}
	  <form style="display: inline;" id="version_compare" name="version_compare" 
	  		method="post" action="lib/testcases/tcCompareVersions.php">
	 		<input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />
	  		<input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
	  		<input type="submit" name="compare_versions" value="{$tcView_viewer_labels.btn_compare_versions}" />
	  </form>
	{/if}
	</span>
	{* 20110304 - franciscom - BUGID 4286: Option to print single test case  *}
	<span>
	<form style="display: inline;" id="tcprint" name="tcprint" method="post" action="" >
		<input type="button" name="tcPrinterFriendly" style="margin-left: 3px;" value="{$tcView_viewer_labels.btn_print_view}" 
		       onclick="javascript:openPrintPreview('tc',{$args_testcase.testcase_id},{$args_testcase.id},null,
			                                          '{$gui->printTestCaseAction}');"/>
	</form>
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

<form id="stepsControls" name="stepsControls" method="post" action="lib/testcases/tcEdit.php">
  <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />
  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
  <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />

  <input type="hidden" name="has_been_executed" value="{$has_been_executed}" />
  <input type="hidden" name="goback_url" value="{$goBackAction}" />

  <input type="hidden" id="stepsControls_step_id" name="step_id" value="0" />
  <input type="hidden" id="stepsControls_show_mode" name="show_mode" value="{$gui->show_mode}" />
  <input type="hidden" id="stepsControls_doAction" name="doAction" value="" />


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

  <span class="order_info" style='display:none'>
  <input type="submit" name="renumber_step" 
  	 	   onclick="doAction.value='doReorderSteps';{$gui->submitCode};validateStepsReorder('stepsControls');" 
  	 	   value="{$tcView_viewer_labels.btn_reorder_steps}" />
  </span>
  {/if}
</div>
</form>

{if $gui->automationEnabled}
  <div {$addInfoDivStyle}>
		<span class="labelHolder">{$tcView_viewer_labels.execution_type} {$smarty.const.TITLE_SEP}</span>
		{if isset($gui->execution_types[$args_testcase.execution_type])}
		  {$gui->execution_types[$args_testcase.execution_type]}
		{else}
		  Unknown execution type code: {$args_testcase.execution_type}
		{/if}  
	</div>
{/if}

{if $gui->testPriorityEnabled}
   <div {$addInfoDivStyle}>
		<span class="labelHolder">{$tcView_viewer_labels.test_importance} {$smarty.const.TITLE_SEP}</span>
		{$gsmarty_option_importance[$args_testcase.importance]}
	</div>
{/if}
   <div {$addInfoDivStyle}>
		<span class="labelHolder">{$tcView_viewer_labels.status} {$smarty.const.TITLE_SEP}</span>
		{$gui->domainTCStatus[$args_testcase.status]}
	</div>

  {* 20090718 - franciscom *}
	{if $args_cf.standard_location neq ''}
	<div {$addInfoDivStyle}>
        <div id="cfields_design_time" class="custom_field_container">{$args_cf.standard_location}</div>
	</div>
	{/if}

	<div {$addInfoDivStyle}>
		<table cellpadding="0" cellspacing="0" style="font-size:100%;">
			    <tr>
			     	<td width="35%" style="vertical-align:top;"><a href={$gui->keywordsViewHREF}>{$tcView_viewer_labels.keywords}</a>: &nbsp;
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

	{if $gui->opt_requirements == TRUE && $gui->view_req_rights == "yes"}
	<div {$addInfoDivStyle}>
		<table cellpadding="0" cellspacing="0" style="font-size:100%;">
     			  <tr>
       			  <td colspan="{$tableColspan}" style="vertical-align:text-top;"><span><a title="{$tcView_viewer_labels.requirement_spec}" href="{$hrefReqSpecMgmt}"
      				target="mainframe" class="bold">{$tcView_viewer_labels.Requirements}</a>
      				: &nbsp;</span>
      			  </td>
      			  <td>
      				{section name=item loop=$args_reqs}
      					<span onclick="javascript: openLinkedReqWindow({$gui->tproject_id},{$args_reqs[item].id});"
      					style="cursor:  pointer;  color: #059; ">[{$args_reqs[item].req_spec_title|escape}]&nbsp;{$args_reqs[item].req_doc_id|escape}:{$args_reqs[item].title|escape}</span>
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
    <th>{$tlImages.sort_hint}{$tcView_viewer_labels.platform}</th>
    {foreach from=$args_linked_versions item=link2tplan_platform}
      {foreach from=$link2tplan_platform item=link2platform key=tplan_id}
        {foreach from=$link2platform item=version_info}
          <tr>
          <td style="width:10%;text-align:center;">{$version_info.version}</td>
          <td>{$version_info.tplan_name|escape}</td>
          <td>
          {* BUGID 3181 *}
          {if $version_info.platform_id > 0}
            {$gui->platforms[$version_info.platform_id]|escape}
          {/if}          
          </td>
          </tr>
        {/foreach}
      {/foreach}
    {/foreach}
	  </table>
	</div>
{/if}
