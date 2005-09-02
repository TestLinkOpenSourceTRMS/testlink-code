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
					<option value="0" selected>0</option>
					{foreach key=build item=buildid from=$arrBuilds}
						{* by default have the start build be the first build *}
						<option value="{$build}">{$buildid|escape}</option>
					{/foreach}				
				</select>
			</td>
		</tr>
	</table>

	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
		<tr><th>select keyword </th></tr>
		<tr><td>
        	        <select name="keyword" size=5>
			<option value="" selected></option>
                        {section name=Row loop=$arrKeywords}
                        <option value="{$arrKeywords[Row].keyword|escape}">{$arrKeywords[Row].keyword|escape}</option>
                        {/section}
		</td></tr>
	</table>
		
	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
		<tr><th>select owner </th></tr>
		<tr>
			<td>
				<select name='owner' size=5 >
					<option value="" selected></option>
					{foreach key=owner item=ownerid from=$arrOwners}
						{* by default the owner should be the current user *}
						<option value="{$ownerid|escape}">{$ownerid|escape}</option>
					{/foreach}				
				</select>
			</td>
		</tr>
	</table>

	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
		<tr><th>select last result </th></tr>	
		<tr><td> 
			<select name='lastStatus' size=5>
				<option selected>any</option>
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
