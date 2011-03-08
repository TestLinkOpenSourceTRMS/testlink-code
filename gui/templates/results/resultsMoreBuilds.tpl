{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsMoreBuilds.tpl,v 1.3 2010/10/17 09:46:37 franciscom Exp $

rev :
     20100917 - franciscom - BUGID 3789
     20100218 - amitkhullar - BUGID 2541
     20100215 - eloff - BUGID 3160 - fixed missing platform column
     20090409 - amitkhullar - BUGID 2156 - added new option on 	Query Metrics for latest results
     20080524 - franciscom - BUGID 1430
*}
{lang_get var="labels"
          s="query_metrics_report,th_test_plan,th_builds,th_test_suites,th_keyword,
             assigned_to,th_last_result,th_start_time,th_end_time,th_executor,results_all,
             th_total_cases,th_total_pass,th_total_fail,th_total_block,th_total_not_run,
             generated_by_TestLink_on,test_status_not_run,display_results_tc,results_latest,
             th_test_case_id,th_build,th_tester_id,th_execution_ts,th_status,th_notes,th_bugs,
             th_test_case,th_platform,
             th_search_notes_string,any,caption_user_selected_query_parameters"}

{include file="inc_head.tpl" openHead='yes' enableTableSorting="yes"}
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
var bAllShown = false;
var g_progress = null;
var g_pCount = 0;
progress();
</script>
</head>
<body>
{assign var="depth" value="0"}
<h1 class="title">{$labels.query_metrics_report}</h1>
{include file="inc_result_tproject_tplan.tpl"
         arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}

{if $gui->display->query_params}
	<h2>{$labels.caption_user_selected_query_parameters}</h2>
	<table class="simple" style="text-align:center; margin-left: 0px;" border="0">
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
			<th>{$labels.display_results_tc}</th>
		</tr>
		<tr>
			<td>
				{$gui->tplan_name|escape}
			</td>
			<td>
				{foreach item=build_id from=$gui->buildsSelected}
				    {$gui->builds_html[$build_id]|escape} <br />
				{/foreach}
			</td>
			<td>
				{foreach item=tsuite_name from=$gui->testsuitesSelected}
						{$tsuite_name|escape} <br />
				{/foreach}
			</td>
			<td>
				{$gui->keywordSelected|escape}	<br />
			</td>

			<td>
			  {$gui->ownerSelected|escape}
				&nbsp;
			</td>
      <td>
				{foreach key=idx item=status_localized from=$gui->lastStatus}
						{$status_localized|escape} <br />
				{/foreach}
      </td>

			<td>{$gui->startTime|escape}</td>
			<td>{$gui->endTime|escape}</td>
			<td>
			  {$gui->executorSelected|escape}
				&nbsp;
			</td>
			<td>{$gui->search_notes_string|escape}</td>
			{if ($gui->display->latest_results == 0)}
			<td>{$labels.results_latest}</td>
			{else}
			<td>{$labels.results_all}</td>
			{/if}
		</tr>
	</table>
{/if}


{if $gui->display->totals}
	<table class="simple" style="color: blue; text-align:center; margin-left: 0px;" border="0">
		<tr>
		  {foreach item=l18n from=$gui->totals->labels}
			<th>{$l18n}</th>
      {/foreach}
		</tr>
		<tr>
		  {foreach item=figure from=$gui->totals->items}
			<td>{$figure}</td>
      {/foreach}
		</tr>
	</table>
{/if}

	{if !$gui->display->suite_summaries}
		<table class="simple sortable" style="color:blue; margin-left: 0px;" border="0">
			<tr>
				<th>{$tlImages.sort_hint}{$labels.th_test_case}</th>
				{if $gui->showPlatforms}
					<th>{$tlImages.sort_hint}{$labels.th_platform}</th>
				{/if}
				<th>{$tlImages.sort_hint}{$labels.th_build}</th>
				<th>{$tlImages.sort_hint}{$labels.th_tester_id}</th>
				<th>{$tlImages.sort_hint}{$labels.th_execution_ts}</th>
				<th>{$tlImages.sort_hint}{$labels.th_status}</th>
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
			    {* KL 20061021 - Only display title of category if it has test cases in the test plan *}
			    {* not a total fix - I need to adjust results.class.php to not pass suite names in
				       which are not in the plan *}

			{if $gui->display->suite_summaries}
			<h2>{$suiteNameText}</h2>
			<table class="simple" style="color:blue; text-align:center; margin-left: 0px;" border="0">
				<tr>
				  {foreach  key=status item=figure  from=$gui->mapOfSuiteSummary[$currentSuiteId]}
              {if $status == 'total'} 
                  <th>{$labels.th_total_cases}</th>
              {else}
                  <th>{lang_get s=$gui->resultsCfg.status_label[$status]}</th>
              {/if}
          {/foreach}
				</tr>
				<tr>
				  {foreach  key=status item=figure  from=$gui->mapOfSuiteSummary[$currentSuiteId]}
					    <td>{$figure}</td>
          {/foreach}
          {* 
					<td>{$gui->mapOfSuiteSummary[$currentSuiteId].total}</td>
					<td>{$gui->mapOfSuiteSummary[$currentSuiteId].pass}</td>
					<td>{$gui->mapOfSuiteSummary[$currentSuiteId].fail}</td>
					<td>{$gui->mapOfSuiteSummary[$currentSuiteId].blocked}</td>
					<td>{$gui->mapOfSuiteSummary[$currentSuiteId].notRun}</td>
					*}
				</tr>
			</table>
			{/if}
			{else}
				{if $gui->display->suite_summaries}
					</div>
				{/if}
			{/if}
      
      {if $gui->display->test_cases}
			
	        {foreach key=suiteId item=array from=$gui->suiteList}
			    	{* probably can be done better. If suiteId in $suiteList matches the current
			    	suite id - print that suite's information *}
			    	{if ($suiteId == $currentSuiteId)}
			    	{* test to make sure there are test cases to diplay before  print table and headers *}
			    	{if $gui->suiteList[$suiteId]}
			    		{if $gui->display->suite_summaries}
			    			<table class="simple sortable" style="margin-left: 0px;" border="0">
			    		{/if}
			    
			    		{if $gui->display->suite_summaries}
			    		<tr>
			    			<th>{$tlImages.sort_hint}{$labels.th_test_case}</th>
			    			{if $gui->showPlatforms}
			    			  <th>{$tlImages.sort_hint}{$labels.th_platform}</th>
			    			{/if}
			    			<th>{$tlImages.sort_hint}{$labels.th_build}</th>
			    			<th>{$tlImages.sort_hint}{$labels.th_tester_id}</th>
			    			<th>{$labels.th_execution_ts}</th>
			    			<th>{$tlImages.sort_hint}{$labels.th_status}</th>
			    			<th>{$labels.th_notes}</th>
			    			<th>{$labels.th_bugs}</th>
			    		</tr>
			    		{/if}
			    		{foreach key=executionInstance item=array from=$gui->suiteList[$suiteId]}
			    			{assign var=inst value=$gui->suiteList[$suiteId][$executionInstance]}

			    			{if $gui->displayResults[$inst.status]}
			    			<tr style="background-color:{cycle values='#eeeeee,#d0d0d0'}">
								  {if $inst.status == $gui->resultsCfg.status_code.not_run}
								  	<td>{$inst.testcasePrefix|escape}{$inst.external_id}:&nbsp;{$inst.name|escape}</td>
								  	{if $gui->showPlatforms}
								  	  <td>{$gui->platformSet[$inst.platform_id]|escape}</td>
								  	{/if}
								  	<td>&nbsp;</td>
								  	<td>&nbsp;</td>
								  	<td>&nbsp;</td>
								  {else}
								  	<td>{$inst.execute_link}</td>
									{if $gui->showPlatforms}
										<td>{$gui->platformSet[$inst.platform_id]|escape}</td>
									{/if}
								  	<td style="text-align:center;">{$gui->builds_html[$inst.build_id]|escape}</td>
								  	<td style="text-align:center;">{$gui->users[$inst.tester_id]|escape}</td>
								  	<td style="text-align:center;">{$inst.execution_ts|strip_tags|escape} </td>
								  {/if}
									
									<td class="{$gui->resultsCfg.code_status[$inst.status]}" style="text-align:center;">{$gui->statusLabels[$inst.status]|escape}</td>
								  
								  {if $inst.status == $gui->resultsCfg.status_code.not_run}
								  	<td>&nbsp;</td>
								  	<td>&nbsp;</td>
								  {else}
								  	<td>{$inst.notes}&nbsp;</td>
								  	<td style="text-align:center;">{$inst.bugString}&nbsp;</td>
								  {/if}
							</tr>
			    			{/if}

			    		{/foreach}
			    		{if $gui->display->suite_summaries}
			    			</table>
			    		{/if}
			    	{/if}
			    	{/if}
			    {/foreach}
			 {/if}   
		{/if}
	{/foreach}

		{if !$gui->display->suite_summaries}
			</table>
		{/if}

  {$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
</body>
</html>
