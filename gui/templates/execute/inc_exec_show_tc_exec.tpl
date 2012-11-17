{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	inc_exec_show_tc_exec.tpl
@internal revisions
@since 1.9.5
20121117 - franciscom - TICKET 5350: Bug Tracking Integration - Create Issue with JUST ONE CLICK
*}	
 	{foreach item=tc_exec from=$gui->map_last_exec}

    {assign var="tc_id" value=$tc_exec.testcase_id}
	  {assign var="tcversion_id" value=$tc_exec.id}
	  {* IMPORTANT:
	               Here we use version_number, which is related to tcversion_id SPECIFICATION.
	               When we need to display executed version number, we use tcversion_number
	  *}
	  {assign var="version_number" value=$tc_exec.version}
	  
		<input type='hidden' name='tc_version[{$tcversion_id}]' value='{$tc_id}' />
		<input type='hidden' name='version_number[{$tcversion_id}]' value='{$version_number}' />

    {* ------------------------------------------------------------------------------------ *}
    {lang_get s='th_testsuite' var='container_title'}
    {assign var="div_id" value=tsdetails_$tc_id}
    {assign var="memstatus_id" value=tsdetails_view_status_$tc_id}
    {assign var="ts_name"  value=$tsuite_info[$tc_id].tsuite_name}
    {assign var="container_title" value="$container_title$title_sep$ts_name"}

	{* TICKET 5128,5129,5130 *}
	{assign var="can_delete_exec" value=0}
	{assign var="can_edit_exec_notes" value=$gui->grants->edit_exec_notes}
	{assign var="can_manage_attachments" value=$gsmarty_attachments->enabled}
	{if $tc_exec.can_be_executed}
		{if $gui->grants->delete_execution}
			{assign var="can_delete_exec" value=1}
		{/if}
	{else}
		{assign var="can_edit_exec_notes" value=0}
		{assign var="can_manage_attachments" value=0}
	{/if}

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
		  {$tsuite_info[$tc_id].details}
		  </div>

		  {if $ts_cf_smarty[$tc_id] != ''}
		    <br />
		    <div class="custom_field_container" style="border-color:black;width:95%;">
         {$ts_cf_smarty[$tc_id]}
        </div>
		  {/if}

  		{if $gui->tSuiteAttachments != null && $gui->tSuiteAttachments[$tc_exec.tsuite_id] != null}
  		  <br />
		    {include file="inc_attachments.tpl" 
		             attach_tableName="nodes_hierarchy" 
		             attach_downloadOnly=true
			        	 attach_attachmentInfos=$gui->tSuiteAttachments[$tc_exec.tsuite_id]
			        	 attach_inheritStyle=1
			        	 attach_tableClassName="none"
				         attach_tableStyles="background-color:#ffffcc;width:100%"}
	    {/if}
	    <br />
    </div>


		<div class="exec_tc_title">
		{if $gui->grants->edit_testcase}
			<a href="javascript:openTCaseWindow({$tc_exec.testcase_id},{$tc_exec.id},'editOnExec')">
			<img src="{$smarty.const.TL_THEME_IMG_DIR}/note_edit.png"  title="{$labels.show_tcase_spec}">
			</a>
		{/if}
		
    {$labels.title_test_case}&nbsp;{$labels.th_test_case_id}{$gui->tcasePrefix|escape}{$cfg->testcase_cfg->glue_character}{$tc_exec.tc_external_id|escape} :: {$labels.version}: {$tc_exec.version}
		<br />
		    {$tc_exec.name|escape}<br />
		    {if $tc_exec.assigned_user == ''}
		      {$labels.has_no_assignment}
		    {else}
          {$labels.assigned_to}{$title_sep}{$tc_exec.assigned_user|escape}
        {/if}
    </div>

   	{assign var="drawNotRun" value=0}
 	{if $cfg->exec_cfg->show_last_exec_any_build}
   		{assign var="abs_last_exec" value=$gui->map_last_exec_any_build.$tcversion_id}
 		{assign var="my_build_name" value=$abs_last_exec.build_name|escape}
 		{assign var="show_current_build" value=1}
 		
 		{* this happens when test case has been never run *}
 		{if $my_build_name == ''}
 			{assign var="my_build_name" value=$gui->build_name|escape}
   			{assign var="drawNotRun" value=1}
 		{/if}
    {/if}
    {assign var="exec_build_title" value="$build_title $title_sep $my_build_name"}


		<div id="execution_history" class="exec_history">
  		<div class="exec_history_title">
  		{if $gui->history_on}
  		    {$labels.execution_history} {$title_sep_type3}
  		    {if !$cfg->exec_cfg->show_history_all_builds}
  		      {$exec_build_title}
  		    {/if}
  		{else}
  			  {$labels.last_execution}
  			  {if $show_current_build} {$labels.exec_any_build} {/if}
  			  {$title_sep_type3} {$exec_build_title}
  		{/if}
  		</div>

    {* NUEVO *}
		{if $gui->issueTrackerIntegrationOn}
		  <p/>
			<a style="font-weight:normal" target="_blank" href="{$gui->createIssueURL}">
			{$gui->accessToIssueTracker|escape}</a>
		{/if}

		{* The very last execution for any build of this test plan *}
		{if $cfg->exec_cfg->show_last_exec_any_build && $gui->history_on == 0}
           {if $abs_last_exec.status != '' and $abs_last_exec.status != $tlCfg->results.status_code.not_run}
			    {assign var="status_code" value=$abs_last_exec.status}
     			<div class="{$tlCfg->results.code_status.$status_code}">
     			{$labels.date_time_run} {$title_sep} {localize_timestamp ts=$abs_last_exec.execution_ts}
     			{$title_sep_type3}
     			{$labels.test_exec_by} {$title_sep} 
  				
  				{if isset($users[$abs_last_exec.tester_id])}
  				  {$users[$abs_last_exec.tester_id]->getDisplayName()|escape}
  				{else}
  				  {assign var="deletedTester" value=$abs_last_exec.tester_id}
            	  {assign var="deletedUserString" value=$labels.deleted_user|replace:"%s":$deletedTester}
            	  {$deletedUserString}
  				{/if}  
     			
     			{$title_sep_type3}
     			{$labels.build}{$title_sep} {$abs_last_exec.build_name|escape}
     			{$title_sep_type3}
     			{$labels.exec_status} {$title_sep} {localize_tc_status s=$status_code}
     			</div>
  		  {else}
            	{assign var="drawNotRun" value=1}
   		  {/if}
     {/if}
	 
	 {if $drawNotRun }
	 	<div class="not_run">{$labels.test_status_not_run}</div>
    	{$labels.tc_not_tested_yet}
   	
	 {/if}
     
     

    {* -------------------------------------------------------------------------------------------------- *}
    {if $gui->other_execs.$tcversion_id}
      {assign var="my_colspan" value=$attachment_model->num_cols}
      
      {if $gui->history_on == 0 && $show_current_build}
   		   <div class="exec_history_title">
  			    {$labels.last_execution} {$labels.exec_current_build}
  			    {$title_sep_type3} {$exec_build_title}
  			 </div>
		  {/if}

		  <table cellspacing="0" class="exec_history">
			 <tr>
				<th style="text-align:left">{$labels.date_time_run}</th>
        
				{if $gui->history_on == 0 || $cfg->exec_cfg->show_history_all_builds}
				  <th style="text-align:left">{$labels.build}</th>
				{/if}
				{if $gui->has_platforms && 
				    ($gui->history_on == 0 || $cfg->exec_cfg->show_history_all_platforms)}
					{assign var="my_colspan" value=$my_colspan+1}
				  <th style="text-align:left">{$labels.platform}</th>
				{/if}
				<th style="text-align:left">{$labels.test_exec_by}</th>
				<th style="text-align:center">{$labels.exec_status}</th>
				<th style="text-align:center">{$labels.testcaseversion}</th>
				
				{* show attachments column even if all builds are closed *}
				{if $attachment_model->show_upload_column && $can_manage_attachments}
						<th style="text-align:center">{$labels.attachment_mgmt}</th>
				{else}		
            {assign var="my_colspan" value=$my_colspan-1}
        {/if}

				{if $gui->issueTrackerIntegrationOn}
          <th style="text-align:left">{$labels.bug_mgmt}</th>
          {assign var="my_colspan" value=$my_colspan+1}
        {/if}

		{* TICKET 5128 $gui->grants->delete_execution *}
		{if $can_delete_exec}
          <th style="text-align:left">{$labels.delete}</th>
          {assign var="my_colspan" value=$my_colspan+1}
        {/if}

        <th style="text-align:left">{$labels.run_mode}</th>
        {assign var="my_colspan" value=$my_colspan+2}
			 </tr>

			{* ----------------------------------------------------------------------------------- *}
			{foreach item=tc_old_exec from=$gui->other_execs.$tcversion_id}
  	     {assign var="tc_status_code" value=$tc_old_exec.status}
			{cycle values='#eeeeee,#d0d0d0' assign="bg_color"}
			<tr style="border-top:1px solid black; background-color: {$bg_color}">
  			  <td>
          {* Check also that Build is Open *}
  			  {if $can_edit_exec_notes && $tc_old_exec.build_is_open}
  		      <img src="{$smarty.const.TL_THEME_IMG_DIR}/note_edit.png" style="vertical-align:middle" 
  		           title="{$labels.edit_execution}" onclick="javascript: openExecEditWindow(
  		           {$tc_old_exec.execution_id},{$tc_old_exec.id},{$gui->tplan_id},{$gui->tproject_id});">
  		      {else}
  		         {if $can_edit_exec_notes}
  		            <img src="{$smarty.const.TL_THEME_IMG_DIR}/note_edit_greyed.png" 
  		                 style="vertical-align:middle" title="{$labels.closed_build}">
  		         {/if}
 			  {/if}
  			  {localize_timestamp ts=$tc_old_exec.execution_ts}
  			  </td>
				  {if $gui->history_on == 0 || $cfg->exec_cfg->show_history_all_builds}
  				<td>{if !$tc_old_exec.build_is_open}
  				    <img src="{$smarty.const.TL_THEME_IMG_DIR}/lock.png" title="{$labels.closed_build}">{/if}
  				    {$tc_old_exec.build_name|escape}
  				</td>
  				{/if}

				  {if $gui->has_platforms && 
				      ($gui->history_on == 0 || $cfg->exec_cfg->show_history_all_platforms)}
  				  <td>
					  {$tc_old_exec.platform_name}
  				  </td>
  				{/if}

  				<td>
  				{if isset($users[$tc_old_exec.tester_id])}
  				  {$users[$tc_old_exec.tester_id]->getDisplayName()|escape}
  				{else}
  				  {assign var="deletedTester" value=$tc_old_exec.tester_id}
            {assign var="deletedUserString" value=$labels.deleted_user|replace:"%s":$deletedTester}
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

		  {* adjusted if statement to show executions properly if execution history was configured *}
          {if ($attachment_model->show_upload_column && !$att_download_only && 
          	   $tc_old_exec.build_is_open && $can_manage_attachments) || 
          	   ($attachment_model->show_upload_column && $gui->history_on == 1 && 
          	    $tc_old_exec.build_is_open && $can_manage_attachments)}
      			  <td align="center"><a href="javascript:openFileUploadWindow({$tc_old_exec.execution_id},'executions')">
      			    <img src="{$smarty.const.TL_THEME_IMG_DIR}/upload_16.png" title="{$labels.alt_attachment_mgmt}"
      			         alt="{$labels.alt_attachment_mgmt}"
      			         style="border:none" /></a>
              </td>
			  {else}
			  	{if $attachment_model->show_upload_column && $can_manage_attachments}
					<td align="center">
						<img src="{$smarty.const.TL_THEME_IMG_DIR}/upload_16_greyed.png" title="{$labels.closed_build}">
					</td>
				{/if}
  	      	  {/if}
				
    			{if $gui->issueTrackerIntegrationOn}
       		  <td align="center">
       		  {if $tc_old_exec.build_is_open}
       		    <a href="javascript:open_bug_add_window({$gui->tproject_id},{$tc_old_exec.id},{$tc_old_exec.execution_id},'link')">
       		    <img src="{$tlImages.bug_link_tl_to_bts}" title="{$labels.bug_link_tl_to_bts}" style="border:none" /></a>
       		    &nbsp;&nbsp;
              {if $gui->tlCanCreateIssue}
       		  	  <a href="javascript:open_bug_add_window({$gui->tproject_id},{$tc_old_exec.id},{$tc_old_exec.execution_id},'create')">
      			    <img src="{$tlImages.bug_create_into_bts}" title="{$labels.bug_create_into_bts}" style="border:none" /></a>
              {/if}
       		  {else}
       		    <img src="{$tlImages.bug_link_tl_to_bts_disabled}" title="{$labels.bug_link_tl_to_bts}" style="border:none" /></a>
       		    &nbsp;&nbsp;
              {if $gui->tlCanCreateIssue}
       		  	  <img src="{$tlImages.bug_create_into_bts_disabled}" title="{$labels.bug_create_into_bts}" style="border:none" /></a>
              {/if}
            {/if} 
       		  </td>
          {/if}

				{* TICKET 3587, 5128 *}
    			{* if $gui->grants->delete_execution && $tc_old_exec.build_is_open *}
    			{if $can_delete_exec && $tc_old_exec.build_is_open}
	       		  	<td align="center">
    	         	<a href="javascript:confirm_and_submit(msg,'execSetResults','exec_to_delete',
             	                                       {$tc_old_exec.execution_id},'do_delete',1);">
      			    <img src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png" title="{$labels.img_title_delete_execution}"
      			         style="border:none" /></a>
      			 </td>
      			{else}
      				{if $can_delete_execution}
      					<td align="center">
      						<img src="{$smarty.const.TL_THEME_IMG_DIR}/trash_greyed.png" title="{$labels.closed_build}">
      					</td>
      				{/if}
          		{/if}

       		<td class="icon_cell" align="center">
       		  {if $tc_old_exec.execution_run_type == $smarty.const.TESTCASE_EXECUTION_TYPE_MANUAL}
      		    <img src="{$smarty.const.TL_THEME_IMG_DIR}/user.png" title="{$labels.execution_type_manual}"
      		            style="border:none" />
       		  {else}
      		    <img src="{$smarty.const.TL_THEME_IMG_DIR}/bullet_wrench.png" title="{$labels.execution_type_auto}"
      		            style="border:none" />
       		  {/if}
          </td>



  			</tr>
 			  {if $tc_old_exec.execution_notes neq ""}
  			<script>
		{*  BUGID 3522
		Initialize panel if notes exists. There might be multiple note panels
		visible at the same time, so we need to collect those init functions in
		an array and execute them from Ext.onReady(). See execSetResults.tpl *}
        {literal}
        var panel_init = function(){
            var p = new Ext.Panel({
            title: {/literal}'{$labels.exec_notes}'{literal},
            collapsible:true,
            collapsed: true,
            baseCls: 'x-tl-panel',
            renderTo: {/literal}'exec_notes_container_{$tc_old_exec.execution_id}'{literal},
            width:'100%',
            html:''
            });

            p.on({'expand' : function(){load_notes(this,{/literal}{$tc_old_exec.execution_id}{literal});}});
        };
        panel_init_functions.push(panel_init);
        {/literal}

  			</script>
			<tr style="background-color: {$bg_color}">
  			 <td colspan="{$my_colspan}" id="exec_notes_container_{$tc_old_exec.execution_id}"
  			     style="padding:5px 5px 5px 5px;">
  			 </td>
   			</tr>
 			  {/if}

  			{* Custom field values  *}
			<tr style="background-color: {$bg_color}">
  			<td colspan="{$my_colspan}">
  				{assign var="execID" value=$tc_old_exec.execution_id}
  				{assign var="cf_value_info" value=$gui->other_exec_cfields[$execID]}
          {$cf_value_info}
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
   				{include file="inc_show_bug_table.tpl"
   			         	 bugs_map=$gui->bugs[$execID]
   			         	 can_delete=$tc_old_exec.build_is_open
   			         	 exec_id=$execID}
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
