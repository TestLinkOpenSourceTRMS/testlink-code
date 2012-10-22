{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
Purpose: smarty template - delete test suites in test specification view

@filesource testSuiteDelete.tpl

@internal revisions 
*}
{include file="inc_head.tpl"}
{lang_get var='labels'
          s='test_case,th_link_exec_status,question_del_testsuite,btn_yes,btn_no'}

<body>
<h1 class="title">{$gui->page_title|escape}</h1> 
{include file="inc_update.tpl" result=$gui->sqlResult item=$level action='delete' refresh=$gui->refreshTree}

<div class="workBack">
{if $gui->sqlResult == '' && $gui->testsuiteID != ''}
	{if $gui->warning_msg != ""}
		{if $gui->system_msg != ""}
		      <div class="user_feedback">{$gui->system_msg}</div>
		      <br />
		{/if}
		<table class="link_and_exec">
		<tr>
			<th>{$labels.test_case}</th>
			<th>{$labels.th_link_exec_status}</th>
		</tr>
		{section name=idx loop=$gui->warning_msg}
			<tr>
				<td>{$gui->warning[idx]|escape}&nbsp;</td>
				<td>{lang_get s=$link_msg[idx]}<td>
			</tr>
		{/section}
		</table>
		{if $gui->delete_msg != ''}  
			<h2>{$gui->delete_msg}</h2>
		{/if}
	{/if}
  
	<form method="post" action="lib/testsuites/testSuiteEdit.php">
		<input type="hidden" name="doIt" id="doIt" value="1">
		<input type="hidden" name="testsuiteID" id="testsuiteID" value="{$gui->testsuiteID}">
		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
		{if $gui->can_delete}
			<p>{$gui->last_chance_msg|escape}</p>
			<input type="submit" name="delete_testsuite" value="{$labels.btn_yes}" />
		
			<input  type="button" name="cancel_delete_testsuite" id="cancel_delete_testsuite" value="{$labels.btn_no}"
					onclick='javascript: location.href=fRoot+
					        "lib/testcases/archiveData.php?tproject_id={$gui->tproject_id}&edit=testsuite&id={$gui->testsuiteID}";' />
		{/if}
	</form>
{/if}

{if $gui->refreshTree}
   	{include file="inc_refreshTreeWithFilters.tpl"}
{/if}

</div>
</body>
</html>