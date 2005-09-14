{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: resultsMoreBuilds_query_form.tpl,v 1.12 2005/09/14 09:27:15 kevinlevy Exp $
@author Francisco Mancardi - fm - start solving BUGID 97/98
*}
{include file="inc_head.tpl"}

<body>
<h1>Test Plan = {$testPlanName}</h1>
<div class="workBack">	
<form action="lib/results/resultsMoreBuilds_buildReport.php" method='get'>
	<INPUT TYPE=HIDDEN NAME=projectid VALUE={$projectid}>
	<INPUT TYPE=HIDDEN NAME=testPlanName VALUE="{$testPlanName}">
	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
		<tr><th>select build(s)</th><th>select component(s)</th></tr>
		<tr>
			<td>
				<select name='build[]' size=10 multiple>
					{foreach key=build item=buildid from=$arrBuilds}
						{* by default select all builds*}
						<option value="{$build}" selected>{$buildid|escape}</option>
					{/foreach}				
				</select>
			</td>
			<td>
        	        	<select name='component[]' size=10 multiple>
					<option value="*" selected>all</option>
					{foreach key=component item=componentid from=$arrComponents}
						<option value="'{$componentid}'">{$componentid|escape}</option>
					{/foreach}			
				</select>	
			</td>
		</tr>
    <tr><th>select keyword </th><th>select owner </th></tr>
		<tr><td>
        	        <select name="keyword" size=5>
			<option value="" selected>DO NOT QUERY BY KEYWORD</option>
                        {section name=Row loop=$arrKeywords}
                        <option value="{$arrKeywords[Row].keyword|escape}">{$arrKeywords[Row].keyword|escape}</option>
                        {/section}
		</td>
			<td>
				<select name='owner' size=5 >
					<option value="" selected>DO NOT QUERY BY OWNER</option>
					{foreach key=owner item=ownerid from=$arrOwners}
						{* by default the owner should be the current user *}
						<option value="{$ownerid|escape}">{$ownerid|escape}</option>
					{/foreach}				
				</select>
			</td>
		</tr>
    <tr></tr>
		<tr>

		</tr>
    <tr><th>select report format</th><th>select last result </th></tr>	
		<tr><td> 
			<select name='format' size=2>
				<option selected>html</option>
				<option>excel</option>
			</select>
		</td>
		<td> 
			<select name='lastStatus' size=5>
				<option selected>Any</option>
				<option>Passed</option>
				<option>Failed</option>
				<option>Blocked</option>
				<option>Not Run</option>
			</select>
		</td></tr>
	<tr>
		<td>
			<INPUT TYPE=submit VALUE='submit query'/>
		</td>
	</tr>
</table>
</form>
</div>

</body>
</html>
