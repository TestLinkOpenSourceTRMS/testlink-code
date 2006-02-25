{* 
Testlink: smarty template - 
$Id: usersedit.tpl,v 1.2 2006/02/25 21:48:24 schlundus Exp $ 
*}
{* 
20050913 - fm - BUGID 0000103: Localization is changed but not strings

20050815 - changed action to updated 

*}
{include file="inc_head.tpl" jsValidate="yes"}

<body>

<h1> {lang_get s='title_account_settings'} </h1>

{***** TABS *****}
<div class="tabMenu">
	<span class="selected">{lang_get s='menu_new_user'}</span> 
	<span class="unselected"><a href="lib/usermanagement/usersview.php">{lang_get s='menu_mod_user'}</a></span>
	<br /><hr />
	<span class="unselected"><a href="lib/usermanagement/rolesedit.php">{lang_get s='menu_define_roles'}</a></span> 
	<span class="unselected"><a href="lib/usermanagement/rolesview.php">{lang_get s='menu_view_roles'}</a></span> 
	<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testproject">{lang_get s='menu_assign_product_roles'}</a></span> 
	<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testplan">{lang_get s='menu_assign_testplan_roles'}</a></span>
</div>

{include file="inc_update.tpl" result=$result item="user" action="$action"}

<div class="workBack">

{literal}
<script type="text/javascript">
{/literal}
var warning_empty_pwd = "{lang_get s='warning_empty_pwd'}";
var warning_different_pwd = "{lang_get s='warning_different_pwd'}";
var warning_enter_less1 = "{lang_get s='warning_enter_less1'}";
var warning_enter_at_least1 = "{lang_get s='warning_enter_at_least1'}";
var warning_enter_at_least2 = "{lang_get s='warning_enter_at_least2'}";
var warning_enter_less2 = "{lang_get s='warning_enter_less2'}";
{literal}
function valAllText(form)
{
	if (valTextLength(form.first,30,1) && valTextLength(form.last,30,1))
	{
		return true;
	}
	return false;
}
</script>
{/literal}

<form method="post" action="lib/usermanagement/usersedit.php" onsubmit="return valAllText(this)">
	<input type="hidden" name="user_id" value="{$userData.id}" />
	<input type="hidden" name="user_login" value="{$userData.login}" />
	<table class="common">
		<caption>{lang_get s='caption_user_details'}</caption>
		<tr>
			<th>{lang_get s='th_login'}</th>
			<td><input type="text" name="login" maxlength="30" 
			{if $userData neq null}
				disabled="disabled"
			{/if}
			 value="{$userData.login|escape}" /></td>
		</tr>
		<tr>
			<th>{lang_get s='th_first_name'}</th>
			<td><input type="text" name="first" value="{$userData.first|escape}" maxlength="30" /></td></tr>
		<tr>
			<th>{lang_get s='th_last_name'}</th>
			<td><input type="text" name="last" value="{$userData.last|escape}" maxlength="30" /></td>
		</tr>
		{if $userData eq null}
		<tr>
			<th>{lang_get s='th_password'}:</th>
			<td><input type="password" name="password" maxlength="32" /></td>
		</tr>
		{/if}
		<tr>
			<th>{lang_get s='th_email'}</th>
			<td><input type="text" name="email" value="{$userData.email|escape}" size="50" maxlength="100" /></td>
		</tr>
		<tr>
			<th>{lang_get s='th_role'}:</th>
			<td>
				<select name="rights_id"> 
				{html_options options=$optRights selected=$userData.role_id}
				</select>
			</td>
		</tr>

		<tr>
			<th>{lang_get s='th_locale'}</th>
			<td>		   
				<select name="locale">
				{html_options options=$optLocale selected=$userData.locale}
				</select>	
			</td>
		</tr>

		<tr>
			<th>{lang_get s='th_active'}</th>
			<td> 
			  <input type="checkbox"  name="user_is_active" {if $userData.active eq 1} checked {/if} />
			</td>
		</tr>
	</table>
	
	<div class="groupBtn">	
	{if $userData neq null}
		<input type="submit" name="do_update" value="{lang_get s='btn_upd_user_data'}" />
	{else}
		<input type="submit" name="do_update" value="{lang_get s='btn_add'}" />
	{/if}
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: location.href=fRoot+'lib/usermanagement/usersview.php';" />

	</div>
</form>
<hr />

</div>

</body>
</html>