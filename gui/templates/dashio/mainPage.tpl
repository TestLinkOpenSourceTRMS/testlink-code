{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource mainPage.tpl
*}
{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
{lang_get 
  var='labels'
  s='tc_monthly_creation_rate_on_tproj,
     tc_monthly_creation_rate_on_tproj_hint'}


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">

  <!-- Favicons -->
  <link href="{$dashioHomeURL}img/favicon.png" rel="icon">
  <link href="{$dashioHomeURL}img/apple-touch-icon.png" rel="apple-touch-icon">

  <link href="{$dashioHomeURL}lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="{$fontawesomeHomeURL}/css/all.css" rel="stylesheet" />      

  <link href="{$dashioHomeURL}css/style.css" rel="stylesheet">
  <link href="{$dashioHomeURL}css/style-responsive.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="{$basehref}gui/themes/default/css/frame.css">  
</head>
<body>
{include file="aside.tpl"}
    <section id="main-content">
      <section class="wrapper">
        <div class="row">
          <div class="col-lg-9 main-chart">
            <!--CUSTOM CHART START -->
            <div class="border-head">
              <h3 style="border-bottom: 0px; 
                         margin-bottom: 0px;
                         padding-bottom: 0px;">{$gui->tc_monthly_creation_rate_on_tproj|escape}</h3>
              <h5 style="border-bottom: 1px solid #c9cdd7;">{$labels.tc_monthly_creation_rate_on_tproj_hint|escape}</h5>
              <br>
            </div>
            <div class="custom-bar-chart">
              {$gui->dashboard->yAxis}
              {$gui->dashboard->chart}
            </div>
            <!--custom chart end-->
          </div>
          <!-- /col-lg-9 END SECTION MIDDLE -->
        </div>
        <!-- /row -->
      </section>
    </section>
    <!--main content end-->
    <!--footer start-->
    <footer class="site-footer">
      <div class="text-center">
        <p>
          &copy; Copyrights <strong>Dashio</strong>. All Rights Reserved
        </p>
        <div class="credits">
          <!--
            You are NOT allowed to delete the credit link to TemplateMag with free version.
            You can delete the credit link only if you bought the pro version.
            Buy the pro version with working PHP/AJAX contact form: https://templatemag.com/dashio-bootstrap-admin-template/
            Licensing information: https://templatemag.com/license/
          -->
          Created with Dashio template by <a href="https://templatemag.com/">TemplateMag</a>
        </div>
        <a href="index.html#" class="go-top">
          <i class="fa fa-angle-up"></i>
          </a>
      </div>
    </footer>
    <!--footer end-->


  <!-- js placed at the end of the document so the pages load faster -->
  <script type="text/javascript" 
          src="{$basehref}{$smarty.const.TL_JQUERY}" 
          language="javascript"></script>

  <script src="{$dashioHomeURL}lib/bootstrap/js/bootstrap.min.js"></script>
  <script class="include" type="text/javascript" src="{$dashioHomeURL}lib/jquery.dcjqaccordion.2.7.js"></script>

  <script src="{$dashioHomeURL}lib/jquery.scrollTo.min.js"></script>

  <script src="{$dashioHomeURL}lib/jquery.nicescroll.js" type="text/javascript"></script>  

  <!--common script for all pages-->
  <script src="{$dashioHomeURL}lib/left-bar-scripts.js"></script>

  <!--script for this page-->
</body>
</html>