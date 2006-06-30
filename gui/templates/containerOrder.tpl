{* TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: containerOrder.tpl,v 1.5 2006/06/30 18:41:25 schlundus Exp $ 
Purpose: smarty template - reorder containers (actually categories only) 
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">

<h1>{lang_get s='title_change_node_order'}</h1>

<div>	
	{if $arraySelect eq ''}
		{lang_get s='no_nodes_to_reorder'}
	{else}
	<form method="post" action="lib/testcases/containerEdit.php?containerID={$data}">
		<div style="padding: 3px;">
			<input id="submit" type="submit" name="do_testsuite_reorder" value="{lang_get s='btn_upd'}" />
		</div>	
	
		<table class="common" style="width: 70%">
			<tr>
				<th style="width: 10%;">{lang_get s='th_id'}</th>
				<th>{lang_get s='node'}</th>
				<th style="width: 15%;">{lang_get s='th_order'}</th>
			</tr>
	
			{section name=idx loop=$arraySelect}
			<tr>
				<td>{$arraySelect[idx].id}</td>
				<td class="bold">{$arraySelect[idx].name|escape}</td>
				<td>
					<input type="hidden" name="id[{$arraySelect[idx].id}]" 
						value="{$arraySelect[idx].id}" />
					<input type="text" size="5" name="order[{$arraySelect[idx].id}]" 
						value="{$arraySelect[idx].node_order|escape}" />
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