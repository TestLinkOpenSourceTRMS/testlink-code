{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planAddTCNavigator.tpl,v 1.11 2007/09/26 06:27:40 franciscom Exp $
show test specification tree 
*}

{include file="inc_head.tpl" jsTree="yes" OpenHead="yes"}
<script type="text/javascript">
{literal}
function pre_submit()
{
 document.getElementById('called_url').value=parent.workframe.location;
 return true;
}
</script>
{/literal}
</head>
<body>

<h1>{lang_get s='title_navigator'}</h1>
<div style="margin: 3px;">
<form method="post" id="planAddTCNavigator" onSubmit="javascript:return pre_submit();">
  <input type="hidden" id="called_by_me" name="called_by_me" value="1">
  <input type="hidden" id="called_url" name="called_url" value="">

	<table class="smallGrey" width="100%">
		<caption>
			{lang_get s='caption_nav_filter_settings'}
			{include file="inc_help.tpl" filename="execFilter.html" help="execFilter" locale="$locale"}
		</caption>
		<tr>
			<td>{lang_get s='test_plan'}</td>
			<td>
				<select name="tplan_id" onchange="pre_submit();this.form.submit()">
			    {html_options options=$map_tplans selected=$tplan_id}
				</select>
			</td>
		</tr>
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
<script type="text/javascript">
{if $src_workframe != ''}
	parent.workframe.location='{$src_workframe}';
{else}
  {if $do_reload}
	  parent.workframe.location.reload();
  {/if}
{/if}
</script>
  


</body>
</html>