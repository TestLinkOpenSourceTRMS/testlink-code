{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerView.tpl,v 1.3 2005/08/22 07:00:49 franciscom Exp $ *}
{* 
Purpose: smarty template - view test specification containers 
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">

<h1>{$level|capitalize}: {$data[1]|escape}</h1>

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
			<td>{$data[1]|escape}</td>
		</tr>
	</table>

{***** COMPONENT ************************************************}
{elseif $level == 'component'}

	{if $modify_tc_rights == 'yes' || $sqlResult ne ''}
		<div>
		<form method="post" action="lib/testcases/containerEdit.php?data={$data[0]}" />
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
		<form method="post" action="lib/testcases/containerEdit.php?data={$data[0]}" />
			<input type="submit" name="newCAT" value="{lang_get s='btn_new_cat'}" />
		</form>
		</div>
	{/if}

	<table class="simple" style="width: 90%">
		<tr><th>{lang_get s='component'}: {$data[1]|escape}</th></tr>
	{if $data[2] ne ''}
		<tr><td class="bold">{lang_get s='introduction'}</td></tr>
    	<tr><td><pre>{$data[2]|escape}</pre></td></tr>
    {/if}
	<tr><td class="bold">{lang_get s='scope'}</td></tr>
	{if $data[3] ne ''}
	    <tr><td>{$data[3]}</td></tr>
	{else}
	    <tr><td>{lang_get s='not_defined'}</td></tr>
    {/if}
	{if $data[4] ne ''}
		<tr><td class="bold">{lang_get s='references'}</td></tr>
    	<tr><td><pre>{$data[4]|escape}</pre></td></tr>
    {/if}
	{if $data[5] ne ''}
		<tr><td class="bold">{lang_get s='methodology'}</td></tr>
    	<tr><td><pre>{$data[5]|escape}</pre></td></tr>
    {/if}
	{if $data[6] ne ''}
		<tr><td class="bold">{lang_get s='limitations'}</td></tr>
		<tr><td><pre>{$data[6]|escape}</pre></td></tr>
    {/if}
	</table>

{***** CATEGORY ************************************************}
{elseif $level == 'category'}
	{if $modify_tc_rights == 'yes' || $sqlResult ne ''}
		<div>
		<form method="post" action="lib/testcases/containerEdit.php?data={$data[0]}" />
			<input type="submit" name="editCat"   value="{lang_get s='btn_edit_cat'}" />  
			<input type="submit" name="deleteCat" value="{lang_get s='btn_del_cat'}" />   
			<input type="submit" name="moveCat"   value="{lang_get s='btn_move_cp_cat'}" />
			<input type="submit" name="reorderTC" value="{lang_get s='btn_reorder_tc'}" />
		</form>
		</div>
		<div>
		<form method="post" action="lib/testcases/tcEdit.php?data={$data[0]}" />
			<input type="submit" name="newTC" value="{lang_get s='btn_new_tc'}" />  
		</form>
		</div>
	{/if}

	<table class="simple" style="width: 90%">
		<tr>
			<th>{lang_get s='category'}: {$data[1]|escape}</th>
		</tr>
		<tr>
			<td class="bold">{lang_get s='cat_scope'}</td>
		</tr>
	{if $data[2] ne ''}
    	<tr>
			<td>{$data[2]}</td>
		</tr>
	{else}
	    <tr>
			<td>{lang_get s='not_defined'}</td>
		</tr>
    {/if}
	{if $data[3] ne ''}
		<tr>
			<td class="bold">{lang_get s='configuration'}</td>
		</tr>
	    <tr>
			<td><pre>{$data[3]|escape}</pre></td>
		</tr>
    {/if}
	{if $data[4] ne ''}
		<tr>
			<td class="bold">{lang_get s='data'}</td>
		</tr>
    	<tr>
			<td><pre>{$data[4]|escape}</pre></td>
		</tr>
    {/if}
	{if $data[5] ne ''}
		<tr>
			<td class="bold">{lang_get s='tools'}</td>
		</tr>
    	<tr>
			<td><pre>{$data[5]|escape}</pre></td>
		</tr>
    {/if}
	</table>
{/if}

</div>
</body>
</html>