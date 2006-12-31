{* TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: containerOrder.tpl,v 1.8 2006/12/31 18:20:49 franciscom Exp $ 
Purpose: smarty template - reorder container contents

20061231 - franciscom - $data -> $objectID
20061119 - franciscom - trying to improve user interface

*}
{include file="inc_head.tpl"}

<body>
{config_load file="input_dimensions.conf" section="containerOrder"} {* Constant definitions *}
<h1>{lang_get s=$level}{$gsmarty_title_sep}{$object_name|escape}</h1>

<div class="workBack">
<h1>{lang_get s='title_change_node_order'}</h1>

<div>	
	{if $arraySelect eq ''}
		{lang_get s='no_nodes_to_reorder'}
	{else}
	<form method="post" action="lib/testcases/containerEdit.php?containerID={$objectID}">
	
		<table class="common" style="width: 70%">
			<tr>
				<th style="width: 10%;">{lang_get s='th_id'}</th>
				<th>{lang_get s='node'}</th>
				<th>{lang_get s='th_node_type'}</th>
				<th style="width: 15%;">{lang_get s='th_order'}</th>
			</tr>
	
			{section name=idx loop=$arraySelect}
   		{assign var="node_table" value=$arraySelect[idx].node_table}
			<tr {if $node_table=='testsuites'} style="font-style:italic;" {/if}>
				<td>{$arraySelect[idx].id}</td>
				<td class="bold">{$arraySelect[idx].name|escape}</td>
				<td>
				{lang_get s=node_type_dbtable_$node_table }</td>
				<td>
					<input type="hidden" name="id[{$arraySelect[idx].id}]" 
						value="{$arraySelect[idx].id}" />
					<input type="text" size="{#ORDER_SIZE#}" maxlength="{#ORDER_MAXLEN#}"
					       name="order[{$arraySelect[idx].id}]" 
						     value="{$arraySelect[idx].node_order|escape}"/>
				</td>
			</tr>
			{/section}
		</table>
		<div style="padding: 3px;">
			<input type="submit" id="do_testsuite_reorder" 
			       name="do_testsuite_reorder" value="{lang_get s='btn_upd'}" />
		</div>	
	</form>
	{/if}
</div>

</div>

</body>
</html>