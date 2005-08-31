{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerView.tpl,v 1.7 2005/08/31 08:46:28 franciscom Exp $ *}
{* 
Purpose: smarty template - view test specification containers 

20050830 - fm - added hidden input fields to convey component or category name

20050829 - fm
1. remove |escape on all data fields that use fckeditor during the input phase.
2. remove pre for the same fields
3. use associative array instead of ordinal

20050828 - scs - adding import of tcs to a specific category
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">

<h1>{$level|capitalize}: {$container_data.name|escape}</h1>

{include file="inc_update.tpl" result=$sqlResult item=$level name=$moddedItem[1] refresh='yes'}

{if $level == 'product'}
	{if $modify_tc_rights == 'yes'}
		<div>
			<form method="post" action="lib/testcases/containerEdit.php">
				<input type="submit" name="newCOM" value="{lang_get s='btn_new_com'}" />
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
	</table>

{***** COMPONENT ************************************************}
{elseif $level == 'component'}

	{if $modify_tc_rights == 'yes' || $sqlResult ne ''}
		<div>
		<form method="post" action="lib/testcases/containerEdit.php?componentID={$container_data.id}" />
		  {* 20050830 - fm *}
			<input type="hidden" name="componentName" value="{$container_data.name}" />

			<input type="submit" name="editCOM" value="{lang_get s='btn_edit_com'}"
				     alt="{lang_get s='alt_edit_com'}" />
			<input type="submit" name="deleteCOM" value="{lang_get s='btn_del_com'}" 
				     alt="{lang_get s='alt_del_com'}" />
			<input type="submit" name="moveCom" value="{lang_get s='btn_move_cp_com'}" 
				     alt="{lang_get s='alt_move_cp_com'}" />
			<input type="submit" name="reorderCAT" value="{lang_get s='btn_reorder_cat'}" />
		</form>
		</div>
		<div>
		<form method="post" action="lib/testcases/containerEdit.php?componentID={$container_data.id}" />
			<input type="submit" name="newCAT" value="{lang_get s='btn_new_cat'}" />
		</form>
		</div>
	{/if}

  {include file="inc_comp_viewer_ro.tpl"}

{***** CATEGORY ************************************************}
{elseif $level == 'category'}
	{if $modify_tc_rights == 'yes' || $sqlResult ne ''}
		<div>
		<form method="post" action="lib/testcases/containerEdit.php?categoryID={$container_data.id}" />
			{* 20050830 - fm *}
			<input type="hidden" name="categoryName" value="{$container_data.name}" />

			<input type="submit" name="editCat"   value="{lang_get s='btn_edit_cat'}" />  
			<input type="submit" name="deleteCat" value="{lang_get s='btn_del_cat'}" />   
			<input type="submit" name="moveCat"   value="{lang_get s='btn_move_cp_cat'}" />
			<input type="submit" name="reorderTC" value="{lang_get s='btn_reorder_tc'}" />
		</form>
		</div>
		<div>
		<form method="post" action="lib/testcases/tcEdit.php?categoryID={$container_data.id}" />
			<input type="submit" name="newTC" value="{lang_get s='btn_new_tc'}" />  
		</form>
		</div>
		
		<div>
		<form method="post" action="lib/testcases/tcImport.php"/>
			<input type="hidden" name="catID" value="{$container_data.id}"/>
			<input type="submit" name="tcImport" value="{lang_get s='btn_import_tc'}" />
		</form>
		</div>

		
	{/if}

 {include file="inc_cat_viewer_ro_m0.tpl"}
{/if}

</div>
</body>
</html>