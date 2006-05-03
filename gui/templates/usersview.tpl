{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: usersview.tpl,v 1.4 2006/05/03 06:48:02 franciscom Exp $ *}
{* 
Purpose: smarty template - users overview

 20051115 - fm - new model
 20051231 - scs - cleanup due to removing bulk update of users
*}
{include file="inc_head.tpl"}

<body>

{literal}
<script type="text/javascript">
{/literal}
var warning_delete_user = "{lang_get s='warning_delete_user'}";
{literal}
</script>
{/literal}

<h1>{lang_get s='title_user_mgmt'}</h1>

{***** TABS *****}
<div class="tabMenu">
	<span class="unselected"><a href="lib/usermanagement/usersedit.php">{lang_get s='menu_new_user'}</a></span> 
	<span class="selected">{lang_get s='menu_mod_user'}</span>
	<br /><hr />
	<span class="unselected"><a href="lib/usermanagement/rolesedit.php">{lang_get s='menu_define_roles'}</a></span> 
	<span class="unselected"><a href="lib/usermanagement/rolesview.php">{lang_get s='menu_view_roles'}</a></span> 
	<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testproject">{lang_get s='menu_assign_product_roles'}</a></span> 
	<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testplan">{lang_get s='menu_assign_testplan_roles'}</a></span>
</div>

{include file="inc_update.tpl" result=$result item="user" action="$action"}

{***** existing users form *****}
<div class="workBack">
	<form method="post" action="lib/usermanagement/usersview.php">
	<table class="common" width="95%">
		<tr>
			<th>{lang_get s='th_login'}</th>
			<th>{lang_get s='th_first_name'}</th>
			<th>{lang_get s='th_last_name'}</th>
			<th>{lang_get s='th_email'}</th>
			<th>{lang_get s='th_role'}</th>
			<th>{lang_get s='th_locale'}</th>	
			<th>{lang_get s='th_active'}</th>	
			<th>{lang_get s='th_delete'}</th>
		</tr>
		
		{section name=row loop=$users start=0}
		<tr>
			<td><a href="lib/usermanagement/usersedit.php?user_id={$users[row].id}"> 
			    {$users[row].login|escape}</a></td>
			<td>{$users[row].first|escape}</td>
			<td>{$users[row].last|escape}</td>
			<td>{$users[row].email|escape}</td>
			<td>
				{assign var="roleID" value="$users[row].role_id"}
				{$optRoles[$roleID]|escape}
			</td>
			<td>
				{assign var="lc" value="$users[row]"}
				{$optLocale[$lc.locale]|escape}
			</td>
			<td>
				{if $users[row].active eq 1}
				{lang_get s='Yes'}
				{else}
				{lang_get s='No'}
				{/if}
			</td>
			<td>
				<a href="javascript:deleteUser_onClick({$users[row].id})">
				<img style="border:none" alt="{lang_get s='alt_delete_user'}"	 src="icons/thrash.png"/>
				</a>
			</td>
		</tr>
		{/section}
	</table>
	</form>
</div>

{*  BUGID 0000103: Localization is changed but not strings *}
{if $update_title_bar == 1}
{literal}
<script type="text/javascript">
	parent.titlebar.location.reload();
</script>
{/literal}
{/if}
{if $reload == 1}
{literal}
<script type="text/javascript">
	top.location.reload();
</script>
{/literal}
{/if}

</body>
</html>