{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: login.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - login page *}
{include file="inc_head.tpl" title="TestLink - Login" }

<body onload="document.forms[0].elements[0].focus()">

<div class="title">TestLink {$tlVersion|escape}</div>

<div class="forms">

	<form method="post" action="index.php">
		<p>{$note}</p>
		
		<p class="bold">{lang_get s='login_name'}<br />
		<input type="text" name="login" size="20" maxlength="30" /></p>
		
		<p class="bold">{lang_get s='password'}<br />
		<input type="password" name="password" size="20" maxlength="32" /></p>
		
		<input type="submit" name="submit" value="{lang_get s='btn_login'}" />
	</form>
	
	<p>
	<a href="firstLogin.php">{lang_get s='new_user_q'}</a><br />
	<a href="lostPassword.php">{lang_get s='lost_password_q'}</a>
	</p>
	
	{include file="inc_copyrightnotice.tpl"}

</div>
</body>
</html>