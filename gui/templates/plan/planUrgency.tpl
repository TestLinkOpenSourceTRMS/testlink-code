{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planUrgency.tpl,v 1.1 2008/07/18 14:26:23 havlat Exp $

Purpose: smarty template - Show existing builds

Revision:
	None
---------------------------------------------------------------------------- *}
{assign var="ownURL" value="lib/plan/planUrgency.php"}
{lang_get var="labels" 
          s='title_plan_urgency, th_testcase, th_urgency, btn_low, btn_medium, btn_high,
             label_set_urgency, urgency_description'}

{include file="inc_head.tpl"}
<body>

<h1 class="title">{$tplan_name}{$tlCfg->gui_title_separator_2}{$labels.title_plan_urgency}
	 {$tlCfg->gui_title_separator_1}{$node_name}</h1>

<div class="workBack">
{include file="inc_feedback.tpl"}

	<div class="groupBtn">
    <form method="post" action="{$ownURL}" id="set_urgency">
	<span>{$labels.label_set_urgency}
    	<input type="submit" name="high_urgency" value="{$labels.btn_high}" />
    	<input type="submit" name="medium_urgency" value="{$labels.btn_medium}" />
    	<input type="submit" name="low_urgency" value="{$labels.btn_low}" />
		<input type="hidden" name="tplan_id" value="{$tplan_id}" />
		<input type="hidden" name="id" value="{$node_id}" />
    </span>
    </form>
	</div>

{* ------------------------------------------------------------------------------------------- *}

	<table class="simple" style="width: 600px; text-align: center">
	<tr>
		<th style="text-align: left;">{$labels.th_testcase}</th>
		<th>{$labels.th_urgency}</th>
	</tr>

	{foreach item=res from=$listTestCases}
	<tr>
  			<td style="text-align: left;">{$res.name|escape}</td>
  			<td>{$res.urgency}</td>
	</tr>
	{/foreach}
	</table>


{* ------------------------------------------------------------------------------------------- *}


	<p>{$labels.urgency_description}</p>

</div>

</body>
</html>
