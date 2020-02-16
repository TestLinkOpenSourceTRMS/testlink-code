{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_results_show_table.tpl
*}

{$args_title=$args_title|default:""}
{$args_first_column_header=$args_first_column_header|default:"first column"}
{$args_show_percentage=$args_show_percentage|default:true}
{$colForTotal=$args_column_for_total|default:"total_tc"} 

{if $args_column_definition != ""}

<h2>{$args_title|escape}</h2>
<table class="simple_tableruler sortable" style="text-align: center; margin-left: 0px;">
	<tr>
    {foreach item=the_column from=$args_column_definition}
      <th>{$the_column}</th>
    {/foreach}
	</tr>

 {foreach item=res from=$args_column_data}
  	<tr>
    {foreach item=the_column from=$res}
      <td>{$the_column}</td>
    {/foreach}
  	</tr>
  {/foreach}
</table>
{/if}