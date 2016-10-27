<!DOCTYPE html>
{config_load file="input_dimensions.conf" section="login"}
{lang_get var="labels"
          s='login_name,password,password_again,first_name,last_name,e_mail,
             password_mgmt_is_external,btn_add_user_data,link_back_to_login'}

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
      <img src="{$tlCfg->theme_dir}images/{$tlCfg->logo_login}">
      </div>
      
      {if $gui->message != ''}
      <div class="grid__container">
      {$gui->message}
      </div>
      {/if}
  
      <div class="grid__container">
      <form name="signup" id="signup" action="firstLogin.php?viewer={$gui->viewer}" method="post" class="form form--login">
  
        <div class="form__field">
          <label for="login"><i class="fa fa-user"></i></label>
          <input maxlength="{#LOGIN_MAXLEN#}" name="login" id="login" type="text" class="form__input" placeholder="{$labels.login_name}" value="{$gui->login|escape}" required>
        </div>

        {if $gui->external_password_mgmt eq 0}
        <div class="form__field">
          <label for="password"><i class="fa fa-lock"></i></label>
          <input name="password" id="password" type="password" class="form__input" placeholder="{$labels.password}" 
           maxlength="{$gui->pwdInputMaxLength}" required>
        </div>

        <div class="form__field">
          <label for="password2"><i class="fa fa-lock"></i></label>
          <input name="password2" id="password2" type="password" class="form__input" placeholder="{$labels.password_again}" 
           maxlength="{$gui->pwdInputMaxLength}" required>
        </div>
        {/if}

        <div class="form__field">
          <label for="firstName"><i class="fa fa-child"></i></label>
          <input maxlength="{#NAMES_SIZE#}" name="firstName" id="firstName" type="text" class="form__input" placeholder="{$labels.first_name}" value="{$gui->firstName|escape}" required>
        </div>

        <div class="form__field">
          <label for="lastName"><i class="fa fa-group"></i></label>
          <input maxlength="{#NAMES_SIZE#}" name="lastName" id="lastName" type="text" class="form__input" placeholder="{$labels.last_name}" value="{$gui->lastName|escape}" required>
        </div>

        <div class="form__field">
          <label for="email"><i class="fa fa-envelope"></i></label>
          <input maxlength="{#EMAIL_MAXLEN#}" name="email" id="email" type="text" class="form__input" placeholder="{$labels.e_mail}" value="{$gui->email|escape}" required>
        </div>

        <div class="form__field">
          <input type="submit" name="doEditUser" value="{$labels.btn_add_user_data}">
        </div>

      </form>
    </div>
  </div>
</body>
</html>
