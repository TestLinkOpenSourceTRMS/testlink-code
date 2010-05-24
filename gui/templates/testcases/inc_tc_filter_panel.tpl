{*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: inc_tc_filter_panel.tpl,v 1.8 2010/05/24 18:43:07 franciscom Exp $
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


{* Assigning/initializing of all used variables is done here.
   I did not use foreach or some construct like that here because this should be more performant.
   It is more code, but doing this here in one place at the top keeps the 
   template code below simple, clean and readable. *}



{if isset($gui->keywordsFilterItemQty)}
	{assign var="keywordsFilterItemQty" value=$gui->keywordsFilterItemQty}
{else}
	{assign var="keywordsFilterItemQty" value=0}
{/if}

{if $keywordsFilterItemQty == 0}
    {assign var="keywordsFilterDisplayStyle" value="display:none;"}
{else}
	{assign var="keywordsFilterDisplayStyle" value=""}
{/if}

{if isset($gui->assigneeFilterItemQty)}
    {assign var="assigneeFilterItemQty" value=$gui->assigneeFilterItemQty}
{else}
	{assign var="assigneeFilterItemQty" value=0}
{/if}

{if isset($gui->statusFilterItemQty)}
    {assign var="statusFilterItemQty" value=$gui->statusFilterItemQty}
{else}
	{assign var="statusFilterItemQty" value=0}
{/if}

{if isset($gui->keywordsMap)}
    {assign var="keywordsMap" value=$gui->keywordsMap}
{else}
	{assign var="keywordsMap" value=0}
{/if}

{if isset($gui->optBuild)}
    {assign var="optBuild" value=$gui->optBuild}
{else}
	{assign var="optBuild" value=0}
{/if}

{if isset($gui->keywordID)}
    {assign var="keywordID" value=$gui->keywordID}
{else}
	{assign var="keywordID" value=0}
{/if}

{if isset($gui->tPlanID)}
    {assign var="tPlanID" value=$gui->tPlanID}
{else}
	{assign var="tPlanID" value=0}
{/if}

{if isset($gui->keywordsFilterTypes)}
	{assign var="keywordsFilterType" value=$gui->keywordsFilterTypes}
{else}
	{assign var="keywordsFilterType" value=""}
{/if}


{if isset($gui->toggleFilterModeLabel)}
	{assign var="toggleFilterModeLabel" value=$gui->toggleFilterModeLabel}
{else}
	{assign var="toggleFilterModeLabel" value=""}
{/if}

{if isset($gui->mapTPlans)}
	{assign var="mapTPlans" value=$gui->mapTPlans}
{else}
	{assign var="mapTPlans" value=""}
{/if}

{if isset($gui->optPlatform)}
	{assign var="optPlatform" value=$gui->optPlatform}
{else}
	{assign var="optPlatform" value=""}
{/if}

{if isset($gui->testers)}
	{assign var="testers" value=$gui->testers}
{else}
	{assign var="testers" value=""}
{/if}

{if isset($gui->buildCount)}
	{assign var="buildCount" value=$gui->buildCount}
{else}
	{assign var="buildCount" value=0}
{/if}

{if isset($gui->execType)}
	{assign var="execType" value=$gui->execType}
{else}
	{assign var="execType" value=0}
{/if}

{if isset($gui->tcSpecRefreshOnAction)}
	{assign var="tcSpecRefreshOnAction" value=$gui->tcSpecRefreshOnAction}
{else}
	{assign var="tcSpecRefreshOnAction" value=0}
{/if}

{if isset($gui->tsuitesCombo)}
	{assign var="tsuitesCombo" value=$gui->tsuitesCombo}
{else}
	{assign var="tsuitesCombo" value=0}
{/if}

{if isset($gui->tsuiteChoice)}
	{assign var="tsuiteChoice" value=$gui->tsuiteChoice}
{else}
	{assign var="tsuiteChoice" value=0}
{/if}

{if isset($gui->optFilterBuild)}
	{assign var="optFilterBuild" value=$gui->optFilterBuild}
{else}
	{assign var="optFilterBuild" value=0}
{/if}

{if isset($gui->optFilterMethodSelected)}
	{assign var="optFilterMethodSelected" value=$gui->optFilterMethodSelected}
{else}
	{assign var="optFilterMethodSelected" value=0}
{/if}

{if isset($gui->filterMethods)}
	{assign var="filterMethods" value=$gui->filterMethods}
{else}
	{assign var="filterMethods" value=0}
{/if}

{if isset($gui->filterMethodSpecificBuild)}
	{assign var="filterMethodSpecificBuild" value=$gui->filterMethodSpecificBuild}
{else}
	{assign var="filterMethodSpecificBuild" value=0}
{/if}

{if isset($gui->optResult)}
	{assign var="optResult" value=$gui->optResult}
{else}
	{assign var="optResult" value=0}
{/if}

{if isset($gui->optResultSelected)}
	{assign var="optResultSelected" value=$gui->optResultSelected}
{else}
	{assign var="optResultSelected" value=0}
{/if}

{if isset($gui->includeUnassigned)}
	{assign var="includeUnassigned" value=$gui->includeUnassigned}
{else}
	{assign var="includeUnassigned" value=0}
{/if}

{if isset($gui->filterAssignedTo)}
	{assign var="filterAssignedTo" value=$gui->filterAssignedTo}
{else}
	{assign var="filterAssignedTo" value=0}
{/if}

{if isset($gui->strOptionAny)}
	{assign var="strOptionAny" value=$gui->strOptionAny}
{else}
	{assign var="strOptionAny" value=0}
{/if}

{if isset($gui->strOptionSomebody)}
	{assign var="strOptionSomebody" value=$gui->strOptionSomebody}
{else}
	{assign var="strOptionSomebody" value=0}
{/if}

{if isset($gui->strOptionNone)}
	{assign var="strOptionNone" value=$gui->strOptionNone}
{else}
	{assign var="strOptionNone" value=0}
{/if}

{if isset($gui->assigned_to_user)}
	{assign var="assignedToUser" value=$gui->assigned_to_user}
{else}
	{assign var="assignedToUser" value=0}
{/if}

{if isset($gui->disable_filter_assigned_to)}
	{assign var="disableFilterAssignedTo" value=$gui->disable_filter_assigned_to}
{else}
	{assign var="disableFilterAssignedTo" value=0}
{/if}

{if isset($gui->urgencyImportanceSelectable)}
	{assign var="urgencyImportanceSelectable" value=$gui->urgencyImportanceSelectable}
{else}
	{assign var="urgencyImportanceSelectable" value=0}
{/if}

{if isset($gui->urgencyImportance)}
	{assign var="urgencyImportance" value=$gui->urgencyImportance}
{else}
	{assign var="urgencyImportance" value=0}
{/if}

{if isset($gui->design_time_cfields)}
	{assign var="designTimeCFields" value=$gui->design_time_cfields}
{else}
	{assign var="designTimeCFields" value=""}
{/if}

{if isset($gui->feature)}
	{assign var="feature" value=$gui->feature}
{else}
	{assign var="feature" value=0}
{/if}




<form method="get" id="tc_filter_panel_form">

{if $gui->controlPanel->drawTCUnassignButton}
	<input type="button" name="unassign_all_tcs" value="{$labels.btn_unassign_all_tcs}" 
		onclick="javascript:PL({$tPlanID});" />
{/if}

{* hidden feature input (mainly for testcase edit when refreshing frame) *}
{if $feature}
<input type="hidden" id="feature" name="feature" value="{$feature}" />
{/if}

{include file="inc_help.tpl" helptopic="hlp_executeFilter" show_help_icon=false}

{if $showSettings == 'yes'}
	
	<div id="settings_panel">
		<div class="x-panel-header x-unselectable">
			{$labels.caption_nav_settings}
		</div>
	
		<div id="tplan_settings" class="x-panel-body exec_additional_info" "style="padding-top: 3px;">
			<input type='hidden' id="tpn_view_settings"  name="tpn_view_status"  value="0" />
			
			<table class="smallGrey" style="width:98%;">
			
			{if $mapTPlans}
				<tr>
					<th>{$labels.test_plan}</th>
					<td>
						<select name="tplan_id" onchange="this.form.submit()">
						{html_options options=$mapTPlans selected=$tPlanID}
						</select>
					</td>
				</tr>
			{/if}
	
			{if $optPlatform && $optPlatform.items != ''}
				<tr>
					<th>{$labels.platform}</th>
					<td>
						<select name="platform_id" onchange="this.form.submit()">
						{html_options options=$optPlatform.items selected=$optPlatform.selected}
						</select>
					</td>
				</tr>
			{/if}
			
			{if $optBuild && $optBuild.items != ''}
				<tr>
					<th>{$labels.exec_build}</th>
					<td>
						<select name="build_id" onchange="this.form.submit()">
						{html_options options=$optBuild.items selected=$optBuild.selected}
						</select>
					</td>
				</tr>
			{/if}
			
			<tr>
	   			<td>{$labels.do_auto_update}</td>
	  			<td>
	  			   <input type="hidden" id="hidden_tcspec_refresh_on_action"   
	  			           name="hidden_tcspec_refresh_on_action" />
	  			
	  			   <input type="checkbox" 
	  			           id="cbtcspec_refresh_on_action"   name="tcspec_refresh_on_action"
	  			           value="1"
	  			           {if $tcSpecRefreshOnAction eq "yes"} checked {/if}
	  			           style="font-size: 90%;" onclick="this.form.submit()"/>
	  			</td>
	  		</tr>
			
			</table>
		</div> {* tplan_settings *}
	</div> {* settings_panel *}
	
{/if} {* show settings *}

{if $showFilters == 'yes'}
	
	<div id="filter_panel">
		<div class="x-panel-header x-unselectable">
			{$labels.caption_nav_filters}
		</div>
	
	<div id="filter_settings" class="x-panel-body exec_additional_info" style="padding-top: 3px;">

		<input type="hidden" id="called_by_me" name="called_by_me" value="1" />
		<input type="hidden" id="called_url" name="called_url" value="" />
		<input type='hidden' id="panelFiltersAdvancedFilterMode"  name="panelFiltersAdvancedFilterMode"  
		       value="{$gui->controlPanel->advancedFilterMode}" />
	
		<table class="smallGrey" style="width:98%;">
	    {if $mapTPlans != '' && $executionMode == 'no'}
			<tr>
				<td>{$labels.test_plan}</td>
				<td>
					<select name="tplan_id" onchange="this.form.submit()">
				    {html_options options=$mapTPlans selected=$tPlanID}
					</select>
				</td>
			</tr>
		{/if}
			
		{if $tsuitesCombo}
			<tr>
	    		<td>{$labels.testsuite}</td>
	    		<td>
	    			<select name="tsuites_to_show" style="width:auto">
	    				{html_options options=$tsuitesCombo selected=$tsuiteChoice}
	    			</select>
	    		</td>
	    	</tr>
    	{/if}
			
		{if $keywordsMap}
			<tr style="{$keywordsFilterDisplayStyle}">
				<td>{$labels.keyword}</td>
				<td><select name="keyword_id[]" title="{$labels.keywords_filter_help}"
				            multiple="multiple" size={$keywordsFilterItemQty}>
				    {html_options options=$keywordsMap selected=$keywordID}
					</select>
				
	      {html_radios name='keywordsFilterType' 
	                   options=$keywordsFilterType->options
	                   selected=$keywordsFilterType->selected}
				</td>
			</tr>
		{/if}
		
			{if $optPlatform && $optPlatform.items != '' && $executionMode == 'no'}
			  <tr>
			  	<th>{$labels.platform}</th>
			  	<td><select name="platform_id">
			  		{html_options options=$optPlatform.items selected=$optPlatform.selected}
			  		</select>
			  	</td>
			  </tr>
			{/if}
			
			{if $urgencyImportanceSelectable}
				<tr>
					<th width="75">{$labels.priority}</th>
					<td>
						<select name="urgencyImportance">
						<option value="">{$strOptionAny}</option>
						{html_options options=$gsmarty_option_importance selected=$urgencyImportance}
						</select>
					</td>
				</tr>
			{/if}
			
			{if $session['testprojectOptions']->automationEnabled && $gui->controlPanel->filters.execTypes.items != ''}
				<tr>
					<td>{$labels.execution_type}</td>
		  			<td>
				    <select name="panelFiltersExecType">
	    	  	  {html_options options=$gui->controlPanel->filters.execTypes.items 
	    	  	                selected=$gui->controlPanel->filters.execTypes.selected}
		    	  </select>
					</td>	
				</tr>
			{/if}
			
			{if $testers}
			<tr>
				<td>{$labels.filter_owner}</td>
				<td>
				
				{if $disableFilterAssignedTo && $assignedToUser}
					{$assignedToUser}
				{else}
					  {if $gui->controlPanel->advancedFilterMode}
					  <select name="filter_assigned_to[]" id="filter_assigned_to" 
					  		multiple="multiple" size={$assigneeFilterItemQty}
					  		{html_options options=$testers selected=$filterAssignedTo}
						</select>						
					  {else}
						<select name="filter_assigned_to" id="filter_assigned_to"
							onchange="javascript: triggerAssignedBox('filter_assigned_to',
											'include_unassigned',
											'{$strOptionAny}', '{$strOptionNone}',
											'{$strOptionSomebody}');">
							{html_options options=$testers selected=$filterAssignedTo}
						</select>
						
						<br/>		
						<input type="checkbox" id="include_unassigned" name="include_unassigned"
			  		           value="1" {if $includeUnassigned} checked="checked" {/if} />
						{$labels.include_unassigned_testcases}
						{/if}
				{/if}
				
	 			</td>
			</tr>
	    	{/if}
	
	
	{* custom fields are placed here *}
	
	{if $designTimeCFields}
		<tr><td>&nbsp;</td></tr> {* empty row for a little separation *}
		{$designTimeCFields}
	{/if}
	
	
	
	{* result filtering parts *}
	{if $buildCount neq 0}
		
		<tr><td>&nbsp;</td></tr> {* empty row for a little separation *}
	
		{if $optResult}
	   		<tr>
				<th>{$labels.filter_result}</th>
				<td>
				  {if $gui->controlPanel->advancedFilterMode}
				  	<select name="filter_status[]" multiple="multiple" size={$statusFilterItemQty}>
				  {else}
				  	<select name="filter_status">
				  {/if}
				  	{html_options options=$optResult selected=$optResultSelected}
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
						                                                {$filterMethodSpecificBuild});">
					  	{html_options options=$filterMethods selected=$optFilterMethodSelected}
				  	</select>
				</td>
			</tr>
			
			<tr id="deactivatable">
				<th>{$labels.build}</th>
				<td><select id="filter_build_id" name="filter_build_id">
					{html_options options=$optFilterBuild.items selected=$optFilterBuild.selected}
					</select>
				</td>
			</tr>
			
	{/if}

	
		</table>
			
			<div>
				<input type="submit" value="{$labels.btn_apply_filter}" 
				       id="doUpdateTree" name="doUpdateTree" style="font-size: 90%;" />
	
				{if $gui->controlPanel->chooseFilterModeEnabled}
				<input type="submit" id="toggleFilterMode"  name="toggleFilterMode" 
				     value="{$toggleFilterModeLabel}"  
				     onclick="toggleInput('panelFiltersAdvancedFilterMode');"
				     style="font-size: 90%;"  />
	      		{/if}
			</div>
	
		{if $gui->controlPanel->drawBulkUpdateButton}
	    	<input type="button" value="{$labels.btn_bulk_update_to_latest_version}" 
	    	       name="doBulkUpdateToLatest" 
	    	       onclick="update2latest({$tPlanID})" />
		{/if}
	
	</form>
	
	</div> {* filter_settings *}
	
	</div> {* filter_panel *}
	
{/if} {* show filters *}
