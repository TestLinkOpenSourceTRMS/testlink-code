{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: execSetResults.tpl,v 1.22 2006/07/26 08:31:13 franciscom Exp $ *}
{* Purpose: smarty template - show tests to add results *}
{* Revisions:
              20060722 - franciscom - sintax for value on history_on
              20060602 - franciscom - new code for attachments display 
              20060528 - franciscom - $show_last_exec_any_build
*}	

{* {include file="inc_head.tpl" popup='yes'} *}
{include file="inc_head.tpl" popup='yes' openHead='yes'}
<script language="JavaScript" src="gui/javascript/radio_utils.js" type="text/javascript"></script>
</head>

<body>

<h1>	
	<img alt="{lang_get s='help'}" class="help" 
	src="icons/sym_question.gif" style="float: right;"
	onclick="javascript:open_popup('{$helphref}execMain.html');" />
	{lang_get s='title_t_r_on_build'} {$build_name|escape} {lang_get s='title_t_r_owner'} ( {$owner|escape} )
</h1>


{* show echo about update if applicable *}
{$updated}

{* 20051108 - fm - BUGID 00082*}
{assign var="input_enabled_disabled" value="disabled"}
  	
<div id="main_content" class="workBack">
<form method="post">
  {if $map_last_exec eq ""}
     {lang_get s='no_data_available'}
  {else}
      {* -------------------------------------------------------------------------------------- *}
      {* 20060207 - franciscom - BUGID 303
         Added to make Test Results editable only if Current build is latest Build - Tools-R-Us *}
      {* 20051108 - fm - BUGID 00082*}
      {if $rightsEdit == "yes" and $edit_test_results == "yes"}
      	{assign var="input_enabled_disabled" value=""}
        <h1> {lang_get s='bulk_tc_status_management'} </h1>
        {* 20060528 - franciscom - bulk management of test case status *}
        {foreach key=verbose_status item=locale_status from=$gsmarty_tc_status_for_ui}
      	   <input type="button" id="btn_{$verbose_status}" name="btn_{$verbose_status}"
      	          value="{lang_get s='set_all_tc_to'} {lang_get s=$locale_status}"
      	          onclick="javascript:check_all_radios('{$gsmarty_tc_status.$verbose_status}');" />
      	{/foreach}		
        <br />
        <p>
    		  <input type="submit" id="do_bulk_save" name="do_bulk_save" value="{lang_get s='btn_save_all_tests_results'}"/>
        <hr />
    	{/if}
    
    
      <div class="groupBtn">
    		  <input type="button" name="print" value="{lang_get s='btn_print'}" 
    		         onclick="javascript:window.print();" />
    		  <input type="submit" id="toggle_history_on_off" 
    		         name="{$history_status_btn_name}" 
    		         value="{lang_get s=$history_status_btn_name}" />
    		  <input type="hidden" id="history_on" 
    		         name="history_on" value="{$history_on}" />
      </div>
    <hr />

	{/if}
	
	{if $tSuiteAttachments neq null}
		<h2>{lang_get s='testsuite_attachments'}</h2>	
		{include file="inc_attachments.tpl" tableName="nodes_hierarchy" downloadOnly=true 
				 attachmentInfos=$tSuiteAttachments tableClassName="bordered"
				 tableStyles="background-color:#dddddd;width:100%"
		}
	{/if}
		
	{foreach item=tc_exec from=$map_last_exec}
	
	  {assign var="tcversion_id" value=$tc_exec.id}
		<input type='hidden' name='tc_version[{$tcversion_id}]' value='{$tc_exec.testcase_id}' />
		<h2>{lang_get s='th_test_case_id'}{$tc_exec.testcase_id} :: {lang_get s='title_test_case'} {$tc_exec.name|escape}</h2>

		<div id="execution_history" class="exec_history">
		{if $history_on}
		    <h3>{lang_get s='execution_history'}</h3>
		{else}
			<h3>{lang_get s='just_last_execution_for_this_build'}</h3>
		{/if}

		{* The very last execution for any build of this test plan                                                  *}
		{* 20060528 - franciscom *}
		{if $show_last_exec_any_build}
    		{assign var="abs_last_exec" value=$map_last_exec_any_build.$tcversion_id}
        {if $abs_last_exec.status != '' and $abs_last_exec.status != $gsmarty_tc_status.not_run}			
			{assign var="status_code" value=$abs_last_exec.status}
    
   			<div class="{$gsmarty_tc_status_css.$status_code}">
   			{lang_get s='test_exec_last_run_date'} {localize_timestamp ts=$abs_last_exec.execution_ts}
   			{lang_get s='test_exec_by'} {$abs_last_exec.tester_first_name|escape} {$abs_last_exec.tester_last_name|escape} 
   			{lang_get s='test_exec_on_build'} {$abs_last_exec.build_name|escape}: 			
   			{localize_tc_status s=$status_code}
   			</div>
		{else}
    		<div class="not_run">{lang_get s='test_status_not_run'}</div>
    			{lang_get s='tc_not_tested_yet'}
   		{/if}
    {/if}

    {* -------------------------------------------------------------------------------------------------- *}
    {if $other_exec.$tcversion_id}
		  <table cellspacing="0" class="exec_history">
			 <tr>
				<th style="text-align:left">{lang_get s='date_time_run'}</th>
				<th style="text-align:left">{lang_get s='test_exec_by'}</th>
				<th style="text-align:left">{lang_get s='exec_status'}</th>
				<th style="text-align:left">{lang_get s='exec_notes'}</th>
				
				{if $att_model->show_upload_column}
						<th style="text-align:left">{lang_get s='attachment_mgmt'}</th>
        {/if}
        
			 </tr>
			{foreach item=tc_old_exec from=$other_exec.$tcversion_id}
 			<tr style="border-top:1px solid black">
				<td>{localize_timestamp ts=$tc_old_exec.execution_ts}</td>
				<td>{$tc_old_exec.tester_first_name|escape} {$tc_old_exec.tester_last_name|escape}</td> 
				<td>{localize_tc_status s=$tc_old_exec.status}</td>
				<td>{$tc_old_exec.execution_notes|escape}</td>

	        {if $att_model->show_upload_column}
    			<td align="center"><a href="javascript:openFileUploadWindow({$tc_old_exec.execution_id},'executions')">
    			    <img src="icons/upload_16.png" alt="{lang_get s='alt_attachment_mgmt'}" 
    			         style="border:none" /></a>
            </td>
	        {/if}
			</tr>  
			<tr>
			<td colspan="{$att_model->num_cols}">
				{assign var="execID" value=$tc_old_exec.execution_id}

				{assign var="attach_info" value=$attachments[$execID]}
				{include file="inc_attachments.tpl" 
				         attachmentInfos=$attach_info 
				         id=$execID tableName="executions"
				         show_upload_btn=$att_model->show_upload_btn
				         show_title=$att_model->show_title }
			</td>
			</tr>
			{/foreach}
			</table>
		{/if}
  </div> 

  <div>
		<table class="notesBox">
		<tr>
			<td colspan="2" class="title">{lang_get s='test_exec_summary'}</td>
		</tr>
		<tr>
			<td colspan="2">{$tc_exec.summary}</td>
		</tr>
		<tr>
			<td class="title" width="50%">{lang_get s='test_exec_steps'}</td>
			<td class="title" width="50%">{lang_get s='test_exec_expected_r'}</td>
		</tr>
		<tr>
			<td>{$tc_exec.steps}</td>
			<td>{$tc_exec.expected_results}</td>
		</tr>
		<tr>
			<td colspan="2">
			{assign var="tcID" value=$tc_exec.testcase_id}
			{if $tcAttachments[$tcID] neq null}
				{include file="inc_attachments.tpl" tableName="nodes_hierarchy" downloadOnly=true 
						 attachmentInfos=$tcAttachments[$tcID] tableClassName="bordered"
						 tableStyles="background-color:#dddddd;width:100%"
				}
			{/if}
			</td>
		</tr>
		</table>

		<table border="2">
		<tr>
			<td rowspan="2">
				<div class="title">{lang_get s='test_exec_notes'}</div>
				<textarea {$input_enabled_disabled} class="tcDesc" name='notes[{$tcversion_id}]' 
					cols=50 rows=10></textarea>			
			</td>
			<td>			
  				{* status of test *}
  				<div class="title" style="text-align: center;">{lang_get s='test_exec_result'}</div>
  				<div class="resultBox">

              {foreach key=verbose_status item=locale_status from=$gsmarty_tc_status_for_ui}
  						<input type="radio" {$input_enabled_disabled} name="status[{$tcversion_id}]" 
  							value="{$gsmarty_tc_status.$verbose_status}"
  							{if $gsmarty_tc_status.$verbose_status eq $gsmarty_tc_status.not_run}
  							checked="checked" 
  							{/if} />{lang_get s=$locale_status}<br />
  					 {/foreach}		
  					<br />		
  		 			<input type='submit' name='save_results[{$tcversion_id}]' value="{lang_get s='btn_save_tc_exec_results'}" />
  				</div>
  			</td>
  		</tr>
		</table>

	<hr />
	</div>
	{/foreach}
</form>
</div>
</body>
</html>