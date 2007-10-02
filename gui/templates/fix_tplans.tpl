{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: fix_tplans.tpl,v 1.2 2007/10/02 16:30:32 asielb Exp $
Purpose: assign test plans to a test project - bug 1021
*}

{include file="inc_head.tpl" jsValidate="yes"}
<body>

{if $count > 0}

<p>Listing <span style="color:red">{$count}</span> Test Plans that are currently not associated with a Test Project</p>

<form method="post" action="lib/project/fix_tplans.php"

<table>
<tr>
	<th>
		Test Plan
	</th>
	<th>
		Associated Test Project
	</th>
</tr>
{foreach from=$testPlans item=testPlan}			
	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
		<td>
			{$testPlan.name}
		</td>
		<td>				
			<select name="{$testPlan.id}" id="{$testPlan.id}">
				<option value="none"></option>	
				{foreach from=$testProjects item=testProject}
					<option value="{$testProject.id}">{$testProject.name}</option>
				{/foreach}		
			</select>
		</td>
	</tr>
{/foreach}
</table>

<input type="submit" value="Change" />
</form>

{else}
	<p>You currently have no Test Plans that are not associated with a Test Project - That's Good!</p>

{/if}
</body>
</html>