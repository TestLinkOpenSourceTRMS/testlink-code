{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: execNavigator.tpl,v 1.4 2006/04/10 09:17:34 franciscom Exp $ *}
{* Purpose: smarty template - show test set tree *}
{* 20050828 - scs - added searching for tcID *}
{include file="inc_head.tpl" jsTree="yes"}

<body>

<h1>{lang_get s='title_tc_suite_navigator'}</h1>

{* $filterForm *}
<div style="margin: 3px;">
<form method="post" onchange="document.tree.style.display = 'hidden';">

	<table class="smallGrey" >
		<caption>
			{lang_get s='caption_nav_filter_settings'}
			{include file="inc_help.tpl" filename="execFilter.html"}
		</caption>
		<tr>
			<td>{lang_get s='filter_tcID'}</td>
			<td><input type="text" name="tcID" value="{$tcID}" maxlength="10" size="5"/></td>
		</tr>
		<tr>
			<td>{lang_get s='filter_owner'}</td>
			<td><select name="owner">
				<option value="All">{lang_get s='opt_all'}</option>
			{section name=Row loop=$arrOwner}
				<option value="{$arrOwner[Row].id}" {$arrOwner[Row].selected}>{$arrOwner[Row].id}</option>
			{/section}
			</select></td>
		</tr>
		<tr>
			<td>{lang_get s='filter_keyword'}</td>
			<td>{$filterKeyword}</td>
		</tr>
		<tr>
			<td>{lang_get s='filter_result'}</td>
			<td><select name="result">
			{html_options options=$optResult selected=$optResultSelected}
			</select></td>
		</tr>
		<tr>
			<td>{lang_get s='filter_build'}</td>
			<td><select name="build_id">
			{html_options options=$optBuild selected=$optBuildSelected}
			</select></td>
		</tr>

		<tr>
			<td>{lang_get s='tree_colored_to'}</td>
			<td><select name="colored">
				<option value="by_build">{lang_get s='opt_build'}</option>
				<option value="by_result" {$treeColored}>{lang_get s='opt_last_result'}</option>
			</select></td>

		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submitOptions" value="{lang_get s='btn_update_menu'}" style="font-size: 90%;" /></td>
		</tr>

	</table>
</form>
</div>


{* javascript tree menu *}
<div class="tree" id="tree">
{$tree}
</div>

{* 20050828 - scs - added searching for tcID *}
{if $tcIDFound}
	{literal}
		<script language="javascript">
	{/literal}
		ST({$testCaseID});
	{literal}
		</script>
	{/literal}
{/if}
</body>
</html>
