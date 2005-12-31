{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: adminUserNew.tpl,v 1.6 2005/12/31 14:38:10 schlundus Exp $ *}
{* 
Purpose: smarty template - Add new user 

20051115 - fm - added active

*}
{include file="inc_head.tpl" popup="yes"}

<body>

<h1>{lang_get s='title_user_mgmt'}</h1>

{* tabs *}
<div class="tabMenu">
	<span class="selected">{lang_get s='menu_new_user'}</span> 
	<span class="unselected"><a href="lib/admin/adminUsers.php">{lang_get s='menu_mod_user'}</a></span>
</div>

<div class="workBack">

{* user was added *}
{include file="inc_update.tpl" result=$sqlResult item="user" action="add" name=$name}

{* new user form *}
<div>
	<form method="post" action="lib/admin/adminUserNew.php">

	<table class="common" width="50%">
		<caption>{lang_get s='caption_new_user'}</caption>
		<tr><td>{lang_get s='th_login'}:</td><td><input type="text" name="login" maxlength="30" /></td></tr>
		<tr><td>{lang_get s='th_password'}:</td><td><input type="password" name="password" maxlength="32" /></td></tr>
		<tr><td>{lang_get s='th_first_name'}:</td><td><input type="text" name="first" maxlength="30" /></td></tr>
		<tr><td>{lang_get s='th_last_name'}:</td><td><input type="text" name="last" maxlength="30" /></td></tr>
		<tr><td>{lang_get s='th_email'}:</td><td><input type="text" name="email" maxlength="100" size="50" /></td></tr>
		<tr>
			<td>{lang_get s='th_rights'}:</td>
			<td>
				<select name="rights" style="width:150px"> 
					{html_options options=$roles selected="5"}
				</select>
				{include file="inc_help.tpl" filename="userRights.html"}
			</td>
		</tr>

		<tr>
			<td>{lang_get s='th_locale'}:</td>
			<td>
				<select name="locale" style="width:150px"> 
					{html_options options=$optLocale selected=$defaultLocale}
				</select>
			</td>
		</tr>

    {* --------------------------------------------------------------- *}
		<tr>
			<td>{lang_get s='th_active'}:</td>
			<td>
				<input type="checkbox" name="user_is_active" />
      </td>
		</tr>
    {*  --------------------------------------------------------------- *}

	</table>

	<input type="submit" name="newUser" value={lang_get s='btn_add'} />

	</form>
</div>

</body>
</html>