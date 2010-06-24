{*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: inc_tc_filter_panel.tpl,v 1.14 2010/06/24 17:25:53 asimon83 Exp $
 * 
 * Shows the filter panel. Included by some other templates.
 * At the moment: planTCNavigator, execNavigator, planAddTCNavigator, tcTree.
 * Inspired by idea in discussion regarding BUGID 3301.
 *
 * Naming conventions for variables are based on the names 
 * used in plan/planTCNavigator.tpl.
 * That template was also the base for most of the html code used in here.
 *
 * @author Andreas Simon
 * @internal revision
 *  20100501 - franciscom - BUGID 3410: Smarty 3.0 compatibility
 *}


{lang_get var=labels s='caption_nav_settings, caption_nav_filters, platform, test_plan,
                        exec_build,build,filter_tcID,filter_on,filter_result,
                        btn_update_menu,btn_apply_filter,keyword,keywords_filter_help,
                        filter_owner,TestPlan,test_plan,caption_nav_filters,
                        platform, include_unassigned_testcases, btn_unassign_all_tcs,
                        execution_type, do_auto_update, testsuite, 
                        btn_bulk_update_to_latest_version, priority'} 


{assign var="showFilters" value=false}
{assign var="showSettings" value=false}


<form method="get" id="tc_filter_panel_form">

{if isset($gui->drawTCUnassignButton)}
	<input type="button" name="unassign_all_tcs" value="{$labels.btn_unassign_all_tcs}" 
		onclick="javascript:PL({$gui->tPlanID});" />
{/if}

{if isset($gui->drawBulkUpdateButton)}
    <input type="button" value="{$labels.btn_bulk_update_to_latest_version}" 
           name="doBulkUpdateToLatest" 
           onclick="update2latest({$gui->tPlanID})" />
{/if}

{* hidden feature input (mainly for testcase edit when refreshing frame) *}
{if isset($gui->feature)}
<input type="hidden" id="feature" name="feature" value="{$gui->feature}" />
{/if}

{include file="inc_help.tpl" helptopic="hlp_executeFilter" show_help_icon=false}
	
{* 
 * Settings are not configurable by the user. 
 * They depend on the mode that is defined when including this template.
 * @TODO will be replaced by logic in filterPanel classes
 *}

<div id="settings_panel">
	<div class="x-panel-header x-unselectable">
		{$labels.caption_nav_settings}
	</div>

	<div id="tplan_settings" class="x-panel-body exec_additional_info" "style="padding-top: 3px;">
		<input type='hidden' id="tpn_view_settings"  name="tpn_view_status"  value="0" />
		
		<table class="smallGrey" style="width:98%;">
		
		{if $mode == 'plan_mode' || $mode == 'plan_add_mode' || $mode == 'exec_mode'}
			<tr>
				<th>{$labels.test_plan}</th>
				<td>
					<select name="tplan_id" onchange="this.form.submit()">
					{html_options options=$gui->mapTPlans selected=$gui->tPlanID}
					</select>
				</td>
			</tr>
		{/if}

		{if $mode == 'exec_mode' || $mode == 'plan_mode'}
			<tr>
				<th>{$labels.platform}</th>
				<td>
					<select name="platform_id" onchange="this.form.submit()">
					{html_options options=$gui->optPlatform.items selected=$gui->optPlatform.selected}
					</select>
				</td>
			</tr>
		{/if}
		
		{if $mode == 'exec_mode'}
			<tr>
				<th>{$labels.exec_build}</th>
				<td>
					<select name="build_id" onchange="this.form.submit()">
					{html_options options=$gui->optBuild.items selected=$gui->optBuild.selected}
					</select>
				</td>
			</tr>
		{/if}
		
		{if $mode == 'exec_mode' || $mode == 'edit_mode'}
			<tr>
	   			<td>{$labels.do_auto_update}</td>
	  			<td>
	  			   <input type="hidden" id="hidden_setting_refresh_tree_on_action"   
	  			           name="hidden_setting_refresh_tree_on_action" />
	  			
	  			   <input type="checkbox" 
	  			           id="cbsetting_refresh_tree_on_action"   name="setting_refresh_tree_on_action"
	  			           value="1"
	  			           {if isset($gui->tcSpecRefreshOnAction) 
	  			           	&& $gui->tcSpecRefreshOnAction == "yes"} checked {/if}
	  			           style="font-size: 90%;" onclick="this.form.submit()"/>
	  			</td>
	  		</tr>
		{/if}
		
		</table>
	</div> {* tplan_settings *}
</div> {* settings_panel *}


{if $gui->filter_config->show_filters}
	
	<div id="filter_panel">
		<div class="x-panel-header x-unselectable">
			{$labels.caption_nav_filters}
		</div>
	
	<div id="filter_settings" class="x-panel-body exec_additional_info" style="padding-top: 3px;">

		<input type="hidden" id="called_by_me" name="called_by_me" value="1" />
		<input type="hidden" id="called_url" name="called_url" value="" />
		{* this hidden input is only needed when advanced filter mode is provided *}
		{if isset($gui->advancedFilterMode)}
		<input type='hidden' id="advancedFilterMode"  name="advancedFilterMode"  value="{$gui->advancedFilterMode}" />
		{/if}
		<table class="smallGrey" style="width:98%;">
				
		{if isset($gui->filter_config->filter_toplevel_testsuite) 
		    && $gui->filter_config->filter_toplevel_testsuite}
			<tr>
	    		<td>{$labels.testsuite}</td>
	    		<td>
	    			<select name="tsuites_to_show" style="width:auto">
	    				{html_options options=$gui->tsuitesCombo selected=$gui->tsuiteChoice}
	    			</select>
	    		</td>
	    	</tr>
    	{/if}
			
		{if isset($gui->filter_config->filter_keywords)
			&& $gui->filter_config->filter_keywords}
			{if !isset($keywordsFilterDisplayStyle)}
				{assign name=keywordsFilterDisplayStyle value=''}
			{/if}
			<tr style="{$keywordsFilterDisplayStyle}">
				<td>{$labels.keyword}</td>
				<td><select name="keyword_id[]" title="{$labels.keywords_filter_help}"
				            multiple="multiple" size={$gui->keywordsFilterItemQty}>
				    {html_options options=$gui->keywordsMap selected=$gui->keywordID}
					</select>
				
			{html_radios name='keywordsFilterType' 
	                   options=$gui->keywordsFilterType->options
	                   selected=$gui->keywordsFilterType->selected}
				</td>
			</tr>
		{/if}
		
		{if isset($gui->filter_config->filter_priority)
			&& $gui->filter_config->filter_priority
			&& $gui->urgencyImportanceSelectable} {* TODO: prüfen, wo die Variable herkommt und ob sie von anderen Einstellungen abhängt oder einfach hier entfernt werden kann*}
			<tr>
				<th width="75">{$labels.priority}</th>
				<td>
					<select name="urgencyImportance">
					<option value="">{$gui->strOptionAny}</option>
					{html_options options=$gsmarty_option_importance selected=$gui->urgencyImportance}
					</select>
				</td>
			</tr>
		{/if}
		
		{if isset($gui->filter_config->filter_execution_type)
			&& $gui->filter_config->filter_execution_type
			&& $session['testprojectOptions']->automationEnabled}
			<tr>
				<td>{$labels.execution_type}</td>
	  			<td>
			    <select name="exec_type">
    	  	  {html_options options=$gui->execTypeMap selected=$gui->execType}
	    	  </select>
				</td>	
			</tr>
		{/if}
		
		{if isset($gui->filter_config->filter_assigned_user) 
			&& $gui->filter_config->filter_assigned_user}
		<tr>
			<td>{$labels.filter_owner}</td>
			<td>
			
			{if $gui->disable_filter_assigned_to && $gui->assignedToUser}
				{$gui->assignedToUser}
			{else}
				  {if $gui->advancedFilterMode}
				  <select name="filter_assigned_to[]" id="filter_assigned_to" 
				  		multiple="multiple" size={$gui->assigneeFilterItemQty}
				  		{html_options options=$gui->testers selected=$gui->filterAssignedTo}
					</select>						
				  {else}
					<select name="filter_assigned_to" id="filter_assigned_to"
						onchange="javascript: triggerAssignedBox('filter_assigned_to',
										'include_unassigned',
										'{$gui->strOptionAny}', '{$gui->strOptionNone}',
										'{$gui->strOptionSomebody}');">
						{html_options options=$gui->testers selected=$gui->filterAssignedTo}
					</select>
					
					<br/>		
					<input type="checkbox" id="include_unassigned" name="include_unassigned"
		  		           value="1" {if $gui->includeUnassigned} checked="checked" {/if} />
					{$labels.include_unassigned_testcases}
					{/if}
			{/if}
			
 			</td>
		</tr>
    	{/if}
	
		
		{* custom fields are placed here *}
		
		{if isset($gui->filter_config->filter_custom_fields)
			&& $gui->filter_config->filter_custom_fields 
			&& isset($gui->design_time_cfields)}
			<tr><td>&nbsp;</td></tr> {* empty row for a little separation *}
			{$gui->design_time_cfields}
		{/if}
		
	
	{* result filtering parts *}
	{if isset($gui->filter_config->filter_result)
		&& $gui->filter_config->filter_result
		&& $gui->buildCount != 0}
		
		<tr><td>&nbsp;</td></tr> {* empty row for a little separation *}
	
		{if $gui->optResult}
	   		<tr>
				<th>{$labels.filter_result}</th>
				<td>
				  {if $gui->advancedFilterMode}
				  	<select name="filter_status[]" multiple="multiple" size={$gui->statusFilterItemQty}>
				  {else}
				  	<select name="filter_status">
				  {/if}
				  	{html_options options=$gui->optResult selected=$gui->optResultSelected}
				  	</select>
				</td>
			</tr>
		{/if}
		
			<tr>
				<th>{$labels.filter_on}</th>
				<td>
				  	<select name="filter_method" id="filter_method"
				  		      onchange="javascript: triggerBuildChooser('deactivatable',
				  		                                                'filter_method',
						                                                {$gui->filterMethodSpecificBuild});">
					  	{html_options options=$gui->filterMethods selected=$gui->optFilterMethodSelected}
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
	
				{if isset($gui->chooseFilterModeEnabled)}
				<input type="submit" id="toggleFilterMode"  name="toggleFilterMode" 
				     value="{$gui->toggleFilterModeLabel}"  
				     onclick="toggleInput('advancedFilterMode');"
				     style="font-size: 90%;"  />
	      		{/if}
			</div>
	
	</form>
	
	</div> {* filter_settings *}
	
	</div> {* filter_panel *}
	
{/if} {* show filters *}
