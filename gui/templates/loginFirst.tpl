{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: loginFirst.tpl,v 1.13 2008/06/03 08:40:49 havlat Exp $
Purpose: smarty template - first login
*}
{include file="inc_head.tpl" title="TestLink - New Account" openHead='yes'}

<script language="JavaScript" src="{$basehref}gui/niftycube/niftycube.js" type="text/javascript"></script>
{literal}
<script type="text/javascript">
window.onload=function(){
 Nifty("div#login_div","big");
 Nifty("div.warning_message","normal");
 // set focus on login text box
 focusInputField('loginName');
}
</script>
{/literal}
</head>

<body>
{config_load file="input_dimensions.conf" section="login"} {* Constant definitions *}
{include file="inc_login_title.tpl"}

<div class="forms" id="login_div">
<div class="warning_message" style="text-align:center;">{$message}</div>

<form method="post" action="firstLogin.php">

	<p class="label">{lang_get s='login_name'}<br />
	<input type="text" name="loginName" id="loginName" 
	       size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}" value="{$login|escape}"/></p>

  {if $external_password_mgmt eq 0}
  	<p class="label">{lang_get s='password'}<br />
  	<input type="password" name="password" size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></p>
  	<p class="label">{lang_get s='password_again'}<br />
  	<input type="password" name="password2" size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></p>
  {/if}
  
	<p class="label">{lang_get s='first_name'}<br />
	<input type="text" name="first" size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" value="{$firstName|escape}"/></p>
	<p class="label">{lang_get s='last_name'}<br />
	<input type="text" name="last" size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" value="{$lastName|escape}"/></p>
	<p class="label">{lang_get s='e_mail'}<br />
	<input type="text" name="email" size="{#EMAIL_SIZE#}" maxlength="{#EMAIL_MAXLEN#}" value="{$email|escape}"/></p>

  {if $external_password_mgmt eq 1}
     <p>{lang_get s='password_mgmt_is_external'}<p>
	{/if}

	<br /><input type="submit" name="editUser" value="{lang_get s='btn_add_user_data'}" />
</form>
<hr />
<p><a href="login.php">{lang_get s='link_back_to_login'}</a></p>
</div>
</body>
</html>
