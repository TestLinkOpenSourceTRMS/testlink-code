{* 
Testlink: smarty template - 
$Id: usersassign.tpl,v 1.2 2006/02/22 20:26:38 schlundus Exp $ 
*}
{* 
*}
{include file="inc_head.tpl" jsValidate="yes"}

<body>

<h1>
	{lang_get s='title_assign_roles'}
</h1>

{* tabs *}
<div class="tabMenu">
	<span class="unselected"><a href="lib/usermanagement/usersedit.php">{lang_get s='menu_new_user'}</a></span> 
	<span class="unselected"><a href="lib/usermanagement/usersview.php">{lang_get s='menu_mod_user'}</a></span>
	<br /><hr />
	<span class="unselected"><a href="lib/usermanagement/rolesedit.php">{lang_get s='menu_define_roles'}</a></span> 
	<span class="unselected"><a href="lib/usermanagement/rolesview.php">{lang_get s='menu_view_roles'}</a></span>
	{if $feature == 'product'}
		<span class="selected">{lang_get s='menu_assign_product_roles'}</span> 
		<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testplan">{lang_get s='menu_assign_testplan_roles'}</a></span>
	{else}
		<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=product">{lang_get s='menu_assign_product_roles'}</a></span>
		<span class="selected">{lang_get s='menu_assign_testplan_roles'}</span> 
	{/if}
</div>

{include file="inc_update.tpl" result=$result item="$feature" action="$action"}

<div class="workBack">

<form method="post" action="lib/usermanagement/usersassign.php">
	<input type="hidden" name="featureID" value="{$featureID}" />
	<input type="hidden" name="feature" value="{$feature}" />
	<table class="common" width="75%">
	<caption>
	{if $feature == 'product'}
		{lang_get s='caption_assign_product_user_roles'} - {$productName|escape}
	{else}
		{lang_get s='caption_assign_testplan_user_roles'} - {lang_get s='TestPlan'}
		<select id="testPlanSel">
		{foreach from=$testPlans item=testPlan}
		<option value="{$testPlan.id}" 
		{if $featureID == $testPlan.id}
			selected="selected" 
		{/if}
		>{$testPlan.name|escape}</option>
		{/foreach}
		</select>
		<input type="button" value="{lang_get s='btn_change'}" onclick="changeTestPlan('{$feature}');"/>
	{/if}
	
	</caption>
	<tr>
		<th>{lang_get s='User'}</th>
		<th>{lang_get s='Role'}</th>
	</tr>
	{foreach from=$userData item=user}
	<tr>
		<td>{$user.fullname|escape}</td>
		<td>
			{assign var=uID value=$user.id}
			<select name="userRole{$uID}"> 
			{if $userFeatureRoles[$uID].role_id neq null}
				{html_options options=$optRights selected=$userFeatureRoles[$uID].role_id}
			{else}
				{html_options options=$optRights selected=0}
			{/if}
				{$userFeatureRoles[pID]}
			</select>
		</td>
	</tr>
	{/foreach}
	</table>
	<div class="groupBtn">	
		<input type="submit" name="do_update" value="{lang_get s='btn_upd_user_data'}" />
	</div>
</form>
<hr />

</div>

</body>
</html>