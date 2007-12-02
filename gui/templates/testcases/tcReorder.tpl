{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcReorder.tpl,v 1.1 2007/12/02 17:03:58 franciscom Exp $
Purpose: reorder testcases 
20051015 - fm - BUGID 181 - data -> categoryID
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">

<h1>{lang_get s='title_change_tc_order'}</h1>

<div>	

{if $arrTC eq ''}
	{lang_get s='no_tc_to_reorder'}
{else}
	<form method="post" action="lib/testcases/containerEdit.php?categoryID={$data}">
		<div style="padding: 3px;">
			<input id="submit" type="submit" name="updateTCorder" value="Update" />
		</div>	
	
		<table class="common" style="width: 70%">
			<tr>
				<th style="width: 10%;">{lang_get s='th_id'}</th>
				<th>{lang_get s='th_test_case'}</th>
				<th style="width: 15%;">{lang_get s='th_order'}</th>
			</tr>
	
			{section name=number loop=$arrTC}
			<tr>
				<td>{$arrTC[number].id}</td>
				<td class="bold">{$arrTC[number].name|escape}</td>
				<td>
					<input type="hidden" name="id{$arrTC[number].id}" 
						value="{$arrTC[number].id}" />
					<input type="text" size="5" name="order{$arrTC[number].id}" 
						value="{$arrTC[number].TCorder}" />
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