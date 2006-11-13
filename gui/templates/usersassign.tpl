{* 
Testlink: smarty template - 
$Id: usersassign.tpl,v 1.6 2006/11/13 23:17:39 havlat Exp $ 
*}
{include file="inc_head.tpl" jsValidate="yes"}

<body>

<h1>{lang_get s='title_user_mgmt'} - {lang_get s='title_assign_roles'}</h1>

{* tabs *}
<div class="tabMenu">
{if $mgt_users == "yes"}
	<span class="unselected"><a href="lib/usermanagement/usersedit.php">{lang_get s='menu_new_user'}</a></span> 
	<span class="unselected"><a href="lib/usermanagement/usersview.php">{lang_get s='menu_mod_user'}</a></span>
{/if}
{if $role_management == "yes"}
	<span class="unselected"><a href="lib/usermanagement/rolesedit.php">{lang_get s='menu_define_roles'}</a></span> 
{/if}
	<span class="unselected"><a href="lib/usermanagement/rolesview.php">{lang_get s='menu_view_roles'}</a></span>
	{if $feature == 'testproject'}
		{if $tp_user_role_assignment == "yes"}
			<span class="selected">{lang_get s='menu_assign_testproject_roles'}</span> 
		{/if}
		{if $tproject_user_role_assignment == "yes"}
			<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testplan">{lang_get s='menu_assign_testplan_roles'}</a></span>
		{/if}
	{else}
		{if $tp_user_role_assignment == "yes"}
			<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testproject">{lang_get s='menu_assign_testproject_roles'}</a></span>
		{/if}
		{if $tproject_user_role_assignment == "yes"}
			<span class="selected">{lang_get s='menu_assign_testplan_roles'}</span> 
		{/if}
	{/if}
</div>

{include file="inc_update.tpl" result=$result item="$feature" action="$action"}

<div class="workBack">

<form method="post" action="lib/usermanagement/usersassign.php">
	<input type="hidden" name="featureID" value="{$featureID}" />
	<input type="hidden" name="feature" value="{$feature}" />

	<p>
	{if $feature == 'testproject'}
		{lang_get s='caption_assign_testproject_user_roles'} - {lang_get s='TestProject'}
		<select id="featureSel">
		{foreach from=$features key=id item=f}
		<option value="{$id}" 
		{if $featureID == $id}
			selected="selected" 
		{/if}
		>{$f|escape}</option>
		{/foreach}
		</select>
	{else}
		{lang_get s='caption_assign_testplan_user_roles'} - {lang_get s='TestPlan'}
		<select id="featureSel">
		{foreach from=$features item=f}
		<option value="{$f.id}" 
		{if $featureID == $f.id}
			selected="selected" 
		{/if}
		>{$f.name|escape}</option>
		{/foreach}
		</select>
	{/if}
	<input type="button" value="{lang_get s='btn_change'}" onclick="changeFeature('{$feature}');"/>
	
	</p>

	<table class="common" width="75%">
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