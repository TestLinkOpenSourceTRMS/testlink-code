{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: loginLost.tpl,v 1.3 2006/03/10 22:35:57 schlundus Exp $ *}
{* Purpose: smarty template - lost password page *}


{include file="inc_head.tpl" title=$page_title}

<body onload="document.forms[0].elements[0].focus()">

<div class="title">TestLink {$tlVersion|escape}</div>

<div class="forms">

<form method="post" action="lostPassword.php">
	<p>{$note|escape}</p>
	<p class="bold">{lang_get s='login_name'}<br />
	<input type="text" name="login" size="20" maxlength="30" /></p>
	<p><input type="submit" name="editUser" value="{lang_get s='btn_send'}" /></p>
</form>

<hr />

<p><a href="login.php">{lang_get s='href_back'}</a></p>


</div>
</body>
</html>