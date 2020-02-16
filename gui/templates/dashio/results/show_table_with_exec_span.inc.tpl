{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
show_table_with_exec_span.inc.tpl
*}

{$args_title=$args_title|default:""}
{$args_first_column_header=$args_first_column_header|default:"first column"}
{$args_show_percentage=$args_show_percentage|default:true}
{$colForTotal=$args_column_for_total|default:"total_tc"} 

{if $args_column_definition != ""}

<h2>{$args_title|escape}</h2>
{if null != $gui->spanByPlatform}
  {$labels.firstExec}
  {$gui->spanByPlatform[$platId]['begin']|date_format:$gsmarty_timestamp_format}<br>
  {$labels.latestExec}{$gui->spanByPlatform[$platId]['end']|date_format:$gsmarty_timestamp_format}<br>
  <p>
{/if}
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
    {if $args_show_percentage}
		  <th>{lang_get s='trep_comp_perc'}</th>
    {/if}
	</tr>

 {foreach item=res from=$args_column_data}
  	<tr>
  	<td style="text-align: left;">{$res.$args_first_column_key|escape}</td>
  	<td style="text-align: right;padding-right: 10px;">{$res.$colForTotal}</td>
      {foreach item=the_column from=$res.details}
          <td style="text-align: right;padding-right: 10px;">{$the_column.qty}</td>
        {if $args_show_percentage}
          <td style="text-align: right;padding-right: 10px;">{$the_column.percentage}</td>
        {/if}
      {/foreach}
  	<td>{$res.percentage_completed}</td>
  	</tr>
  {/foreach}
</table>
{/if}