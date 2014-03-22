{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource login.tpl
smarty template - login page 
*}
{lang_get var='labels' s='login_name,password,btn_login,new_user_q,
                         lost_password_q,demo_mode_suggested_user,demo_mode_suggested_password'}

{config_load file="input_dimensions.conf" section="login"}
{include file="inc_head.tpl" title="TestLink - Login" openHead='yes'}

<script language="JavaScript" src="{$basehref}gui/niftycube/niftycube.js" type="text/javascript"></script>
<script type="text/javascript">
window.onload=function()
{
  Nifty("div#login_div","big");
  Nifty("div.messages","normal");
  
  focusInputField('login');
}
</script>
</head>
<body>

<div class="login_form" id="login_div">
  {include file="inc_login_title.tpl"}

  <form method="post" name="login_form" action="login.php">
    {if $gui->login_disabled eq 0}
      <div class="messages" style="width:100%;text-align:center;">{$gui->note}</div>
    <input type="hidden" name="reqURI" value="{$gui->reqURI|escape:'url'}"/>
    <input type="hidden" name="destination" value="{$gui->destination|escape:'url'}"/>
      <p class="label">{$labels.login_name}<br />
      <input type="text" name="tl_login" id="login" size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}" 
             {if $tlCfg->demoMode} placeholder="{$labels.demo_mode_suggested_user}" {/if} required />
    </p>
      <p class="label">{$labels.password}<br />
      <input type="password" name="tl_password" size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" 
             {if $tlCfg->demoMode} placeholder="{$labels.demo_mode_suggested_password}" {/if}
             required />
    </p>
    <input type="submit" name="login_submit" value="{$labels.btn_login}" />
  {/if}
  </form>
  
  <p>
  {if $gui->user_self_signup}
    <a href="firstLogin.php">{$labels.new_user_q}</a><br />
  {/if}
  
  {* the configured authentication method don't allow users to reset his/her password *}    
  {if $gui->external_password_mgmt eq 0 && $tlCfg->demoMode eq 0}
    <a href="lostPassword.php">{$labels.lost_password_q}</a>
  </p>
  {/if}
  
  {include file="inc_copyrightnotice.tpl"}

  {if $gui->securityNotes}
    {include file="inc_msg_from_array.tpl" array_of_msg=$gui->securityNotes arg_css_class="messages"}
  {/if}
  
  {if $tlCfg->login_info != ""}
    <div>{$tlCfg->login_info}</div>
  {/if}

</div>
</body>
</html>