{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsMoreBuilds_report.tpl,v 1.3 2008/05/06 06:26:11 franciscom Exp $

rev :
     20070902 - franciscom - refactoring
*}
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

<h1 class="title"> {lang_get s='query_metrics_report'}</h1>
{include file="inc_result_tproject_tplan.tpl"
         arg_tproject_name=$tproject_name arg_tplan_name=$tplan_name}

{if $show_query_params}
	<h2>{lang_get s="caption_user_selected_query_parameters"}</h2>
	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;" border="2">
		<tr>
			<th>{lang_get s="th_test_plan"}</th>
			<th>{lang_get s="th_builds"}</th>
			<th>{lang_get s="th_test_suites"}</th>
			<th>{lang_get s="th_keyword"}</th>
			<th>{lang_get s="assigned_to"}</th>
			<th>{lang_get s="th_last_result"}</th>
			<th>{lang_get s="th_start_time"}</th>
			<th>{lang_get s="th_end_time"}</th>
			<th>{lang_get s="th_executor"}</th>
			<th>{lang_get s="th_search_notes_string"}</th>
		</tr>
		<tr>
			<td>
				{$tplan_name|escape}
			</td>
			<td>
				{foreach key=buildrow item=array from=$buildsSelected}
					{assign var=buildid value=$buildsSelected[$buildrow]}
					{$mapBuilds[$buildid]|escape} <br />
				{/foreach}
			</td>
			<td>
				{foreach key=x item=array from=$testsuitesSelected}
						{$testsuitesSelected[$x]|escape} <br />
				{/foreach}
			</td>
			<td>
				{foreach key=keywordrow item=array from=$keywordsSelected}
					{assign var=keywordid value=$keywordsSelected[$keywordrow]}
					{$arrKeywords[$keywordid]}	<br />
				{/foreach}
			</td>

			<td>
			  {if $ownerSelected == ''}
			    {lang_get s="any"|escape}
			  {else}
				  {$ownerSelected|escape}
				{/if}
				&nbsp;
			</td>
      <td>
				{foreach key=idx item=status_localized from=$lastStatus}
						{$status_localized|escape} <br />
				{/foreach}
      </td>

			<td>{$startTime}</td>
			<td>{$endTime}</td>
			<td>
			  {if $executorSelected == ''}
			    {lang_get s="any"|escape}
			  {else}
				  {$executorSelected|escape}
				{/if}
				&nbsp;
			</td>
			<td>{$search_notes_string}</td>
		</tr>
	</table>
{/if}
{if $show_totals}
	<table class="simple" style="color: blue; width: 100%; text-align: center; margin-left: 0px;" border="2">
		<tr>
			<th>{lang_get s="th_total_cases"}</th>
			<th>{lang_get s="th_total_pass"}</th>
			<th>{lang_get s="th_total_fail"}</th>
			<th>{lang_get s="th_total_block"}</th>
			<th>{lang_get s="th_total_not_run"}</th>
		</tr>
		<tr>
			<td>{$totals.total}</td>
			<td>{$totals.pass}</td>
			<td>{$totals.fail}</td>
			<td>{$totals.blocked}</td>
			<td>{$totals.notRun}</td>
		</tr>
	</table>
{/if}
	{if !$show_summaries}
		<table class="simple" style="color:blue; width: 100%; text-align: center; margin-left: 0px;" border="2">
			<tr>
				<th>{lang_get s='th_test_case_id'}</th>
				<th>{lang_get s='th_build'}</th>
				<th>{lang_get s='th_tester_id'}</th>
				<th>{lang_get s='th_execution_ts'}</th>
				<th>{lang_get s='th_status'}</th>
				<th>{lang_get s='th_notes'}</th>
				<th>{lang_get s='th_bugs'}</th>
			</tr>

	{/if}
