<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="Dashboard">
  <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">
  <title>TestLink based on Dashio Bootstrap Admin Template</title>

  <!-- Favicons -->
  <link href="{$tlCfg->dashio}/img/favicon.png" rel="icon">
  <link href="{$tlCfg->dashio}/dashio/img/apple-touch-icon.png" rel="apple-touch-icon">

  <link href="{$tlCfg->dashio}/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <link href="{$tlCfg->dashio}/lib/font-awesome/css/all.css" rel="stylesheet" />


  <link rel="stylesheet" type="text/css" 
        href="{$tlCfg->dashio}/css/zabuto_calendar.css">
  <link rel="stylesheet" type="text/css" 
        href="{$tlCfg->dashio}/lib/gritter/css/jquery.gritter.css" />
  <link rel="stylesheet" type="text/css" 
        href="{$tlCfg->dashio}/css/style.css">
  <link rel="stylesheet" type="text/css" 
        href="{$tlCfg->dashio}/css/style-responsive.css">
  <link rel="stylesheet" type="text/css" 
        href="{$basehref}gui/themes/default/css/frame.css">
</head>

<body class="body-noscroll">
  <section id="container">
   <iframe src="{$gui->titleframe}" id="titlebar" name="titlebar" 
           style="display: block;" class="navigationBar">
   </iframe>

   <iframe src="{$gui->mainframe}" id="mainframe" name="mainframe" 
           style="display: block;" class="siteContent">
   </iframe>


  {* include file="../dashio/content.tpl" *}


   <!-- 
   <iframe src="{$gui->mainframe}" name="mainframe" 
           style="display: block;" class="siteContent">
   </iframe>
   -->

  </section>

  <script type="text/javascript" language="javascript"
          src="{$basehref}{$smarty.const.TL_JQUERY}">
  </script>

  
  {$bs = "gui/templates/dashio/lib/"}
  <!-- js placed at the end of the document so the pages load faster -->
  {* <script src="{$bs}jquery/jquery.min.js"></script> *}

  <script src="{$bs}bootstrap/js/bootstrap.min.js"></script>
  <script class="include" type="text/javascript" src="{$bs}jquery.dcjqaccordion.2.7.js"></script>
  <script src="{$bs}jquery.scrollTo.min.js"></script>
  <script src="{$bs}jquery.nicescroll.js" type="text/javascript"></script>
  <!--common script for all pages-->
  <script src="{$bs}common-scripts.js"></script>
  <!--script for this page-->
</body>

</html>
