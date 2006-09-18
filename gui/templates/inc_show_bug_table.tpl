{* 
Testlink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_show_bug_table.tpl,v 1.1 2006/09/18 07:12:06 franciscom Exp $
*}
{* -------------------------------------------------------------------------------------- *}
{* Manage missing arguments                                                               *}
{assign var="tableClassName"  value=$tableClassName|default:"simple"}
{assign var="tableStyles"  value=$tableStyles|default:"font-size:12px"}
{* -------------------------------------------------------------------------------------- *}
<table class="simple" width="100%">
  <tr>
	  <th style="text-align:left">{lang_get s='build'}</th>
	  <th style="text-align:left">{lang_get s='caption_bugtable'}</th>
		{if $can_delete}
	    <th style="text-align:left">&nbsp</th>
	  {/if}  
  </tr>
  
  {foreach from=$bugs_map key=bug_id item=bug_elem}
	<tr>
		<td>{$bug_elem.build_name|escape}</td>
		<td>{$bug_elem.link_to_bts}
		</td>
		{if $can_delete}
		  <td><a href="javascript:deleteBug_onClick({$exec_id},{$bug_id},'{lang_get s='del_bug_warning_msg'}');"><img style="border:none" alt="{lang_get s='alt_delete_build'}" src="icons/thrash.png"/></a>
		  </td>
		{/if}
	</tr>
	{/foreach}
</table>
		