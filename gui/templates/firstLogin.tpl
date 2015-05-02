{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource: firstLogin.tpl
Purpose: smarty template - first login / sign up

*}
{include file="inc_head.tpl" title="TestLink - New Account" openHead='yes'}

{lang_get var="labels"
          s='login_name,password,password_again,first_name,last_name,e_mail,
             password_mgmt_is_external,btn_add_user_data,link_back_to_login'}

<script language="JavaScript" src="{$basehref}gui/niftycube/niftycube.js" type="text/javascript"></script>
<script type="text/javascript">
window.onload=function(){
 Nifty("div#login_div","big");
 Nifty("div.messages","normal");
 focusInputField('login');
}
</script>
</head>

<body>
{config_load file="input_dimensions.conf" section="login"} {* Constant definitions *}
<div class="forms" id="login_div">
<div class="messages" style="text-align:center;">{$gui->message}</div>

{include file="inc_login_title.tpl"}

<form method="post" action="firstLogin.php">

	<p class="label">{$labels.login_name}<br />
	<input type="text" name="login" id="login" 
	       size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}" value="{$gui->login|escape}" required /></p>

  {if $gui->external_password_mgmt eq 0}
  	<p class="label">{$labels.password}<br />
  	<input type="password" name="password" size="{#PASSWD_SIZE#}" 
  	       maxlength="{$gui->pwdInputMaxLength}" required /></p>
  	<p class="label">{$labels.password_again}<br />
  	<input type="password" name="password2" size="{#PASSWD_SIZE#}" 
  	       maxlength="{$gui->pwdInputMaxLength}" required /></p>
  {/if}
  
	<p class="label">{$labels.first_name}<br />
	<input type="text" name="firstName" size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" 
	       value="{$gui->firstName|escape}" required /></p>
	<p class="label">{$labels.last_name}<br />
	<input type="text" name="lastName" size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" 
		value="{$gui->lastName|escape}" required /></p>

	<p class="label">{$labels.e_mail}<br />
	<input type="text" name="email" size="{#EMAIL_SIZE#}" maxlength="{#EMAIL_MAXLEN#}" 
	       value="{$gui->email|escape}" required /></p>

  {if $gui->external_password_mgmt eq 1}
     <p>{$labels.password_mgmt_is_external}<p>
	{/if}

	<br /><input type="submit" name="doEditUser" value="{$labels.btn_add_user_data}" />
</form>
<hr />
<p><a href="login.php">{$labels.link_back_to_login}</a></p>
</div>
</body>
</html>