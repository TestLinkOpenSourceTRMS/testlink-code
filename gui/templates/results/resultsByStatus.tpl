{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsByStatus.tpl,v 1.5 2009/03/25 19:01:19 amkhullar Exp $
Purpose: show Test Results and Metrics
*}

{lang_get var='labels' s='th_test_suite,test_case,version,th_build,th_run_by,th_bugs_not_linked,
                          th_date,th_notes,th_bugs,info_test_results'}

{include file="inc_head.tpl"}

<body>

<h1 class="title">{$title|escape}</h1>

<div class="workBack">
{include file="inc_result_tproject_tplan.tpl"
         arg_tproject_name=$tproject_name arg_tplan_name=$tplan_name}
<table class="simple" style="width: 100%; text-align: left; margin-left: 0px;">
<tr>
	<th>{$labels.th_test_suite}</th>
	<th>{$labels.test_case}</th>
    <th>{$labels.version}</th>
    {if $type != $tlCfg->results.status_code.not_run}
		    <th>{$labels.th_build}</th>
		    <th>{$labels.th_run_by}</th>
		    <th>{$labels.th_date}</th>
		    <th>{$labels.th_notes}</th>
		    <th>{$labels.th_bugs}</th>
	  {/if}
</tr>
{section name=Row loop=$arrData}
<tr>
	{section name=Item loop=$arrData[Row]}
		<td>{$arrData[Row][Item]}</td>
	{/section}
</tr>
{/section}
</table>
<h2 class="simple">{$labels.th_bugs_not_linked}{$count}</h2>

<p class="italic">{$labels.info_test_results}</p>

{lang_get s="generated_by_TestLink_on"} {$smarty.now|date_format:$gsmarty_timestamp_format}
</div>
</body>
</html>