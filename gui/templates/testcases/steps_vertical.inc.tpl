{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource steps_vertical.inc.tpl
            Show the steps for a testcase in vertical layout

@used-by inc_steps.tpl

@param $steps Array of the steps
@param $edit_enabled Steps links to edit page if true

@internal revisions
*}
  {if $edit_enabled && $args_frozen_version=="no"}
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
    {if $edit_enabled && $args_frozen_version=="no"}
    <th>&nbsp;</th>
    {/if}
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td colspan="2" {if $edit_enabled && $args_frozen_version=="no"} style="cursor:pointer;"
        onclick="launchEditStep({$step_info.id})"{/if}
        style="padding: 0.5em">{if $gui->stepDesignEditorType  == 'none'}{$step_info.actions|nl2br}{else}{$step_info.actions}{/if}</td>
    {if $edit_enabled && $args_frozen_version=="no"}
    <td class="clickable_icon">
      <img style="border:none;cursor: pointer;"
           title="{$inc_steps_labels.delete_step}"
           alt="{$inc_steps_labels.delete_step}"
           onclick="delete_confirmation({$step_info.id},
                   '{$step_info.step_number|escape:'javascript'|escape}',
                   '{$del_msgbox_title}','{$warning_msg}');"
           src="{$tlImages.delete}"/>
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
  <tr {if $edit_enabled && $args_frozen_version=="no"} style="cursor:pointer;"
      onclick="launchEditStep({$step_info.id})"{/if}>
      <td>&nbsp;</td>
    <td colspan="2" style="padding: 0.5em 0.5em 2em 0.5em">{if $gui->stepDesignEditorType  == 'none'}{$step_info.expected_results|nl2br}{else}{$step_info.expected_results}{/if}</td>
  </tr>
  {/foreach}