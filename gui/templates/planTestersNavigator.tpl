{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planTestersNavigator.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - show users/plan for assignment *}
{include file="inc_head.tpl"}

<body>

<h1>{$title|escape}</h1>

<div class="tree">

{* menu for users or plan assignment *}
<div>
	<form method="get">
		List of
		<select name="type" onchange="this.form.submit(); 
        {* change date="2005-04-16" author="fm" use helphref *}
				parent.workframe.location.href='{$helphref}planTesters.html';">
			<option value="users">{lang_get s='opt_users'}</option>
			<option value="plans" {$selected}>{lang_get s='opt_test_plans'}</option>
		</select>
	</form>
</div>

<p>
	{section name=Row loop=$arrData}
		<a href="lib/plan/planTestersEdit.php?type={$type}&id={$arrData[Row][0]}" 
			target="workframe">{$arrData[Row][1]|escape}</a><br />
	{/section}
</p>
</div>

</body>
</html>