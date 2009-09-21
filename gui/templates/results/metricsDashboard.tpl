{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 $Id: metricsDashboard.tpl,v 1.9 2009/09/21 09:28:28 franciscom Exp $     
 Purpose: smarty template - main page / site map                 

 rev: 
  20090919 - franciscom - added plaftorm information
*}
{lang_get var="labels"
          s="generated_by_TestLink_on,testproject,test_plan,th_total_tc,th_active_tc,th_executed_tc,
             th_executed_vs_active,th_executed_vs_total,platform"}
{include file="inc_head.tpl"}

<body>
<div class="workBack">
<h1 class="title">{$labels.testproject} {$smarty.const.TITLE_SEP} {$gui->tproject_name|escape}</h1>

<table class="mainTable-x sortable" style="width: 100%">
  <tr>
    <th>{$sortHintIcon}{$labels.test_plan}</th>
    {if $gui->show_platforms}
    <th>{$sortHintIcon}{$labels.platform}</th>
    {/if}
   	<th class="{$noSortableColumnClass}">{$labels.th_total_tc}</th>
   	<th class="{$noSortableColumnClass}">{$labels.th_active_tc}</th>
   	<th class="{$noSortableColumnClass}">{$labels.th_executed_tc}</th>
   	<th class="{$noSortableColumnClass}">{$labels.th_executed_vs_active}</th>
   	<th class="{$noSortableColumnClass}">{$labels.th_executed_vs_total}</th>
  </tr>
  {foreach item=platform_metric from=$gui->tplan_metrics}
    {foreach item=metric from=$platform_metric}
      <tr>
        <td>{$metric.tplan_name|escape}</td>
        {if $gui->show_platforms}
        <td>{$metric.platform_name|escape}</th>
        {/if}
        <td style="text-align:right;">{$metric.total}</td>
        <td style="text-align:right;">{$metric.active}</td>
        <td style="text-align:right;">{$metric.executed}</td>
        <td style="text-align:right;">{if $metric.executed_vs_active gt 0}
                                          {$metric.executed_vs_active}
                                      {else} - {/if} </td>
        <td style="text-align:right;">{if $metric.executed_vs_total gt 0}
                                          {$metric.executed_vs_total}
                                      {else} - {/if} </td>
      </tr> 
    {/foreach}
  {/foreach}
</table>
<br />
{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
</div> 
</body>
</html>
