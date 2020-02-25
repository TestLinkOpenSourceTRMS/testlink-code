{*
Testlink Open Source Project - http://testlink.sourceforge.net/

title bar + menu

@filesource navBar.tpl
*}
{lang_get var="labels"
          s="title_events,event_viewer,home,testproject,title_specification,title_execute,
             title_edit_personal_data,th_tcid,link_logout,title_admin,
             search_testcase,title_results,title_user_mgmt,full_text_search"}
{$cfg_section=$smarty.template|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes"}


  <!-- Favicons -->
  <link href="gui/templates/dashio/img/favicon.png" rel="icon">
  <link href="gui/templates/dashio/img/apple-touch-icon.png" rel="apple-touch-icon">

  <link href="gui/templates/dashio/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="gui/templates/dashio/lib/font-awesome/css/font-awesome.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="gui/templates/dashio/css/zabuto_calendar.css">
  <link rel="stylesheet" type="text/css" href="gui/templates/dashio/lib/gritter/css/jquery.gritter.css" />
  <link href="gui/templates/dashio/css/style.css" rel="stylesheet">
  <link href="gui/templates/dashio/css/style-responsive.css" rel="stylesheet">
  <script src="gui/templates/dashio/lib/chart-master/Chart.js"></script>
</head>

<body style="min-width: 800px;">
    <header class="header black-bg">
      <div class="sidebar-toggle-box">
        <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
      </div>
      <a href="index.html" class="logo"><b>TestLink</b></a>
    </header>
</body>
</html>