{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: resultsAllBuilds.tpl,v 1.3 2008/05/06 06:26:11 franciscom Exp $ 
Purpose: smarty template - show Test Results and Metrics 
Rev: 20080302 - franciscom - refactoring to manage dynamic qty of columns
*}
{include file="inc_head.tpl"}
<body>
<h1 class="title">{$title|escape}</h1>

<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$tproject_name arg_tplan_name=$tplan_name}	

{if $do_report.status_ok}
  <table class="simple" style="width: 95%; text-align: center;">
  	<tr>
  		<th style="width: 10%;">{lang_get s='th_build'}</th>
      <th>{lang_get s='th_tc_total'}</th>
      {foreach item=the_column from=$colDefinition}
          <th>{$the_column.qty}</th>
          <th>{$the_column.percentage}</th>
      {/foreach}
      <th>{lang_get s='th_perc_completed'}</th>
  	</tr>

  {foreach item=res from=$results}
  	<tr>
  	<td>{$res.build_name|escape}</td>
  	<td>{$res.total_tc}</td>
      {foreach item=the_column from=$res.details}
          <td>{$the_column.qty}</td>
          <td>{$the_column.percentage}</td>
      {/foreach}
  	<td>{$res.percentage_completed}</td>
  	</tr>
  {/foreach}
  </table>
  {lang_get s="generated_by_TestLink_on"} {$smarty.now|date_format:$gsmarty_timestamp_format}
{else}
  {$do_report.msg}
{/if}  

</div>

</body>
</html>
