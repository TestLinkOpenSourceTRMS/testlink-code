{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: resultsMoreBuilds_report.tpl,v 1.28 2007/01/23 18:26:41 franciscom Exp $
@author Francisco Mancardi - fm - start solving BUGID 97/98
20051022 - scs - removed ' in component id values
20051121 - scs - added escaping of tpname
20051203 - scs - added missing apo in lang_get
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
	<h2>{lang_get s="caption_user_selected_query_parameters"} :</h2>
	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;" border="2">
		<tr>
			<th>{lang_get s="th_test_plan"}</th>
			<th>{lang_get s="th_builds"}</th>
			<th>{lang_get s="th_test_suites"}</th> 
			<th>{lang_get s="th_keyword"}</th>
			<th>{lang_get s="th_owner"}</th>
			<th>{lang_get s="th_report_format"}</th>
			<th>{lang_get s="th_last_result"}</th>
		</tr> 
		<tr>
			<td>
				{$testPlanName|escape}
			</td>
			<td>
				{foreach key=buildrow item=array from=$buildsSelected}
					{assign var=buildid value=$buildsSelected[$buildrow]}
					{$mapBuilds[$buildid]|escape} <br />
				{/foreach}
			</td>
			<td>
				{foreach key=x item=array from=$componentsSelected}
						{$componentsSelected[$x]|escape} <br />
				{/foreach}
			</td> 
			<td>
				{foreach key=keywordrow item=array from=$keywordsSelected}
					{assign var=keywordid value=$keywordsSelected[$keywordrow]}
					{$arrKeywords[$keywordid]}	<br />
				{/foreach}
			</td>
			
			<td>
				{$ownerSelected}&nbsp;
			</td>
			
			<td>
				html only
			</td>
			
			<td>{$lastStatus|escape}</td>
		</tr>
	</table>		
	
	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;" border="2">
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
				<div class="workBack">

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
			<h2>{$suiteNameText}</h2>			

			<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;" border="2">
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
			{else}
				<!-- 
				{lang_get s='not_yet_executed'}
				-->
				</div>
			{/if}	
			{foreach key=suiteId item=array from=$suiteList}
				{* probably can be done better. If suiteId in $suiteList matches the current 
				suite id - print that suite's information *}
				{if ($suiteId == $currentSuiteId)}
				{* test to make sure there are test cases to diplay before
				   print table and headers *}
				{if $suiteList[$suiteId]}
					<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;" border="2">
					<tr>
						<th>{lang_get s='th_test_case_id'}</th>
						<th>{lang_get s='th_build'}</th>
						<th>{lang_get s='th_tester_id'}</th>
						<th>{lang_get s='th_execution_ts'}</th>
						<th>{lang_get s='th_status'}</th>
						<th>{lang_get s='th_notes'}</th>
						<th>{lang_get s='th_bugs'}</th>
					</tr> 
					{foreach key=executionInstance item=array from=$suiteList[$suiteId]}
						{assign var=inst value=$suiteList[$suiteId][$executionInstance]}
						<tr>
							<td>{$inst.testcaseID}: {$inst.name|escape} </td>
							<td>{$mapBuilds[$inst.build_id]|escape}</td>

 
							<td>{$mapUsers[$inst.tester_id]|escape}</td>

							<td>{$inst.execution_ts|escape} </td>
							<td>{$gsmarty_tc_status_css[$inst.status]|escape}</td>
							<td>{$inst.notes|escape}&nbsp;</td> 
							<td>{$inst.bugString}&nbsp;</td> 
						</tr>
					
					{/foreach}					
					</table>
				{/if}
				{/if}

			{/foreach}											
		{/if}
	{/foreach}
</body>
</html>
