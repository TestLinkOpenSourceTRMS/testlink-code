{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planTCNavigator.tpl,v 1.25 2010/03/02 09:19:37 asimon83 Exp $
Scope: show test plan tree for execution

Revisions : 

	20100302 - asimon - BUGID 3049, added button in filter frame
	20100218 - asimon - BUGID 3049, changed root_href
	20100202 - asimon - BUGID 2455, BUGID 3026, changed filtering 
	                    panel is now ext collapsible panel
	20081223 - franciscom - advanced/simple filters
	20080311 - franciscom - BUGID 1427
* ---------------------------------------------------------------------- *}

{lang_get var="labels" 
          s='btn_update_menu,btn_apply_filter,keyword,keywords_filter_help,title_navigator,
             btn_bulk_update_to_latest_version,
             filter_owner,TestPlan,test_plan,caption_nav_filters,
             build,filter_tcID,filter_on,filter_result,platform, include_unassigned_testcases,
             btn_unassign_all_tcs'}

{assign var="keywordsFilterDisplayStyle" value=""}
{if $gui->keywordsFilterItemQty == 0}
    {assign var="keywordsFilterDisplayStyle" value="display:none;"}
{/if}

    {include file="inc_head.tpl" openHead="yes"}
    {include file="inc_ext_js.tpl" bResetEXTCss=1}

	{* includes Ext.ux.CollapsiblePanel *}
	<script type="text/javascript" src='gui/javascript/ext_extensions.js'></script>
    
    <script type="text/javascript">
    {literal}
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

    <script type="text/javascript">
    treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",
               loader:"", enableDD:false, dragDropBackEndUrl:'',children:""};
    </script>
    {/literal}
    
    <script type="text/javascript">
	    treeCfg.root_name = '{$gui->ajaxTree->root_node->name}';
	    treeCfg.root_id = {$gui->ajaxTree->root_node->id};
	    // BUGID 3049
	    treeCfg.root_href = "javascript:PL({$gui->tplan_id})";
	    treeCfg.children = {$gui->ajaxTree->children};
    </script>
    
    <script type="text/javascript" src='gui/javascript/execTree.js'>
    </script>

<script type="text/javascript">
{literal}
function pre_submit()
{
	document.getElementById('called_url').value = parent.workframe.location;
	return true;
}

/*
  function: update2latest
  args :
  returns:
*/
function update2latest(id)
{
	var action_url = fRoot+'/'+menuUrl+"?doAction=doBulkUpdateToLatest&level=testplan&id="+id+args;
	parent.workframe.location = action_url;
}

/**
 * open page to unassign all testcases in workframe
 *
 * @param id Testplan ID
 */
function goToUnassignPage(id)
{
	var action_url = fRoot + 'lib/testcases/containerEdit.php?doAction=doUnassignFromPlan&tplan_id=' + id;
	parent.workframe.location = action_url;
}

{/literal}
</script>

</head>

<body onload="javascript:
	triggerBuildChooser('deactivatable',
						'filter_method',
						{$gui->filter_method_specific_build});
	triggerAssignedBox('filter_assigned_to',
						'include_unassigned',
						'{$gui->str_option_any}',
						'{$gui->str_option_none}',
						'{$gui->str_option_somebody}');
	{if $gui->buildCount eq 1}
	disableUnneededFilters('filter_method',
							{$gui->filter_method_specific_build});
	{/if}
">

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{$labels.title_navigator} {$labels.TestPlan} {$gui->additional_string|escape}</h1>

{*<form method="post" action="" />
<input type="submit" id="unassign_all_tcs_button" name="unassign_all_tcs_button" value="unassign_all_tcs_button"/>
</form>*}

{if $gui->draw_tc_unassign_button}
	<form id="tc_unassign_from_tp" name="tc_unassign_from_tp" method="post" >
	<input type="button" name="unassign_all_tcs" value="{$labels.btn_unassign_all_tcs}" 
		onclick="javascript:PL({$gui->tplan_id});" />
	</form>
{/if}

{include file="inc_help.tpl" helptopic="hlp_executeFilter" show_help_icon=false}
<div id="filter_panel">
	<div class="x-panel-header x-unselectable">
		{$labels.caption_nav_filters}
	</div>

