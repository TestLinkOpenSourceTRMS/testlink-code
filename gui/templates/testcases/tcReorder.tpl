{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcReorder.tpl,v 1.3 2008/07/22 09:25:14 havlat Exp $
Purpose: reorder testcases 
Revisions:
	20080722 - havlatm - layout update
	20051015 - fm - BUGID 181 - data -> categoryID
*}
{include file="inc_head.tpl"}

<body>
<h1 class="title">{lang_get s='title_change_tc_order'}</h1>

<div class="workBack">

{if $arrTC eq ''}
	{lang_get s='no_tc_to_reorder'}
{else}
	<form method="post" action="{$basehref}lib/testcases/containerEdit.php?categoryID={$data}">
		<div class="groupBtn">
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

</body>
</html>