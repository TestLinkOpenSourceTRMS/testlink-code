{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_steps.tpl,v 1.4.2.2 2011/01/08 09:43:16 franciscom Exp $
Purpose: Show the steps for a testcase in vertical or horizontal layout
         Included from files tcView_viewer.tpl and inc_exec_test_spec.tpl
Author : eloff, 2010


@param $layout "horizontal" or "vertical"
@param $steps Array of the steps
@param $edit_enabled Steps links to edit page if true


@internal revisions:
  20110108 - franciscom - BUGID 4144 - missing insert step feature on VERTICAL layout
  20101016 - franciscom - added id to table rows with step data 
	20100621 - eloff - initial commit

*}
{lang_get var="inc_steps_labels" 
          s="show_hide_reorder, step_number, step_actions,expected_results, 
             execution_type_short_descr,delete_step,insert_step"}
{lang_get s='warning_delete_step' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{if $layout == 'horizontal'}
	<tr>
		<th width="25px"><nobr>
		{if $edit_enabled && $steps != ''}
			<img class="clickable" src="{$tlImages.reorder}" align="left"
			     title="{$inc_steps_labels.show_hide_reorder}"
			     onclick="showHideByClass('span','order_info');">
		{/if}
			{$inc_steps_labels.step_number}
		</nobr></th>
		<th>{$inc_steps_labels.step_actions}</th>
		<th>{$inc_steps_labels.expected_results}</th>
		{if $session['testprojectOptions']->automationEnabled}
		<th width="25">{$inc_steps_labels.execution_type_short_descr}</th>
		{/if}
		{if $edit_enabled}
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		{/if}
	</tr>
	{* BUGID 3376 *}
	{foreach from=$steps item=step_info}
	<tr id="step_row_{$step_info.step_number}">
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
			     title="{$inc_steps_labels.delete_step}"
			     alt="{$inc_steps_labels.delete_step}"
			     onclick="delete_confirmation({$step_info.id},'{$step_info.step_number|escape:'javascript'|escape}',
					                               '{$del_msgbox_title}','{$warning_msg}');"
			     src="{$tlImages.delete}"/>
		</td>
		
		<td class="clickable_icon">
			<img style="border:none;cursor: pointer;"  title="{$inc_steps_labels.insert_step}"    
					 alt="{$inc_steps_labels.insert_step}"
			     onclick="launchInsertStep({$step_info.id});"    src="{$tlImages.insert_step}"/>
		</td>
		
		{/if}
	</tr>
	{/foreach}
{else}
	{* Vertical layout *}
	{if $edit_enabled}
	<tr><td>
		<img class="clickable" src="{$tlImages.reorder}" align="left" title="{$inc_steps_labels.show_hide_reorder}"
		onclick="showHideByClass('span','order_info');"></td>
		<td>{$inc_steps_labels.show_hide_reorder}</td>
	</tr>
	{/if}
	{foreach from=$steps item=step_info}
	<tr>
		<th width="25px"><nobr>{$inc_steps_labels.step_number}
		<span class="order_info" style='display:none'>
		<input type="text" name="step_set[{$step_info.id}]" id="step_set_{$step_info.id}"
		       value="{$step_info.step_number}"
		       size="{#STEP_NUMBER_SIZE#}"
		       maxlength="{#STEP_NUMBER_MAXLEN#}">
		{include file="error_icon.tpl" field="step_number"}
		</span>{$step_info.step_number}</nobr></th>
		<th>{$inc_steps_labels.step_actions}</th>
		{if $session['testprojectOptions']->automationEnabled}
		<th>{$inc_steps_labels.execution_type_short_descr}:
		    {$gui->execution_types[$step_info.execution_type]}</th>
		{else}
		<th>&nbsp;</th>
		{/if}
		{if $edit_enabled}
		<th>&nbsp;</th>
		{/if}
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="2" {if $edit_enabled} style="cursor:pointer;"
		    onclick="launchEditStep({$step_info.id})"{/if}
		    style="padding: 0.5em">{$step_info.actions}</td>
		{if $edit_enabled}
		<td class="clickable_icon">
			<img style="border:none;cursor: pointer;"
			     title="{$inc_steps_labels.delete_step}"
			     alt="{$inc_steps_labels.delete_step}"
			     onclick="delete_confirmation({$step_info.id},
			             '{$step_info.step_number|escape:'javascript'|escape}',
			             '{$del_msgbox_title}','{$warning_msg}');"
			     src="{$tlImages.delete}"/>
			{* BUGID 4144 *}
			<img style="border:none;cursor: pointer;"  title="{$inc_steps_labels.insert_step}"    
					 alt="{$inc_steps_labels.insert_step}"
			     onclick="launchInsertStep({$step_info.id});" src="{$tlImages.insert_step}"/>

		</td>
		{/if}
	</tr>
	<tr>
		<th style="background: transparent; border: none"></th>
		<th colspan="2">{$inc_steps_labels.expected_results}</th>
	</tr>
	<tr {if $edit_enabled} style="cursor:pointer;"
	    onclick="launchEditStep({$step_info.id})"{/if}>
	    <td>&nbsp;</td>
		<td colspan="2" style="padding: 0.5em 0.5em 2em 0.5em">{$step_info.expected_results}</td>
	</tr>
	{/foreach}
{/if}
