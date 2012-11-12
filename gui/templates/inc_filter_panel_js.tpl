{*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: inc_filter_panel_js.tpl,v 1.5 2010/11/13 11:24:25 franciscom Exp $
 *
 * This file is included by the *navigator templates.
 * It contains and handles the javascript functions which switch some filters on and off,
 * depending on selected values, or preselect values in special cases.
 * Since this code is always the same, for all features with which the filters
 * are used, it is all handled in this single file.
 *
 * Also included is the code necessary to initialize the ExtJS CollapsiblePanel and help files.
 *
 * @author Andreas Simon
 * @internal revision
 *}

<script>
{if !is_null($control->filters.filter_assigned_user)}
	/**
	 * Used to disable the "include unassigned testcases" checkbox when anything else 
	 * but a username is selected in "assigned to" select box.
	 * In case of a selected username the box will be activated again.
	 * (testcase execution & testcase execution assignment, BUGID 2455, BUGID 3026)
	 * 
	 * @author Andreas Simon
	 * @param filter_assigned_to combobox in which assignment is chosen
	 * @param include_unassigned checkbox for including unassigned testcases
	 * @param str_option_any string value anybody
	 * @param str_option_none string value nobody
	 * @param str_option_somebody string value somebody
	 */
	function triggerAssignedBox(filter_assigned_to_id, include_unassigned_id,
								              str_option_any, str_option_none, str_option_somebody) 
	{
		var __FUNCTION__ = 'triggerAssignedBox';
		var filter_assigned_to = document.getElementById(filter_assigned_to_id);
		var include_unassigned = document.getElementById(include_unassigned_id);
		var index = filter_assigned_to.options.selectedIndex;
		var choice = filter_assigned_to.options[index].label;

		include_unassigned.disabled = false;
		if (choice == str_option_any || choice == str_option_none || choice == str_option_somebody) 
		{
			include_unassigned.disabled = true;
			include_unassigned.checked = false;
		} 
	}
{/if}

{if !is_null($control->filters.filter_result)}
	/**
	 * If filter method ("filter on...") selection is set to "specific build",
	 * enable build selector, otherwise disable it.
	 * 
	 * @author Andreas Simon
	 * @param container_oid: inside this element, lives the element we want to hide/show 
	 * @param oid
	 * @param target ONLY if object(oid) selected value == target => make element (oid) visible.
	 */
	function triggerBuildChooser(container_oid, oid,target)
	{
		var container = document.getElementById(container_oid);
		var obj = document.getElementById(oid);
		var __FUNCTION__ = 'triggerBuildChooser';
		
		container.style.visibility = "hidden";
		if(obj[obj.options.selectedIndex].value == target) 
		{
			container.style.visibility = "visible";
		} 
	}
	
	/**
	 * Disable unneeded filters in the filter method combo box.
	 * If only one build is selectable in filter, then the filter method
	 * gets set to "build chosen for execution" because no other method should be used in that case.
	 *  
	 * @author Andreas Simon
	 * @param oid id of box which shall be disabled
	 * @param value2select the string which shall be selected in the box before disabling it
	 */
	function triggerFilterMethodSelector(oid, value2select) 
	{
		var obj = document.getElementById(oid);
		var idx = 0;
		var __FUNCTION__ = 'triggerFilterMethodSelector';
		for (idx = 0; idx < obj.options.length; idx ++) 
		{
			if (obj.options[idx].value == value2select) 
			{
				obj.options.selectedIndex = idx;
				break;
			}
		}
		obj.disabled = true;
	}
{/if}



/**
 *
 *
 */
function filter_panel_body_onload()
{
	{if $control->filters.filter_result}
		{if $control->filters.filter_result.filter_result_build.items|@count == 1}
			triggerFilterMethodSelector('filter_result_method',
										{$control->filters.filter_result.filter_result_method.js_selection});
		{/if}
		triggerBuildChooser('filter_result_build_row','filter_result_method',
		                    {$control->cfg->filter_methods.status_code.specific_build});
	{/if}
	
	{if $control->filters.filter_assigned_user}
		triggerAssignedBox('filter_assigned_user','filter_assigned_user_include_unassigned',
		                   '{$control->option_strings.any}','{$control->option_strings.none}',
		                   '{$control->option_strings.somebody}');
	{/if}
}
</script>
</head>

{* only add "onload" to <body> if we need these filtering capabilities *}
{if $control->filters.filter_result || $control->filters.filter_assigned_user}
	<body onload="javascript:filter_panel_body_onload();">
{else}
	<body>
{/if}