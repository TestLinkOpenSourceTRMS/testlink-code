{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: execSetResults.tpl,v 1.3 2005/08/25 17:40:59 schlundus Exp $ *}
{* Purpose: smarty template - show tests to add results *}
{* 20050815 - scs - small changes because of code changes in execSetResults.php *}
{include file="inc_head.tpl" popup='yes'}

<body>

<h1>{lang_get s='title_t_r_on_build'} {$build} {lang_get s='title_t_r_owner'} ( {$owner} )</h1>

{* show echo about update if applicable *}
{$updated}

<div class="workBack">
<form method='post'>
	<div>
		<input type='submit' name='submitTestResults' value="{lang_get s='btn_save_tc_exec_results'}" />
		<img align=top src="icons/sym_question.gif" onclick="javascript:open_popup('{$helphref}execMain.html');">
	</div>
	<hr />

	{* display test cases from array $arrTC*}
	{section name=Row loop=$arrTC}
		<input type='hidden' name='tc{$arrTC[Row].id}' value='{$arrTC[Row].id}' />
			<h2>{$arrTC[Row].title|escape}</h2>
		<div>
 		{if $arrTC[Row].recentResult.status != '' and $arrTC[Row].recentResult.status != 'n'}			
			{if $arrTC[Row].recentResult.status == $g_tc_status.passed}
				<div class="passBox">
			{elseif $arrTC[Row].recentResult.status == $g_tc_status.failed}
				<div class="failBox">
			{elseif $arrTC[Row].recentResult.status == $g_tc_status.blocked}
				<div class="blockedBox">
			{/if}
			
			{lang_get s='test_exec_last_run_date'} {$arrTC[Row].recentResult.daterun} {lang_get s='test_exec_by'} {$arrTC[Row].recentResult.runby|escape} 
			{lang_get s='test_exec_on_build'} {$arrTC[Row].recentResult.build|escape}: 
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
				<textarea class="tcDesc" name='notes{$arrTC[Row].id}' 
					cols=35 rows=4>{$arrTC[Row].note|escape}</textarea>			
			</td>
			<td>			
				{* status of test *}
				<div class="resultBox">
					<span class="title">{lang_get s='test_exec_result'}</span><br /> 
						<input type="radio" name='status{$arrTC[Row].id}' 
							value="{$g_tc_status.not_run}" {if $arrTC[Row].status == $g_tc_status.not_run
							|| $arrTC[Row].status == ''} checked="checked" {/if} />{lang_get s='test_status_not_run'}<br />
						<input type="radio" name='status{$arrTC[Row].id}' 
							value="{$g_tc_status.passed}" {if $arrTC[Row].status == $g_tc_status.passed} 
							checked="checked" {/if} />{lang_get s='test_status_passed'}<br />
						<input type="radio" name='status{$arrTC[Row].id}' 
							value="{$g_tc_status.failed}" {if $arrTC[Row].status == $g_tc_status.failed} 
							checked="checked" {/if} />{lang_get s='test_status_failed'}<br />
						<input type="radio" name='status{$arrTC[Row].id}' 
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
				<input name='bugs{$arrTC[Row].id}' value='{$arrTC[Row].bugs}' /><a style="font-weight:normal" target="_blank" href="{$g_bugInterface->getEnterBugURL()}">{lang_get s='button_enter_bug'}</a>
				<table class="simple" width="100%">
				<caption style="text-align:left">{lang_get s='caption_bugtable'}</caption>
					{section name=link loop=$arrTC[Row].bugLinkList}
					<tr>
						<td>{$arrTC[Row].bugLinkList[link]}</td>
					</tr>
				{/section}
				</table>
			</td>
		</tr>
		{/if}
		</table>		

		
	</div>
	<hr />
	{/section}

	<div>
		<input type='submit' name='submitTestResults' value="{lang_get s='btn_save_tc_exec_results'}" />
		<img align=top src="icons/sym_question.gif" onclick="javascript:open_popup('{$helphref}execMain.html');" />
	</div>	

</form>
</div>

</body>
</html>