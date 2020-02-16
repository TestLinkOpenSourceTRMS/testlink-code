{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_results_show_table.tpl,v 1.1 2008/03/03 18:53:20 franciscom Exp $ 
*}

{$args_title=$args_title|default:""}
{$args_first_column_header=$args_first_column_header|default:"first column"}
{$args_show_percentage=$args_show_percentage|default:true}

{if $args_column_definition != ""}

<h2 class="big-font">{$args_title|escape}</h2>
<table class="simple_tableruler sortable" style="text-align: center; margin-left: 0px;">
	<tr>
		<th style="text-align: center;">{$args_first_column_header|escape}</th>
		<th style="text-align: center;">{lang_get s='trep_total'}</th>
    {foreach item=the_column from=$args_column_definition}
        <th style="text-align: center;">{$the_column.qty}</th>
        {if $args_show_percentage}
        <th style="text-align: center;">{$the_column.percentage}</th>
        {/if}
    {/foreach}
		<th style="text-align: center;">{lang_get s='trep_comp_perc'}</th>
	</tr>
	
 {foreach item=res from=$args_column_data}
  	<tr>
  	<td style="text-align: left;">{$res.$args_first_column_key|escape}</td>
  	<td style="text-align: right;padding-right:10px;">{$res.total_tc}</td>
      {foreach item=the_column from=$res.details}
          <td style="text-align: right;padding-right:10px;">{$the_column.qty}</td>
        {if $args_show_percentage}
          <td style="text-align: right;padding-right:10px;">{$the_column.percentage}</td>
        {/if}
      {/foreach}
  	<td style="text-align: right;padding-right:10px;">{$res.percentage_completed}</td>
  	</tr>
  {/foreach}
</table>
{/if}