{* 
	Testlink Open Source Project - http://testlink.sourceforge.net/ 
	$Id: navBar.tpl,v 1.33 2008/04/25 17:49:23 franciscom Exp $ 
	Purpose: smarty template - title bar + menu 
	
	rev :
	     20080211 - changes action for user management
	     20070331 - BUGID 760 - added truncate to fix
*}

{*******************************************************************}
{lang_get var="labels"
          s="event_viewer,home,testproject,title_specification,title_execute,
             title_edit_personal_data,th_tcid,link_logout,
             search_testcase,title_results,title_user_mgmt"}

{include file="inc_head.tpl"}
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{assign var="action_users_view" value="lib/usermanagement/usersView.php"}
{assign var="action_user_create" value="lib/usermanagement/usersEdit.php?doAction=create"}
{assign var="action_user_mgmt" value=$action_users_view}

<body>
<div style="float:left; height: 100%"><a href="index.php" target="_parent">{$gui->logo}</a></div>

<div class="menu_title">

	{if $gui->TestProjects != ""}
	<div style="float: right; padding: 2px;">
		<form name="productForm" action="lib/general/navBar.php" method="get"> 
		<span style="font-size: 80%">{$labels.testproject} </span>
		<select class="menu_combo" name="testproject" onchange="this.form.submit();">
      	{foreach key=tproject_id item=tproject_name from=$gui->TestProjects}
  		  <option value="{$tproject_id}" title="{$tproject_name|escape}"
  		    {if $tproject_id == $gui->tprojectID} selected="selected" {/if}>
  		    {$tproject_name|truncate:#TESTPROJECT_TRUNCATE_SIZE#|escape}</option>
  		{/foreach}
		</select>
		</form>
	</div>
	{/if}

	<div class="bold" style="padding: 5px 10px 5px 25px;">
		TestLink {$tlVersion|escape} : {$gui->whoami|escape}
	</div>

</div>

<div class="menu_bar">
   	<a href="index.php" target="_parent" accesskey="h" tabindex="1">{$labels.home}</a> | 
   	{if $gui->tprojectID && $gui->grants->view_testcase_spec == "yes"}
   	<a href="lib/general/frmWorkArea.php?feature=editTc" target="mainframe" accesskey="s" 
      		tabindex="2">{$labels.title_specification}</a> | 
   	{/if}	
   	{if $gui->grants->testplan_execute == "yes" and $gui->TestPlanCount > 0}
   	<a href="lib/general/frmWorkArea.php?feature=executeTest" target="mainframe" accesskey="e" 
     		tabindex="3">{$labels.title_execute}</a> | 
   	{/if}	
   	{if $gui->grants->testplan_metrics == "yes" and $gui->TestPlanCount > 0}
   	<a href="lib/general/frmWorkArea.php?feature=showMetrics" target="mainframe" accesskey="r" 
      		tabindex="3">{$labels.title_results}</a> | 
   	{/if}	
   	{if $gui->grants->user_mgmt == "yes"}
   	<a href="{$action_user_mgmt}" target="mainframe" accesskey="u" 
      		tabindex="4">{$labels.title_user_mgmt}</a> | 
   	{/if}	
	{if $gui->grants->view_events_mgmt eq "yes"}
		<a href="lib/events/eventviewer.php" target="mainframe" 
		   accesskey="v" tabindex="5">{$labels.event_viewer}</a> |
	{/if}
   	<a href='lib/usermanagement/userInfo.php' target="mainframe" accesskey="i" 
      		tabindex="6">{$labels.title_edit_personal_data}</a> |

	{if $gui->tprojectID && $gui->grants->view_testcase_spec == "yes"}
		<form style="display:inline" target="mainframe" name="searchTC" id="searchTC"
		      action="lib/testcases/archiveData.php" method="get"> 
		<span style="font-size: 80%">{$labels.th_tcid}: </span>
		<input style="font-size: 80%; width: 50px;" type="text" 
		       title="{$labels.search_testcase}" name="targetTestCase" value="" /> 
		<img src="{$smarty.const.TL_THEME_IMG_DIR}/magnifier.png" 
		     title="{$labels.search_testcase}" alt="{$labels.search_testcase}"
		     onclick="document.getElementById('searchTC').submit()" /> | 
		<input type="hidden" name="edit" value="testcase"/>
		<input type="hidden" name="allow_edit" value="0"/>
		</form>
	{/if}
	<a href="logout.php" target="_parent" accesskey="q">{$labels.link_logout}</a>
</div>

{if $gui->updateMainPage == 1}
{literal}
<script type="text/javascript">
	parent.mainframe.location = "{/literal}{$basehref}{literal}lib/general/mainPage.php";
</script>
{/literal}
{/if}

</body>
</html>