<!-- KL - 20061021 - comment out until I can figure out how to fix
	<a href="javascript:showOrCollapseAll()">{lang_get s='show_hide_all'}</a>

	<h2 onClick="plusMinus_onClick(this);"><img class="minus" src="{$smarty.const.TL_THEME_IMG_DIR}/minus.gif" />{lang_get s="caption_show_collapse"}</h2>
	-->
	<!-- KL - 20061021 - don't think we need this
	<div class="workBack">
-->
	{foreach key=id item=array from=$flatArray}
		{if ($id mod 3) == 0}
			{assign var=depthChange value=$flatArray[$id]}
		{elseif ($id mod 3) == 1}
			{assign var=suiteNameText value=$flatArray[$id]}
		{elseif ($id mod 3) == 2}
			{assign var=currentSuiteId value=$flatArray[$id]}

			<!-- KL - 20061021 - make sure  suite is even in mapOfSuiteSummary -->
			{if ($depthChange == 0) && ($mapOfSuiteSummary[$currentSuiteId])}
<!--				<div class="workBack">
				DIV -->
				<!-- KL - 20061021 - comment out until I can figure out how to fix
				<h2 onClick="plusMinus_onClick(this);"><img class="minus" src="{$smarty.const.TL_THEME_IMG_DIR}/minus.gif" />
				{lang_get s="caption_show_collapse"}</h2>
				-->
			{elseif ($depthChange gt 0) && ($mapOfSuiteSummary[$currentSuiteId])}
				{section name="loopOutDivs" loop="$flatArray" max="$depthChange"}
				{if $show_summaries}
					<div class="workBack">
				{/if}
				<!-- KL - 20061021 - comment out until I can figure out how to fix
				<h2 onClick="plusMinus_onClick(this);">
				<img class="minus" src="{$smarty.const.TL_THEME_IMG_DIR}/minus.gif" />
				{lang_get s="caption_show_collapse"}</h2>
				-->
				{/section}
			{elseif ($depthChange == -1) && ($mapOfSuiteSummary[$currentSuiteId])}
					</div>
			{elseif ($depthChange == -2) && ($mapOfSuiteSummary[$currentSuiteId])}
					</div></div>
			{elseif ($depthChange == -3) && ($mapOfSuiteSummary[$currentSuiteId])}
					</div></div></div>
			{elseif ($depthChange == -4) && ($mapOfSuiteSummary[$currentSuiteId])}
				 </div></div></div></div>
			{elseif ($depthChange == -5) && ($mapOfSuiteSummary[$currentSuiteId])}
				</div></div></div></div></div>
			<!-- handle scenario where suite is not in test plan -->
			{elseif (!$mapOfSuiteSummary[$currentSuiteId])}

			{/if}

			{assign var=previousDepth value=$depth}
			{if $mapOfSuiteSummary[$currentSuiteId]}
			<!-- KL 20061021 - Only display title of category if it has test cases in the test plan -->
			<!-- not a total fix - I need to adjust results.class.php to not pass suite names in
				which are not in the plan -->

			{if $show_summaries}
			<h2>{$suiteNameText}</h2>

			<table class="simple" style="color:blue; width: 100%; text-align: center; margin-left: 0px;" border="2">
				<tr>
					<th>{lang_get s="th_total_cases"}</th>
					<th>{lang_get s="th_total_pass"}</th>
					<th>{lang_get s="th_total_fail"}</th>
					<th>{lang_get s="th_total_block"}</th>
					<th>{lang_get s="th_total_not_run"}</th>
				</tr>
				<tr>
					<td>{$mapOfSuiteSummary[$currentSuiteId].total}</td>
					<td>{$mapOfSuiteSummary[$currentSuiteId].pass}</td>
					<td>{$mapOfSuiteSummary[$currentSuiteId].fail}</td>
					<td>{$mapOfSuiteSummary[$currentSuiteId].blocked}</td>
					<td>{$mapOfSuiteSummary[$currentSuiteId].notRun}</td>
				</tr>
			</table>
			{/if}
			{else}
				<!--
				{lang_get s='not_yet_executed'}
				-->
				{if $show_summaries}
					</div>
				{/if}
			{/if}
	{foreach key=suiteId item=array from=$suiteList}
				{* probably can be done better. If suiteId in $suiteList matches the current
				suite id - print that suite's information *}
				{if ($suiteId == $currentSuiteId)}
				{* test to make sure there are test cases to diplay before
				   print table and headers *}
				{if $suiteList[$suiteId]}
					{if $show_summaries}
						<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;" border="2">
					{/if}
					{if $show_summaries}
					<tr>
						<th>{lang_get s='th_test_case_id'}</th>
						<th>{lang_get s='th_build'}</th>
						<th>{lang_get s='th_tester_id'}</th>
						<th>{lang_get s='th_execution_ts'}</th>
						<th>{lang_get s='th_status'}</th>
						<th>{lang_get s='th_notes'}</th>
						<th>{lang_get s='th_bugs'}</th>
					</tr>
					{/if}
					{foreach key=executionInstance item=array from=$suiteList[$suiteId]}
						{assign var=inst value=$suiteList[$suiteId][$executionInstance]}
						{if $displayUnexecutedRows && $inst.status == 'n'}
						<tr>
						<!--	<td>{$inst.testcaseID}: {$inst.name|escape} </td> -->
							<td>{$inst.execute_link}</td>
							<td></td>
							<td></td>
							<td></td>
							<td style="color: grey; font-weight: bold;">{lang_get s='test_status_not_run'}</td>
							<td></td>
							<td></td>
						</tr>
						{elseif $displayPassedRows && $inst.status == 'p'}
							<tr>
						<!--	<td>{$inst.testcaseID}: {$inst.name|escape} </td> -->
							<td>{$inst.execute_link}</td>
							<td>{$mapBuilds[$inst.build_id]|escape}</td>
							<td>{$mapUsers[$inst.tester_id]|escape}</td>
							<td>{$inst.execution_ts|strip_tags|escape} </td>
							<td style="color: green; font-weight: bold;">{$gsmarty_tc_status_css[$inst.status]|escape}</td>
							<td>{$inst.notes}&nbsp;</td>
							<td>{$inst.bugString}&nbsp;</td>
						</tr>
						{elseif $displayFailedRows && $inst.status == 'f'}
							<tr>
							<!--	<td>{$inst.testcaseID}: {$inst.name|escape} </td> -->
							<td>{$inst.execute_link}</td>
							<td>{$mapBuilds[$inst.build_id]|escape}</td>
							<td>{$mapUsers[$inst.tester_id]|escape}</td>
							<td>{$inst.execution_ts|strip_tags|escape} </td>
							<td style="color: red; font-weight: bold;">{$gsmarty_tc_status_css[$inst.status]|escape}</td>
							<td>{$inst.notes|strip_tags}&nbsp;</td>
							<td>{$inst.bugString}&nbsp;</td>
						</tr>
						{elseif $displayBlockedRows && $inst.status == 'b'}
							<tr>
							<!--
							<td>{$inst.testcaseID}: {$inst.name|escape} </td> -->
							<td>{$inst.execute_link}</td>
							<td>{$mapBuilds[$inst.build_id]|escape}</td>
							<td>{$mapUsers[$inst.tester_id]|escape}</td>
							<td>{$inst.execution_ts|strip_tags|escape} </td>
							<td style="color: blue; font-weight: bold;">{$gsmarty_tc_status_css[$inst.status]|escape}</td>
							<td>{$inst.notes}&nbsp;</td>
							<td>{$inst.bugString}&nbsp;</td>
						</tr>
						{/if}
					{/foreach}
					{if $show_summaries}
						</table>
					{/if}
				{/if}
				{/if}
			{/foreach}
		{/if}
	{/foreach}

		{if !$show_summaries}
			</table>
		{/if}

  {lang_get s="generated_by_TestLink_on"} {$smarty.now|date_format:$gsmarty_timestamp_format}
</body>
</html>
