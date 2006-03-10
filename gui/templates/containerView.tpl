{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerView.tpl,v 1.11 2006/03/10 17:59:45 franciscom Exp $ *}
{* 
Purpose: smarty template - view test specification containers 

20050830 - fm - added hidden input fields to convey component or category name

20050829 - fm
1. remove |escape on all data fields that use fckeditor during the input phase.
2. remove pre for the same fields
3. use associative array instead of ordinal

20050828 - scs - adding import of tcs to a specific category
20051202 - scs - adding escaping of container names, fix for 267
20060225 - franciscom - new 1.7 terms instead of deprecated
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">

<h1>{$level|capitalize}: {$container_data.name|escape}</h1>

{include file="inc_update.tpl" result=$sqlResult item=$level name=$moddedItem[1] refresh='yes'}

{if $level == 'testproject'}
	{if $modify_tc_rights == 'yes'}
		<div>
			<form method="post" action="lib/testcases/containerEdit.php">
				{* 20060226 - franciscom *}
				<input type="hidden" name="containerID" value={$container_data.id}>
				<input type="submit" name="new_testsuite" value="{lang_get s='btn_new_com'}" />
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
		
		{* 20060225 - franciscom *}
		<tr>
			<th>{lang_get s='th_notes'}</th>
		</tr>
		<tr>
			<td>{$container_data.notes}</td>
		</tr>
		
	</table>

{elseif $level == 'testsuite'}

	{if $modify_tc_rights == 'yes' || $sqlResult ne ''}
		<div>
		<form method="post" action="lib/testcases/containerEdit.php?testsuiteID={$container_data.id}" />
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

		{* Add a new testsuite children for this parent *}
		<div>
		<form method="post" action="lib/testcases/containerEdit.php?containerID={$container_data.id}" />
			<input type="submit" name="new_testsuite" value="{lang_get s='btn_new_cat'}" />
		</form>
		</div>
		
		{* Add a new testcase - 20060226 - franciscom *}
		<div>
		<form method="post" action="lib/testcases/tcEdit.php?containerID={$container_data.id}" />
			<input type="submit" name="newTC" value="{lang_get s='btn_new_tc'}" />  
		</form>
		</div>
	{/if}

  {include file="inc_testsuite_viewer_ro.tpl"}
	{/if}

</div>
</body>
</html>