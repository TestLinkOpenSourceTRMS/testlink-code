{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planAddTCNavigator.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - show test specification tree *}
{include file="inc_head.tpl" jsTree="yes"}

<body>

<h1>{lang_get s='title_navigator'}</h1>

<div style="margin: 10px;">
<form method="post">
	<table class="common">
		<caption>{lang_get s='caption_assign_tc_with_kewords'}</caption>
		<tr>
			<th>{lang_get s='choose_keyword'}</th>
		</tr>
		<tr>
			<td>
			<select name="keyword">
				<option value="NONE">{lang_get s='opt_none'}</option>
				{section name=Row loop=$arrKeys}
					<option value="{$arrKeys[Row].keyword}"
							{$arrKeys[Row].selected}>{$arrKeys[Row].keyword}</option>
				{/section}
			</select>
			</td>
		</tr>
		<tr>
			<td>
			<input type="submit" value="{lang_get s='btn_set_filter'}" name="filter" />
			</td>
		</tr>
	</table>
</form>
</div>

<div class="tree">
	{$tree}
</div>

</body>
</html>