{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: execSetResults.tpl,v 1.1 2007/12/02 17:02:26 franciscom Exp $
Purpose: smarty template - show tests to add results
Rev:
    20071103 - franciscom - BUGID 700
    20071101 - franciscom - added test automation code
    20070826 - franciscom - added some niftycube effects
    20070519 - franciscom - 
    BUGID 856: Guest user can execute test case
    
    20070211 - franciscom - addede delete logic
    20070205 - franciscom - display test plan custom fields.
    20070125 - franciscom - management of closed build
    20070104 - franciscom - custom field management for test cases
    20070101 - franciscom - custom field management for test suite div
*}	
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" popup='yes' openHead='yes'}
<script language="JavaScript" src="gui/javascript/radio_utils.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{if #ROUND_EXEC_HISTORY# || #ROUND_TC_TITLE# || #ROUND_TC_SPEC#}
  {assign var="round_enabled" value=1}
  <script language="JavaScript" src="{$basehref}gui/niftycube/niftycube.js" type="text/javascript"></script>
{/if}

<script language="JavaScript">
var msg="{lang_get s='warning_delete_execution'}";
var import_xml_results="{lang_get s='import_xml_results'}";
</script>

</head>

<body onLoad="show_hide('tplan_notes','tpn_view_status',{$tpn_view_status});
              show_hide('build_notes','bn_view_status',{$bn_view_status});
              show_hide('bulk_controls','bc_view_status',{$bc_view_status});
              multiple_show_hide('{$tsd_div_id_list}','{$tsd_hidden_id_list}',
                                 '{$tsd_val_for_hidden_list}');
              {if $round_enabled}Nifty('div.exec_additional_info');{/if}  
              {if #ROUND_TC_SPEC# }Nifty('div.exec_test_spec');{/if}                                 
              {if #ROUND_EXEC_HISTORY# }Nifty('div.exec_history');{/if}
              {if #ROUND_TC_TITLE# }Nifty('div.exec_tc_title');{/if}"> 

<h1>	
 {lang_get s='help' var='common_prefix'}
 {assign var="text_hint" value="$common_prefix"}
 {include file="inc_help.tpl" help="execMain" locale=$locale 
          alt="$text_hint" title="$text_hint"  style="float: right;"}
 
	{lang_get s='title_t_r_on_build'} {$build_name|escape} 
	
	{if $ownerDisplayName != ""}
	  {$smarty.const.TITLE_SEP_TYPE3}{lang_get s='only_test_cases_assigned_to'}{$smarty.const.TITLE_SEP}{$ownerDisplayName|escape}  
	{/if}
</h1>


{* show echo about update if applicable *}
{$updated}

{assign var="input_enabled_disabled" value="disabled"}
{assign var="att_download_only" value=true}
{assign var="enable_custom_fields" value=false}
{assign var="draw_submit_button" value=false}

<div id="main_content" class="workBack">

  {if $edit_test_results eq "no"}
  <div class="warning_message" style="align:center;">
     {lang_get s="build_is_closed"}<br>
     {lang_get s="test_cases_cannot_be_executed"}
  </div>
  <p>
  {/if}
  
  <div class="show_hide_title">
    <img src="{$smarty.const.TL_THEME_IMG_DIR}/icon-foldout.gif" border="0" alt="{lang_get s='show_hide'}" 
         title="{lang_get s='show_hide'}" 
         onclick="show_hide('tplan_notes','tpn_view_status',
                            document.getElementById('tplan_notes').style.display=='none')" />
    {lang_get s='test_plan_notes'}
  </div>
  <div id="tplan_notes" class="exec_additional_info">
  {$tplan_notes}
  {if $tplan_cf neq ''}
     <div class="custom_field_container">
     {$tplan_cf} 
     </div>
  {/if}
  </div>
  
<div class="show_hide_title">
<img src="{$smarty.const.TL_THEME_IMG_DIR}/icon-foldout.gif" border="0" alt="{lang_get s='show_hide'}" 
     title="{lang_get s='show_hide'}" 
     onclick="show_hide('build_notes','bn_view_status',
                        document.getElementById('build_notes').style.display=='none')" />
{lang_get s='builds_notes'}
</div>
<div id="build_notes" class="exec_additional_info">
{$build_notes}
</div>



<form method="post" id="execSetResults" name="execSetResults" >
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
  
  {* 20070211 - franciscom *}
  <input type="hidden" id="do_delete"  name="do_delete" value="0" />
  <input type="hidden" id="exec_to_delete"  name="exec_to_delete" value="0" />
  
  
  {if $map_last_exec eq ""}
     <div class="warning_message" style="text-align:center"> {lang_get s='no_data_available'}</div>
  {else}
      {* $edit_test_results = "no",  if build is closed  *}
      {if $rightsEdit == "yes" and $edit_test_results == "yes"}
        {assign var="input_enabled_disabled" value=""}
        {assign var="att_download_only" value=false}
        {assign var="enable_custom_fields" value=true}
        {assign var="draw_submit_button" value=true}


        <div class="show_hide_title">
        <img src="{$smarty.const.TL_THEME_IMG_DIR}/icon-foldout.gif" border="0" alt="{lang_get s='show_hide'}" 
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
        </div>
    	{/if}
    
      <hr />
      <div class="groupBtn">
    		  <input type="button" name="print" value="{lang_get s='btn_print'}" 
    		         onclick="javascript:window.print();" />
    		  <input type="submit" id="toggle_history_on_off" 
    		         name="{$history_status_btn_name}" 
    		         value="{lang_get s=$history_status_btn_name}" />
    		  <input type="button" id="pop_up_import_button" name="import_xml_button" 
    		         value="{lang_get s='import_xml_results'}" 
    		         onclick="javascript: openImportResult(import_xml_results);" />
		      
		      {if $test_automation_enabled}
		      <input type="submit" id="execute_cases" name="execute_cases" 
		             value="{lang_get s='execute_and_save_results'}"/>
		      {/if}       
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
		<img src="{$smarty.const.TL_THEME_IMG_DIR}/icon-foldout.gif" border="0" alt="{lang_get s='show_hide'}" 
             title="{lang_get s='show_hide'}" 
             onclick="show_hide('tsdetails_{$tc_exec.testcase_id}',
                                'tsdetails_view_status_{$tc_exec.testcase_id}',
                                document.getElementById('tsdetails_{$tc_exec.testcase_id}').style.display=='none')" />
    
		{lang_get s='th_testsuite'} {$tsuite_info[$tc_exec.testcase_id].tsuite_name|escape}
		</div>

		<div id="tsdetails_{$tc_exec.testcase_id}" name="tsdetails_{$tc_exec.testcase_id}" 
		     class="exec_additional_info">

      <br>
      <div class="exec_testsuite_details" style="width:95%;">
      <span class="legend_container">{lang_get s='details'}</span><br>
		  {$tsuite_info[$tc_exec.testcase_id].details}
		  </div>
		  
		  {* 20070104 - franciscom *}
		  {if $ts_cf_smarty[$tc_exec.testcase_id] neq ''}
		    <p>
		    <div class="custom_field_container" style="border-color:black;width:95%;">
         {$ts_cf_smarty[$tc_exec.testcase_id]}
        </div>
		  {/if}
		  
  		{if $tSuiteAttachments[$tc_exec.tsuite_id] neq null}
  		  <p>
           <script language="JavaScript">
           var msg="{lang_get s='warning_delete_execution'}";
           </script>
  		  
		    {include file="inc_attachments.tpl" tableName="nodes_hierarchy" downloadOnly=true 
			        	 attachmentInfos=$tSuiteAttachments[$tc_exec.tsuite_id] 
			        	 inheritStyle=1
			        	 tableClassName="none"
				         tableStyles="background-color:#ffffcc;width:100%" }
	    {/if}
	    <p>
    </div>
  

		<div class="exec_tc_title">
		{lang_get s='title_test_case'} {lang_get s='th_test_case_id'}{$tc_exec.testcase_id} :: {lang_get s='version'}: {$tc_exec.version}<br />
		    {$tc_exec.name|escape}<br>
		    {if $tc_exec.assigned_user eq ''}
		      {lang_get s='has_no_assignment'}
		    {else}  
          {lang_get s='assigned_to'}{$smarty.const.TITLE_SEP}{$tc_exec.assigned_user|escape}
        {/if}  
    </div>

 		{if $show_last_exec_any_build}
   		{assign var="abs_last_exec" value=$map_last_exec_any_build.$tcversion_id}
 		  {assign var="the_build" value=$abs_last_exec.build_name|escape}
 		  {assign var="show_current_build" value=1}
 		{else}
 		  {assign var="the_build" value=$build_name|escape}
 		  {assign var="show_current_build" value=0}
    {/if}

		<div id="execution_history" class="exec_history">
  		<div class="exec_history_title">
  		{if $history_on}
  		    {lang_get s='execution_history'} {$smarty.const.TITLE_SEP_TYPE3}
  		    
  		    {if !$show_history_all_builds} 
  		      {lang_get s='build'} {$smarty.const.TITLE_SEP} {$build_name|escape}
  		    {/if}  
  		{else}
  			  {lang_get s='last_execution'} 
  			  {if $show_current_build} {lang_get s='exec_any_build'} {/if}
  			  {$smarty.const.TITLE_SEP_TYPE3} {lang_get s='build'} 
  			  {$smarty.const.TITLE_SEP} {$the_build}
  		{/if}
  		</div>

		{* The very last execution for any build of this test plan *}
		{if $show_last_exec_any_build && $history_on==0}
        {if $abs_last_exec.status != '' and $abs_last_exec.status != $gsmarty_tc_status.not_run}			
			    {assign var="status_code" value=$abs_last_exec.status}
    
     			<div class="{$gsmarty_tc_status_css.$status_code}">
     			{lang_get s='date_time_run'} {$smarty.const.TITLE_SEP} {localize_timestamp ts=$abs_last_exec.execution_ts}
     			{$smarty.const.TITLE_SEP_TYPE3}
     			{lang_get s='test_exec_by'} {$smarty.const.TITLE_SEP} {$alluserInfo[$abs_last_exec.tester_id].fullname|escape} 
     			{$smarty.const.TITLE_SEP_TYPE3}
     			{lang_get s='build'}{$smarty.const.TITLE_SEP} {$abs_last_exec.build_name|escape} 			
     			{$smarty.const.TITLE_SEP_TYPE3}
     			{lang_get s='exec_status'} {$smarty.const.TITLE_SEP} {localize_tc_status s=$status_code}
     			</div>
  		    
  		  {else}
    		   <div class="not_run">{lang_get s='test_status_not_run'}</div>
    			   {lang_get s='tc_not_tested_yet'}
   		  {/if}
    {/if}

    {* -------------------------------------------------------------------------------------------------- *}
    {if $other_exec.$tcversion_id}
    
      {if $history_on == 0 && $show_current_build}
   		   <div class="exec_history_title">
  			    {lang_get s='last_execution'} {lang_get s='exec_current_build'} 
  			    {$smarty.const.TITLE_SEP_TYPE3} {lang_get s='build'} 
  			    {$smarty.const.TITLE_SEP} {$build_name|escape}
  			 </div>   
		  {/if}
    
		  <table cellspacing="0" class="exec_history">
			 <tr>
				<th style="text-align:left">{lang_get s='date_time_run'}</th>
        {* 20071103 - BUGID 700 *}
				{if $history_on == 0 || $show_history_all_builds}
				  <th style="text-align:left">{lang_get s='build'}</th>
				{/if}  
				<th style="text-align:left">{lang_get s='test_exec_by'}</th>
				<th style="text-align:left">{lang_get s='exec_status'}</th>
				<th style="text-align:center" >
				   {lang_get s='exec_notes'}
  			</th>
				
				{if $att_model->show_upload_column && !$att_download_only}
						<th style="text-align:center">{lang_get s='attachment_mgmt'}</th>
            {assign var="my_colspan" value=$att_model->num_cols}
        {/if}

				{if $g_bugInterfaceOn}
				   HOLA
          <th style="text-align:left">{lang_get s='bug_mgmt'}</th>
          {assign var="my_colspan" value=$my_colspan+1}
        {/if}
        
				{if $can_delete_execution}
          <th style="text-align:left">{lang_get s='delete'}</th>
          {assign var="my_colspan" value=$my_colspan+1}
        {/if}


			 </tr>
			 
			{* ----------------------------------------------------------------------------------- *} 
			{foreach item=tc_old_exec from=$other_exec.$tcversion_id}
  	     {assign var="tc_status_code" value=$tc_old_exec.status}

   			<tr style="border-top:1px solid black">
  				<td>{localize_timestamp ts=$tc_old_exec.execution_ts}</td>
  				
				  {if $history_on == 0 || $show_history_all_builds}
  				<td>{if !$tc_old_exec.build_is_open}
  				    <img src="{$smarty.const.TL_THEME_IMG_DIR}/lock.png" title="{lang_get s='closed_build'}">{/if}
  				    {$tc_old_exec.build_name|escape}
  				</td>
  				{/if}
  				
  				<td>{$alluserInfo[$tc_old_exec.tester_id].fullname|escape}</td> 
  				<td class="{$gsmarty_tc_status_css.$tc_status_code}">
  				    {localize_tc_status s=$tc_old_exec.status}
  				</td>

   			  <td align="center">
     			  {if $tc_old_exec.execution_notes neq ""}
       			  <a href="javascript:open_show_notes_window({$tc_old_exec.execution_id})">
          			    <img src="{$smarty.const.TL_THEME_IMG_DIR}/contact_16.png" alt="{lang_get s='alt_notes'}" 
          			         title="{lang_get s='alt_notes'}"  style="border:none" /></a>
          	{else}
          	 &nbsp;
          	{/if}		         
          </td>
            
          {if $att_model->show_upload_column && !$att_download_only && $tc_old_exec.build_is_open}
      			  <td align="center"><a href="javascript:openFileUploadWindow({$tc_old_exec.execution_id},'executions')">
      			    <img src="{$smarty.const.TL_THEME_IMG_DIR}/upload_16.png" title="{lang_get s='alt_attachment_mgmt'}"
      			         alt="{lang_get s='alt_attachment_mgmt'}" 
      			         style="border:none" /></a>
              </td>
  	      {/if}
  
    			{if $g_bugInterfaceOn}
    			    HOLA
       		  	<td align="center"><a href="javascript:open_bug_add_window({$tc_old_exec.execution_id})">
      			    <img src="{$smarty.const.TL_THEME_IMG_DIR}/bug1.gif" title="{lang_get s='img_title_bug_mgmt'}" 
      			         style="border:none" /></a>
              </td>
          {/if}


    			{if $can_delete_execution}
       		  	<td align="center">
             	<a href="javascript:confirm_and_submit(msg,'execSetResults','exec_to_delete',
             	                                       {$tc_old_exec.execution_id},'do_delete',1);">
      			    <img src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png" title="{lang_get s='img_title_delete_execution'}" 
      			         style="border:none" /></a>
              </td>
          {/if}

            
  			</tr>  
  
  			{* 20070105 - Custom field values  *}
  			<tr>
  			<td colspan="{$my_colspan}">
  				{assign var="execID" value=$tc_old_exec.execution_id}
  				{assign var="cf_value_info" value=$other_exec_cfexec[$execID]}
          {$cf_value_info}
  			</td>
  			</tr>
  
  			
  			
  			{* Attachments *}
  			<tr>
  			<td colspan="{$my_colspan}">
  				{assign var="execID" value=$tc_old_exec.execution_id}
  
  				{assign var="attach_info" value=$attachments[$execID]}
  				{include file="inc_attachments.tpl" 
  				         attachmentInfos=$attach_info 
  				         id=$execID tableName="executions"
  				         show_upload_btn=$att_model->show_upload_btn
  				         show_title=$att_model->show_title 
  				         downloadOnly=$att_download_only
  				         }
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
   
    <div class="exec_test_spec">
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
		
    {* ------------------------------------------------------------------------------------- *}
    {if $enable_custom_field and $tc_exec.active eq 1}
  	  {if $execution_time_cf[$tc_exec.testcase_id]}
  	 		<tr>
  				<td colspan="2">
  					<div class="custom_field_container" 
  						style="background-color:#dddddd;">{$execution_time_cf[$tc_exec.testcase_id]}
  					</div>
  				</td>
  			</tr>
  		{/if}
    {/if} {* if $enable_custom_field *}
    {* ------------------------------------------------------------------------------------- *}
    
    
    	{if $design_time_cf[$tc_exec.testcase_id] neq ''}
			<tr>
				<td colspan="2">
					<div class="custom_field_container" 
					style="background-color:#dddddd;">{$design_time_cf[$tc_exec.testcase_id]}
					</div>
				</td>
			</tr>
 		{/if} 
		
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
		</div>
		
    {* ----------------------------------------------------------------------------------- *}
    {* 20070708 - franciscom *}
    {assign var="can_execute" value=0}
   
    {if $rightsEdit == "yes" and $edit_test_results eq "yes" and $tc_exec.active eq 1}
   
      {assign var="can_execute" value=1}
      {* --------------------------------------------------------------------------------- *}
      {if $exec_mode neq 'all'}
        {assign var="can_execute" value=0}
    	
    	  {if $exec_mode eq 'assigned_to_me'} 
    	      {if $tc_exec.assigned_user_id eq $tester_id}
    	        {assign var="can_execute" value=1}
    	      {/if} 
    	  {/if}
  
    	  {if $exec_mode eq 'assigned_to_me_or_free'} 
    	      {if $tc_exec.assigned_user_id eq $tester_id || $tc_exec.assigned_user_id eq ''}
    	        {assign var="can_execute" value=1}
    	      {/if} 
        {/if}
      {/if}
      {* --------------------------------------------------------------------------------- *}
      
  	{/if}
    {* ----------------------------------------------------------------------------------- *}

  	
  	{if $can_execute}
  		<table border="0" width="100%">
  		<tr>
  			<td rowspan="2" align="center">
  				<div class="title">{lang_get s='test_exec_notes'}</div>
  				<textarea {$input_enabled_disabled} class="tcDesc" name='notes[{$tcversion_id}]' 
  					rows="10" style="width:99%"></textarea>			
  			</td>
  			<td valign="top" style="width:30%">			
    				{* status of test *}
      			<div class="title" style="text-align: center;">{lang_get s='test_exec_result'}</div>
    				
    				<div class="resultBox">
  
                {foreach key=verbose_status item=locale_status from=$gsmarty_tc_status_for_ui}
    						<input type="radio" {$input_enabled_disabled} name="status[{$tcversion_id}]" 
    							value="{$gsmarty_tc_status.$verbose_status}"
    							{if $gsmarty_tc_status.$verbose_status eq $gsmarty_tc_status.$default_status}
    							checked="checked" 
    							{/if} />{lang_get s=$locale_status}<br />
    					 {/foreach}		
    					<br />		
    		 			<input type="submit" name="save_results[{$tcversion_id}]" 
    		 			       {$input_enabled_disabled}
    		 			       value="{lang_get s='btn_save_tc_exec_results'}" />
    				</div>
    			</td>
    		</tr>
  		</table>
	 {/if}
 	 {if $tc_exec.active eq 0}
 	  <h1><center>{lang_get s='testcase_version_is_inactive_on_exec'}</center></h1>
 	 {/if}
  
  
  
	<hr />
	</div>
	{/foreach}
</form>
</div>
</body>
</html>
