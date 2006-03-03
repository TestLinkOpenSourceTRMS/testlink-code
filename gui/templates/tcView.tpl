{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcView.tpl,v 1.9 2006/03/03 16:20:59 franciscom Exp $
Purpose: smarty template - view test case in test specification

20060303 - franciscom
*}

{include file="inc_head.tpl"}
<body>
<div class="workBack">


{if $testcase eq null}
	{lang_get s='no_records_found'}
{else}
{section name=row loop=$testcase}
<h1>{lang_get s='title_test_case'} {$testcase[row].name|escape} </h1>


{if $can_edit == "yes" }

	{include file="inc_update.tpl" result=$sqlResult item="TestCase" refresh="yes"}

	<div class="groupBtn">
	<form method="post" action="lib/testcases/tcEdit.php?&testcaseID={$testcase[row].testcase_id}">
		<input type="submit" name="editTC"   value="{lang_get s='btn_edit'}">
		<input type="submit" name="deleteTC" value="{lang_get s='btn_del'}">
		<input type="submit" name="moveTC"   value="{lang_get s='btn_mv_cp'}">
	</form>
	</div>	
{/if}

	<table width="95%" class="simple" border="0">
		<tr>
			<th  colspan="2">{lang_get s='th_test_case_id'}{$testcase[row].testcase_id} :: 
			{lang_get s='title_test_case'} {$testcase[row].name|escape}</th>
		</tr>
		<tr>
			<td class="bold" colspan="2">{lang_get s='version'} 
			{$testcase[row].version|escape}</td>
		</tr>
		<tr >
			<td class="bold" colspan="2">{lang_get s='summary'}</td>
		</tr>
		<tr>
			<td  colspan="2">{$testcase[row].summary}</td>
		</tr>
		<tr>
			<td class="bold" width="50%">{lang_get s='steps'}</td>
			<td class="bold" width="50%">{lang_get s='expected_results'}</td>
		</tr>
		<tr>
			<td>{$testcase[row].steps}</td>
			<td>{$testcase[row].expected_results}</td>
		</tr>
		<tr>
			<td colspan="2"><a href="lib/keywords/keywordsView.php" 
				target="mainframe" class="bold">{lang_get s='keywords'}</a>: &nbsp;
				{$testcase[row].keywords|escape}
			</td>
		</tr>
	{if $opt_requirements == TRUE && $view_req_rights == "yes"}
		<tr>
			<td colspan="2"><span><a href="lib/req/reqSpecList.php" 
				target="mainframe" class="bold">{lang_get s='Requirements'}</a>
				: &nbsp;</span>
			
				{section name=item loop=$arrReqs}
					<span onclick="javascript: open_top(fRoot+'lib/req/reqView.php?idReq={$arrReqs[item].id}');"
					style="cursor:  pointer;">
					{$arrReqs[item].title|escape}</span>, 
				{sectionelse}
					{lang_get s='none'}
				{/section}
			</td>
		</tr>
	{/if}
	</table>
	
	<div>
		<p>{lang_get s='title_created'}&nbsp;{localize_timestamp ts=$testcase[row].creation_ts }&nbsp;
			{lang_get s='by'}&nbsp;{$testcase[row].author_first_name|escape}&nbsp;{$testcase[row].author_last_name|escape}
		
		{if $testcase[row].updater_last_name ne "" || $testcase[row].updater_first_name ne ""}
		<br />{lang_get s='title_last_mod'}&nbsp;{localize_timestamp ts=$testcase[row].modification_ts}
		&nbsp;{lang_get s='by'}&nbsp;{$testcase[row].updater_first_name|escape}
		                       &nbsp;{$testcase[row].updater_last_name|escape}
		{/if}
		</p>
	</div>
{/section}
{/if}
</body>
</html>