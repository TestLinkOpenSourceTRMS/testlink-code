{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: loginFirst.tpl,v 1.5 2006/08/21 13:17:37 franciscom Exp $
Purpose: smarty template - first login

rev :
     20060819 - franciscom - added constant definitions through smarty conf file
                             logo added 
*}

{include file="inc_head.tpl" title="TestLink - New Account" }

<body onload="document.forms[0].elements[0].focus()">

{config_load file="input_dimensions.conf" section="login"} {* Constant definitions *}

<div class="title">{$login_logo}<br />TestLink {$tlVersion|escape}</div>
<div class="forms">
<p class="title">{$message}</p>

<form method="post" action="firstLogin.php">

	<p class="label">{lang_get s='login_name'}<br />
	<input type="text" name="loginName" size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}" /></p>

  {* 20060507 - franciscom *}
  {if $external_password_mgmt eq 0}
  	<p class="label">{lang_get s='password'}<br />
  	<input type="password" name="password" size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></p>
  	<p class="label">{lang_get s='password_again'}<br />
  	<input type="password" name="password2" size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></p>
  {/if}
  
	<p class="label">{lang_get s='first_name'}<br />
	<input type="text" name="first" size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" /></p>
	<p class="label">{lang_get s='last_name'}<br />
	<input type="text" name="last" size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" /></p>
	<p class="label">{lang_get s='e_mail'}<br />
	<input type="text" name="email" size="{#EMAIL_SIZE#}" maxlength="{#EMAIL_MAXLEN#}" /></p>

  {* 20060507 - franciscom *}
  {if $external_password_mgmt eq 1}
     <p>{lang_get s='password_mgmt_is_external'}<p>
	{/if}

	<br><input type="submit" name="editUser" value="{lang_get s='btn_add_user_data'}" />
</form>
<hr />
<br><a href="login.php">{lang_get s='link_back_to_login'}</a>
</div>
</body>
</html>