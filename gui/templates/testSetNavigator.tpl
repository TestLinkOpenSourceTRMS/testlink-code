{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: testSetNavigator.tpl,v 1.2 2006/11/02 10:07:37 franciscom Exp $
show test specification tree 
*}

{include file="inc_head.tpl" jsTree="yes"}
<body>

<h1>{lang_get s='title_navigator'}</h1>
<div style="margin: 3px;">
<form method="post">
	<table class="smallGrey" style="width:100%;">
		<caption>
			{lang_get s='caption_nav_filter_settings'}
			{include file="inc_help.tpl" filename="execFilter.html"}
		</caption>
		<tr>
			<td>{lang_get s='keyword'}</td>
			<td><select name="keyword_id">
			    {html_options options=$keywords_map selected=$keyword_id}
				</select>
			</td>
		</tr>
		<tr>
			<td>
			<input type="submit" value="{lang_get s='btn_update_menu'}" name="filter" />
			</td>
		</tr>
	</table>
</form>
</div>

<div class="tree" id="tree">
	{$tree}
</div>

</body>
</html>