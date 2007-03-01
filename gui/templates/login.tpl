{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: login.tpl,v 1.9 2007/03/01 16:10:12 franciscom Exp $
Purpose: smarty template - login page 

20070301 - franciscom - BUGID 695 (fawel contribute)
*}
{include file="inc_head.tpl" title="TestLink - Login" openHead='yes'}
<script language="JavaScript" src="gui/javascript/rounded.js" type="text/javascript"></script>
</head>

<body onload="document.forms[0].elements[0].focus()">

{config_load file="input_dimensions.conf" section="login"} {* Constant definitions *}
<div class="title">{$login_logo}<br />TestLink {$tlVersion|escape}</div>
<div class="forms">

	<form method="post" action="index.php">
	  <div class="login_warning_message" style="text-align:center;">{$note}</div>
		
		<p class="label">{lang_get s='login_name'}<br />
		<input type="text" name="login" size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}" /></p>
		
		<p class="label">{lang_get s='password'}<br />
		<input type="password" name="password" size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></p>
		
		<input type="submit" name="submit" value="{lang_get s='btn_login'}" />
	</form>
	
	<p>
	{* BUGID 695 *} 
	{if $g_user_self_signup eq true}
	  <a href="firstLogin.php">{lang_get s='new_user_q'}</a><br />
	{/if}
	<a href="lostPassword.php">{lang_get s='lost_password_q'}</a>
	</p>

	
	{include file="inc_copyrightnotice.tpl"}

	{if $securityNotes}
    {include file="inc_msg_from_array.tpl" array_of_msg=$securityNotes arg_css_class="warning_message"}
	{/if}

</div>
<script type="text/javascript">
Rounded('warning_message', 8, 8);
</script>

</body>
</html>