{*
Testlink Open Source Project - http://testlink.sourceforge.net/

title bar + menu

@filesource navBar.tpl
*}
{lang_get var="labels"
  s="title_events,event_viewer,home,testproject,title_specification,
     title_execute,testplan,title_edit_personal_data,th_tcid,
     link_logout,title_admin,search_testcase,title_results,
     title_user_mgmt,full_text_search,reload_main_view,
     toggle_navigation"}

{$cfg_section=$smarty.template|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes"}

  <link href="{$dashioHome}lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="{$fontawesomeHomeURL}/css/all.css" rel="stylesheet" />



  <link href="{$dashioHome}css/style.css" rel="stylesheet">
  <link href="{$dashioHome}css/style-responsive.css" rel="stylesheet">
</head>

<!--
IMPORTANT DEVELOPMENT INFORMATION
=================================
About the _top Value
The _top value of the target attribute specifies that the URL should open in the top browsing context (or current, if the current is already the top browsing context).
-->
{$topBrowsingContext = "_top"}

<body style="min-width: 800px;">
    <header class="header black-bg">
      <div class="sidebar-toggle-box">
        <a href="index.php?tproject_id={$gui->tproject_id}&tplan_id={$gui->tplan_id}" 
           target="{$topBrowsingContext}">
        <div class="fas fa-sync tooltips" data-placement="right" data-original-title="{$labels.reload_main_view}"></div>
        </a>
      </div>
      <div class="sidebar-toggle-box">
        <div class="fa fa-bars tooltips" data-placement="right" data-original-title="{$labels.toggle_navigation}"></div>
      </div>
      <a class="logo" 
         href="index.php?tproject_id={$gui->tproject_id}&tplan_id={$gui->tplan_id}" target="{$topBrowsingContext}" title="{$labels.reload_main_view}">
         <b>TEST<span>LINK</span></b></a>

  <div class="top-menu">
        <ul class="nav pull-right top-menu">

{* style="margin-top: 20px;padding-right: 30px;" *}
{if $gui->testProjects != ""}
  <li class="combo">
    <form style="display:inline" name="projectForm" 
          target="{$topBrowsingContext}" 
          action="index.php?action=projectChange" 
          method="post">
       {$labels.testproject}
      <select style="font-size: 80%;position:relative; top:-1px;" 
        name="tproject_id" onchange="this.form.submit();">
          {foreach key=item_id item=tproject_name from=$gui->testProjects}
          <option value="{$item_id}" title="{$tproject_name|escape}"
            {if $item_id == $gui->tproject_id} selected="selected" {/if}>
            {$tproject_name|truncate:#TESTPROJECT_TRUNCATE_SIZE#|escape}</option>
        {/foreach}
      </select>
    </form>
  </li>

  {* the place for test plans will be always displayed*}
  <li class="combo">
    <form style="display:inline" name="planForm" 
          target="{$topBrowsingContext}"
          action="index.php?action=planChange" 
          method="post">
       {$labels.testplan}
      <input type="hidden" name="tproject_id" value="{$gui->tproject_id}"> 
      <select style="font-size: 80%;position:relative; top:-1px;" 
          name="tplan_id" onchange="this.form.submit();">
          {foreach key=idx item=tplan from=$gui->testPlans}
           {$planID = $tplan['id']} 
           {$planName = $tplan['name']}
          <option value="{$planID}" title="{$planName|escape}"
            {if $planID == $gui->tplan_id} selected="selected" {/if}>
            {$planName|escape}</option>
        {/foreach}
      </select>
    </form>
  </li>


{/if}

          <li>&nbsp;</li>
          <li><a class="logout" href="{$gui->logout}" target="top">Logout</a></li>

        </ul>
      </div>
    </header>


  <script type="text/javascript" 
    src="{$basehref}{$smarty.const.TL_JQUERY}" 
    language="javascript"></script>


  <script 
    src="{$dashioHome}lib/bootstrap/js/bootstrap.min.js"></script>
  <script src="{$dashioHome}lib/jquery.nicescroll.js" type="text/javascript"></script>
  <script class="include" type="text/javascript" src="{$dashioHome}lib/jquery.dcjqaccordion.2.7.js"></script>
  
  <!--common script for all pages-->
  <script src="{$dashioHome}lib/common-scripts.js"></script>

</body>
</html>