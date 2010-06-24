{* 
	TestLink Open Source Project - http://testlink.sourceforge.net/
	$Id: containerDelete.tpl,v 1.6 2010/06/24 17:25:53 asimon83 Exp $ 
	Purpose: smarty template - delete containers in test specification

rev : 
     20070218 - franciscom - changed refresh management
     20070213 - franciscom - BUGID 0000629: Test Case/Suite - Delete confirmation without Cancel or No option
*}
{include file="inc_head.tpl"}

<body>
<h1 class="title">{$page_title}{$smarty.const.TITLE_SEP}{$objectName|escape}</h1> 

{include file="inc_update.tpl" result=$sqlResult item=$level action='delete' 
         refresh=$smarty.session.setting_refresh_tree_on_action}

<div class="workBack">

{if $sqlResult == '' && $objectID != ''}
	{if $warning neq ""}
		<table class="link_and_exec">
		<tr>
			<th>{lang_get s='test_case'}</th>
			<th>{lang_get s='th_link_exec_status'}</th>
		</tr>
		{section name=idx loop=$warning}
			<tr>
				<td>{$warning[idx]|escape}&nbsp;</td>
				<td>{lang_get s=$link_msg[idx]}<td>
			</tr>
		{/section}
		</table>
		{if $delete_msg neq ''}  
			<h2>{$delete_msg}</h2>
		{/if}
	{/if}
  
	<p>{lang_get s='question_del_testsuite'}</p>
		<form method="post" 
			action="lib/testcases/containerEdit.php?sure=yes&amp;objectID={$objectID}">
	    
	<input type="submit" name="delete_testsuite" value="{lang_get s='btn_yes_del_comp'}" />
		
	{* 20070213 - franciscom - BUGID 0000629 *}
	<input type="button" name="cancel_delete_testsuite" value="{lang_get s='btn_no'}"
			onclick='javascript: location.href=fRoot+
			"lib/testcases/archiveData.php?&amp;edit=testsuite&amp;id={$objectID}";' />
	</form>
{/if}

{if $refreshTree}
   	{include file="inc_refreshTreeWithFilters.tpl"}
	{*include file="inc_refreshTree.tpl"*}
{/if}

</div>
</body>
</html>