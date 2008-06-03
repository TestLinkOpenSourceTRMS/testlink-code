{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: loginLost.tpl,v 1.9 2008/06/03 08:40:49 havlat Exp $ 
Purpose: smarty template - lost password page 

rev :
     20070401 - added rounding GUI
*}

{include file="inc_head.tpl" title=$page_title openHead='yes'}

<script language="JavaScript" src="{$basehref}gui/niftycube/niftycube.js" type="text/javascript"></script>
<script type="text/javascript">
	{literal}
	window.onload=function(){
 		Nifty("div#login_div","big");
 		Nifty("div.warning_message","normal");
 		// set focus on login text box
		focusInputField('login');
	}
	{/literal}
</script>

</head>

<body>
{config_load file="input_dimensions.conf" section="login"} {* Constant definitions *}
{include file="inc_login_title.tpl"}

<div class="forms" id="login_div">
	{if $external_password_mgmt eq 0}
    <p class="title">{lang_get s='password_reset'}</p>

    <form method="post" action="lostPassword.php">
 		  <div class="warning_message" style="text-align:center;">{$note|escape}</div>
    	
    	<p class="label">{lang_get s='login_name'}<br />
    	<input type="text" name="login" id="login" 
    	       size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}" /></p>
    	<p><input type="submit" name="editUser" value="{lang_get s='btn_send'}" /></p>
    </form>
    
	{else}
     <p>{lang_get s='password_mgmt_is_external'}<p>
	{/if}

    <hr />
	<p><a href="login.php">{lang_get s='link_back_to_login'}</a></p>

</div>
</body>
</html>