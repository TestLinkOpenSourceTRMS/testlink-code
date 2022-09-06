<!DOCTYPE html>
{config_load file="input_dimensions.conf" section="login"}
{lang_get var='labels' 
          s='login_name,password,btn_login,new_user_q,login,demo_usage,lost_password_q,oauth_login'}

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="Dashboard">
  <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">
  <title>{$labels.login}{$dashioHome}</title>

  <!-- Favicons -->
  <link href="{$dashioHome}favicon.png" rel="icon">
  <link href="{$dashioHome}apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Bootstrap core CSS -->
  <link href="{$dashioHome}lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!--external css-->
  <link href="{$fontawesomeHomeURL}/css/all.css" rel="stylesheet" />

  <!-- Custom styles for this template -->
  <link href="{$dashioHome}css/style.css" rel="stylesheet">
  <link href="{$dashioHome}css/style-responsive.css" rel="stylesheet">
  
  <!-- =======================================================
    Template Name: Dashio
    Template URL: https://templatemag.com/dashio-bootstrap-admin-template/
    Author: TemplateMag.com
    License: https://templatemag.com/license/
  ======================================================= -->
</head>

<body>
  <div id="login-page">
    <div class="container">
      <form class="form-login" name="login" id="login" 
        action="login.php?viewer={$gui->viewer}" method="post">

        <h2 class="form-login-heading">
        <img src="{$tlCfg->theme_dir}images/{$tlCfg->logo_login}"><br>
        {$tlVersion|escape}</h2>

        {if $gui->note != ''}
          <div class="alert-danger">
          {$gui->note}
          </div>
        {/if}

        {if $gui->draw}
          <input type="hidden" name="reqURI" 
            value="{$gui->reqURI|escape:'url'}"/>
          <input type="hidden" name="destination" 
            value="{$gui->destination|escape:'url'}"/>

          {if $gui->ssodisable}
            <input type="hidden" name="ssodisable" value="{$gui->ssodisable}"/>
          {/if}

          <div class="login-wrap">
            <input maxlength="{#LOGIN_MAXLEN#}" name="tl_login" id="tl_login"
              type="text" class="form-control" placeholder="{$labels.login_name}"
              required autofocus>
            <br>
            <input type="password" name="tl_password" id="tl_password" 
              class="form-control" placeholder="{$labels.password}" required>

            <label class="checkbox">&nbsp;</label>

            <button name="tl_login_btn" id="tl_login_btn" 
              class="btn btn-theme btn-block" type="submit">
              <i class="fa fa-lock"></i> {$labels.btn_login} </button>
            <hr>
            
            {foreach from=$gui->oauth item=oauth_item}
                <div class="button">
                <a style="text-decoration: none;" href="{$oauth_item->link}">
                <img src="{$tlCfg->theme_dir}images/{$oauth_item->icon}" style="height: 42px; vertical-align:middle;">
                <span style="padding: 10px;">{$labels.oauth_login}{$oauth_item->name}</span></a>
                </div>
            {/foreach}
            <hr>

            {if $gui->user_self_signup}
              <div class="registration">
                <a class="" href="firstLogin.php?viewer=new" id="tl_sign_up">
                  {$labels.new_user_q}
                </a>

                {* the configured authentication method don't allow 
                   users to reset his/her password *}    
                {if $gui->external_password_mgmt eq 0 && $tlCfg->demoMode eq 0}
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  <a href="lostPassword.php?viewer=new" id="tl_lost_password">
                  {$labels.lost_password_q}</a>
                {/if}
              </div>
            {/if}



          </div>
        {/if}
      </form>
    </div>
  </div>

  <!-- js placed at the end of the document so the pages load faster -->
  <script src="{$dashioHome}lib/jquery/jquery.min.js"></script>
  <script src="{$dashioHome}lib/bootstrap/js/bootstrap.min.js"></script>
  <!--BACKSTRETCH-->
  <!-- You can use an image of whatever size. This script will stretch to fit in any screen size.-->
  <script type="text/javascript" 
          src="{$dashioHome}lib/jquery.backstretch.min.js"></script>
  <script>
    $.backstretch("{$gui->loginBackgroundImg}", {
      speed: 500
    });
  </script>
</body>

</html>