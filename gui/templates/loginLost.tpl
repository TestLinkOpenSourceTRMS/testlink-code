{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: loginLost.tpl,v 1.5 2006/08/21 13:17:37 franciscom Exp $ *}
{* Purpose: smarty template - lost password page *}


{include file="inc_head.tpl" title=$page_title}

<body onload="document.forms[0].elements[0].focus()">
{config_load file="input_dimensions.conf" section="login"} {* Constant definitions *}

<div class="title">{$login_logo}<br />TestLink {$tlVersion|escape}</div>
<div class="forms">
{if $external_password_mgmt eq 0}
    <p class="title">{lang_get s='password_reset'}</p>
    
    <form method="post" action="lostPassword.php">
    	<p>{$note|escape}</p>
    	<p class="label">{lang_get s='login_name'}<br />
    	<input type="text" name="login" size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}" /></p>
    	<p><input type="submit" name="editUser" value="{lang_get s='btn_send'}" /></p>
    </form>
    
    <hr />
{else}
     <p>{lang_get s='password_mgmt_is_external'}<p>
{/if}
<p><a href="login.php">{lang_get s='link_back_to_login'}</a></p>
</div>
</body>
</html>