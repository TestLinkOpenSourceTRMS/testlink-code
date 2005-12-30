{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: adminUsers_m1.tpl,v 1.1 2005/12/30 16:03:20 franciscom Exp $ *}
{* 
Purpose: smarty template - Edit user data 

@author Francisco Mancardi - 20051115 - new model
*}
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
			<th>{lang_get s='th_login'}</th>
			<th>{lang_get s='th_first_name'}</th>
			<th>{lang_get s='th_last_name'}</th>
			<th>{lang_get s='th_email'}</th>
			<th>{lang_get s='th_role'}</th>
			<th>{lang_get s='th_locale'}</th>	
			<th>{lang_get s='th_active'}</th>	
			
		</tr>
		
		{section name=row loop=$users start=0}
		<tr>
			<input type="hidden" name="id[]" value="{$users[row].id}" />
			<td><a href="lib/admin/adminUserEdit.php?user_id={$users[row].id}&user_login={$users[row].login}&rigths_id={$users[row].rightsid}"> 
			    {$users[row].login|escape}</a></td>
			<td>{$users[row].first|escape}</td>
			<td>{$users[row].last|escape}</td>
			<td>{$users[row].email|escape}</td>
			<td>
				<select name="rights[]" disabled>
				{html_options options=$optRights selected=$users[row].rightsid}
				</select>
			</td>
      <td>
				<select name="locale[]" disabled >
				{html_options options=$optLocale selected=$users[row].locale}
				</select>
			</td>
			
			<td>
				<input type="checkbox" 
				       name="user_is_active" {if $users[row].active eq 1} checked {/if}
				       disabled />
			</td>
			
		</tr>
		{/section}
	</table>
	</form>
</div>

</body>
</html>