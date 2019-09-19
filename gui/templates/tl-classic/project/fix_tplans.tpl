{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: fix_tplans.tpl,v 1.2 2010/07/13 07:36:46 asimon83 Exp $
Purpose: assign test plans to a test project - bug 1021

rev:
  20100713 - asimon - added missing ">" on opening form tag
*}

{include file="inc_head.tpl" jsValidate="yes"}
<body>

{if $count > 0}

 
<p>{lang_get s='list_inactive_tplans1'} <span style="color:red">{$count}</span> {lang_get s='list_inactive_tplans2'}</p>

<form method="post" action="lib/project/fix_tplans.php">

<table>
<tr>
	<th>
		{lang_get s='test_plan'}
	</th>
	<th>
		{lang_get s='assoc_test_project'}
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
	<p>{lang_get s='no_tplans_to_fix'}</p>

{/if}
</body>
</html>