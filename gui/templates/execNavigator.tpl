{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: execNavigator.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - show test set tree *}
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
{*			<td><select name="keyword">
				<option value="All">{lang_get s='opt_all'}</option>
			{section name=Row loop=$arrKeyword}
				<option value="{$arrKeyword[Row].id}" {$arrKeyword[Row].selected}>{$arrKeyword[Row].name}</option>
			{/section}
			</select></td>
*}
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
			<td><select name="build">
			{html_options options=$optBuild selected=$optBuildSelected}
			</select></td>
		</tr>

		<tr>
			<td>{lang_get s='tree_colored_to'}</td>
{*			
			<td><input type="checkbox" name="colored" {$treeColored}/></td> 
*}
			<td><select name="colored">
				<option value="build">{lang_get s='opt_build'}</option>
				<option value="result" {$treeColored}>{lang_get s='opt_last_result'}</option>
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


</body>
</html>
