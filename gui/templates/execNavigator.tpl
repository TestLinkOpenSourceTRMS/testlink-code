{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: execNavigator.tpl,v 1.15 2007/07/06 06:28:34 franciscom Exp $ *}
{* Purpose: smarty template - show test set tree *}
{* 
rev :
     20070225 - franciscom - fixed auto-bug BUGID 642
     20070212 - franciscom - name changes on html inputs
                             use input_dimensions.conf

*}
{include file="inc_head.tpl" jsTree="yes"}

<body>
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

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
			<td><input type="text" name="tcID" value="{$tcID}" maxlength="{#TC_ID_MAXLEN#}" size="{#TC_ID_SIZE#}"/></td>
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
			<td>
			  <select name="filter_status">
			  {html_options options=$optResult selected=$optResultSelected}
			  </select>
			</td>
		</tr>
		<tr>
			<td>{lang_get s='build'}</td>
			<td><select name="build_id">
				{html_options options=$optBuild selected=$optBuildSelected}
				</select>
			</td>
		</tr>
		<tr>
			<td>{lang_get s='filter_owner'}</td>
			<td>
 			{if $disable_filter_assigned_to}
			  {$assigned_to_user}
			{else}
			  <select name="filter_assigned_to">
				{html_options options=$users selected=$filter_assigned_to}
				</select>
      {/if}
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submitOptions" value="{lang_get s='btn_apply_filter'}" style="font-size: 90%;" /></td>
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
