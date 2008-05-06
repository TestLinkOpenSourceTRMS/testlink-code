{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 $Id: metricsDashboard.tpl,v 1.4 2008/05/06 06:26:10 franciscom Exp $     
 Purpose: smarty template - main page / site map                 
                                                                 
 rev :                                                   
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">
<h1 class="title">{lang_get s='testproject'} {$smarty.const.TITLE_SEP} {$tproject_name}</h1>

<table class="mainTable-x" style="width: 100%">
  <tr>
    <th>{lang_get s='test_plan'}</th>
   	<th>{lang_get s='th_total_tc'}</th>
   	<th>{lang_get s='th_active_tc'}</th>
   	<th>{lang_get s='th_executed_tc'}</th>
   	<th>{lang_get s='th_executed_vs_active'}</th>
   	<th>{lang_get s='th_executed_vs_total'}</th>
  </tr>
  {foreach item=metric from=$tplan_metrics}
  <tr>
    <td>{$metric.tplan_name|escape}</td>
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

</table>
<br />
{lang_get s="generated_by_TestLink_on"} {$smarty.now|date_format:$gsmarty_timestamp_format}
</div> 
</body>
</html>
