{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: login.tpl,v 1.15 2007/08/18 14:08:26 franciscom Exp $
Purpose: smarty template - login page 

20070818 - franciscom - BUGID xxxx
20070401 - franciscom - new rounding engine
20070301 - franciscom - BUGID 695 (fawel contribute)
*}
{include file="inc_head.tpl" title="TestLink - Login" openHead='yes'}

<script language="JavaScript" src="{$basehref}gui/niftycube/niftycube.js" type="text/javascript"></script>
{literal}
<script type="text/javascript">
window.onload=function(){
 Nifty("div#login_div","big");
 Nifty("div.warning_message","normal");
 Nifty("div.login_warning_message","normal");
 document.forms[0].elements[0].focus();
}
</script>
{/literal}


</head>

<body>

{config_load file="input_dimensions.conf" section="login"} {* Constant definitions *}
<div class="title">{$login_logo}<br />TestLink {$tlVersion|escape}</div>
<div class="forms" id="login_div">

	<form method="post" action="index.php">
    {if $login_disabled eq 0}		
  	  <div class="login_warning_message" style="text-align:center;">{$note}</div>

  		<p class="label">{lang_get s='login_name'}<br />
  		<input type="text" name="login" size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}" /></p>
  		
  		<p class="label">{lang_get s='password'}<br />
  		<input type="password" name="password" size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></p>
  		
  		<input type="submit" name="submit" value="{lang_get s='btn_login'}" />
		{/if}
	</form>
	
	<p>
	{* BUGID 695 *} 
	{if $g_user_self_signup eq true}
	  <a href="firstLogin.php">{lang_get s='new_user_q'}</a><br />
	{/if}
	
	{* BUGID xxxx
	   the configured authentication method don't allow users to reset his/her password *}		
	{if $external_password_mgmt eq 0}
	<a href="lostPassword.php">{lang_get s='lost_password_q'}</a>
	</p>
	{/if}

	
	{include file="inc_copyrightnotice.tpl"}

	{if $securityNotes}
    	{include file="inc_msg_from_array.tpl" array_of_msg=$securityNotes arg_css_class="warning_message"}
	{/if}

</div>
</body>
</html>