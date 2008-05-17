{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: execNavigator.tpl,v 1.11 2008/05/17 14:19:50 franciscom Exp $ *}
{* Purpose: smarty template - show test set tree *}
{*
rev :
     20080427 - franciscom - refactoring
     20080224 - franciscom - BUGID 1056
     20070225 - franciscom - fixed auto-bug BUGID 642
     20070212 - franciscom - name changes on html inputs
                             use input_dimensions.conf

*}
{lang_get var="labels"
          s="filter_result,caption_nav_filter_settings,filter_owner,
             btn_apply_filter,build,keyword,filter_tcID,include_unassigned_testcases"}
       
       
{assign var="keywordsFilterDisplayStyle" value=""}
{if $gui->keywordsFilterItemQty == 0}
    {assign var="keywordsFilterDisplayStyle" value="display:none;"}
{/if}
          
{include file="inc_head.tpl" jsTree="yes"}

<body>
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{lang_get s='TestPlan'} {$gui->tplan_id|escape} {$gui->tplan_name|escape}</h1>

<div style="margin: 3px;">
<form method="post">
	<table class="smallGrey" width="100%">
		<caption>
			{$labels.caption_nav_filter_settings}
			{include file="inc_help.tpl" filename="execFilter.html" help="execFilter" locale="$locale"}
		</caption>
		<tr>
			<td>{$labels.filter_tcID}</td>
			<td><input type="text" name="targetTestCase" value="{$gui->targetTestCase}" 
			           maxlength="{#TC_ID_MAXLEN#}" size="{#TC_ID_SIZE#}"/></td>
		</tr>
		<tr style="{$keywordsFilterDisplayStyle}">
			<td>{$labels.keyword}</td>
			<td><select name="keyword_id[]" multiple="multiple" size={$gui->keywordsFilterItemQty}>
			    {html_options options=$gui->keywords_map selected=$gui->keyword_id}
				</select>
			</td>
		</tr>
		<tr>
				<td>{$labels.filter_result}</td>
			<td>
			  <select name="filter_status">
			  {html_options options=$gui->optResult selected=$gui->optResultSelected}
			  </select>
			</td>
		</tr>
		<tr>
			<td>{$labels.build}</td>
			<td><select name="build_id">
				{html_options options=$gui->optBuild selected=$gui->optBuildSelected}
				</select>
			</td>
		</tr>
		<tr>
			<td>{$labels.filter_owner}</td>
			<td>
 			{if $gui->disable_filter_assigned_to}
			  {$gui->assigned_to_user}
			{else}
				<select name="filter_assigned_to">
					{html_options options=$gui->users selected=$gui->filter_assigned_to}
				</select>
			{/if}
			</td>
		</tr>
  	<tr>
   		<td>{$labels.include_unassigned_testcases}</td>
  		<td>
  		   <input type="checkbox"
  		           id="include_unassigned" name="include_unassigned"
  		           value="1"
  		           {if $gui->include_unassigned} checked="checked" {/if} />
  		</td>
  	</tr>
        	{$gui->design_time_cfields}
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submitOptions" value="{$labels.btn_apply_filter}" style="font-size: 90%;" /></td>
		</tr>
	</table>
</form>
</div>

<div class="tree" id="tree">
{$gui->tree}
</div>

{if $gui->src_workframe != ''}
<script type="text/javascript">
	parent.workframe.location='{$gui->src_workframe}';
</script>
{/if}

</body>
</html>
