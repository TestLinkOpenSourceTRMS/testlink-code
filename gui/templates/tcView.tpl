{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcView.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - view test case in test specification *}
{* I18N: 20050528 - fm *}

{include file="inc_head.tpl"}
<body>
<div class="workBack">

<h1>{lang_get s='title_test_case'} {$testcase[1]|escape}</h1>

{if $modify_tc_rights == "yes"}
	{include file="inc_update.tpl" result=$sqlResult item="test case" refresh="yes"}

	<form method="post" action="lib/testcases/tcEdit.php?&data={$testcase[0]}">
		<input type="submit" name="editTC"   value="{lang_get s='btn_edit'}">
		<input type="submit" name="deleteTC" value="{lang_get s='btn_del'}">
		<input type="submit" name="moveTC"   value="{lang_get s='btn_mv_cp'}">
	</form>
{/if}

	<table width="90%" class="simple">
		<tr>
			<th>{lang_get s='th_test_case'}{$testcase[0]}: {$testcase[1]|escape}</th>
		</tr>
		<tr>
			<td class="bold">{lang_get s='version'}{$testcase[5]|escape}</td>
		</tr>
		<tr>
			<td class="bold">{lang_get s='summary'}</td>
		</tr>
		<tr>
			<td>{$testcase[2]}</td>
		</tr>
		<tr>
			<td class="bold">{lang_get s='steps'}</td>
		</tr>
		<tr>
			<td>{$testcase[3]}</td>
		</tr>
		<tr>
			<td class="bold">{lang_get s='expected_results'}</td>
		</tr>
		<tr>
			<td>{$testcase[4]}</td>
		</tr>
		<tr>
			<td><a href="lib/keywords/keywordsView.php" 
				target="mainframe" class="bold">{lang_get s='keywords'}</a> {$testcase[6]|escape}
			</td>
		</tr>
	</table>
	
	<div>
		<p>{lang_get s='title_created'}&nbsp;{$testcase[8]|escape}&nbsp;{lang_get s='by'}&nbsp;{$testcase[7]|escape}
		{if $testcase[9] ne ""}
		<br />{lang_get s='title_last_mod'}&nbsp;{$testcase[10]|escape}&nbsp;{lang_get s='by'}&nbsp;{$testcase[9]|escape}
		{/if}
		</p>
	</div>

</body>
</html>