<!DOCTYPE html>
{config_load file="input_dimensions.conf" section="login"}
{lang_get var='labels' 
          s='login_name,password,btn_login,new_user_q,login,demo_usage,e_mail,mail,password_again,
             lost_password_q,demo_mode_suggested_user,demo_mode_suggested_password,old_style_login'}
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
      <img src="http://localhost/development/logo/tl-logo-transparent-25.png"><br>
      <span style="margin-left:220px;">{$tlVersion|escape} </span>
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
  
      <div class="grid__container">
      <form name="login" id="login" action="login.php?viewer={$gui->viewer}" method="post" class="form form--login">
        <input type="hidden" name="reqURI" value="{$gui->reqURI|escape:'url'}"/>
        <input type="hidden" name="destination" value="{$gui->destination|escape:'url'}"/>


        <div class="form__field">
          <label for="tl_login"><i class="fa fa-user"></i></label>
          <input maxlength="{#LOGIN_MAXLEN#}" size="{#LOGIN_SIZE#}" name="tl_login" id="tl_login" type="text" class="form__input" placeholder="{$labels.login_name}" required>
        </div>

        <div class="form__field">
          <label for="tl_password"><i class="fa fa-lock"></i></label>
          <input name="tl_password" id="tl_password" type="password" class="form__input" placeholder="{$labels.password}" required>
        </div>

        <div class="form__field">
          <input type="submit" value="{$labels.btn_login}">
        </div>

      </form>

      <p class="text--center"><a href="firstLogin.php?viewer=new">{$labels.new_user_q}</a> &nbsp; &nbsp;

      <a href="lostPassword.php?viewer=new">{$labels.lost_password_q}</a> </p>

      <p class="text--center"><a href="login.php?viewer=old">{$labels.old_style_login}</a> &nbsp; &nbsp;

    </div>
  </div>
</body>
</html>
