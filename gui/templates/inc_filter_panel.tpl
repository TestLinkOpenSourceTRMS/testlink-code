{*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: inc_filter_panel.tpl,v 1.12.2.2 2010/11/22 09:16:00 asimon83 Exp $
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
 *  20110222 - asimon - BUGID 4253: enable filter method selector before submitting 
                                    the form so its value doesn't get lost
 *  20101121 - asimon - BUGID 4042: "Expand/Collapse" Button for Trees
 *  20101101 - franciscom - openExportTestPlan() interface changes
 *  20101009 - franciscom - fixed error viewer warning
 *  20101007 - franciscom - BUGID 3270 - Export Test Plan in XML Format
 *  20100811 - asimon - BUGID 3566: show/hide CF
 *  20100810 - asimon - added TC ID filter for Test Cases
 *  20100808 - asimon - additional fields for requirement filtering
 *  20100709 - asimon - BUGID 3406: changes to unassign button
 *  20100610 - asimon - first implementation of filter control class hierarchy
 *  20100501 - franciscom - BUGID 3410: Smarty 3.0 compatibility
 *}

{lang_get var=labels s='caption_nav_settings, caption_nav_filters, platform, test_plan,
                        build,filter_tcID,filter_on,filter_result,
                        btn_update_menu,btn_apply_filter,keyword,keywords_filter_help,
                        filter_owner,TestPlan,test_plan,caption_nav_filters,
                        platform, include_unassigned_testcases,
                        btn_remove_all_tester_assignments, execution_type, 
                        do_auto_update, testsuite, btn_reset_filters,
                        btn_bulk_update_to_latest_version, priority, tc_title,
                        custom_field, search_type_like,
                        document_id, req_expected_coverage, title,
                        status, req_type, req_spec_type, th_tcid, has_relation_type,btn_export_testplan_tree'}

{config_load file="input_dimensions.conf" section="treeFilterForm"}

{* BUGID 4253: enable filter method selector before submitting the form so its value doesn't get lost *}
<form method="post" id="filter_panel_form" name="filter_panel_form" 
      onsubmit="document.getElementById('filter_result_method').disabled=false;">

{* hidden input with token to manage transfer of data between left and right frame *}
{if isset($control->form_token)}
  <input type="hidden" name="form_token" value="{$control->form_token}">
{/if}

{assign var="platformID" value=0}

{if $control->draw_tc_unassign_button}
	<input type="button" 
	       name="removen_all_tester_assignments"
	       value="{$labels.btn_remove_all_tester_assignments}"
	       onclick="javascript:delete_testers_from_build({$control->settings.setting_build.selected});"
	/>
{/if}

{if $control->draw_bulk_update_button}
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
 * The settings are not configurable by the user.
 * They depend on the mode that is defined by the logic in *filterControl classes.
 *}

{if $control->display_settings}
	<div id="settings_panel">
		<div class="x-panel-header x-unselectable">
			{$labels.caption_nav_settings}
		</div>

		<div id="settings" class="x-panel-body exec_additional_info" "style="padding-top: 3px;">
			<input type='hidden' id="tpn_view_settings" name="tpn_view_status"  value="0" />

			<table class="smallGrey" style="width:98%;">

			{if $control->settings.setting_testplan}
				<tr>
					<th>{$labels.test_plan}</th>
					<td>
						<select name="setting_testplan" onchange="this.form.submit()">
						{html_options options=$control->settings.setting_testplan.items
						              selected=$control->settings.setting_testplan.selected}
						</select>
					</td>
				</tr>
			{/if}

			{if $control->settings.setting_platform}
			  {assign var="platformID" value=$control->settings.setting_platform.selected}
				<tr>
					<th>{$labels.platform}</th>
					<td>
						<select name="setting_platform" onchange="this.form.submit()">
						{html_options options=$control->settings.setting_platform.items
						              selected=$control->settings.setting_platform.selected}
						</select>
					</td>
				</tr>
			{/if}

			{if $control->settings.setting_build}
				<tr>
					<th>{$control->settings.setting_build.label}</th>
					<td>
						<select name="setting_build" onchange="this.form.submit()">
						{html_options options=$control->settings.setting_build.items
						              selected=$control->settings.setting_build.selected}
						</select>
					</td>
				</tr>
			{/if}

			{if $control->settings.setting_refresh_tree_on_action}
				<tr>
		   			<td>{$labels.do_auto_update}</td>
		  			<td>
		  			   <input type="hidden" 
		  			          id="hidden_setting_refresh_tree_on_action"
		  			          name="hidden_setting_refresh_tree_on_action" 
		  			          value="{$control->settings.setting_refresh_tree_on_action.hidden_setting_refresh_tree_on_action}" />

		  			   <input type="checkbox"
		  			           id="cbsetting_refresh_tree_on_action"
		  			           name="setting_refresh_tree_on_action"
		  			           {if $control->settings.setting_refresh_tree_on_action.selected} checked {/if}
		  			           style="font-size: 90%;" onclick="this.form.submit()"/>
		  			</td>
		  		</tr>
			{/if}

			{if $control->draw_export_testplan_button}
				<tr>
		   			<td colspan="2">
	          <input type="button" id="doTestPlanExport" name="doTestPlanExport" value="{$labels.btn_export_testplan_tree}"
         	         onclick="javascript: openExportTestPlan('export_testplan','{$session.testprojectID}',
         	                                                 '{$control->settings.setting_testplan.selected}','{$platformID}',
         	                                                 '{$control->settings.setting_build.selected}');" />
            </td>
		  		</tr>
			{/if}
			</table>
		</div> {* settings *}
	</div> {* settings_panel *}
{/if} {* display settings *}

