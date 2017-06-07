<!DOCTYPE html>
{config_load file="input_dimensions.conf" section="login"}

{lang_get var="labels" 
          s="password_reset,login_name,btn_send,password_mgmt_is_external,link_back_to_login"}

<html >
  <head>
    <meta charset="UTF-8">
    <title>{$labels.password_reset}</title>
    <link rel="stylesheet" href="gui/icons/font-awesome-4.5.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="gui/themes/default/login/codepen.io/marcobiedermann/css/style.css">
  </head>
  <body class="align">
    <div class="site__container">
      <div class="grid__container">
      <img src="{$tlCfg->theme_dir}images/{$tlCfg->logo_login}">
      </div>
      
      {if $gui->note != ''}
      <br>
      <div class="grid__container">
      <div class="user__feedback">
      {$gui->note}
      </div>
      </div>
      {/if}

      <div class="grid__container">
      <form name="lostPassword" id="lostPassword" action="lostPassword.php?viewer={$gui->viewer}" method="post" class="form form--login">
        <input type="hidden" name="reqURI" value="{$gui->reqURI|escape:'url'}"/>
        <input type="hidden" name="destination" value="{$gui->destination|escape:'url'}"/>


        <div class="form__field">
          <label for="tl_login"><i class="fa fa-user"></i></label>
          <input maxlength="{#LOGIN_MAXLEN#}" size="{#LOGIN_SIZE#}" name="login" id="login" type="text" class="form__input" placeholder="{$labels.login_name}" value="{$gui->login|escape}" required>
        </div>

        <div class="form__field">
          <input type="submit" name="editUser" value="{$labels.btn_send}">
        </div>

      </form>
    </div>
  </div>
</body>
</html>
