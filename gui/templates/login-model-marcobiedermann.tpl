<!DOCTYPE html>
{config_load file="input_dimensions.conf" section="login"}
{lang_get var='labels' 
          s='login_name,password,btn_login,new_user_q,login,demo_usage,lost_password_q,oauth_login'}
<html >
  <head>
    <meta charset="UTF-8">
    <title>{$labels.login}</title>
    <link rel="stylesheet" href="gui/icons/font-awesome-4.5.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="gui/themes/default/login/codepen.io/marcobiedermann/css/style.css">
  </head>
  <body class="align">
    <div class="site__container">
      <div class="grid__container">
      <img src="{$tlCfg->theme_dir}images/{$tlCfg->logo_login}"><br>
      <span>{$tlVersion|escape} </span>
      </div>
      
      {if $gui->note != ''}
      <br>
      <div class="grid__container">
      <div class="user__feedback">
      {$gui->note}
      </div>
      </div>
      {/if}

      {if $tlCfg->demoMode}
      <br>
      <div class="grid__container">
      {$labels.demo_usage}
      </div>
      {/if}

      {if $tlCfg->login_info != ""}
      <div class="text--center">
      {$tlCfg->login_info}
      </div>
      {/if}
      
      {if $gui->draw}  
        <div class="grid__container">
          <form name="login" id="login" action="login.php?viewer={$gui->viewer}" method="post" class="form form--login">
            <input type="hidden" name="reqURI" value="{$gui->reqURI|escape:'url'}"/>
            <input type="hidden" name="destination" value="{$gui->destination|escape:'url'}"/>

            {if $gui->ssodisable}
            <input type="hidden" name="ssodisable" value="{$gui->ssodisable}"/>
            {/if}

            <div class="form__field">
              <label for="tl_login"><i class="fa fa-user"></i></label>
              <input maxlength="{#LOGIN_MAXLEN#}" name="tl_login" id="tl_login" type="text" class="form__input" placeholder="{$labels.login_name}" required>
            </div>

            <div class="form__field">
              <label for="tl_password"><i class="fa fa-lock"></i></label>
              <input name="tl_password" id="tl_password" type="password" class="form__input" placeholder="{$labels.password}" required>
            </div>

            <div class="form__field">
              <input type="submit" value="{$labels.btn_login}">
            </div>

            {foreach from=$gui->oauth item=oauth_item}
                <div class="button">
                <a style="text-decoration: none; color:#ffffff;" href="{$oauth_item->link}">
                <img src="{$tlCfg->theme_dir}images/{$oauth_item->icon}" style="height: 42px; vertical-align:middle;">
                <span style="padding: 10px;">{$labels.oauth_login}{$oauth_item->name}</span></a>
                </div>
            {/foreach}
          </form>

          <p class="text--center">
          {if $gui->user_self_signup}
            <a href="firstLogin.php?viewer=new">{$labels.new_user_q}</a> &nbsp; &nbsp;
          {/if}

          {* the configured authentication method don't allow users to reset his/her password *}    
          {if $gui->external_password_mgmt eq 0 && $tlCfg->demoMode eq 0}
            <a href="lostPassword.php?viewer=new">{$labels.lost_password_q}</a>
          {/if}
          </p> 
        </div>
      {/if}
  </div>
</body>
</html>
