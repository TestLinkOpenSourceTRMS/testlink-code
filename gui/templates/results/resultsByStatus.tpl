{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsByStatus.tpl,v 1.7 2009/05/17 10:57:28 franciscom Exp $
Purpose: show Test Results and Metrics

rev: 20090517 - franciscom - refactoring
*}

{lang_get var='labels' s='th_test_suite,test_case,version,th_build,th_run_by,th_bugs_not_linked,
                          th_date,th_notes,th_bugs,info_test_results,th_assigned_to'}

{include file="inc_head.tpl"}

<body>

<h1 class="title">{$gui->title|escape}</h1>

<div class="workBack">
{include file="inc_result_tproject_tplan.tpl"
         arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}
<table class="simple" style="width: 100%; text-align: left; margin-left: 0px;">
<tr>
	<th>{$labels.th_test_suite}</th>
	<th>{$labels.test_case}</th>
    <th>{$labels.version}</th>
{if $gui->type == $tlCfg->results.status_code.not_run} {* Add the Assigned To Column }
    <th>{$labels.th_assigned_to}</th>	
{/if}
    {if $gui->type != $tlCfg->results.status_code.not_run}
		    <th>{$labels.th_build}</th>
		    <th>{$labels.th_run_by}</th>
		    <th>{$labels.th_date}</th>
		    <th>{$labels.th_notes}</th>
		    <th>{$labels.th_bugs}</th>
	  {/if}
</tr>
{section name=Row loop=$gui->dataSet}
<tr>
	{section name=Item loop=$gui->dataSet[Row]}
		<td>{$gui->dataSet[Row][Item]}</td>
	{/section}
</tr>
{/section}
</table>
<h2 class="simple">{$labels.th_bugs_not_linked}{$gui->without_bugs_counter}</h2>

<p class="italic">{$labels.info_test_results}</p>

{lang_get s="generated_by_TestLink_on"} {$smarty.now|date_format:$gsmarty_timestamp_format}
</div>
</body>
</html>