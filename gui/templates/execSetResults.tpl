{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: execSetResults.tpl,v 1.33 2006/12/24 11:48:18 franciscom Exp $
Purpose: smarty template - show tests to add results
Revisions:
          20061112 - franciscom - added class management to assign
                                  color to status cells
*}	

{include file="inc_head.tpl" popup='yes' openHead='yes'}
<script language="JavaScript" src="gui/javascript/radio_utils.js" type="text/javascript"></script>

<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
</head>

<body onLoad="show_hide('tplan_notes','tpn_view_status',{$tpn_view_status});
              show_hide('build_notes','bn_view_status',{$bn_view_status});
              show_hide('bulk_controls','bc_view_status',{$bc_view_status});
              multiple_show_hide('{$tsd_div_id_list}','{$tsd_hidden_id_list}',
                                 '{$tsd_val_for_hidden_list}');">

<h1>	
	<img alt="{lang_get s='help'}" class="help" 
	src="icons/sym_question.gif" style="float: right;"
	onclick="javascript:open_popup('{$helphref}execMain.html');" />
	{lang_get s='title_t_r_on_build'} {$build_name|escape} 
	
	{if $ownerDisplayName != ""}{lang_get s='title_t_r_owner'} ( {$ownerDisplayName|escape} ) {/if}
</h1>


{* show echo about update if applicable *}
{$updated}
{assign var="input_enabled_disabled" value="disabled"}
  	
<div id="main_content" class="workBack">

  <div class="show_hide_title">
    <img src="icons/icon-foldout.gif" border="0" alt="{lang_get s='show_hide'}" 
         title="{lang_get s='show_hide'}" 
         onclick="show_hide('tplan_notes','tpn_view_status',
                            document.getElementById('tplan_notes').style.display=='none')" />
    {lang_get s='test_plan_notes'}
  </div>
  <div id="tplan_notes"  class="notes">
  {$tplan_notes}
  </div>

<div class="show_hide_title">
<img src="icons/icon-foldout.gif" border="0" alt="{lang_get s='show_hide'}" 
     title="{lang_get s='show_hide'}" 
     onclick="show_hide('build_notes','bn_view_status',
                        document.getElementById('build_notes').style.display=='none')" />
{lang_get s='builds_notes'}
</div>
<div id="build_notes" class="notes">
{$build_notes}
</div>


