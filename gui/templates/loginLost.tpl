{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: loginLost.tpl,v 1.4 2006/05/17 11:00:25 franciscom Exp $ *}
{* Purpose: smarty template - lost password page *}


{include file="inc_head.tpl" title=$page_title}

<body onload="document.forms[0].elements[0].focus()">

<div class="title">TestLink {$tlVersion|escape}</div>

<h2 style="text-align:center;">{lang_get s='password_reset'}</h2>

<div class="forms">

{* 20060507 - franciscom *}
{if $external_password_mgmt eq 0}

    <form method="post" action="lostPassword.php">
    	<p>{$note|escape}</p>
    	<p class="bold">{lang_get s='login_name'}<br />
    	<input type="text" name="login" size="20" maxlength="30" /></p>
    	<p><input type="submit" name="editUser" value="{lang_get s='btn_send'}" /></p>
    </form>
    
    <hr />
{else}
     <p>{lang_get s='password_mgmt_is_external'}<p>
{/if}
<p><a href="login.php">{lang_get s='href_back'}</a></p>


</div>
</body>
</html>