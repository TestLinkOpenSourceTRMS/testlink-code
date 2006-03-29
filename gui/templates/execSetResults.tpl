{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: execSetResults.tpl,v 1.15 2006/03/29 17:44:01 franciscom Exp $ *}
{* Purpose: smarty template - show tests to add results *}
{* Revisions:
*}	

{include file="inc_head.tpl" popup='yes'}

<body>

<h1>	
	<img alt="{lang_get s='help'}" class="help" 
	src="icons/sym_question.gif" style="float: right;"
	onclick="javascript:open_popup('{$helphref}execMain.html');" />
	{lang_get s='title_t_r_on_build'} {$build|escape} {lang_get s='title_t_r_owner'} ( {$owner|escape} )
</h1>

{* show echo about update if applicable *}
{$updated}

{* 20051108 - fm - BUGID 00082*}
{assign var="input_enabled_disabled" value="disabled"}
  	
<div class="workBack">
<form method='post'>
  {* -------------------------------------------------------------------------------------- *}
  {* 20060207 - franciscom - BUGID 303
     Added to make Test Results editable only if Current build is latest Build - Tools-R-Us *}
  {* 20051108 - fm - BUGID 00082*}
  {if $rightsEdit == "yes" and $edit_test_results == "yes"}
  	{assign var="input_enabled_disabled" value=""}
  	
	  <div class="groupBtn">
  		<input type="submit" name='save_results' value="{lang_get s='btn_save_tc_exec_results'}" />
		  <input type="button" name="print" value="{lang_get s='btn_print'}" 
		         onclick="javascript:window.print();" />
  	</div>
	{/if}
	
	<hr />

	{* display test cases from array $arrTC*}
	{foreach item=tc_exec from=$arrTC}
	
	  {assign var="idx" value=$tc_exec.testcase_id}
		<input type='hidden' name='tc[{$idx}]' value='{$tc_exec.testcase_id}' />
  	<h2>{lang_get s='th_test_case_id'}{$tc_exec.testcase_id} :: {lang_get s='title_test_case'} {$tc_exec.name|escape}</h2>


		<div>
	  {if $tc_exec.status != '' and $tc_exec.status != $gsmarty_tc_status.not_run}			
      
      {assign var="status_code" value=$tc_exec.status}

			<div class="{$gsmarty_tc_status_css.$status_code}">
			{lang_get s='test_exec_last_run_date'} {localize_date d=$tc_exec.execution_ts}
			{lang_get s='test_exec_by'} {$tc_exec.runby|escape} 
			{lang_get s='test_exec_on_build'} {$tc_exec.build_name|escape}: 
			
			
			{if $tc_exec.status == $gsmarty_tc_status.passed}
				{lang_get s='test_status_passed'}
			{elseif $tc_exec.status == $gsmarty_tc_status.failed}
				{lang_get s='test_status_failed'}
			{elseif $tc_exec.status == $gsmarty_tc_status.blocked}
				{lang_get s='test_status_blocked'}
			{/if}
			</div>
	    
		{else}
			<div class="not_run">{lang_get s='test_status_not_run'}</div>
			{lang_get s='tc_not_tested_yet'}
		{/if}

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
		</table>

		<table border="2">
		<tr>
			<td rowspan="2">
				<div class="title">{lang_get s='test_exec_notes'}</div>
				<textarea {$input_enabled_disabled} class="tcDesc" name='notes[{$idx}]' 
					cols=50 rows=10>{$tc_exec.note|escape}</textarea>			
			</td>
			<td>			
  				{* status of test *}
  				<!-- <span class="title">{lang_get s='test_exec_result'}</span><br /> --->
  				<div class="title" style="text-align: center;">{lang_get s='test_exec_result'}</div>
  				
  				<div class="resultBox">
  					
  						<input type="radio" {$input_enabled_disabled} name='status[{$idx}]' 
  							value="{$gsmarty_tc_status.not_run}" {if $tc_exec.status == $gsmarty_tc_status.not_run
  							|| $tc_exec.status == ''} checked="checked" {/if} />{lang_get s='test_status_not_run'}<br />
  						<input type="radio" {$input_enabled_disabled} name='status[{$idx}]' 
  							value="{$gsmarty_tc_status.passed}" {if $tc_exec.status == $gsmarty_tc_status.passed} 
  							checked="checked" {/if} />{lang_get s='test_status_passed'}<br />
  						<input type="radio" {$input_enabled_disabled} name='status[{$idx}]' 
  							value="{$gsmarty_tc_status.failed}" {if $tc_exec.status == $gsmarty_tc_status.failed} 
  							checked="checked" {/if} />{lang_get s='test_status_failed'}<br />
  						<input type="radio" {$input_enabled_disabled} name='status[{$idx}]' 
  							value="{$gsmarty_tc_status.blocked}" {if $tc_exec.status == $gsmarty_tc_status.blocked} 
  							checked="checked" {/if} />{lang_get s='test_status_blocked'}<br />
  					<br>		
  		 			<input type='submit' name='submitTestResults' value="{lang_get s='btn_save_tc_exec_results'}" />
  				</div>
          <!---
  				<div class="resultBox">
  		 			<input type='submit' name='submitTestResults' value="{lang_get s='btn_save_tc_exec_results'}" />
   	  		</div>	
      	--->
  			</td>
  	</tr>
	 </tr>
	</table>




	<hr />
	{/foreach}
  {* {if $rightsEdit == "yes" and $edit_test_results == "yes" } *}
  	<div class="groupBtn">
  		<input type='submit' name='submitTestResults' value="{lang_get s='btn_save_tc_exec_results'}" />
  	</div>	
  {* {/if} *}


</form>
</div>

</body>
</html>