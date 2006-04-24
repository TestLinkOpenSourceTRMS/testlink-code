{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: login.tpl,v 1.6 2006/04/24 10:36:24 franciscom Exp $
Purpose: smarty template - login page 
20060226 - franciscom - logo
20050826 - scs - added display of security note 
*}
{include file="inc_head.tpl" title="TestLink - Login" }

<body onload="document.forms[0].elements[0].focus()">

{* 20060226 - franciscom - logo *}
<div class="title">{$login_logo}<br />TestLink {$tlVersion|escape}</div>

<div class="forms">

	<form method="post" action="index.php">
		{* <p>{$note}</p> *}
	  <div class="bold" style="background-color:#990000; color:white; text-align:center;">
	  {$note}
    </div>
		
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

	{if $securityNotes}
	  {* 20060413 - franciscom *}
    {include file="inc_msg_from_array.tpl" array_of_msg=$securityNotes }
	{/if}

</div>
</body>
</html>