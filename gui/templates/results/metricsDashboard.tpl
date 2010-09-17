{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 $Id: metricsDashboard.tpl,v 1.10 2010/09/17 11:22:19 mx-julian Exp $     
 Purpose: smarty template - main page / site map                 

 rev:
  20100917 - Julian - BUGID 3724 - checkbox to show all/active test plans
  20090919 - franciscom - added plaftorm information
*}
{lang_get var="labels"
          s="generated_by_TestLink_on,testproject,test_plan,th_total_tc,th_active_tc,th_executed_tc,
             th_executed_vs_active,th_executed_vs_total,platform,show_only_active"}
{include file="inc_head.tpl"}

<body>
<div class="workBack">
<h1 class="title">{$labels.testproject} {$smarty.const.TITLE_SEP} {$gui->tproject_name|escape}</h1>

<p><form method="post">
<input type="checkbox" name="show_only_active" value="show_only_active"
       {if $gui->show_only_active} checked="checked" {/if}
       onclick="this.form.submit();" /> {$labels.show_only_active}
<input type="hidden"
       name="show_only_active_hidden"
       value="{$gui->show_only_active}" />
</form></p><br/>

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
