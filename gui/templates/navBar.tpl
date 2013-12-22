{*
Testlink Open Source Project - http://testlink.sourceforge.net/

title bar + menu

@filesource navBar.tpl
@internal revisions
@since 1.9.7

*}
{lang_get var="labels"
          s="title_events,event_viewer,home,testproject,title_specification,title_execute,
             title_edit_personal_data,th_tcid,link_logout,title_admin,
             search_testcase,title_results,title_user_mgmt"}
{assign var="cfg_section" value=$smarty.template|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes"}
</head>
<body style="min-width: 800px;">
<div style="float:left; height: 100%;">
  <a href="index.php" target="_parent">
  <img alt="Company logo" title="logo" src="{$smarty.const.TL_THEME_IMG_DIR}{$tlCfg->logo_navbar}" /></a>
</div>
  
<div class="menu_title">

  <span class="bold">{$gui->whoami|escape}</span>
  <span>[ <a href='lib/usermanagement/userInfo.php' target="mainframe" accesskey="i"
          tabindex="6">{$labels.title_edit_personal_data}</a>
   |  <a href="logout.php" target="_parent" accesskey="q">{$labels.link_logout}</a> ]
  </span>
  <span style="float:right;">TestLink {$tlVersion|escape}</span>
</div>

<div class="menu_bar" style="margin: 0px 5px 0px 135px;">
{if $gui->TestProjects != ""}
  <div style="display: inline; float: right;">
    <form style="display:inline" name="productForm" action="lib/general/navBar.php" method="get">
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
         onclick="document.getElementById('searchTC').submit()" class="clickable" 
         style="position:relative; top:2px;" />
    <input type="hidden" name="edit" value="testcase"/>
    <input type="hidden" name="allow_edit" value="0"/>
    </form>
  {/if}
{/if}
</div>

{if $gui->updateMainPage == 1}
  <script type="text/javascript">
  parent.mainframe.location = "{$basehref}lib/general/mainPage.php";
  </script>
{/if}

</body>
</html>