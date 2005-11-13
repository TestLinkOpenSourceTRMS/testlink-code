{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planTestersNavigator.tpl,v 1.3 2005/11/13 19:19:31 schlundus Exp $ *}
{* Purpose: smarty template - show users/plan for assignment *}
{include file="inc_head.tpl"}

<body>

<h1>{$title|escape}</h1>

<div class="tree">

{* menu for users or plan assignment *}
<div>
	<form method="get">
		{lang_get s='label_list_of'}
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