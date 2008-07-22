{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planUrgency.tpl,v 1.2 2008/07/22 08:58:09 franciscom Exp $

Purpose: smarty template - manage test case urgency

Revision: 20080721 - franciscom 
          1. if test suite has no test case, then give message and remove all controls
          2. use labels instead of code to display urgency
          3. remove feedback -> user get feedback seeing his/her changes has been applied
          
---------------------------------------------------------------------------- *}
{assign var="ownURL" value="lib/plan/planUrgency.php"}
{lang_get var="labels" 
          s='title_plan_urgency, th_testcase, th_urgency, btn_low, btn_medium, btn_high,
             label_set_urgency, urgency_description,testsuite_is_empty'}

{include file="inc_head.tpl"}
<body>

<h1 class="title">{$tplan_name}{$tlCfg->gui_title_separator_2}{$labels.title_plan_urgency}
	 {$tlCfg->gui_title_separator_1}{$node_name}</h1>

<div class="workBack">

{if $listTestCases != ''}
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
