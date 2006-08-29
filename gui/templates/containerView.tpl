{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerView.tpl,v 1.21 2006/08/29 19:41:36 schlundus Exp $ *}
{* 
Purpose: smarty template - view test specification containers 

20060822 - franciscom - fixed bug unable to attach files to test suites
                        due to typo error
                        
20060311 - franciscom - added reorder test suites for test project
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">

<h1>{lang_get s=$level}: {$container_data.name|escape}</h1>

{include file="inc_update.tpl" result=$sqlResult item=$level name=$moddedItem.name refresh='yes'}

{assign var="bDownloadOnly" value=false}
{if $level == 'testproject'}
	{if $mgt_modify_product neq 'yes'}
		{assign var="bDownloadOnly" value=true}
	{/if}
	
	{if $modify_tc_rights == 'yes'}
		<div>
			<form method="post" action="lib/testcases/containerEdit.php">
				<input type="hidden" name="containerID" value={$container_data.id}>
				<input type="submit" name="new_testsuite" value="{lang_get s='btn_new_com'}" />
			  <input type="submit" name="reorder_testsuites" value="{lang_get s='btn_reorder_cat'}" />
			</form>
		</div>
	{/if}

	<table width="90%" class="simple">
		<tr>
			<th>{lang_get s='th_product_name'}</th>
		</tr>
		<tr>
			<td>{$container_data.name|escape}</td>
		</tr>
		<tr>
			<th>{lang_get s='th_notes'}</th>
		</tr>
		<tr>
			<td>{$container_data.notes}</td>
		</tr>
		
	</table>

	{include file="inc_attachments.tpl" id=$id tableName="nodes_hierarchy" downloadOnly=$bDownloadOnly}
{elseif $level == 'testsuite'}

	{if $modify_tc_rights == 'yes' || $sqlResult ne ''}
		<div>
		<form method="post" action="lib/testcases/containerEdit.php?testsuiteID={$container_data.id}">
			<input type="hidden" name="testsuiteName" value="{$container_data.name|escape}" />

			<input type="submit" name="edit_testsuite" value="{lang_get s='btn_edit_com'}"
				     alt="{lang_get s='alt_edit_com'}" />
			<input type="submit" name="delete_testsuite" value="{lang_get s='btn_del_com'}" 
				     alt="{lang_get s='alt_del_com'}" />
			<input type="submit" name="move_testsuite_viewer" value="{lang_get s='btn_move_cp_com'}" 
				     alt="{lang_get s='alt_move_cp_com'}" />
			<input type="submit" name="reorder_testsuites" value="{lang_get s='btn_reorder_cat'}" />
		</form>
		</div>
		<br/>	

		{* Add a new testsuite children for this parent *}
		<div>
		<form method="post" action="lib/testcases/containerEdit.php?containerID={$container_data.id}">
			<input type="submit" name="new_testsuite" value="{lang_get s='btn_new_cat'}" />
		</form>
		</div>
		<br/>	

		{* Add a new testcase *}
		<div>
		<form method="post" action="lib/testcases/tcEdit.php?containerID={$container_data.id}">
			<input type="submit" id="create_tc" name="create_tc" value="{lang_get s='btn_new_tc'}" />  
			<input type="button" onclick="location='tcimport.php?containerID={$container_data.id}'" value="{lang_get s='btn_import_tc'}" />  
		</form>
		</div>
	{/if}

	{include file="inc_testsuite_viewer_ro.tpl"}
    
	{if $modify_tc_rights neq 'yes'}
		{assign var="bDownloadOnly" value=true}
	{/if}
	{include file="inc_attachments.tpl" id=$id tableName="nodes_hierarchy" downloadOnly=$bDownloadOnly}

	{/if}

</div>
</body>
</html>