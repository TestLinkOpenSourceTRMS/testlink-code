{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: loginFirst.tpl,v 1.4 2006/05/17 11:00:25 franciscom Exp $ *}
{* Purpose: smarty template - first login *}
{include file="inc_head.tpl" title="TestLink - New Account" }

<body onload="document.forms[0].elements[0].focus()">

<div class="title">TestLink {$tlVersion|escape}</div>

<div class="forms">

<p class="bold">{$message}</p>

<form method="post" action="firstLogin.php">

	<p class="bold">{lang_get s='login_name'}<br />
	<input type="text" name="loginName" size="20" maxlength="30" /></p>

  {* 20060507 - franciscom *}
  {if $external_password_mgmt eq 0}

	<p class="bold">{lang_get s='password'}<br />
	<input type="password" name="password" size="20" maxlength="32" /></p>
	<p class="bold">{lang_get s='password_again'}<br />
	<input type="password" name="password2" size="20" maxlength="32" /></p>
  {/if}
  
	<p class="bold">{lang_get s='first_name'}<br />
	<input type="text" name="first" size="20" maxlength="30" /></p>
	<p class="bold">{lang_get s='last_name'}<br />
	<input type="text" name="last" size="20" maxlength="30" /></p>
	<p class="bold">{lang_get s='e_mail'}<br />
	<input type="text" name="email" size="20" maxlength="100" /></p>

  {* 20060507 - franciscom *}
  {if $external_password_mgmt eq 1}
     <p>{lang_get s='password_mgmt_is_external'}<p>
	{/if}

	<p><input type="submit" name="editUser" value="{lang_get s='btn_add_user_data'}" /></p>
</form>

<hr />

<p><a href="login.php">{lang_get s='link_back_to_login'}</a></p>


</div>
</body>
</html>