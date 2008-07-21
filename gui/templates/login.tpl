{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: login.tpl,v 1.24 2008/07/21 09:25:01 havlat Exp $
Purpose: smarty template - login page 
-------------------------------------------------------------------------------------- *}
{config_load file="input_dimensions.conf" section="login"}
{include file="inc_head.tpl" title="TestLink - Login" openHead='yes'}

<script language="JavaScript" src="{$basehref}gui/niftycube/niftycube.js" 
		type="text/javascript"></script>
{literal}
<script type="text/javascript">
window.onload=function()
{
	Nifty("div#login_div","big");
	Nifty("div.messages","normal");
 
	// set focus on login text box
	focusInputField('login');
}
</script>
{/literal}

</head>
<body>
{include file="inc_login_title.tpl"}

<div class="forms" id="login_div">

	<form method="post" name="login_form" action="login.php">
    {if $login_disabled eq 0}		
  	  <div class="messages" style="text-align:center;">{$note}</div>
		  <input type="hidden" name="reqURI" value="{$reqURI}"/>
  		<p class="label">{lang_get s='login_name'}<br />
			<input type="text" name="tl_login" id="login" size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}" />
		</p>
  		<p class="label">{lang_get s='password'}<br />
			<input type="password" name="tl_password" size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" />
		</p>
		<input type="submit" name="submit" value="{lang_get s='btn_login'}" />
		{/if}
	</form>
	
	<p>
	{if $g_user_self_signup}
		<a href="firstLogin.php">{lang_get s='new_user_q'}</a><br />
	{/if}
	
	{* the configured authentication method don't allow users to reset his/her password *}		
	{if $external_password_mgmt eq 0}
		<a href="lostPassword.php">{lang_get s='lost_password_q'}</a>
	</p>
	{/if}

	
	{include file="inc_copyrightnotice.tpl"}

	{if $securityNotes}
    	{include file="inc_msg_from_array.tpl" array_of_msg=$securityNotes arg_css_class="messages"}
	{/if}
	
	{if $tlCfg->login_info ne ""}
		<div>{$tlCfg->login_info}</div>
	{/if}

</div>
</body>
</html>