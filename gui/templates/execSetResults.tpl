{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: execSetResults.tpl,v 1.12 2005/12/05 01:30:26 havlat Exp $ *}
{* Purpose: smarty template - show tests to add results *}
{* Revisions:
	
  20050815 - scs - small changes because of code changes in execSetResults.php
  20050827 - scs - added display of tcID
  20050828 - fm - localize_date; use $g_tc_status.not_run instead of magic letter 
  20050911 - fm - using $smarty.section.Row.index 
  (change needed to use assoc array to simplify processing in PHP)
  20050919 - fm - BUGID 82
  20051022 - scs - build identifier not displayed
  20051118 - scs - enlargened the notes textarea
  20051119 - scs - added fix for 227
  20051126 - scs - added escaping of build and owner
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
  {* 20051108 - fm - BUGID 00082*}
  {if $rightsEdit == "yes"}
  	{assign var="input_enabled_disabled" value=""}
  	
	<div class="groupBtn">
  		<input type="submit" name='submitTestResults' value="{lang_get s='btn_save_tc_exec_results'}" />
		<input type="button" name="print" value="{lang_get s='btn_print'}" 
		onclick="javascript:window.print();" />
  	</div>
	{/if}
	
	<hr />

	{* display test cases from array $arrTC*}
	{section name=Row loop=$arrTC}
	  {assign var="idx" value=$smarty.section.Row.index}
		<input type='hidden' name='tc[{$idx}]' value='{$arrTC[Row].id}' />
			<h2>{lang_get s='th_test_case_id'}{$arrTC[Row].mgttcid} :: {lang_get s='title_test_case'} {$arrTC[Row].title|escape}</h2>
		<div>
 		{if $arrTC[Row].recentResult.status != '' and $arrTC[Row].recentResult.status != $g_tc_status.not_run}			
			{if $arrTC[Row].recentResult.status == $g_tc_status.passed}
				<div class="passBox">
			{elseif $arrTC[Row].recentResult.status == $g_tc_status.failed}
				<div class="failBox">
			{elseif $arrTC[Row].recentResult.status == $g_tc_status.blocked}
				<div class="blockedBox">
			{/if}
			
			{lang_get s='test_exec_last_run_date'} {localize_date d=$arrTC[Row].recentResult.daterun}
			{lang_get s='test_exec_by'} {$arrTC[Row].recentResult.runby|escape} 
			{lang_get s='test_exec_on_build'} {$arrTC[Row].recentResult.build_name|escape}: 
			{if $arrTC[Row].recentResult.status == $g_tc_status.passed}
				{lang_get s='test_status_passed'}
			{elseif $arrTC[Row].recentResult.status == $g_tc_status.failed}
				{lang_get s='test_status_failed'}
			{elseif $arrTC[Row].recentResult.status == $g_tc_status.blocked}
				{lang_get s='test_status_blocked'}
			{/if}
			</div>
	
		{else}
			<div class="notRunBox">{lang_get s='test_status_not_run'}</div>
			{lang_get s='tc_not_tested_yet'}
		{/if}
		<table class="notesBox">
		<tr>
			<td colspan="2" class="title">{lang_get s='test_exec_summary'}</td>
		</tr>
		<tr>
			<td colspan="2">{$arrTC[Row].summary}</td>
		</tr>
		<tr>
			<td class="title" width="50%">{lang_get s='test_exec_steps'}</td>
			<td class="title" width="50%">{lang_get s='test_exec_expected_r'}</td>
		</tr>
		<tr>
			<td>{$arrTC[Row].steps}</td>
			<td>{$arrTC[Row].outcome}</td>
		</tr>
		</table>

		<table border="0">
		<tr>
			<td>
				<div class="title">{lang_get s='test_exec_notes'}</div>
				<textarea {$input_enabled_disabled} class="tcDesc" name='notes[{$idx}]' 
					cols=50 rows=10>{$arrTC[Row].note|escape}</textarea>			
			</td>
			<td>			
				{* status of test *}
				<div class="resultBox">
					<span class="title">{lang_get s='test_exec_result'}</span><br /> 
						<input type="radio" {$input_enabled_disabled} name='status[{$idx}]' 
							value="{$g_tc_status.not_run}" {if $arrTC[Row].status == $g_tc_status.not_run
							|| $arrTC[Row].status == ''} checked="checked" {/if} />{lang_get s='test_status_not_run'}<br />
						<input type="radio" {$input_enabled_disabled} name='status[{$idx}]' 
							value="{$g_tc_status.passed}" {if $arrTC[Row].status == $g_tc_status.passed} 
							checked="checked" {/if} />{lang_get s='test_status_passed'}<br />
						<input type="radio" {$input_enabled_disabled} name='status[{$idx}]' 
							value="{$g_tc_status.failed}" {if $arrTC[Row].status == $g_tc_status.failed} 
							checked="checked" {/if} />{lang_get s='test_status_failed'}<br />
						<input type="radio" {$input_enabled_disabled} name='status[{$idx}]' 
							value="{$g_tc_status.blocked}" {if $arrTC[Row].status == $g_tc_status.blocked} 
							checked="checked" {/if} />{lang_get s='test_status_blocked'}<br />
				</div>
			</td>				
		</tr>
		{if $g_bugInterfaceOn}					
		<tr>
			<td colspan="2">
				<br />
				<span class="title">{lang_get s='test_exec_bug_report'}</span>
				<input name='bugs[{$idx}]' value='{$arrTC[Row].bugs}' /><a style="font-weight:normal" target="_blank" href="{$g_bugInterface->getEnterBugURL()}">{lang_get s='button_enter_bug'}</a>
				{if $arrTC[Row].bugLinkList}
				<table class="simple" width="100%">
					<tr>
						<th style="text-align:left">{lang_get s='build'}</th>
						<th style="text-align:left">{lang_get s='caption_bugtable'}</th>
					</tr>
					{section name=link loop=$arrTC[Row].bugLinkList}
					<tr>
						<td>{$arrTC[Row].bugLinkList[link][1]|escape}</td>
						<td>{$arrTC[Row].bugLinkList[link][0]}</td>
					</tr>
					{/section}
				{/if}
				</table>
			</td>
		</tr>
		{/if}
		</table>		

		
	</div>
	<hr />
	{/section}

  {* 20051108 - fm - BUGID 00082*}
  {if $rightsEdit == "yes"}
  	<div class="groupBtn">
  		<input type='submit' name='submitTestResults' value="{lang_get s='btn_save_tc_exec_results'}" />
  	</div>	
  {/if}
</form>
</div>

</body>
</html>