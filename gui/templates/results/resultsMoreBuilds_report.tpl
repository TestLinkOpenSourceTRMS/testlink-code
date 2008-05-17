{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsMoreBuilds_report.tpl,v 1.4 2008/05/17 17:40:00 franciscom Exp $

rev :
     20070902 - franciscom - refactoring
*}
{lang_get var="labels"
          s="query_metrics_report,th_test_plan,th_builds,th_test_suites,th_keyword,
             assigned_to,th_last_result,th_start_time,th_end_time,th_executor,
             th_total_cases,th_total_pass,th_total_fail,th_total_block,th_total_not_run,
             generated_by_TestLink_on,test_status_not_run,
             th_test_case_id,th_build,th_tester_id,th_execution_ts,th_status,th_notes,th_bugs,
             th_search_notes_string,any,caption_user_selected_query_parameters"}

{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
		var bAllShown = false;
		var g_progress = null;
		var g_pCount = 0;
		progress();
</script>
</head>
<body>

{assign var=depth value=0}
{assign var='resultsCfg' value=$tlCfg->results}


<h1 class="title"> {$labels.query_metrics_report}</h1>
{include file="inc_result_tproject_tplan.tpl"
         arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}

{if $gui->display->query_params}
	<h2>{$labels.caption_user_selected_query_parameters}</h2>
	<table class="simple" style="width: 100%; text-align:center; margin-left: 0px;" border="0">
		<tr>
			<th>{$labels.th_test_plan}</th>
			<th>{$labels.th_builds}</th>
			<th>{$labels.th_test_suites}</th>
			<th>{$labels.th_keyword}</th>
			<th>{$labels.assigned_to}</th>
			<th>{$labels.th_last_result}</th>
			<th>{$labels.th_start_time}</th>
			<th>{$labels.th_end_time}</th>
			<th>{$labels.th_executor}</th>
			<th>{$labels.th_search_notes_string}</th>
		</tr>
		<tr>
			<td>
				{$gui->tplan_name|escape}
			</td>
			<td>
				{foreach key=buildrow item=array from=$gui->buildsSelected}
					{assign var=buildid value=$gui->buildsSelected[$buildrow]}
					{$mapBuilds[$buildid]|escape} <br />
				{/foreach}
			</td>
			<td>
				{foreach key=x item=array from=$gui->testsuitesSelected}
						{$gui->testsuitesSelected[$x]|escape} <br />
				{/foreach}
			</td>
			<td>
				{foreach key=keywordrow item=array from=$gui->keywordsSelected}
					{assign var=keywordid value=$gui->keywordsSelected[$keywordrow]}
					{$arrKeywords[$keywordid]}	<br />
				{/foreach}
			</td>

			<td>
			  {if $gui->ownerSelected == ''}
			    {$labels.any|escape}
			  {else}
				  {$gui->ownerSelected|escape}
				{/if}
				&nbsp;
			</td>
      <td>
				{foreach key=idx item=status_localized from=$gui->lastStatus}
						{$status_localized|escape} <br />
				{/foreach}
      </td>

			<td>{$gui->startTime}</td>
			<td>{$gui->endTime}</td>
			<td>
			  {if $gui->executorSelected == ''}
			    {$labels.any|escape}
			  {else}
				  {$gui->executorSelected|escape}
				{/if}
				&nbsp;
			</td>
			<td>{$gui->search_notes_string}</td>
		</tr>
	</table>
{/if}
{if $gui->display->totals}
	<table class="simple" style="color: blue; width: 100%; text-align:center; margin-left: 0px;" border="0">
		<tr>
			<th>{$labels.th_total_cases}</th>
			<th>{$labels.th_total_pass}</th>
			<th>{$labels.th_total_fail}</th>
			<th>{$labels.th_total_block}</th>
			<th>{$labels.th_total_not_run}</th>
		</tr>
		<tr>
			<td>{$gui->totals.total}</td>
			<td>{$gui->totals.pass}</td>
			<td>{$gui->totals.fail}</td>
			<td>{$gui->totals.blocked}</td>
			<td>{$gui->totals.notRun}</td>
		</tr>
	</table>
{/if}
	{if !$gui->display->suite_summaries}
		<table class="simple" style="color:blue; width: 100%; text-align:center; margin-left: 0px;" border="0">
			<tr>
				<th>{$labels.th_test_case_id}</th>
				<th>{$labels.th_build}</th>
				<th>{$labels.th_tester_id}</th>
				<th>{$labels.th_execution_ts}</th>
				<th>{$labels.th_status}</th>
				<th>{$labels.th_notes}</th>
				<th>{$labels.th_bugs}</th>
			</tr>

	{/if}
	{foreach key=id item=array from=$gui->flatArray}
		{if ($id mod 3) == 0}
			{assign var=depthChange value=$gui->flatArray[$id]}
		{elseif ($id mod 3) == 1}
			{assign var=suiteNameText value=$gui->flatArray[$id]}
		{elseif ($id mod 3) == 2}
			{assign var=currentSuiteId value=$gui->flatArray[$id]}

			<!-- KL - 20061021 - make sure  suite is even in mapOfSuiteSummary -->
			{if ($depthChange == 0) && ($gui->mapOfSuiteSummary[$currentSuiteId])}
			{elseif ($depthChange gt 0) && ($gui->mapOfSuiteSummary[$currentSuiteId])}
				{section name="loopOutDivs" loop="$gui->flatArray" max="$depthChange"}
				{if $gui->display->suite_summaries}
					<div class="workBack">
				{/if}
				{/section}
			{elseif ($depthChange == -1) && ($gui->mapOfSuiteSummary[$currentSuiteId])}
					</div>
			{elseif ($depthChange == -2) && ($gui->mapOfSuiteSummary[$currentSuiteId])}
					</div></div>
			{elseif ($depthChange == -3) && ($gui->mapOfSuiteSummary[$currentSuiteId])}
					</div></div></div>
			{elseif ($depthChange == -4) && ($gui->mapOfSuiteSummary[$currentSuiteId])}
				 </div></div></div></div>
			{elseif ($depthChange == -5) && ($gui->mapOfSuiteSummary[$currentSuiteId])}
				</div></div></div></div></div>
			<!-- handle scenario where suite is not in test plan -->
			{elseif (!$gui->mapOfSuiteSummary[$currentSuiteId])}

			{/if}

			{assign var=previousDepth value=$depth}
			{if $gui->mapOfSuiteSummary[$currentSuiteId]}
			<!-- KL 20061021 - Only display title of category if it has test cases in the test plan -->
			<!-- not a total fix - I need to adjust results.class.php to not pass suite names in
				which are not in the plan -->

			{if $gui->display->suite_summaries}
			<h2>{$suiteNameText}</h2>

			<table class="simple" style="color:blue; width: 100%; text-align:center; margin-left: 0px;" border="0">
				<tr>
					<th>{$labels.th_total_cases}</th>
					<th>{$labels.th_total_pass}</th>
					<th>{$labels.th_total_fail}</th>
					<th>{$labels.th_total_block}</th>
					<th>{$labels.th_total_not_run}</th>
				</tr>
				<tr>
					<td>{$gui->mapOfSuiteSummary[$currentSuiteId].total}</td>
					<td>{$gui->mapOfSuiteSummary[$currentSuiteId].pass}</td>
					<td>{$gui->mapOfSuiteSummary[$currentSuiteId].fail}</td>
					<td>{$gui->mapOfSuiteSummary[$currentSuiteId].blocked}</td>
					<td>{$gui->mapOfSuiteSummary[$currentSuiteId].notRun}</td>
				</tr>
			</table>
			{/if}
			{else}
				<!--
				{$labels.not_yet_executed'}
				-->
				{if $gui->display->suite_summaries}
					</div>
				{/if}
			{/if}
	{foreach key=suiteId item=array from=$gui->suiteList}
				{* probably can be done better. If suiteId in $suiteList matches the current
				suite id - print that suite's information *}
				{if ($suiteId == $currentSuiteId)}
				{* test to make sure there are test cases to diplay before
				   print table and headers *}
				{if $gui->suiteList[$suiteId]}
					{if $gui->display->suite_summaries}
						<table class="simple" style="width: 100%;margin-left: 0px;" border="0">
					{/if}
					{if $gui->display->suite_summaries}
					<tr>
						<th>{$labels.th_test_case_id}</th>
						<th>{$labels.th_build}</th>
						<th>{$labels.th_tester_id}</th>
						<th>{$labels.th_execution_ts}</th>
						<th>{$labels.th_status}</th>
						<th>{$labels.th_notes}</th>
						<th>{$labels.th_bugs}</th>
					</tr>
					{/if}
					{foreach key=executionInstance item=array from=$gui->suiteList[$suiteId]}
						{assign var=inst value=$gui->suiteList[$suiteId][$executionInstance]}
						<tr style="background-color:{cycle values='#eeeeee,#d0d0d0'}">
						{if $displayUnexecutedRows && $inst.status == 'n'}
							<td>{$inst.execute_link}</td>
							<td></td>
							<td></td>
							<td></td>
							<td style="color: grey; font-weight: bold;">{$labels.test_status_not_run}</td>
							<td></td>
							<td></td>
						{elseif $displayPassedRows && $inst.status == 'p' || 
						        $displayFailedRows && $inst.status == 'f' ||
						        $displayBlockedRows && $inst.status == 'b' 
						        }
							<td>{$inst.execute_link}</td>
							<td style="text-align:center;">{$gui->builds_html[$inst.build_id]|escape}</td>
							<td style="text-align:center;">{$gui->users[$inst.tester_id]|escape}</td>
							<td style="text-align:center;">{$inst.execution_ts|strip_tags|escape} </td>
							<td class="{$resultsCfg.code_status[$inst.status]}" style="text-align:center;">{$resultsCfg.code_status[$inst.status]|escape}</td>
							<td>{$inst.notes}&nbsp;</td>
							<td style="text-align:center;">{$inst.bugString}&nbsp;</td>
						{/if}
						</tr>
					{/foreach}
					{if $gui->display->suite_summaries}
						</table>
					{/if}
				{/if}
				{/if}
			{/foreach}
		{/if}
	{/foreach}

		{if !$gui->display->suite_summaries}
			</table>
		{/if}

  {$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
</body>
</html>
