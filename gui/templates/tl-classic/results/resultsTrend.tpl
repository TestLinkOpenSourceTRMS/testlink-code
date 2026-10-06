{*
TestLink Open Source Project - http://testlink.sourceforge.net/

Purpose: smarty template - execution trend & flaky test report

@filesource resultsTrend.tpl
*}

{lang_get var="labels"
     s='testproject,test_plan,th_test_case,trend_no_data,
        trend_daily_executions,trend_flaky_title,trend_flaky_help,
        trend_flaky_flips,trend_flaky_execs,trend_analyzed_hint'}

{include file="inc_head.tpl"}

<body>
<h1 class="title">{$gui->title}</h1>

<div style="padding: 4px 10px;">
  {$labels.testproject}: <b>{$gui->tproject_name|escape}</b> &nbsp;
  {$labels.test_plan}: <b>{$gui->tplan_name|escape}</b>
</div>

<h2>{$labels.trend_daily_executions}</h2>
{if $gui->trendSVG != ''}
  <div style="padding: 4px 10px; overflow-x: auto;">
    {$gui->trendSVG}
  </div>
{else}
  <div style="padding: 4px 10px;">{$labels.trend_no_data}</div>
{/if}

<h2>{$labels.trend_flaky_title}</h2>
<div style="padding: 0px 10px 8px;">
  {$labels.trend_flaky_help}<br>
  <i>{$labels.trend_analyzed_hint}: {$gui->flakyAnalyzed}</i>
</div>
{if count($gui->flaky) > 0}
  <table class="simple" style="margin: 0px 10px;">
    <tr>
      <th>{$labels.th_test_case}</th>
      <th>{$labels.trend_flaky_flips}</th>
      <th>{$labels.trend_flaky_execs}</th>
    </tr>
    {foreach $gui->flaky as $tcvid => $item}
    <tr>
      <td>{$item.name|escape}</td>
      <td style="text-align: center;">{$item.flips}</td>
      <td style="text-align: center;">{$item.total}</td>
    </tr>
    {/foreach}
  </table>
{else}
  <div style="padding: 4px 10px;">{$labels.trend_no_data}</div>
{/if}

</body>
</html>