{if $control->display_filters}

	<div id="filter_panel">
		<div class="x-panel-header x-unselectable">
			{$labels.caption_nav_filters}
		</div>

	<div id="filters" class="x-panel-body exec_additional_info" style="padding-top: 3px;">
		
		<table class="smallGrey" style="width:98%;">

		{if $control->filters.filter_tc_id}
			<tr>
				<td>{$labels.th_tcid}</td>
				<td><input type="text" name="filter_tc_id"
				                       size="{#TC_ID_SIZE#}"
				                       maxlength="{#TC_ID_MAXLEN#}"
				                       value="{$control->filters.filter_tc_id.selected}" />
				</td>
			</tr>
		{/if}

		{if $control->filters.filter_testcase_name}
			<tr>
				<td>{$labels.tc_title}</td>
				<td><input type="text" name="filter_testcase_name"
				                       size="{#TC_TITLE_SIZE#}"
				                       maxlength="{#TC_TITLE_MAXLEN#}"
				                       value="{$control->filters.filter_testcase_name.selected}" />
				</td>
			</tr>
		{/if}

		{if $control->filters.filter_toplevel_testsuite}
			<tr>
	    		<td>{$labels.testsuite}</td>
	    		<td>
	    			<select name="filter_toplevel_testsuite">
	    				{html_options options=$control->filters.filter_toplevel_testsuite.items
	    				              selected=$control->filters.filter_toplevel_testsuite.selected}
	    			</select>
	    		</td>
	    	</tr>
    	{/if}

		{if $control->filters.filter_keywords}
			<tr>
				<td>{$labels.keyword}</td>
				<td><select name="filter_keywords[]"
				            title="{$labels.keywords_filter_help}"
				            multiple="multiple"
				            size="{$control->filters.filter_keywords.size}">
				    {html_options options=$control->filters.filter_keywords.items
				                  selected=$control->filters.filter_keywords.selected}
					</select>

			{html_radios name='filter_keywords_filter_type'
	                   options=$control->filters.filter_keywords.filter_keywords_filter_type.items
	                   selected=$control->filters.filter_keywords.filter_keywords_filter_type.selected}
				</td>
			</tr>
		{/if}

		{if $control->filters.filter_priority}
			<tr>
				<th width="75">{$labels.priority}</th>
				<td>
					<select name="filter_priority">
					{* add "any" option to smarty global variable for priority *}
					<option value="">{$control->option_strings.any}</option>
					{html_options options=$gsmarty_option_importance
                                  selected=$control->filters.filter_priority.selected}
					</select>
				</td>
			</tr>
		{/if}

		{if $control->filters.filter_execution_type}
			<tr>
				<td>{$labels.execution_type}</td>
	  			<td>
				<select name="filter_execution_type">
					{html_options options=$control->filters.filter_execution_type.items
					              selected=$control->filters.filter_execution_type.selected}
	    	  </select>
				</td>
			</tr>
		{/if}

		{if $control->filters.filter_assigned_user}
		<tr>
			<td>{$labels.filter_owner}</td>
			<td>

			{if $control->advanced_filter_mode}
				<select name="filter_assigned_user[]"
				        id="filter_assigned_user"
				        multiple="multiple"
				        size="{$control->filter_item_quantity}" >
				{html_options options=$control->filters.filter_assigned_user.items
				              selected=$control->filters.filter_assigned_user.selected}
				</select>
		    {else}
				<select name="filter_assigned_user" 
				        id="filter_assigned_user"
				        onchange="javascript: triggerAssignedBox('filter_assigned_user',
	                                                             'filter_assigned_user_include_unassigned',
	                                                             '{$control->option_strings.any}',
	                                                             '{$control->option_strings.none}',
	                                                             '{$control->option_strings.somebody}');">
				{html_options options=$control->filters.filter_assigned_user.items
                              selected=$control->filters.filter_assigned_user.selected}
				</select>

				<br/>
				
				<input type="checkbox"
				       id="filter_assigned_user_include_unassigned"
				       name="filter_assigned_user_include_unassigned"
	  		           value="1"
	  		           {if $control->filters.filter_assigned_user.filter_assigned_user_include_unassigned}
	  		           		checked="checked"
	  		           {/if}
	  		    />
				{$labels.include_unassigned_testcases}
			{/if}

 			</td>
		</tr>
    	{/if}


		{* custom fields are placed here *}

		{if $control->filters.filter_custom_fields && !$control->filters.filter_custom_fields.collapsed}
			<tr><td>&nbsp;</td></tr>
			{$control->filters.filter_custom_fields.items}
		{/if}


	{* result filtering parts *}
	{if $control->filters.filter_result}

		<tr><td>&nbsp;</td></tr> {* empty row for a little separation *}

	   		<tr>
				<th>{$labels.filter_result}</th>
				<td>
				{if $control->advanced_filter_mode}
				  	<select name="filter_result_result[]" 
				  	        multiple="multiple"
				  	        size="{$control->filter_item_quantity}">
				{else}
				  	<select name="filter_result_result">
				{/if}
				{html_options options=$control->filters.filter_result.filter_result_result.items
				              selected=$control->filters.filter_result.filter_result_result.selected}
				</select>
				</td>
			</tr>

			<tr>
				<th>{$labels.filter_on}</th>
				<td>
				  	<select name="filter_result_method" id="filter_result_method"
				  		      onchange="javascript: triggerBuildChooser('filter_result_build_row',
						                                                'filter_result_method',
						      {$control->configuration->filter_methods.status_code.specific_build});">
					{html_options options=$control->filters.filter_result.filter_result_method.items
					              selected=$control->filters.filter_result.filter_result_method.selected}
				  	</select>
				</td>
			</tr>

			<tr id="filter_result_build_row">
				<th>{$labels.build}</th>
				<td><select id="filter_result_build" name="filter_result_build">
					{html_options options=$control->filters.filter_result.filter_result_build.items
					              selected=$control->filters.filter_result.filter_result_build.selected}
					</select>
				</td>
			</tr>

	{/if}

		</table>

		<div>
			<input type="submit"
			       value="{$labels.btn_apply_filter}"
			       id="doUpdateTree"
			       name="doUpdateTree"
			       style="font-size: 90%;" />

			<input type="submit"
			       value="{$labels.btn_reset_filters}"
			       id="doResetTree"
			       name="btn_reset_filters"
			       style="font-size: 90%;" />
			
			{if $control->filters.filter_custom_fields}
			<input type="submit"
			       value="{$control->filters.filter_custom_fields.btn_label}"
			       id="doToggleCF"
			       name="btn_toggle_cf"
			       style="font-size: 90%;" />
			{/if}
			
			{if $control->filter_mode_choice_enabled}
			
				{if $control->advanced_filter_mode}
					<input type="hidden" name="btn_advanced_filters" value="1" />
				{/if}
			
				<input type="submit" id="toggleFilterMode"  name="{$control->filter_mode_button_name}"
				     value="{$control->filter_mode_button_label}"
				     style="font-size: 90%;"  />
      		{/if}
      		
		</div>

	</div> {* filters *}

	</div> {* filter_panel *}

