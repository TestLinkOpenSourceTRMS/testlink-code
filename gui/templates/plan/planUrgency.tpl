{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planUrgency.tpl,v 1.8 2009/02/09 21:41:22 havlat Exp $

Purpose: smarty template - manage test case urgency

Revision: 20080901 - franciscom - display testcase external id

          20080721 - franciscom 
          1. if test suite has no test case, then give message and remove all controls
          2. use labels instead of code to display urgency
          3. remove feedback -> user get feedback seeing his/her changes has been applied
          
*}
{assign var="ownURL" value="lib/plan/planUrgency.php"}
{lang_get var="labels" 
          s='title_plan_urgency, th_testcase, th_urgency, urgency_low, urgency_medium, urgency_high,
             label_set_urgency, urgency_description,testsuite_is_empty'}

{include file="inc_head.tpl"}
<body>

<h1 class="title">{$gui->tplan_name|escape}{$tlCfg->gui_title_separator_2}{$labels.title_plan_urgency}
	 {$tlCfg->gui_title_separator_1}{$gui->node_name|escape}</h1>

<div class="workBack">

{if $gui->listTestCases != ''}
	<div class="groupBtn">
    <form method="post" action="{$ownURL}" id="set_urgency">
	<span>{$labels.label_set_urgency}
    	<input type="submit" name="high_urgency" value="{$labels.urgency_high}" />
    	<input type="submit" name="medium_urgency" value="{$labels.urgency_medium}" />
    	<input type="submit" name="low_urgency" value="{$labels.urgency_low}" />
		<input type="hidden" name="tplan_id" value="{$gui->tplan_id}" />
		<input type="hidden" name="id" value="{$gui->node_id}" />
    </span>
    </form>
	</div>

{* ------------------------------------------------------------------------------------------- *}
	<table class="simple" style="width: 600px; text-align: center">
	<tr>
		<th style="text-align: left;">{$labels.th_testcase}</th>
		<th>{$labels.th_urgency}</th>
	</tr>

	{foreach item=res from=$gui->listTestCases}
	<tr>
			<td style="text-align: left;">{$res.tcprefix|escape}{$res.tc_external_id}&nbsp;:&nbsp;{$res.name|escape}</td>
  			{assign var=urgencyCode value=$res.urgency}
  			<td>{lang_get s=$gui->urgencyCfg.code_label[$urgencyCode]}</td>
	</tr>
	{/foreach}
	</table>
{* ------------------------------------------------------------------------------------------- *}
	<p>{$labels.urgency_description}</p>
{else}
	<p>{$labels.testsuite_is_empty}</p>
{/if}
</div>
</body>
</html>