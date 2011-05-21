{* 
Testlink Open Source Project - http://testlink.sourceforge.net/ 
@filespurce	inc_show_bug_table.tpl

@internal revisions
*}
{* -------------------------------------------------------------------------------------- *}
{* Manage missing arguments                                                               *}
{if !isset($tableClassName)}
    {assign var="tableClassName"  value="simple"}
{/if}
{if !isset($tableStyles)}
    {assign var="tableStyles"  value="font-size:12px"}
{/if}
{* -------------------------------------------------------------------------------------- *}
<table class="simple">
  <tr>
	  <th style="text-align:left">{lang_get s='build'}</th>
	  <th style="text-align:left">{lang_get s='caption_bugtable'}</th>
	  <th style="text-align:left">&nbsp;</th>
  </tr>
  
 	{foreach from=$bugs_map key=bug_id item=bug_elem}
	<tr>
		<td>{$bug_elem.build_name|escape}</td>
		<td>{$bug_elem.link_to_bts}</td>
		{if $can_delete}
		  <td class="clickable_icon">
		  	<img class="clickable" onclick="action_confirmation('{$exec_id}-{$bug_id|escape:'javascript'|escape}','{$bug_id|escape:'javascript'|escape}',
			            '{lang_get s='delete_bug'}','{lang_get s='del_bug_warning_msg'} ({lang_get s='bug_id'} {$bug_id})',deleteBug);" style="border:none" title="{lang_get s='delete_bug'}" alt="{lang_get s='delete_bug'}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"/></td>
		{else}
		  <td class="clickable_icon">
		  	<img style="border:none" src="{$smarty.const.TL_THEME_IMG_DIR}/trash_greyed.png"/ title="{$labels.closed_build}">
		  </td>
		{/if}
	</tr>
	{/foreach}
</table>		