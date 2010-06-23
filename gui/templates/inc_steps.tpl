{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_steps.tpl,v 1.1 2010/06/23 07:06:50 erikeloff Exp $
Purpose: Show the steps for a testcase in vertical or horizontal layout
         Included from files tcView_viewer.tpl and inc_exec_test_spec.tpl
Author : eloff, 2010

@internal revisions:
	20100621 - eloff - initial commit

	@param $layout "horizontal" or "vertical"
	@param $steps Array of the steps
	@param $edit_enabled Steps links to edit page if true
*}
{lang_get var="labels" s="show_hide_reorder, step_number, step_actions,
                          expected_results, execution_type_short_descr,
                          delete_step"}
{lang_get s='warning_delete_step' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{if $layout == 'horizontal'}
	<tr>
		<th>
		{if $edit_enabled && $steps != ''}
			<img class="clickable" src="{$tlImages.reorder}" align="left"
			     title="{$labels.show_hide_reorder}"
			     onclick="showHideByClass('span','order_info');">
		{/if}
			{$labels.step_number}
		</th>
		<th>{$labels.step_actions}</th>
		<th>{$labels.expected_results}</th>
		{if $session['testprojectOptions']->automationEnabled}
		<th width="25">{$labels.execution_type_short_descr}</th>
		{/if}
		{if $edit_enabled}
		<th>&nbsp;</th>
		{/if}
	</tr>
	{* BUGID 3376 *}
	{foreach from=$steps item=step_info}
	<tr>
		<td style="text-align:left;">
			<span class="order_info" style='display:none'>
			<input type="text" name="step_set[{$step_info.id}]" id="step_set_{$step_info.id}"
				value="{$step_info.step_number}"
				size="{#STEP_NUMBER_SIZE#}"
				maxlength="{#STEP_NUMBER_MAXLEN#}">
			{include file="error_icon.tpl" field="step_number"}
			</span>{$step_info.step_number}
		</td>
		<td {if $edit_enabled} style="cursor:pointer;" onclick="launchEditStep({$step_info.id})" {/if}>{$step_info.actions}</td>
		<td {if $edit_enabled} style="cursor:pointer;" onclick="launchEditStep({$step_info.id})" {/if}>{$step_info.expected_results}</td>
		{if $session['testprojectOptions']->automationEnabled}
		<td {if $edit_enabled} style="cursor:pointer;" onclick="launchEditStep({$step_info.id})" {/if}>{$gui->execution_types[$step_info.execution_type]}</td>
		{/if}

		{if $edit_enabled}
		<td class="clickable_icon">
			<img style="border:none;cursor: pointer;"
			     title="{$labels.delete_step}"
			     alt="{$labels.delete_step}"
			     onclick="delete_confirmation({$step_info.id},'{$step_info.step_number|escape:'javascript'|escape}',
					                               '{$del_msgbox_title}','{$warning_msg}');"
			     src="{$delete_img}"/>
		</td>
		{/if}
	</tr>
	{/foreach}
{else}
	{* Vertical layout *}
	{if $edit_enabled}
	<tr><td>
		<img class="clickable" src="{$tlImages.reorder}" align="left" title="{$labels.show_hide_reorder}"
		onclick="showHideByClass('span','order_info');"></td>
		<td>{$labels.show_hide_reorder}</td>
	</tr>
	{/if}
	{foreach from=$steps item=step_info}
	<tr>
		<th width="20">{$labels.step_number}
		<span class="order_info" style='display:none'>
		<input type="text" name="step_set[{$step_info.id}]" id="step_set_{$step_info.id}"
		       value="{$step_info.step_number}"
		       size="{#STEP_NUMBER_SIZE#}"
		       maxlength="{#STEP_NUMBER_MAXLEN#}">
		{include file="error_icon.tpl" field="step_number"}
		</span>{$step_info.step_number}</th>
		<th>{$labels.step_actions}</th>
		{if $session['testprojectOptions']->automationEnabled}
		<th>{$labels.execution_type_short_descr}:
		    {$gui->execution_types[$step_info.execution_type]}</th>
		{else}
		<th>&nbsp;</th>
		{/if}
		{if $edit_enabled}
		<th>&nbsp;</th>
		{/if}
	</tr>
	<tr>
		<td colspan="3" {if $edit_enabled} style="cursor:pointer;"
		    onclick="launchEditStep({$step_info.id})"{/if}
		    style="padding: 0.5em">{$step_info.actions}</td>
		{if $edit_enabled}
		<td class="clickable_icon">
			<img style="border:none;cursor: pointer;"
			     title="{$labels.delete_step}"
			     alt="{$labels.delete_step}"
			     onclick="delete_confirmation({$step_info.id},
			             '{$step_info.step_number|escape:'javascript'|escape}',
			             '{$del_msgbox_title}','{$warning_msg}');"
			     src="{$delete_img}"/>
		</td>
		{/if}
	</tr>
	<tr>
		<th style="background: transparent; border: none"></th>
		<th colspan="2">{$labels.expected_results}</th>
	</tr>
	<tr {if $edit_enabled} style="cursor:pointer;"
	    onclick="launchEditStep({$step_info.id})"{/if}>
		<td colspan="3" style="padding: 0.5em 0.5em 2em 0.5em">{$step_info.expected_results}</td>
	</tr>
	{/foreach}
{/if}
