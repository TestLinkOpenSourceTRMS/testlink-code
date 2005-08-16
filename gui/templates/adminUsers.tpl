{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: adminUsers.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - Edit user data *}
{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_user_mgmt'}</h1>

{***** TABS *****}
<div class="tabMenu">
	<span class="unselected"><a href="lib/admin/adminUserNew.php">{lang_get s='menu_new_user'}</a></span> 
	<span class="selected">{lang_get s='menu_mod_user'}</span>
	<span class="unselected"><a href="lib/admin/adminUsersDelete.php">{lang_get s='menu_del_user'}</a></span>
</div>

{if $updated eq "yes"}
<div class="workBack">
	<p class="bold">{lang_get s='msg_users_upd'}</p>

	<table class="simple" width="50%">
		<tr>
			<th>{lang_get s='th_login'}</th>
			<th>{lang_get s='th_result'}</th>
		</tr>
		{section name=myResults loop=$arrResults}
		<tr>
			<td>{$arrResults[myResults].login|escape}</td>
			<td>{$arrResults[myResults].action|escape}</td>
		</tr>
		{/section}
	</table>
</div>
{/if}

{***** existing users form *****}
<div class="workBack">
	<form method="post" action="lib/admin/adminUsers.php">
	<table class="common" width="95%">
		<tr>
			<th>#</th>
			<th>{lang_get s='th_login'}</th>
			<th>{lang_get s='th_first_name'}</th>
			<th>{lang_get s='th_last_name'}</th>
			<th>{lang_get s='th_email'}</th>
			<th>{lang_get s='th_role'}</th>
			<th>{lang_get s='th_locale'}</th>	
		</tr>
		{section name=row loop=$users start=0}
		<tr>
			<td>{$smarty.section.row.index}
				<input type="hidden" name="id[]" value="{$users[row].id}" />
			</td>
			<td><input type="text" size="10" name="login[]" value="{$users[row].login|escape}" maxlength="30" /></td>
			<td><input type="text" size="10" name="first[]" value="{$users[row].first|escape}" maxlength="30" /></td>
			<td><input type="text" size="10" name="last[]" value="{$users[row].last|escape}" maxlength="30" /></td>
			<td><input type="text" size="30" name="email[]" value="{$users[row].email|escape}" maxlength="100" /></td>
			<td>
				<select name="rights[]">
				{html_options options=$optRights selected=$users[row].rightsid}
				</select>
			</td>
      		<td>
				<select name="locale[]">
				{html_options options=$optLocale selected=$users[row].locale}
				</select>
			</td>
		</tr>
		{/section}
	</table>
	<p><input type="submit" name="editUser" value="{lang_get s='btn_upd_users'}" /></p>
	</form>
</div>

</body>
</html>