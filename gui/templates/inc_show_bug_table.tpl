{* 
Testlink Open Source Project - http://testlink.sourceforge.net/ 
@filesource inc_show_bug_table.tpl

@internal revisions
*}

{* -------------------------------------------------------------------------------------- *}
{* Manage missing arguments                                                               *}
{if !isset($tableClassName) }
    {$tableClassName="simple"}
{/if}
{if !isset($tableStyles) }
    {$tableStyles="font-size:12px"}
{/if}
{* -------------------------------------------------------------------------------------- *}
{lang_get var="l10nb"
          s="build,caption_bugtable,bug_id,delete_bug,del_bug_warning_msg,add_issue_note"}

<table class="simple">
  <tr>
	  <th style="text-align:left">{$l10nb.build}</th>
	  <th style="text-align:left">{$l10nb.caption_bugtable}</th>
	  {if $gui->tlCanAddIssueNote} <th style="text-align:left">&nbsp;</th> {/if}
	  {if $can_delete} <th style="text-align:left">&nbsp;</th> {/if}
  </tr>
  
 	{foreach from=$bugs_map key=bug_id item=bug_elem}
	<tr>
		<td>{$bug_elem.build_name|escape}</td>
		<td>{$bug_elem.link_to_bts}</td>
		{if $gui->tlCanAddIssueNote}
		  <td>
		    {* Attention: 
		       bug_id can be a number (i.e. for Mantis) or a string (i.e. for JIRA) depending of Issue Tracker System 
               Only choice to avoid JS issues => treat always as string 
		    *}
   		    <a href="javascript:open_bug_note_add_window('{$bug_id}',{$gui->tproject_id},{$tc_old_exec.id},{$tc_old_exec.execution_id},'add_note')">
   		    <img src="{$tlImages.bug_add_note}" title="{$labels.bug_add_note}" style="border:none" /></a>
		  </td>
		{/if}

		{if $can_delete}
		  <td class="clickable_icon">
		  	<img class="clickable" onclick="delete_confirmation('{$exec_id}-{$bug_id|escape:'javascript'|escape}','{$bug_id|escape:'javascript'|escape}',
			            '{$l10nb.delete_bug}','{$l10nb.del_bug_warning_msg} ({$l10nb.bug_id} {$bug_id})',deleteBug);" style="border:none" title="{$l10nb.delete_bug}" alt="{$l10nb.delete_bug}" 
			            src="{$tlImages.delete}"/></td>
		{/if}
	</tr>
	{/foreach}
</table>
		