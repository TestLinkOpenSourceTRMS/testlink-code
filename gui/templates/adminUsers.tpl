{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: adminUsers.tpl,v 1.3 2005/12/31 14:38:10 schlundus Exp $ *}
{* 
Purpose: smarty template - Edit user data 

 20051115 - fm - new model
 20051231 - scs - cleanup due to removing bulk update of users
*}
{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_user_mgmt'}</h1>

{***** TABS *****}
<div class="tabMenu">
	<span class="unselected"><a href="lib/admin/adminUserNew.php">{lang_get s='menu_new_user'}</a></span> 
	<span class="selected">{lang_get s='menu_mod_user'}</span>
</div>

{include file="inc_update.tpl" result=$result item="user" action="$action"}

{***** existing users form *****}
<div class="workBack">
	<form method="post" action="lib/admin/adminUsers.php">
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
			<td><a href="lib/admin/adminUserEdit.php?user_id={$users[row].id}"> 
			    {$users[row].login|escape}</a></td>
			<td>{$users[row].first|escape}</td>
			<td>{$users[row].last|escape}</td>
			<td>{$users[row].email|escape}</td>
			<td>
				{assign var="rightID" value="$users[row].rightsid}
				{$optRights[$rightID]|escape}
			</td>
			<td>
				{assign var="local" value="$users[row].locale"}
				{$optLocale[$locale]|escape}
			</td>
			<td>
				{if $users[row].active eq 1}
				{lang_get s='Yes'}
				{else}
				{lang_get s='No'}
				{/if}
			</td>
			<td>
				<a href="lib/admin/adminUsers.php?delete=1&user={$users[row].id}">
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