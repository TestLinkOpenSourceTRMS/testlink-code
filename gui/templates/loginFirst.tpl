{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: loginFirst.tpl,v 1.18 2009/06/08 17:40:21 schlundus Exp $
Purpose: smarty template - first login
*}
{include file="inc_head.tpl" title="TestLink - New Account" openHead='yes'}

{lang_get var="labels"
          s='login_name,password,password_again,first_name,last_name,e_mail,
             password_mgmt_is_external,btn_add_user_data,link_back_to_login'}

<script language="JavaScript" src="{$basehref}gui/niftycube/niftycube.js" type="text/javascript"></script>
{literal}
<script type="text/javascript">
window.onload=function(){
 Nifty("div#login_div","big");
 Nifty("div.messages","normal");
 // set focus on login text box
 focusInputField('login');
}
</script>
{/literal}
</head>

<body>
{config_load file="input_dimensions.conf" section="login"} {* Constant definitions *}
{include file="inc_login_title.tpl"}

<div class="forms" id="login_div">
<div class="messages" style="text-align:center;">{$message}</div>

<form method="post" action="firstLogin.php">

	<p class="label">{$labels.login_name}<br />
	<input type="text" name="login" id="login" 
	       size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}" value="{$login|escape}"/></p>

  {if $external_password_mgmt eq 0}
  	<p class="label">{$labels.password}<br />
  	<input type="password" name="password" size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></p>
  	<p class="label">{$labels.password_again}<br />
  	<input type="password" name="password2" size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></p>
  {/if}
  
	<p class="label">{$labels.first_name}<br />
	<input type="text" name="first" size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" value="{$firstName|escape}"/></p>
	<p class="label">{$labels.last_name}<br />
	<input type="text" name="last" size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" value="{$lastName|escape}"/></p>
	<p class="label">{$labels.e_mail}<br />
	<input type="text" name="email" size="{#EMAIL_SIZE#}" maxlength="{#EMAIL_MAXLEN#}" value="{$email|escape}"/></p>

  {if $external_password_mgmt eq 1}
     <p>{$labels.password_mgmt_is_external}<p>
	{/if}

	<br /><input type="submit" name="bEditUser" value="{$labels.btn_add_user_data}" />
</form>
<hr />
<p><a href="login.php">{$labels.link_back_to_login}</a></p>
</div>
</body>
</html>
