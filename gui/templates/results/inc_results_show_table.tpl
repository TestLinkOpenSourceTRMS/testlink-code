{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_results_show_table.tpl,v 1.1 2008/03/03 18:53:20 franciscom Exp $ 
*}

{assign var="args_title" value=$args_title|default:""}
{assign var="args_first_column_header" value=$args_first_column_header|default:"first column"}
{assign var="args_show_percentage" value=$args_show_percentage|default:true}

{if $args_column_definition != ""}

<h2>{$args_title|escape}</h2>
<table class="simple_tableruler sortable" style="text-align: center; margin-left: 0px;">
	<tr>
		<th>{$args_first_column_header|escape}</th>
		<th>{lang_get s='trep_total'}</th>
    {foreach item=the_column from=$args_column_definition}
        <th>{$the_column.qty}</th>
        {if $args_show_percentage}
        <th>{$the_column.percentage}</th>
        {/if}
    {/foreach}
		<th>{lang_get s='trep_comp_perc'}</th>
	</tr>
	
 {foreach item=res from=$args_column_data}
  	<tr>
  	<td>{$res.$args_first_column_key|escape}</td>
  	<td>{$res.total_tc}</td>
      {foreach item=the_column from=$res.details}
          <td>{$the_column.qty}</td>
        {if $args_show_percentage}
          <td>{$the_column.percentage}</td>
        {/if}
      {/foreach}
  	<td>{$res.percentage_completed}</td>
  	</tr>
  {/foreach}
</table>
{/if}