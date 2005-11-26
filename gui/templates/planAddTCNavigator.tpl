{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planAddTCNavigator.tpl,v 1.3 2005/11/26 19:58:21 schlundus Exp $ *}
{* Purpose: smarty template - show test specification tree *}
{include file="inc_head.tpl" jsTree="yes"}
{*
 20051126 - scs - changed passing keyword to keywordID, 
 				  added escaping of keywords
				  changed the width of the "select keyword" box
*}

<body>

<h1>{lang_get s='title_navigator'}</h1>

<div>
<form method="post">
	<table class="common" width="100%">
		<caption>{lang_get s='caption_assign_tc_with_kewords'}</caption>
		<tr>
			<th>{lang_get s='choose_keyword'}</th>
		</tr>
		<tr>
			<td>
			<select name="keyword">
				<option value="NONE">{lang_get s='opt_none'}</option>
				{section name=Row loop=$arrKeys}
					<option value="{$arrKeys[Row].id|escape}"
							{$arrKeys[Row].selected}>{$arrKeys[Row].keyword|escape}</option>
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