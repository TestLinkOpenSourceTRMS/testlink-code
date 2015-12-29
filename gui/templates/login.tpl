{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource login.tpl
smarty template - login page 
*}
{lang_get var='labels' s='login_name,password,btn_login,new_user_q,login,demo_usage,new_style_login,
                         lost_password_q,demo_mode_suggested_user,demo_mode_suggested_password'}

{config_load file="input_dimensions.conf" section="login"}
{include file="inc_head.tpl" title="TestLink - Login" openHead='yes'}

<script type="text/javascript">
window.onload=function()
{
  focusInputField('login');
}
</script>
</head>
<body class="landing">

<div class="login_form medium" id="login_div">
  {include file="inc_login_title.tpl"}
  {if $tlCfg->demoMode}{$labels.demo_usage}{/if}
  <form method="post" name="login_form" action="login.php">
    {if $gui->login_disabled eq 0}
      {* can not escape not because sometimes contains valid HTML like HREF *}
      {if $gui->note != ''}
      <div class="messages_rounded" style="width:100%;text-align:center;border-radius: 5px;">{$gui->note}</div>
      {/if}
      <input type="hidden" name="reqURI" value="{$gui->reqURI|escape:'url'}"/>
      <input type="hidden" name="destination" value="{$gui->destination|escape:'url'}"/>
      <div class="messages_rounded" style="width:100%;border-radius: 5px;">

      <p class="label">{$labels.login}<br />
      <input type="text" name="tl_login" id="login" size="{#LOGIN_SIZE#}" 
             style="height: 18px;" maxlength="{#LOGIN_MAXLEN#}" 
             {if $tlCfg->demoMode} placeholder="{$labels.demo_mode_suggested_user}" {/if} required />
    </p>
      <p class="label">{$labels.password}<br />
      <input type="password" name="tl_password" style="height: 18px;"
             size="{#PASSWD_SIZE#}" maxlength="{$gui->pwdInputMaxLenght}" 
             {if $tlCfg->demoMode} placeholder="{$labels.demo_mode_suggested_password}" {/if}
             required />
    </p>

    <input type="submit" name="login_submit" class="big_button" value="{$labels.btn_login}" />
    </div>
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
  
  <a href="login.php?viewer=new">{$labels.new_style_login}</a><br />
  
  {include file="inc_copyrightnotice.tpl"}

  {if $gui->securityNotes}
    {include file="inc_msg_from_array.tpl" array_of_msg=$gui->securityNotes arg_css_class="messages_rounded"}
  {/if}
  
  {if $tlCfg->login_info != ""}
    <div>{$tlCfg->login_info}</div>
  {/if}

</div>
</body>
</html>