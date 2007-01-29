{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: execNavigator.tpl,v 1.12 2007/01/29 20:19:42 schlundus Exp $ *}
{* Purpose: smarty template - show test set tree *}
{* 20050828 - scs - added searching for tcID *}
{include file="inc_head.tpl" jsTree="yes"}

<body>
<h1>{lang_get s='TestPlan'} {$tplan_name|escape}</h1> 

{* $filterForm *}
<div style="margin: 3px;">
<form method="post" onchange="document.tree.style.display = 'hidden';">

	<table class="smallGrey" width="100%">
		<caption>
			{lang_get s='caption_nav_filter_settings'}
			{include file="inc_help.tpl" filename="execFilter.html" help="execFilter" locale="$locale"}
		</caption>
		<tr>
			<td>{lang_get s='filter_tcID'}</td>
			<td><input type="text" name="tcID" value="{$tcID}" maxlength="10" size="5"/></td>
		</tr>
		<tr>
			<td>{lang_get s='keyword'}</td>
			<td><select name="keyword_id">
			    {html_options options=$keywords_map selected=$keyword_id}
				</select>
			</td>
		</tr>
		<tr>
				<td>{lang_get s='filter_result'}</td>
			<td><select name="result">
			{html_options options=$optResult selected=$optResultSelected}
			</select></td>
		</tr>
		<tr>
			<td>{lang_get s='current_build'}</td>
			<td><select name="build_id">
				{html_options options=$optBuild selected=$optBuildSelected}
				</select>
			</td>
		</tr>
		<tr>
			<td>{lang_get s='filter_owner'}</td>
			<td><select name="owner">
				{html_options options=$users selected=$selectedOwner}
				</select>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submitOptions" value="{lang_get s='btn_update_menu'}" style="font-size: 90%;" /></td>
		</tr>
	</table>
</form>
</div>

<div class="tree" id="tree">
{$tree}
</div>

{if $tcIDFound}
	{literal}
		<script language="javascript">
	{/literal}
		ST({$testCaseID});
	{literal}
		</script>
	{/literal}
{/if}

{if $src_workframe != ''}
<script type="text/javascript">
	parent.workframe.location='{$src_workframe}';
</script>
{/if}

</body>
</html>
