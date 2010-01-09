{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: execNavigator.tpl,v 1.30 2010/01/09 13:41:32 erikeloff Exp $ *}
{* Purpose: smarty template - show test set tree *}
{*
rev :
     20100109 - eloff      - BUGID 2800 - filter panel is now ext CollapsiblePanel
     20090808 - franciscom - added contribution platform
     20081227 - franciscom - BUGID 1913 - filter by same results on ALL previous builds
     20081220 - franciscom - advanced/simple filters
     20080621 - franciscom - adding ext js treemenu
     20080427 - franciscom - refactoring
     20080224 - franciscom - BUGID 1056
     20070225 - franciscom - fixed auto-bug BUGID 642
     20070212 - franciscom - name changes on html inputs
                             use input_dimensions.conf

*}
{lang_get var="labels"
          s="filter_result,caption_nav_filter_settings,filter_owner,TestPlan,
             filter_result_all_prev_builds,filter_result_any_prev_builds,platform,
             btn_apply_filter,build,keyword,filter_tcID,include_unassigned_testcases,priority"}
       
       
{assign var="keywordsFilterDisplayStyle" value=""}
{if $gui->keywordsFilterItemQty == 0}
    {assign var="keywordsFilterDisplayStyle" value="display:none;"}
{/if}

{* ===================================================================== *}
{include file="inc_head.tpl" openHead="yes"}
{if $smarty.const.USE_EXT_JS_LIBRARY}
    {include file="inc_ext_js.tpl" bResetEXTCss=1}
{/if}
          

{* includes Ext.ux.CollapsiblePanel *}
<script type="text/javascript" src='gui/javascript/ext_extensions.js'></script>
{literal}
<script type="text/javascript">
	treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",
	           loader:"", enableDD:false, dragDropBackEndUrl:'',children:""};
	Ext.onReady(function() {
		Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

		// Use a collapsible panel for filter settings
		// and place a help icon in ther header
		var panel = new Ext.ux.CollapsiblePanel({
			id: 'tl_exec_filter_settings',
			applyTo: 'filter_panel',
			tools: [{
				id: 'help',
				handler: function(event, toolEl, panel) {
					show_help(help_localized_text);
				}
			}]
		});
	});
</script>
{/literal}

<script type="text/javascript">
	treeCfg.root_name='{$gui->ajaxTree->root_node->name|escape:'javascript'}';
	treeCfg.root_id={$gui->ajaxTree->root_node->id};
	treeCfg.root_href='{$gui->ajaxTree->root_node->href}';
	treeCfg.children={$gui->ajaxTree->children};
</script>

<script type="text/javascript" src='gui/javascript/execTree.js'></script>


<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
</head>

{* ===================================================================== *}
<body>
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{assign var="build_number" value=$gui->optBuild.selected }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{$labels.TestPlan}{$tlCfg->gui_title_separator_1} {$gui->tplan_name|escape}
{$tlCfg->gui_separator_open}{$labels.build}{$tlCfg->gui_title_separator_1}
{$gui->optBuild.items.$build_number|escape}{$tlCfg->gui_separator_close}</h1>

{* include localized help message as a js-variable without icon *}
{include file="inc_help.tpl" helptopic="hlp_executeFilter" icon=false}
<div id="filter_panel">
	<div class="x-panel-header x-unselectable">
		{$labels.caption_nav_filter_settings}
	</div>

