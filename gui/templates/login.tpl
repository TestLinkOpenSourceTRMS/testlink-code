{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: login.tpl,v 1.7 2006/08/21 13:17:05 franciscom Exp $
Purpose: smarty template - login page 

20060819 - franciscom - css changes, smarty config file
*}
{include file="inc_head.tpl" title="TestLink - Login" }

<body onload="document.forms[0].elements[0].focus()">

{config_load file="input_dimensions.conf" section="login"} {* Constant definitions *}
<div class="title">{$login_logo}<br />TestLink {$tlVersion|escape}</div>
<div class="forms">

	<form method="post" action="index.php">
	  <div class="warning_message" style="text-align:center;">{$note}</div>
		
		<p class="label">{lang_get s='login_name'}<br />
		<input type="text" name="login" size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}" /></p>
		
		<p class="label">{lang_get s='password'}<br />
		<input type="password" name="password" size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></p>
		
		<input type="submit" name="submit" value="{lang_get s='btn_login'}" />
	</form>
	
	<p>
	<a href="firstLogin.php">{lang_get s='new_user_q'}</a><br />
	<a href="lostPassword.php">{lang_get s='lost_password_q'}</a>
	</p>

	
	{include file="inc_copyrightnotice.tpl"}

	{if $securityNotes}
    {include file="inc_msg_from_array.tpl" array_of_msg=$securityNotes arg_css_class="warning_message"}
	{/if}

</div>
</body>
</html>