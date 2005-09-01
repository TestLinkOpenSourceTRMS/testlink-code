{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{include file="inc_head.tpl"}

<body>
<h1>Test Plan = {$testPlanName}</h1>
<div class="workBack">	
<form action="lib/results/resultsMoreBuilds_buildReport.php" method='get'>

	<INPUT TYPE=HIDDEN NAME=projectid VALUE={$projectid}>
	<INPUT TYPE=HIDDEN NAME=testPlanName VALUE="{$testPlanName}">
	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
		<tr><th>select builds </th></tr>
		<tr>
			<td>
				<select name='build[]' size=10 multiple>
					{foreach key=build item=buildid from=$arrBuilds}
						{* by default have the start build be the first build *}
						<option value="{$build}">{$buildid|escape}</option>
					{/foreach}				
				</select>
			</td>
		</tr>
	</table>

	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
		<tr><td>closest match will be used for these query parameters</td></tr>
		<tr><td>keyword : </td><td><INPUT TYPE=textinput NAME=keyword /></td></tr>
		<tr><td>owner : </td><td><INPUT TYPE=textinput NAME=owner /></td></tr>
		<tr><td>View Only Test Cases With Last Status of: </td></tr>	
		<tr><td> 
			<select name='lastStatus' size=5>
				<option selected=true>any</option>
				<option>passed</option>
				<option>failed</option>
				<option>blocked</option>
				<option>unexecuted</option>
			</select>
		</td></tr>
	</table>

	<INPUT TYPE=submit VALUE='submit query'/>
</form>
</div>

</body>
</html>