<div id="tplan_settings" class="x-panel-body exec_additional_info" style="padding-top: 3px;">
<form method="post" id="filters">
  <input type='hidden' id="tpn_view_settings"  name="tpn_view_status"  value="0" />
	<input type='hidden' id="advancedFilterMode"  name="advancedFilterMode"  value="{$gui->advancedFilterMode}" />
	
	<table class="smallGrey" style="width:98%">
		<tr>
			<th>{$labels.build}</th>
			<td><select name="build_id">
				{html_options options=$gui->optBuild.items selected=$gui->optBuild.selected}
				</select>
			</td>
		</tr>
		{if $gui->optPlatform.items != ''}
		  <tr>
		  	<td>{$labels.platform}</td>
		  	<td><select name="platform_id">
		  		{html_options options=$gui->optPlatform.items selected=$gui->optPlatform.selected}
		  		</select>
		  	</td>
		  </tr>
		{/if}
	</table>

	<table class="smallGrey" width="98%">
		<tr>
			<td>{$labels.filter_tcID}</td>
			<td><input type="text" name="targetTestCase" value="{$gui->targetTestCase}" 
			           maxlength="{#TC_ID_MAXLEN#}" size="{#TC_ID_SIZE#}"/></td>
		</tr>
		<tr style="{$keywordsFilterDisplayStyle}">
			<th>{$labels.keyword}</th>
			<td>
				<select name="keyword_id[]" multiple="multiple" size={$gui->keywordsFilterItemQty}>
			    {html_options options=$gui->keywords_map selected=$gui->keyword_id}
				</select>
			</td>
		</tr>
		<tr>
			<th>{$labels.priority}</th>
			<td>
				<select name="urgencyImportance">
				<option value="">{$gui->str_option_any}</option>
				{html_options options=$gsmarty_option_importance selected=$gui->urgencyImportance}
				</select>
			</td>
		</tr>
		<tr>
			<th>{$labels.filter_result}</th>
			<td>
			  {if $gui->advancedFilterMode }
			  	<select name="filter_status[]" multiple="multiple" size={$gui->statusFilterItemQty}>
			  {else}
			  	<select name="filter_status">
			  {/if}
			  	{html_options options=$gui->optResult selected=$gui->optResultSelected}
			  	</select>
			</td>
		</tr>
		
		<tr>
			<th>{$labels.filter_result_all_prev_builds}</th>
			<td>
				<select name="filter_status_all_prev_builds">
			  	{html_options options=$gui->resultAllPrevBuilds selected=$gui->resultAllPrevBuildsSelected}
			  	</select>
				<br />
			  	{html_radios name='resultAllPrevBuildsFilterType' 
                	options=$gui->resultAllPrevBuildsFilterType->options
                   	selected=$gui->resultAllPrevBuildsFilterType->selected }
      		</td>
		</tr>
		
		<tr>
			<th>{$labels.filter_result_any_prev_builds}</th>
			<td>
				<select name="statusAnyOfPrevBuilds">
			  	{html_options options=$gui->statusAnyOfPrevBuilds selected=$gui->statusAnyOfPrevBuildsSelected}
			  	</select>
			</td>
		</tr>



		
		<tr>
			<th>{$labels.filter_owner}</th>
			<td>
 			{if $gui->disable_filter_assigned_to}
			  {$gui->assigned_to_user}
			{else}
			  {if $gui->advancedFilterMode }
			  <select name="filter_assigned_to[]" multiple="multiple" size={$gui->assigneeFilterItemQty}>
			  {else}
				<select name="filter_assigned_to">
			  {/if}
					{html_options options=$gui->users selected=$gui->filter_assigned_to}
			  </select>
			{/if}
			<br />
			<input type="checkbox" id="include_unassigned" name="include_unassigned"
  		           value="1" {if $gui->include_unassigned} checked="checked" {/if} />
			{$labels.include_unassigned_testcases}
 			</td>
		</tr>
		{$gui->design_time_cfields}
	</table>
		
		<div>
			<input type="submit" name="submitOptions" value="{$labels.btn_apply_filter}" 
					style="font-size: 90%;" />
			<input type="submit" id="toggleFilterMode"  name="toggleFilterMode" 
					value="{$gui->toggleFilterModeLabel}" style="font-size: 90%;"  
					onclick="toggleInput('advancedFilterMode');" />
		</div>
</form>
</div>
</div> {* end filter panel *}


{* ===================================================================== *}
<div id="tree" style="overflow:auto; height:500px;border:1px solid #c3daf9;"></div>

{if $gui->src_workframe != ''}
<script type="text/javascript">
	parent.workframe.location='{$gui->src_workframe}';
</script>
{/if}

</body>
</html>