{/if} {* show filters *}

{* here the requirement part starts *}

{if $control->display_req_settings}
	<div id="settings_panel">
		<div class="x-panel-header x-unselectable">
			{$labels.caption_nav_settings}
		</div>

		<div id="settings" class="x-panel-body exec_additional_info" "style="padding-top: 3px;">
			<input type='hidden' id="tpn_view_settings" name="tpn_view_status"  value="0" />

			<table class="smallGrey" style="width:98%;">

			{if $control->settings.setting_refresh_tree_on_action}
				<tr>
		   			<td>{$labels.do_auto_update}</td>
		  			<td>
		  			   <input type="hidden" 
		  			          id="hidden_setting_refresh_tree_on_action"
		  			          name="hidden_setting_refresh_tree_on_action" 
		  			          value="{$control->settings.setting_refresh_tree_on_action.hidden_setting_refresh_tree_on_action}" />

		  			   <input type="checkbox"
		  			           id="cbsetting_refresh_tree_on_action"
		  			           name="setting_refresh_tree_on_action"
		  			           {if $control->settings.setting_refresh_tree_on_action.selected} checked {/if}
		  			           style="font-size: 90%;" onclick="this.form.submit();" />
		  			</td>
		  		</tr>
			{/if}

			</table>
		</div> {* settings *}
	</div> {* settings_panel *}
{/if} {* display req settings *}

