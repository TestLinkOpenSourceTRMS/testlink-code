{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: adminUsersDelete.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - Delete user *}
{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_user_mgmt'}</h1>

{***** TABS *****}
<div class="tabMenu">
	<span class="unselected"><a href="lib/admin/adminUserNew.php">{lang_get s='menu_new_user'}</a></span> 
	<span class="unselected"><a href="lib/admin/adminUsers.php">{lang_get s='menu_mod_user'}</a></span>
	<span class="selected">{lang_get s='menu_del_user'}</span>
</div>

{include file="inc_update.tpl" result=$result item="user" action="delete"}


{***** form *****}

<div class="workBack">
	<form method="post" action="lib/admin/adminUsersDelete.php" 
	      onsubmit="return confirm('{lang_get s='confirm_user_del'}')">
		<span>
			<select style="width:300px" name="user">
			{html_options options=$arrLogin}
			</select>
		</span>
		<span>
			<input type="submit" name="delete" value="{lang_get s='btn_del_user'}" />
		</span>
	</form>
</div>

</body>
</html>