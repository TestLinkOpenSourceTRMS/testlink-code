{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: lostPassword.tpl,v 1.2 2009/04/21 09:29:42 franciscom Exp $ 
Purpose: lost password page 

rev :
     20070401 - added rounding GUI
*}

{lang_get var="labels" s="password_reset,login_name,btn_send,
                          password_mgmt_is_external,link_back_to_login"}
{include file="inc_head.tpl" title=$gui->page_title openHead='yes'}

<script language="JavaScript" src="{$basehref}gui/niftycube/niftycube.js" type="text/javascript"></script>
<script type="text/javascript">
	{literal}
	window.onload=function(){
 		Nifty("div#login_div","big");
 		Nifty("div.messages","normal");
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
	{if $gui->external_password_mgmt eq 0}
    <p class="title">{$labels.password_reset}</p>

    <form method="post" action="lostPassword.php">
 		  <div class="messages" style="text-align:center;">{$gui->note|escape}</div>
    	
    	<p class="label">{$labels.login_name}<br />
    	<input type="text" name="login" id="login" 
    	       size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}" /></p>
    	<p><input type="submit" name="editUser" value="{$labels.btn_send}" /></p>
    </form>
    
	{else}
     <p>{$labels.password_mgmt_is_external}</p>
	{/if}

    <hr />
	<p><a href="login.php">{$labels.link_back_to_login}</a></p>

</div>
</body>
</html>