{if $control->display_req_filters}

	<div id="filter_panel">
	<div class="x-panel-header x-unselectable">
		{$labels.caption_nav_filters}
	</div>

	<div id="filters" class="x-panel-body exec_additional_info" style="padding-top: 3px;">

	<table class="smallGrey" style="width:98%;">

	{if $control->filters.filter_doc_id}
		<tr>
			<td>{$labels.document_id}</td>
			<td><input type="text" name="filter_doc_id"
			                       size="{#REQ_DOCID_SIZE#}"
			                       maxlength="{#REQ_DOCID_MAXLEN#}"
			                       value="{$control->filters.filter_doc_id.selected}" />
			</td>
		</tr>
	{/if}

	{if $control->filters.filter_title}
		<tr>
			<td>{$labels.title}</td>
			<td><input type="text" name="filter_title"
			                       size="{#REQ_NAME_SIZE#}"
			                       maxlength="{#REQ_NAME_MAXLEN#}"
			                       value="{$control->filters.filter_title.selected}" />
			</td>
		</tr>
	{/if}
	
	{if $control->filters.filter_status}
		<tr>
			<td>{$labels.status}</td>
			<td>
				{if $control->advanced_filter_mode}
					<select id="filter_status" 
					        name="filter_status[]"
					        multiple="multiple"
					        size="{$control->filter_item_quantity}" >
				{else}
					<select id="filter_status" name="filter_status">
				{/if}
					{html_options options=$control->filters.filter_status.items
					              selected=$control->filters.filter_status.selected}
					</select>
			    
			</td>
		</tr>
	{/if}
	
	{if $control->filters.filter_type}
		<tr>
			<td>{$labels.req_type}</td>
			<td>
				{if $control->advanced_filter_mode}
					<select id="filter_type" 
					        name="filter_type[]"
					        multiple="multiple"
					        size="{$control->filter_item_quantity}" >
				{else}
					<select id="filter_type" name="filter_type">
				{/if}
					{html_options options=$control->filters.filter_type.items
					              selected=$control->filters.filter_type.selected}
					</select>
			</td>
		</tr>
	{/if}

	{if $control->filters.filter_spec_type}
		<tr>
			<td>{$labels.req_spec_type}</td>
			<td>
				{if $control->advanced_filter_mode}
					<select id="filter_spec_type" 
					        name="filter_spec_type[]"
					        multiple="multiple"
					        size="{$control->filter_item_quantity}" >
				{else}
					<select id="filter_spec_type" name="filter_spec_type">
				{/if}
					{html_options options=$control->filters.filter_spec_type.items
					              selected=$control->filters.filter_spec_type.selected}
					</select>
			</td>
		</tr>
	{/if}

	{if $control->filters.filter_coverage}
		<tr>
			<td>{$labels.req_expected_coverage}</td>
			<td><input type="text" name="filter_coverage"
			                       size="{#COVERAGE_SIZE#}"
			                       maxlength="{#COVERAGE_MAXLEN#}"
			                       value="{$control->filters.filter_coverage.selected}" />
			</td>
		</tr>
	{/if}
	
	{if $control->filters.filter_relation}
		<tr>
			<td>{$labels.has_relation_type}</td>
			<td>
				{if $control->advanced_filter_mode}
					<select id="filter_relation" 
					        name="filter_relation[]"
					        multiple="multiple"
					        size="{$control->filter_item_quantity}" >
				{else}
					<select id="filter_relation" name="filter_relation">
				{/if}
					{html_options options=$control->filters.filter_relation.items
					              selected=$control->filters.filter_relation.selected}
					</select>
			</td>
		</tr>
	{/if}
	
	{if $control->filters.filter_tc_id}
		<tr>
			<td>{$labels.th_tcid}</td>
			<td><input type="text" name="filter_tc_id"
			                       size="{#TC_ID_SIZE#}"
			                       maxlength="{#TC_ID_MAXLEN#}"
			                       value="{$control->filters.filter_tc_id.selected}" />
			</td>
		</tr>
	{/if}
	
	{if $control->filters.filter_custom_fields && !$control->filters.filter_custom_fields.collapsed}
		<tr><td>&nbsp;</td></tr>
		{$control->filters.filter_custom_fields.items}
	{/if}
	
	</table>
	
	<div>
		<input type="submit"
		       value="{$labels.btn_apply_filter}"
		       id="doUpdateTree"
		       name="doUpdateTree"
		       style="font-size: 90%;" />

		<input type="submit"
		       value="{$labels.btn_reset_filters}"
		       id="doResetTree"
		       name="btn_reset_filters"
		       style="font-size: 90%;" />
		
		{if $control->filters.filter_custom_fields}
			<input type="submit"
			       value="{$control->filters.filter_custom_fields.btn_label}"
			       id="doToggleCF"
			       name="btn_toggle_cf"
			       style="font-size: 90%;" />
		{/if}
		
		{if $control->filter_mode_choice_enabled}
			
			{if $control->advanced_filter_mode}
				<input type="hidden" name="btn_advanced_filters" value="1" />
			{/if}
			
			<input type="submit" id="toggleFilterMode"  name="{$control->filter_mode_button_name}"
			     value="{$control->filter_mode_button_label}"
			     style="font-size: 90%;"  />
      	{/if}

	</div>
	
	</div> {* filters *}
	</div> {* filter_panel *}
{/if} {* show requirement filters *}

</form>