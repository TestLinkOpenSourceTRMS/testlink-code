{* 
Testlink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_show_bug_table.tpl,v 1.8 2009/08/11 19:48:51 schlundus Exp $

rev :
      20070304 - franciscom - added single quotes on bug_id on deleteBug_onClick() call
                              message improvement
                              added title on delete image. 
*}
{* -------------------------------------------------------------------------------------- *}
{* Manage missing arguments                                                               *}
{if !isset($tableClassName) }
    {assign var="tableClassName"  value="simple"}
{/if}
{if !isset($tableStyles) }
    {assign var="tableStyles"  value="font-size:12px"}
{/if}
{* -------------------------------------------------------------------------------------- *}
<table class="simple" width="100%">
  <tr>
	  <th style="text-align:left">{lang_get s='build'}</th>
	  <th style="text-align:left">{lang_get s='caption_bugtable'}</th>
		{if $can_delete}
	    	<th style="text-align:left">&nbsp;</th>
		{/if}  
  </tr>
  
 	{foreach from=$bugs_map key=bug_id item=bug_elem}
	<tr>
		<td>{$bug_elem.build_name|escape}</td>
		<td>{$bug_elem.link_to_bts}</td>
		{if $can_delete}
		  <td class="clickable_icon">
		  	<img class="clickable" onclick="delete_confirmation('{$exec_id}-{$bug_id|escape:'javascript'|escape}','{$bug_id|escape:'javascript'|escape}',
			            '{lang_get s='delete_bug'}','{lang_get s='del_bug_warning_msg'} ({lang_get s='bug_id'} {$bug_id})',deleteBug);" style="border:none" title="{lang_get s='delete_bug'}" alt="{lang_get s='delete_bug'}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"/></td>
		{/if}
	</tr>
	{/foreach}
</table>
		