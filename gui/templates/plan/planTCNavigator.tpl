{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planTCNavigator.tpl,v 1.7 2008/05/19 10:24:02 havlat Exp $
show test plan tree

rev : 20080311 - franciscom - BUGID 1427 - first developments
*}
{lang_get var="labels" 
          s='btn_update_menu,keyword,keywords_filter_help,
             filter_owner,TestPlan,test_plan,caption_nav_filter_settings'}

{assign var="keywordsFilterDisplayStyle" value=""}
{if $gui->keywordsFilterItemQty == 0}
    {assign var="keywordsFilterDisplayStyle" value="display:none;"}
{/if}

{include file="inc_head.tpl" jsTree="yes" openHead="yes"}
<script type="text/javascript">
{literal}
function pre_submit()
{
	document.getElementById('called_url').value = parent.workframe.location;
	return true;
}
</script>
{/literal}
</head>
<body>

<h1 class="title">{lang_get s='title_navigator'} {lang_get s='TestPlan'} {$additional_string|escape}</h1>
<div style="margin: 3px;">
<form method="post" id="testSetNavigator" onSubmit="javascript:return pre_submit();">
	<input type="hidden" id="called_by_me" name="called_by_me" value="1" />
	<input type="hidden" id="called_url" name="called_url" value="" />

	<table class="smallGrey" style="width:100%;">
		<caption>
			{$labels.caption_nav_filter_settings}
			{include file="inc_help.tpl" helptopic="hlp_executeFilter"}
		</caption>
    {if $gui->map_tplans != '' }
		<tr>
			<td>{$labels.test_plan}</td>
			<td>
				<select name="tplan_id" onchange="pre_submit();this.form.submit()">
			    {html_options options=$gui->map_tplans selected=$gui->tplan_id}
				</select>
			</td>
		</tr>
		{/if}
		<tr style="{$keywordsFilterDisplayStyle}">
			<td>{$labels.keyword}</td>
			<td><select name="keyword_id[]" title="{$labels.keywords_filter_help}"
			            multiple="multiple" size={$gui->keywordsFilterItemQty}>
			    {html_options options=$gui->keywords_map selected=$gui->keyword_id}
				</select>
			</td>
			<td>
      {html_radios name='keywordsFilterType' 
                   options=$gui->keywordsFilterType->options
                   selected=$gui->keywordsFilterType->selected }
			</td>
		</tr>

    {if $gui->testers }
		<tr>
			<td>{$labels.filter_owner}</td>
			<td>
				<select name="filter_assigned_to">
					{html_options options=$gui->testers selected=$gui->filter_assigned_to}
				</select>
			</td>
		</tr>
    {/if}
		<tr>
			<td>
			<input type="submit" value="{$labels.btn_update_menu}" name="doUpdateTree" />
			</td>
		</tr>
	</table>
</form>
</div>

<div class="tree" id="tree">
	{$gui->tree}
</div>

{* 20070925 *}
<script type="text/javascript">
{if $gui->src_workframe != ''}
	parent.workframe.location='{$gui->src_workframe}';
{/if}
</script>

</body>
</html>