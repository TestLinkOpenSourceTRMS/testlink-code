{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource planUrgency.tpl

Smarty template - manage test case urgency
         
*}
{$ownURL="lib/plan/planUrgency.php"}
{lang_get var="labels" 
          s='title_plan_urgency, th_testcase, th_urgency, urgency_low, urgency_medium, urgency_high,
             label_set_urgency_ts, btn_set_urgency_tc, urgency_description,testsuite_is_empty,
             priority, importance, execution_history, design'}

{include file="inc_head.tpl"}
<body>

<h1 class="title">{$gui->tplan_name|escape}{$tlCfg->gui_title_separator_2}{$labels.title_plan_urgency}
   {$tlCfg->gui_title_separator_1}{$gui->node_name|escape}</h1>

<div class="workBack">

{if $gui->listTestCases != ''}
  <div class="groupBtn">
    <form method="post" action="{$ownURL}" id="set_urgency">
  <span>{$labels.label_set_urgency_ts}
      <input type="submit" name="high_urgency" value="{$labels.urgency_high}" />
      <input type="submit" name="medium_urgency" value="{$labels.urgency_medium}" />
      <input type="submit" name="low_urgency" value="{$labels.urgency_low}" />
    <input type="hidden" name="tplan_id" value="{$gui->tplan_id}" />
    <input type="hidden" name="id" value="{$gui->node_id}" />
    </span>
    </form>
  </div>

{* ------------------------------------------------------------------------------------------- *}
  <form method="post" action="{$ownURL}" id="set_urgency_tc">
  <input type="hidden" name="tplan_id" value="{$gui->tplan_id}" />
  <input type="hidden" name="id" value="{$gui->node_id}" />
  <table class="simple_tableruler" style="text-align: center">
  <tr>
    <th style="text-align: left;">{$labels.th_testcase}</th>
    <th>{$labels.importance}</th>
    <th colspan="3">{$labels.th_urgency}</th>
    <th>{$labels.priority}</th>
  </tr>

  {foreach item=res from=$gui->listTestCases}
  <tr>
    <td style="text-align: left;">
      <img class="clickable" src="{$tlImages.history_small}"
           onclick="javascript:openExecHistoryWindow({$res.testcase_id});"
           title="{$labels.execution_history}" />
      <img class="clickable" src="{$tlImages.edit_icon}"
           onclick="javascript:openTCaseWindow({$res.testcase_id});"
           title="{$labels.design}" />
        {$res.tcprefix|escape}{$res.tc_external_id}{$gsmarty_gui->title_separator_1}{$res.name|escape}
    </td>
    {$importance=$res.importance}
    <td>{$gsmarty_option_importance.$importance}</td>
      {$urgencyCode=$res.urgency}
    <td><input type="radio"
           name="urgency[{$res.tcversion_id}]"
           value="{$smarty.const.HIGH}" 
           {if $urgencyCode == $smarty.const.HIGH}
          checked="checked"
           {/if}
          />
      <span style="vertical-align:middle;">{$labels.urgency_high}</span>
    </td>
    <td><input type="radio"
           name="urgency[{$res.tcversion_id}]"
           value="{$smarty.const.MEDIUM}" 
           {if $urgencyCode == $smarty.const.MEDIUM}
               checked="checked"
           {/if}
          />
      <span style="vertical-align:middle;">{$labels.urgency_medium}</span>
    </td>
    <td><input type="radio"
           name="urgency[{$res.tcversion_id}]"
           value="{$smarty.const.LOW}" 
           {if $urgencyCode == $smarty.const.LOW}
               checked="checked"
           {/if}
          />
      <span style="vertical-align:middle;">{$labels.urgency_low}</span>
    </td>
    {$priority=$res.priority}
    <td>{$gsmarty_option_priority.$priority}</td>
  </tr>
  {/foreach}
  </table>
  <div class="groupBtn">
    <input type="submit" value="{$labels.btn_set_urgency_tc}" />
  </div>
  </form>
{* ------------------------------------------------------------------------------------------- *}
  <p>{$labels.urgency_description}</p>
{else}
  <p>{$labels.testsuite_is_empty}</p>
{/if}
</div>
</body>
</html>