{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
Purpose: smarty template - delete containers in test specification

@filesource containerDelete.tpl

@internal revisions 
20110402 - franciscom - BUGID 4322: New Option to block delete of executed test cases
20101202 - asimon - BUGID 4067: refresh tree problems
*}
{include file="inc_head.tpl"}
{lang_get var='labels'
          s='test_case,th_link_exec_status,question_del_testsuite,btn_yes_del_comp,btn_no'}

<body>
<h1 class="title">{$page_title}{$smarty.const.TITLE_SEP}{$objectName|escape}</h1> 
{include file="inc_update.tpl" result=$sqlResult item=$level action='delete' refresh=$gui->refreshTree}

<div class="workBack">

{if $sqlResult == '' && $objectID != ''}
	{if $warning != ""}
		{if $system_message != ""}
		      <div class="user_feedback">{$system_message}</div>
		      <br />
		{/if}
		<table class="link_and_exec">
		<tr>
			<th>{$labels.test_case}</th>
			<th>{$labels.th_link_exec_status}</th>
		</tr>
		{section name=idx loop=$warning}
			<tr>
				<td>{$warning[idx]|escape}&nbsp;</td>
				<td>{lang_get s=$link_msg[idx]}<td>
			</tr>
		{/section}
		</table>
		{if $delete_msg != ''}  
			<h2>{$delete_msg}</h2>
		{/if}
	{/if}
  
	<form method="post" action="lib/testcases/containerEdit.php?sure=yes&objectID={$objectID}">
		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
		{if $can_delete}
			<p>{$labels.question_del_testsuite}</p>
			<input type="submit" name="delete_testsuite" value="{$labels.btn_yes_del_comp}" />
		
			<input  type="button" name="cancel_delete_testsuite" value="{$labels.btn_no}"
					onclick='javascript: location.href=fRoot+
					"lib/testcases/archiveData.php?tproject_id={$gui->tproject_id}&edit=testsuite&id={$objectID}";' />
		{/if}
	</form>
{/if}
{if $gui->refreshTree} {$tlRefreshTreeJS} {/if}
</div>
</body>
</html>