<form method="post">
  {* franciscom - implementation note - 
     1. function of these inputs save the status when user saves executiosn.
     2. value is setted via javascript using the body onload event
     3. the same concepts applies to the hidden inputs tsdetails_view_status.
  *}   
  <input type="hidden" id="tpn_view_status" 
                       name="tpn_view_status" 
                       value="0" />
  <input type="hidden" id="bn_view_status" 
                       name="bn_view_status" 
                       value="0" />
  <input type="hidden" id="bc_view_status" 
                       name="bc_view_status" 
                       value="0" />
  
  {if $map_last_exec eq ""}
     <div class="warning_message" style="text-align:center"> {lang_get s='no_data_available'}</div>
  {else}
      {*  Added to make Test Results editable only if Current build is latest Build - Tools-R-Us *}
      {if $rightsEdit == "yes" and $edit_test_results == "yes"}
        {assign var="input_enabled_disabled" value=""}

        <div class="show_hide_title">
        <img src="icons/icon-foldout.gif" border="0" alt="{lang_get s='show_hide'}" 
            title="{lang_get s='show_hide'}" 
            onclick="show_hide('bulk_controls','bc_view_status',
                               document.getElementById('bulk_controls').style.display=='none')" />
        {lang_get s='bulk_tc_status_management'} </div>
 
        
        <div id="bulk_controls" name="bulk_controls">
        	{foreach key=verbose_status item=locale_status from=$gsmarty_tc_status_for_ui}
        	   <input type="button" id="btn_{$verbose_status}" name="btn_{$verbose_status}"
        	          value="{lang_get s='set_all_tc_to'} {lang_get s=$locale_status}"
        	          onclick="javascript:check_all_radios('{$gsmarty_tc_status.$verbose_status}');" />
        	{/foreach}		
          <br />
          <p>
      		  <input type="submit" id="do_bulk_save" name="do_bulk_save" 
      		         value="{lang_get s='btn_save_all_tests_results'}"/>
          <hr />
        </div>
    	{/if}
    
      <hr />
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

  	{foreach item=tc_exec from=$map_last_exec}
	
	  {assign var="tcversion_id" value=$tc_exec.id}
		<input type='hidden' name='tc_version[{$tcversion_id}]' value='{$tc_exec.testcase_id}' />
		<input type='hidden' id="tsdetails_view_status_{$tc_exec.testcase_id}" 
		                     name="tsdetails_view_status_{$tc_exec.testcase_id}"  value="0" />

		<div class="show_hide_title">
		<img src="icons/icon-foldout.gif" border="0" alt="{lang_get s='show_hide'}" 
             title="{lang_get s='show_hide'}" 
             onclick="show_hide('tsdetails_{$tc_exec.testcase_id}',
                                'tsdetails_view_status_{$tc_exec.testcase_id}',
                                document.getElementById('tsdetails_{$tc_exec.testcase_id}').style.display=='none')" />
    
		{lang_get s='th_testsuite'} {$tsuite_info[$tc_exec.testcase_id].tsuite_name|escape}
		</div>

		<div id="tsdetails_{$tc_exec.testcase_id}" name="tsdetails_{$tc_exec.testcase_id}" class="notes">
		{$tsuite_info[$tc_exec.testcase_id].details}
  		{if $tSuiteAttachments[$tc_exec.tsuite_id] neq null}
  		<p>
		  {include file="inc_attachments.tpl" tableName="nodes_hierarchy" downloadOnly=true 
			      	 attachmentInfos=$tSuiteAttachments[$tc_exec.tsuite_id] 
			      	 tableClassName="bordered"
				       tableStyles="background-color:#ffffcc;width:100%" }
	  {/if}
    </div>
  

		<h1>{lang_get s='title_test_case'} {lang_get s='th_test_case_id'}{$tc_exec.testcase_id} :: {lang_get s='version'}: {$tc_exec.version}<br>
		    {$tc_exec.name|escape}
    </h1>

		<div id="execution_history" class="exec_history">
  		<div class="exec_history_title">
  		{if $history_on}
  		    {lang_get s='execution_history'}
  		{else}
  			  {lang_get s='just_last_execution_for_this_build'}
  		{/if}
  		</div>

		{* The very last execution for any build of this test plan *}
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
            {assign var="my_colspan" value=$att_model->num_cols}
        {/if}

				{if $g_bugInterfaceOn}
          <th style="text-align:left">{lang_get s='bug_mgmt'}</th>
          {assign var="my_colspan" value=$my_colspan+1}
        {/if}
        
			 </tr>
			 
			{* ----------------------------------------------------------------------------------- *} 
			{foreach item=tc_old_exec from=$other_exec.$tcversion_id}
  	    
	     {assign var="tc_status_code" value=$tc_old_exec.status}

   			<tr style="border-top:1px solid black">
  				<td>{localize_timestamp ts=$tc_old_exec.execution_ts}</td>
  				<td>{$tc_old_exec.tester_first_name|escape} {$tc_old_exec.tester_last_name|escape}</td> 
  				<td class="{$gsmarty_tc_status_css.$tc_status_code}">
  				    {localize_tc_status s=$tc_old_exec.status}</td>
   			  <td align="center">
   			  <a href="javascript:open_show_notes_window({$tc_old_exec.execution_id})">
      			    <img src="icons/contact_16.png" alt="{lang_get s='alt_notes'}" 
      			         style="border:none" /></a>
              </td>
  
  	        {if $att_model->show_upload_column}
      			  <td align="center"><a href="javascript:openFileUploadWindow({$tc_old_exec.execution_id},'executions')">
      			    <img src="icons/upload_16.png" alt="{lang_get s='alt_attachment_mgmt'}" 
      			         style="border:none" /></a>
              </td>
  	        {/if}
  
    				{if $g_bugInterfaceOn}
      			<td align="center"><a href="javascript:open_bug_add_window({$tc_old_exec.execution_id})">
      			    <img src="icons/bug1.gif" title="{lang_get s='img_title_bug_mgmt'}" 
      			         style="border:none" /></a>
              </td>
            {/if}
            
  			</tr>  
  			<tr>
  			<td colspan="{$my_colspan}">
  				{assign var="execID" value=$tc_old_exec.execution_id}
  
  				{assign var="attach_info" value=$attachments[$execID]}
  				{include file="inc_attachments.tpl" 
  				         attachmentInfos=$attach_info 
  				         id=$execID tableName="executions"
  				         show_upload_btn=$att_model->show_upload_btn
  				         show_title=$att_model->show_title }
  			</td>
  			</tr>
  
        {* Execution Bugs (if any) *}
        {if $bugs_for_exec[$execID] neq ""}
    			<tr>
    			<td colspan="{$my_colspan}">
    				{include file="inc_show_bug_table.tpl" 
    				         bugs_map=$bugs_for_exec[$execID] 
    				         can_delete=true
    				         exec_id=$execID}
    			</td>
    			</tr>
    		{/if}	
			{/foreach}
			{* ----------------------------------------------------------------------------------- *} 

			</table>
		{/if}
  </div> 

  <p>
  <div>
   
		<table class="test_exec">
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

		<table border="0" width="100%">
		<tr>
			<td rowspan="2" align="center">
				<div class="title">{lang_get s='test_exec_notes'}</div>
				<textarea {$input_enabled_disabled} class="tcDesc" name='notes[{$tcversion_id}]' 
					cols=50 rows=10></textarea>			
			</td>
			<td valign="top">			
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