{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planAddTCNavigator.tpl,v 1.8 2007/01/24 08:10:24 franciscom Exp $
show test specification tree 
*}

{include file="inc_head.tpl" jsTree="yes"}
<body>

<h1>{lang_get s='title_navigator'}</h1>
<div style="margin: 3px;">
<form method="post">
	<table class="smallGrey" width="100%">
		<caption>
			{lang_get s='caption_nav_filter_settings'}
			{include file="inc_help.tpl" filename="execFilter.html" help="execFilter" locale="$locale"}}
		</caption>
		<tr>
			<td>{lang_get s='keyword'}</td>
			<td>
				<select name="keyword_id">
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

{* 20061030 - update the right pane *}
{if $src_workframe != ''}
<script type="text/javascript">
	parent.workframe.location='{$src_workframe}';
</script>
{/if}


</body>
</html>