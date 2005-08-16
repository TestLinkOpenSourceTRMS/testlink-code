{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerOrder.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - reorder containers (actually categories only) *}
{include file="inc_head.tpl"}

<body>
<div class="workBack">

<h1>{lang_get s='title_change_comp_cat_order'}</h1>

<div>	
	{if $arraySelect eq ''}
		{lang_get s='no_cat_to_reorder'}
	{else}
	<form method="post" action="lib/testcases/containerEdit.php?data={$data}">
		<div style="padding: 3px;">
			<input id="submit" type="submit" name="updateCategoryOrder" value="{lang_get s='btn_upd'}" />
		</div>	
	
		<table class="common" style="width: 70%">
			<tr>
				<th style="width: 10%;">{lang_get s='th_id'}</th>
				<th>{lang_get s='category'}</th>
				<th style="width: 15%;">{lang_get s='th_order'}</th>
			</tr>
	
			{section name=number loop=$arraySelect}
			<tr>
				<td>{$arraySelect[number].id}</td>
				<td class="bold">{$arraySelect[number].name|escape}</td>
				<td>
					<input type="hidden" name="id{$arraySelect[number].id}" 
						value="{$arraySelect[number].id|escape}" />
					<input type="text" size="5" name="order{$arraySelect[number].id|escape}" 
						value="{$arraySelect[number].CATorder|escape}" />
				</td>
			</tr>
			{/section}
	
		</table>
	</form>
	{/if}
</div>

</div>

</body>
</html>