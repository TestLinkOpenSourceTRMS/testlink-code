{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planTestersNavigator.tpl,v 1.1 2007/12/02 17:03:00 franciscom Exp $ 
Purpose: smarty template - show users/plan for assignment 
*}
{include file="inc_head.tpl"}

<body>

<h1>{$title|escape}</h1>

<div class="tree">

{* menu for users or plan assignment *}
<div>
	<form method="get">
		{lang_get s='label_list_of'}
		<select name="type" onchange="this.form.submit(); 
				parent.workframe.location.href='{$helphref}planTesters.html';">
			<option value="users">{lang_get s='opt_users'}</option>
			<option value="plans" {$selected}>{lang_get s='opt_test_plans'}</option>
		</select>
	</form>
</div>

<p>
	{section name=Row loop=$arrData}
		<a href="lib/plan/planTestersEdit.php?type={$type}&id={$arrData[Row][0]}" 
			target="workframe">
			{* Changes to show the full name & ID of Users*}
			{if $type eq "plans"}
				{$arrData[Row][1]|escape}
			{else}
				{$arrData[Row].fullname|escape}
			{/if}
		</a><br />
	{/section}
</p>
</div>

</body>
</html>