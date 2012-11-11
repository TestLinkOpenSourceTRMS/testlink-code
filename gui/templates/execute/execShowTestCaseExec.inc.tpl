{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filespurce	execShowTestCaseExec.inc.tpl

@internal revisions
*}	
{foreach item=tc_exec from=$gui->map_last_exec}

    {$tc_id = $tc_exec.testcase_id}
	  {$tcversion_id = $tc_exec.id}
	  {* IMPORTANT:
	               Here we use version_number, which is related to tcversion_id SPECIFICATION.
	               When we need to display executed version number, we use tcversion_number
	  *}
	  {$version_number = $tc_exec.version}
	  
		<input type='hidden' name='tc_version[{$tcversion_id}]' value='{$tc_id}' />
		<input type='hidden' name='version_number[{$tcversion_id}]' value='{$version_number}' />

    {* ------------------------------------------------------------------------------------ *}
    {lang_get s='th_testsuite' var='container_title'}
    {$div_id = "tsdetails_$tc_id"}
    {$memstatus_id = "tsdetails_view_status_$tc_id"}
    {$ts_name = $gui->tsuite_info[$tc_id].tsuite_name}
    {$container_title = "$container_title$title_sep$ts_name"}

    {include file="inc_show_hide_mgmt.tpl"
             show_hide_container_title=$container_title
             show_hide_container_id=$div_id
             show_hide_container_draw=false
             show_hide_container_class='exec_additional_info'
             show_hide_container_view_status_id=$memstatus_id}

		<div id="{$div_id}" name="{$div_id}" class="exec_additional_info">
      <br />
      <div class="exec_testsuite_details" style="width:95%;">
      <span class="legend_container">{$labels.details}</span><br />
		  {$gui->tsuite_info[$tc_id].details}
		  </div>

		  {if $gui->ts_cf_smarty[$tc_id] != ''}
		    <br />
		    <div class="custom_field_container" style="border-color:black;width:95%;">
         {$ts_cf_smarty[$tc_id]}
        </div>
		  {/if}

  		{if $gui->tSuiteAttachments != null && $gui->tSuiteAttachments[$tc_exec.tsuite_id] != null}
  		  <br />
		    {include file="inc_attachments.tpl" 
		             attach_tableName = "nodes_hierarchy" 
		             attach_downloadOnly = true
			        	 attach_attachmentInfos = $gui->tSuiteAttachments[$tc_exec.tsuite_id]
			        	 attach_inheritStyle=1
			        	 attach_tableClassName="none"
				         attach_tableStyles="background-color:#ffffcc;width:100%"}
	    {/if}
	    <br />
    </div>


		<div class="exec_tc_title">
		{if $gui->grants->edit_testcase}
		<a href="javascript:openTCaseWindow({$gui->tproject_id},{$tc_exec.testcase_id},{$tc_exec.id},'editOnExec')">
		<img src="{$tlImages.note_edit}"  title="{$labels.show_tcase_spec}">
		</a>
		{/if}
		
    {$labels.title_test_case}&nbsp;{$labels.th_test_case_id}{$gui->tcasePrefix|escape}{$gui->cfg->testcase_cfg->glue_character}{$tc_exec.tc_external_id|escape} :: {$labels.version}: {$tc_exec.version}
		<br />
		    {$tc_exec.name|escape}<br />
		    {if $tc_exec.assigned_user == ''}
		      {$labels.has_no_assignment}
		    {else}
          {$labels.assigned_to}{$title_sep}{$tc_exec.assigned_user|escape}
        {/if}
    </div>

 		{if $gui->cfg->exec_cfg->show_last_exec_any_build}
   		{$abs_last_exec = $gui->map_last_exec_any_build.$tcversion_id}
 		  {$my_build_name = $abs_last_exec.build_name|escape}
 		  {$show_current_build = 1}
    {/if}
    {$exec_build_title = "$build_title $title_sep $my_build_name"}


		<div id="execution_history" class="exec_history">
  		<div class="exec_history_title">
  		{if $gui->history_on}
  		    {$labels.execution_history} {$title_sep_type3}
  		    {if !$gui->cfg->exec_cfg->show_history_all_builds}
  		      {$exec_build_title}
  		    {/if}
  		{else}
  			  {$labels.last_execution}
  			  {if $show_current_build} {$labels.exec_any_build} {/if}
  			  {$title_sep_type3} {$exec_build_title}
  		{/if}
  		</div>

		{* The very last execution for any build of this test plan *}
		{if $gui->cfg->exec_cfg->show_last_exec_any_build && $gui->history_on==0}
        {if $abs_last_exec.status != '' and $abs_last_exec.status != $tlCfg->results.status_code.not_run}
			    {$status_code = $abs_last_exec.status}
     			<div class="{$tlCfg->results.code_status.$status_code}">
     			{$labels.date_time_run} {$title_sep} {localize_timestamp ts=$abs_last_exec.execution_ts}
     			{$title_sep_type3}
     			{$labels.test_exec_by} {$title_sep} 
  				{if isset($gui->users[$abs_last_exec.tester_id])}
  				  {$gui->users[$abs_last_exec.tester_id]->getDisplayName()|escape}
  				{else}
  				  {$deletedTester = $abs_last_exec.tester_id}
            {$deletedUserString = $labels.deleted_user|replace:"%s":$deletedTester}
            {$deletedUserString}
  				{/if}  
     			{$title_sep_type3}
     			{$labels.build}{$title_sep} {$abs_last_exec.build_name|escape}
     			{$title_sep_type3}
     			{$labels.exec_status} {$title_sep} {localize_tc_status s=$status_code}
     			</div>

  		  {else}
    		   <div class="not_run">{$labels.test_status_not_run}</div>
    			   {$labels.tc_not_tested_yet}
   		  {/if}
    {/if}

    {* -------------------------------------------------------------------------------------------------- *}
    {if $gui->other_execs.$tcversion_id}
      {$my_colspan = $attachment_model->num_cols}
      {if $gui->history_on == 0 && $show_current_build}
   		   <div class="exec_history_title">
  			    {$labels.last_execution} {$labels.exec_current_build}
  			    {$title_sep_type3} {$exec_build_title}
  			 </div>
		  {/if}

		  <table cellspacing="0" class="exec_history">
			 <tr>
				<th style="text-align:left">{$labels.date_time_run}</th>
        
				{if $gui->showBuildColumn}
				  <th style="text-align:left">{$labels.build}</th>
				{/if}
				{if $gui->showPlatformColumn}
					{$my_colspan = $my_colspan+1}
				  <th style="text-align:left">{$labels.platform}</th>
				{/if}
				<th style="text-align:left">{$labels.test_exec_by}</th>
				<th style="text-align:center">{$labels.exec_status}</th>
				<th style="text-align:center">{$labels.testcaseversion}</th>
				
				{* show attachments column even if all builds are closed *}
				{if $attachment_model->show_upload_column && $gsmarty_attachments->enabled}
						<th style="text-align:center">{$labels.attachment_mgmt}</th>
				{else}		
            {$my_colspan = $my_colspan-1}
        {/if}

				{if $gui->issueTrackerIntegrationOn}
          <th style="text-align:left">{$labels.bug_mgmt}</th>
          {$my_colspan = $my_colspan+1}
        {/if}

				{if $gui->grants->delete_execution}
          <th style="text-align:left">{$labels.delete}</th>
          {$my_colspan = $my_colspan+1}
        {/if}

        <th style="text-align:left">{$labels.run_mode}</th>
        {$my_colspan = $my_colspan+2}
			 </tr>

			{* ----------------------------------------------------------------------------------- *}
			{foreach item=tc_old_exec from=$gui->other_execs.$tcversion_id}
  	    {$tc_status_code = $tc_old_exec.status}
			  {cycle values='#eeeeee,#d0d0d0' assign="bg_color"}
			<tr style="border-top:1px solid black; background-color: {$bg_color}">
  			  <td>
          {* Check also that Build is Open *}
  			  {if $gui->grants->edit_exec_notes && $tc_old_exec.build_is_open}
  		      <img src="{$tlImages.note_edit}" style="vertical-align:middle" 
  		           title="{$labels.edit_execution}" onclick="javascript: openExecEditWindow(
  		           {$tc_old_exec.execution_id},{$tc_old_exec.id},{$gui->tplan_id},{$gui->tproject_id});">
  		      {else}
  		         {if $gui->grants->edit_exec_notes}
  		            <img src="{$tlImages.note_edit_greyed}" 
  		                 style="vertical-align:middle" title="{$labels.closed_build}">
  		         {/if}
 			  {/if}
  			  {localize_timestamp ts=$tc_old_exec.execution_ts}
  			  </td>
				  {if $gui->showBuildColumn}
  				<td>{if !$tc_old_exec.build_is_open}
  				    <img src="{$tlImages.lock}" title="{$labels.closed_build}">{/if}
  				    {$tc_old_exec.build_name|escape}
  				</td>
  				{/if}

				  {if $gui->showPlatformColumn}
  				  <td>
					  {$tc_old_exec.platform_name}
  				  </td>
  				{/if}

  				<td>
  				{if isset($gui->users[$tc_old_exec.tester_id])}
  				  {$gui->users[$tc_old_exec.tester_id]->getDisplayName()|escape}
  				{else}
  				  {$deletedTester = $tc_old_exec.tester_id}
            {$deletedUserString = $labels.deleted_user|replace:"%s":$deletedTester}
            {$deletedUserString}
  				{/if}  
  				</td>
  				<td class="{$tlCfg->results.code_status.$tc_status_code}" style="text-align:center">
  				    {localize_tc_status s=$tc_old_exec.status}
  				</td>
  				
  		   {* IMPORTANT:
	               Here we use tcversion_number because we want to display
	               version number used when this execution was recorded.
      	  *}

  				<td  style="text-align:center">{$tc_old_exec.tcversion_number}</td>
          {if ($attachment_model->show_upload_column && !$att_download_only && $tc_old_exec.build_is_open 
               && $gsmarty_attachments->enabled) || ($attachment_model->show_upload_column && $gui->history_on == 1 
               && $tc_old_exec.build_is_open && $gsmarty_attachments->enabled)}
      			  <td align="center"><a href="javascript:openFileUploadWindow({$tc_old_exec.execution_id},'executions')">
      			    <img src="{$tlImages.attachMgmt}" title="{$labels.alt_attachment_mgmt}"
      			         alt="{$labels.alt_attachment_mgmt}"
      			         style="border:none" /></a>
              </td>
			  {else}
			  	{if $attachment_model->show_upload_column && $gsmarty_attachments->enabled}
					<td align="center">
						<img src="{$tlImages.attachMgmtGreyed}" title="{$labels.closed_build}">
					</td>
				{/if}
  	      	  {/if}
				
    			{if $gui->issueTrackerIntegrationOn && $tc_old_exec.build_is_open}
       		  	<td align="center"><a href="javascript:open_bug_add_window({$tc_old_exec.execution_id})">
      			    <img src="{$tlImages.bugMgmt}" title="{$labels.img_title_bug_mgmt}"
      			         style="border:none" /></a>
                </td>
                {else}
                	{if $gui->issueTrackerIntegrationOn}
                		<td align="center">
							<img src="{$tlImages.bugMgmtGreyed}" title="{$labels.closed_build}">
						</td>
                	{/if}
          		{/if}
    			{if $gui->grants->delete_execution && $tc_old_exec.build_is_open}
       		  	<td align="center">
             	<a href="javascript:confirm_and_submit(msg,'execSetResults','exec_to_delete',
             	                                       {$tc_old_exec.execution_id},'do_delete',1);">
      			    <img src="{$tlImages.delete}" title="{$labels.img_title_delete_execution}"
      			         style="border:none" /></a>
      			 </td>
      			{else}
      				{if $gui->grants->delete_execution}
      					<td align="center">
      						<img src="{$tlImages.delete_disabled}" title="{$labels.closed_build}">
      					</td>
      				{/if}
          		{/if}

       		<td class="icon_cell" align="center">
     		    <img src="{$tc_old_exec.execution_run_type_icon}" 
      		       title="{$tc_old_exec.execution_run_type_label|escape}" style="border:none" />
          </td>



  			</tr>
 			  {if $tc_old_exec.execution_notes neq ""}
  			<script>
		    {* ------------------------------------------------------------------------- *} 
		    {*  Initialize panel if notes exists.                                        *}
		    {*  There might be multiple note panels visible at the same time,            *}
		    {*  so we need to collect those init functions in an array and execute them  *}
		    {*  from Ext.onReady(). See execSetResults.tpl                               *}
		    {* ------------------------------------------------------------------------- *}
        var panel_init = function(){
            var p = new Ext.Panel({
            title: '{$labels.exec_notes}',
            collapsible:true,
            collapsed: true,
            baseCls: 'x-tl-panel',
            renderTo: 'exec_notes_container_{$tc_old_exec.execution_id}',
            width:'100%',
            html:''
            });

            p.on({ 'expand':function(){ load_notes(this,{$tc_old_exec.execution_id}); } });
        };
        panel_init_functions.push(panel_init);
  			</script>

			<tr style="background-color: {$bg_color}">
  			 <td colspan="{$my_colspan}" id="exec_notes_container_{$tc_old_exec.execution_id}"
  			     style="padding:5px 5px 5px 5px;">
  			 </td>
   			</tr>
 			  {/if}

			<tr style="background-color: {$bg_color}">
  			<td colspan="{$my_colspan}">
  				{$execID = $tc_old_exec.execution_id}
  				{*
  				  {$cf_value_info = $gui->other_exec_cfields[$execID]}
            {$cf_value_info}
          *}
          {$gui->other_exec_cfields[$execID]}
  			</td>
  			</tr>



			{* Attachments *}
			<tr style="background-color: {$bg_color}">
  			<td colspan="{$my_colspan}">
  				{assign var="execID" value=$tc_old_exec.execution_id}

  				{assign var="attach_info" value=$gui->attachments[$execID]}
  				{include file="inc_attachments.tpl"
  				         attach_attachmentInfos=$attach_info
  				         attach_id=$execID 
  				         attach_tableName="executions"
  				         attach_show_upload_btn=$attachment_model->show_upload_btn
  				         attach_show_title=$attachment_model->show_title
  				         attach_downloadOnly=$att_download_only 
  				         attach_tableClassName=null
                   attach_inheritStyle=0
                   attach_tableStyles=null}
  			</td>
  			</tr>

        {* Execution Bugs (if any) *}
        {if isset($gui->bugs[$execID])}
		    <tr style="background-color: {$bg_color}">
   			<td colspan="{$my_colspan}">
   				{include file="inc_show_bug_table.tpl" bugs_map=$gui->bugs[$execID]
   			         can_delete = $tc_old_exec.build_is_open exec_id=$execID}
   			</td>
   		</tr>
   		{/if}
		{/foreach}
			{* ----------------------------------------------------------------------------------- *}

			</table>
		{/if}
  </div>

  <br />
  {* ----------------------------------------------------------------------------------- *}
  <div>
    {include file="execute/inc_exec_test_spec.tpl"
             args_tc_exec=$tc_exec
             args_labels=$labels
             args_enable_custom_field=$enable_custom_fields
             args_execution_time_cf=$gui->execution_time_cfields
             args_design_time_cf=$gui->design_time_cfields
             args_testplan_design_time_cf=$gui->testplan_design_time_cfields
             args_execution_types=$gui->execution_types
             args_tcAttachments=$gui->tcAttachments
	           args_req_details=$gui->req_details
	           args_cfg=$cfg}

    {if $tc_exec.can_be_executed}
      {include file="execute/inc_exec_controls.tpl"
               args_save_type='single'
               args_input_enable_mgmt=$input_enabled_disabled
               args_tcversion_id=$tcversion_id
               args_webeditor=$gui->exec_notes_editors[$tc_id]
               args_labels=$labels}
	  {/if}
 	  {if $tc_exec.active eq 0}
 	   <h1 class="title"><center>{$labels.testcase_version_is_inactive_on_exec}</center></h1>
 	  {/if}
	<hr />
	</div>
  {* ----------------------------------------------------------------------------------- *}

	{/foreach}