<div id="filter_settings" class="x-panel-body exec_additional_info" style="padding-top: 3px;">
<form method="post" id="testSetNavigator" onSubmit="javascript:return pre_submit();">
	<input type="hidden" id="called_by_me" name="called_by_me" value="1" />
	<input type="hidden" id="called_url" name="called_url" value="" />
	<input type='hidden' id="advancedFilterMode"  name="advancedFilterMode"  value="{$gui->advancedFilterMode}" />

	<table class="smallGrey" style="width:98%;">
		
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
			
      {html_radios name='keywordsFilterType' 
                   options=$gui->keywordsFilterType->options
                   selected=$gui->keywordsFilterType->selected }
			</td>
		</tr>

		{if $gui->optPlatform.items != ''}
		  <tr>
		  	<th>{$labels.platform}</th>
		  	<td><select name="platform_id">
		  		{html_options options=$gui->optPlatform.items selected=$gui->optPlatform.selected}
		  		</select>
		  	</td>
		  </tr>
		{/if}
		
		 {if $gui->testers }
		<tr>
			<td>{$labels.filter_owner}</td>
			<td>
			  {if $gui->advancedFilterMode }
			  <select name="filter_assigned_to[]" id="filter_assigned_to" 
			  		multiple="multiple" size={$gui->assigneeFilterItemQty}
			  		onchange="javascript: triggerAssignedBox('filter_assigned_to',
			  						'include_unassigned',
									'{$gui->str_option_any}', '{$gui->str_option_none}',
									'{$gui->str_option_somebody}');">
			  {else}
				<select name="filter_assigned_to" id="filter_assigned_to"
					onchange="javascript: triggerAssignedBox('filter_assigned_to',
									'include_unassigned',
									'{$gui->str_option_any}', '{$gui->str_option_none}',
									'{$gui->str_option_somebody}');">
			  {/if}
					{html_options options=$gui->testers selected=$gui->filter_assigned_to}
				</select>
				
				<br/>		
				<input type="checkbox" id="include_unassigned" name="include_unassigned"
	  		           value="1" {if $gui->include_unassigned} checked="checked" {/if} />
				{$labels.include_unassigned_testcases}
			
 			</td>
		</tr>
    	{/if}

	{if $gui->buildCount neq 0}
	
	<tr><td>&nbsp;</td></tr> {* empty row for a little separation *}

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
			<th>{$labels.filter_on}</th>
			<td>
			  	<select name="filter_method" id="filter_method"
			  		      onchange="javascript: triggerBuildChooser('deactivatable',
			  		      				                                  'filter_method',
										                                        {$gui->filter_method_specific_build});">
				  	{html_options options=$gui->filter_methods selected=$gui->optFilterMethodSelected}
			  	</select>
			</td>
		</tr>
		
		<tr id="deactivatable">
			<th>{$labels.build}</th>
			<td><select id="filter_build_id" name="filter_build_id">
				{html_options options=$gui->optFilterBuild.items selected=$gui->optFilterBuild.selected}
				</select>
			</td>
		</tr>
		
	{/if}
	</table>
		
		<div>
			<input type="submit" value="{$labels.btn_apply_filter}" 
			       id="doUpdateTree" name="doUpdateTree" style="font-size: 90%;" />

			{if $gui->chooseFilterModeEnabled}
			<input type="submit" id="toggleFilterMode"  name="toggleFilterMode" 
			     value="{$gui->toggleFilterModeLabel}"  
			     onclick="toggleInput('advancedFilterMode');"
			     style="font-size: 90%;"  />
      		{/if}
		</div>

</form>
</div>

{if $gui->draw_bulk_update_button }
    	<input type="button" value="{$labels.btn_bulk_update_to_latest_version}" 
    	       name="doBulkUpdateToLatest" 
    	       onclick="update2latest({$gui->tplan_id})" />
{/if}

</div> {* end filter panel *}

<div id="tree" style="overflow:auto; height:400px;border:1px solid #c3daf9;"></div>

<script type="text/javascript">
{if $gui->src_workframe != ''}
	parent.workframe.location='{$gui->src_workframe}';
{/if}
</script>

</body>
</html>