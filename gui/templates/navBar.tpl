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
</head>
<body id="tl-container" class="container-fluid"style="min-width: 800px;">
<section class="row">
  <nav class="col-lg-12 col-md-12 col-sm-12 col-xs-12 navbar navbar-default tl-box-main">
  <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a href="index.php" target="_parent" class="navbar-brand">
        <img alt="Company logo" title="logo" src="{$smarty.const.TL_THEME_IMG_DIR}{$tlCfg->logo_navbar}" />
      </a>
    </div>

<<<<<<< HEAD
    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li class="active"><a href='lib/usermanagement/userInfo.php' target="mainframe" accesskey="i" tabindex="6"><img src="{$tlImages.account}" title="{$labels.title_edit_personal_data}"><span class="sr-only">(current)</span></a></li>
        <li><a href="{$gui->logout}" target="_parent" accesskey="q"><img src="{$tlImages.logout}" title="{$labels.link_logout}"></a></li>
      </ul>
      <div class="navbar-form">
        <div class="navbar-left">
        {$session.testprojectTopMenu}
        {if $gui->tprojectID}
          {if $gui->grants->view_testcase_spec == "yes"}
          <div class="form-group">
            <form target="mainframe" name="searchTC" id="searchTC" action="lib/testcases/archiveData.php" method="get">
              <input type="text" size="{$gui->searchSize}" title="{$labels.search_testcase}" name="targetTestCase" value="{$gui->tcasePrefix}" class="form-control" placeholder="Search a testcase" />
              {* useful to avoid a call to method to get test case prefix in called page *}
              <input type="hidden" id="tcasePrefix" name="tcasePrefix" value="{$gui->tcasePrefix}" />
              {* Give a hint to archiveData, will make logic simpler to understand *}
              <input type="hidden" id="caller" name="caller" value="navBar" />
              <img src="{$tlImages.magnifier}" title="{$labels.search_testcase}" alt="{$labels.search_testcase}" onclick="document.getElementById('searchTC').submit()" class="clickable" />
              <input type="hidden" name="edit" value="testcase" />
              <input type="hidden" name="allow_edit" value="0" />
            </form>
          </div>
          {/if}
        {/if}
        </div>
        <div class="navbar-nav navbar-middle">
        {if $gui->tprojectID}
          {if $gui->grants->view_testcase_spec == "yes"}
          <div class="form-group">
            <form target="mainframe" name="fullTextSearch" id="fullTextSearch" action="lib/search/searchMgmt.php" method="post">
              <input type="hidden" name="caller" value="navBar" />
              <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />
              <input id="txtFullSearch" type="text" title="{$labels.full_text_search}" name="target" value="" class="form-control" />
              <img src="{$tlImages.magnifier}" title="{$labels.full_text_search}" alt="{$labels.full_text_search}" onclick="document.getElementById('fullTextSearch').submit()" class="clickable" />
            </form>
          </div>
          {/if}
        {/if}
        </div>
      </div>
      <ul class="nav navbar-nav navbar-right">
        <li>
          <a href="#" class="bold">{$gui->whoami|escape}</a>
        </li>
        {if $gui->TestProjects != ""}
        <li>
          <div style="display: inline; float: right;">
            <form name="productForm" action="lib/general/navBar.php?viewer={$gui->viewer}" method="get">
              <label for="ddTProjects">{$labels.testproject}</label>
              <select id="ddTProjects" class="form-control" name="testproject" onchange="this.form.submit();">
              {foreach key=tproject_id item=tproject_name from=$gui->TestProjects}
                <option value="{$tproject_id}" title="{$tproject_name|escape}"
                {if $tproject_id == $gui->tprojectID}
                  selected="selected"
                {/if}>
                  {$tproject_name|truncate:#TESTPROJECT_TRUNCATE_SIZE#|escape}
                </option>
              {/foreach}
              </select>
            </form>
          </div>
        </li>
        {/if}
      </ul>
    </div><!-- /.navbar-collapse -->
  </nav>

  {if $gui->plugins.EVENT_TITLE_BAR}
  	<div align="center" >
  	{foreach from=$gui->plugins.EVENT_TITLE_BAR item=menu_item}
  	  {$menu_item}
  	{/foreach}
  	</div>
  {/if}

  {if $gui->updateMainPage == 1}
    <script type="text/javascript">
    parent.mainframe.location = "{$basehref}lib/general/mainPage.php";
    </script>
=======
  <span class="bold">{$gui->whoami|escape}</span>
  <span>&nbsp;&nbsp;&nbsp;<a href='lib/usermanagement/userInfo.php' target="mainframe" accesskey="i"
          tabindex="6"><img src="{$tlImages.account}" title="{$labels.title_edit_personal_data}"></a>
        <a href="{$gui->logout}" target="_parent" accesskey="q">
        <img src="{$tlImages.logout}" title="{$labels.link_logout}"></a>
  </span>
</div>

<div class="menu_bar" style="margin: 0px 5px 0px 135px;">
{if $gui->TestProjects != ""}
  <div style="display: inline; float: right;">
    <form style="display:inline" name="productForm" action="lib/general/navBar.php?viewer={$gui->viewer}" method="get">
       {$labels.testproject}
      <select style="font-size: 80%;position:relative; top:-1px;" name="testproject" onchange="this.form.submit();">
          {foreach key=tproject_id item=tproject_name from=$gui->TestProjects}
          <option value="{$tproject_id}" title="{$tproject_name|escape}"
            {if $tproject_id == $gui->tprojectID} selected="selected" {/if}>
            {$tproject_name|truncate:#TESTPROJECT_TRUNCATE_SIZE#|escape}</option>
        {/foreach}
      </select>
    </form>
  </div>
{/if}
{$session.testprojectTopMenu}

{if $gui->tprojectID}
  {if $gui->grants->view_testcase_spec == "yes"}
    <form style="display:inline" target="mainframe" name="searchTC" id="searchTC"
          action="lib/testcases/archiveData.php" method="get">
    <input style="font-size: 80%; position:relative; top:-1px;" type="text" size="{$gui->searchSize}"
           title="{$labels.search_testcase}" name="targetTestCase" value="{$gui->tcasePrefix}" />

      {* useful to avoid a call to method to get test case prefix in called page *}
    <input type="hidden" id="tcasePrefix" name="tcasePrefix" value="{$gui->tcasePrefix}" />

      {* Give a hint to archiveData, will make logic simpler to understand *}
    <input type="hidden" id="caller" name="caller" value="navBar" />
    <img src="{$tlImages.magnifier}"
         title="{$labels.search_testcase}" alt="{$labels.search_testcase}"
         onclick="jQuery('#searchTC').submit()" class="clickable" 
         style="position:relative; top:2px;" />
    <input type="hidden" name="edit" value="testcase"/>
    <input type="hidden" name="allow_edit" value="0"/>
    </form>
  {/if}

  {if $gui->grants->view_testcase_spec == "yes"}
    <form style="display:inline" target="mainframe" name="fullTextSearch" id="fullTextSearch"
          action="lib/search/searchMgmt.php" method="post">
    <input type="hidden" name="caller" value="navBar">
    <input type="hidden" name="tproject_id" value="{$gui->tproject_id}">

    <input style="font-size: 80%; position:relative; top:-1px;" type="text" size="50"
           title="{$labels.full_text_search}" name="target" value="" />

    <img src="{$tlImages.magnifier}"
         title="{$labels.full_text_search}" alt="{$labels.full_text_search}"
         onclick="jQuery('#fullTextSearch').submit()" class="clickable" 
         style="position:relative; top:2px;" />
    </form>
>>>>>>> testlink_1_9
  {/if}
</section>
</body>
</html>