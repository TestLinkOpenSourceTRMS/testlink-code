{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcSearchView.tpl,v 1.3 2006/03/03 16:20:59 franciscom Exp $ *}
{* Purpose: smarty template - view searched test case *}
{include file="inc_head.tpl"}
<body>
<div class="workBack">

<h1>Search result</h1>

{if $arrTc eq ''}
	{lang_get s='no_records_found'}
{else}
	{section name=Row loop=$arrTc}
	<table width="90%" class="simple">
		{if $modify_tc_rights eq "yes"}
			<tr>
				<th>
				<a href="lib/testcases/tcEdit.php?editTC=testcase&data={$arrTc[Row].id}" 
				target="mainframe">{lang_get s='href_test_case'} [{$arrTc[Row].id}]: {$arrTc[Row].name|escape}</a>
				</th>
			</tr>
		{else}
			<tr>
				<th>{lang_get s='test_case'} [{$arrTc[Row].id}]: {$arrTc[Row].name|escape}</th>
			</tr>
		{/if}
		<tr>
			<td class="bold">{lang_get s='summary'}</td>
		</tr>
		<tr>
			<td>{$arrTc[Row].summary}</td>
		</tr>
		<tr>
			<td class="bold">{lang_get s='steps'}</td>
		</tr>
		<tr>
			<td>{$arrTc[Row].steps}</td>
		</tr>
		<tr>
			<td class="bold">{lang_get s='expected_results'}</td>
		</tr>
		<tr>
			<td>
				{$arrTc[Row].expected}</td>
			</tr>
		<tr>
			<td><a href="lib/keywords/keywordsView.php" 
				target="mainframe" class="bold">{lang_get s='keywords'}</a>: {$arrTc[Row].keys|escape}
			</td>
		</tr>
	</table>
	{/section}
{/if}
	
</div>

</body>
</html>