{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planUrgency.tpl,v 1.10 2009/06/17 22:10:17 havlat Exp $

Smarty template - manage test case urgency

Revisions: 
	20110415 - Julian - BUGID 4419 - added columns "Importance" and "Priority"
	20080901 - franciscom - display testcase external id
    20080721 - franciscom 
          1. if test suite has no test case, then give message and remove all controls
          2. use labels instead of code to display urgency
          3. remove feedback -> user get feedback seeing his/her changes has been applied
          
*}
{assign var="ownURL" value="lib/plan/planUrgency.php"}
{lang_get var="labels" 
          s='title_plan_urgency, th_testcase, th_urgency, urgency_low, urgency_medium, urgency_high,
             label_set_urgency_ts, btn_set_urgency_tc, urgency_description,testsuite_is_empty,
             priority, importance'}

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
			<td style="text-align: left;">{$res.tcprefix|escape}{$res.tc_external_id}&nbsp;:&nbsp;{$res.name|escape}</td>
			{assign var=importance value=$res.importance}
			<td>{$gsmarty_option_importance.$importance}</td>
  			{assign var=urgencyCode value=$res.urgency}
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
			{assign var=priority value=$res.priority}
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