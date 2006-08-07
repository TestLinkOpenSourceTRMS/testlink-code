{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: resultsMoreBuilds_report.tpl,v 1.14 2006/08/07 06:40:12 kevinlevy Exp $
@author Francisco Mancardi - fm - start solving BUGID 97/98
20051022 - scs - removed ' in component id values
20051121 - scs - added escaping of tpname
20051203 - scs - added missing apo in lang_get
*}

	{include file="inc_head.tpl" openHead='yes'} 
		<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
		<script language="JavaScript">
		var bAllShown = false;
		var g_progress = null;
		var g_pCount = 0;
		progress();
		</script>
</head>

	<h2>user selected query parameters :</h2>
	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;" border="2">
		<tr><th>builds</th><th>test suites</th><th>keywords</th><th>owners</th><th>report format</th><th>last result</th></tr> 
		<tr>
			<td>
				{foreach key=buildrow item=array from=$buildsSelected}
					{assign var=buildid value=$buildsSelected[$buildrow]}
					
					<!-- x is hard to describe in this context -->
					{foreach key=x item=array from=$arrBuilds}					

						{if ($arrBuilds[$x].id) == $buildid}
							{$arrBuilds[$x].name} <BR>
						{/if}
					{/foreach}
				{/foreach}
				
			</td>
			
			<td>
				{foreach key=componentrow item=array from=$componentsSelected}
					{assign var=componentid value=$componentsSelected[$componentrow]}
					
					<!-- x is hard to describe in this context -->
					{foreach key=x item=array from=$arrComponents}					

						{if ($arrComponents[$x].id) == $componentid}
							{$arrComponents[$x].name} <BR>
						{/if}
					{/foreach}
				{/foreach}
				
			</td>
			
			<td>
				{foreach key=keywordrow item=array from=$keywordsSelected}
					{assign var=keywordid value=$keywordsSelected[$keywordrow]}
					{$arrKeywords[$keywordid]}	<BR>
				{/foreach}
			</td>
			
			<td>
				owners - n/a
			</td>
			
			<td>
				html only
			</td>
			
			<td>{$lastStatus}</td>
			
		</tr>
	</table>		
	

	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;" border="2">
		<tr><th>total cases</th><th>total pass</th><th>total fail</th><th>total block</th><th>total not run</th></tr> 
		<tr><td>{$totals.total}</td><td>{$totals.pass}</td><td>{$totals.fail}</td><td>{$totals.blocked}</td><td>{$totals.notRun}</td></tr>
	</table>		
	
	<a href="javascript:showOrCollapseAll()">{lang_get s='show_hide_all'}</a>
	<h2 onClick="plusMinus_onClick(this);"><img class="plus" src="icons/plus.gif">show/collapse</h2>
			
	<div class="workBack">
	{foreach key=id item=array from=$flatArray}
		{if ($id mod 3) == 0}
			{assign var=depthChange value=$flatArray[$id]}
		{elseif ($id mod 3) == 1}
			{assign var=suiteNameText value=$flatArray[$id]}
		{elseif ($id mod 3) == 2}
			{assign var=currentSuiteId value=$flatArray[$id]}
			
			{if ($depthChange == 0)}	
			<div class="workBack">
			<h2 onClick="plusMinus_onClick(this);"><img class="plus" src="icons/plus.gif">show/collapse</h2>
				
			{elseif ($depthChange gt 0) }
				{section name="loopOutDivs" loop="$flatArray" max="$depthChange"}
					<div class="workBack">
					<h2 onClick="plusMinus_onClick(this);"><img class="plus" src="icons/plus.gif">show/collapse</h2>
				{/section}
			{elseif ($depthChange == -1) }
					</div>
			{elseif ($depthChange == -2) }
					</div></div>		
			{elseif ($depthChange == -3) }
					</div></div></div>		
			{elseif ($depthChange == -4) }
					</div></div></div></div>
			{elseif ($depthChange == -5) }
					</div></div></div></div></div>
			{/if}
			{assign var=previousDepth value=$depth}
			
			<h2>{$suiteNameText}</h2>
			<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;" border="2">
				<tr><th>total cases</th><th>total pass</th><th>total fail</th><th>total block</th><th>total not run</th></tr> 
				<tr><td>{$mapOfSuiteSummary[$currentSuiteId].total}</td><td>{$mapOfSuiteSummary[$currentSuiteId].pass}</td><td>{$mapOfSuiteSummary[$currentSuiteId].fail}</td><td>{$mapOfSuiteSummary[$currentSuiteId].blocked}</td><td>{$mapOfSuiteSummary[$currentSuiteId].notRun}</td></tr>
			</table>		
				
			{foreach key=suiteId item=array from=$suiteList}
				{* probably can be done better. If suiteId in $suiteList matches the current 
				suite id - print that suite's information *}
				{if ($suiteId == $currentSuiteId)} 
					<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;" border="2">
					<tr><th>test case id</th><th>build id</th><th>tester_id</th><th>execution_ts</th><th>status</th><th>notes</th></tr> 
					{foreach key=executionInstance item=array from=$suiteList[$suiteId]}
						<tr>
							<td>{$suiteList[$suiteId][$executionInstance].testcaseID} </td>
							<td>{$suiteList[$suiteId][$executionInstance].build_id} </td> 
							<td>{$suiteList[$suiteId][$executionInstance].tester_id} </td>
							<td>{$suiteList[$suiteId][$executionInstance].execution_ts} </td>
							<td>{$suiteList[$suiteId][$executionInstance].status} </td>
							<td>{$suiteList[$suiteId][$executionInstance].notes} </td> 
						</tr>
					{/foreach}					
					</table>
				{/if}
			{/foreach}											
		{/if}
	{/foreach}
	</div>

</body>
</html>
