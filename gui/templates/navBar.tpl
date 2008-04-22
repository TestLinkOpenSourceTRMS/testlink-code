{* 
	Testlink Open Source Project - http://testlink.sourceforge.net/ 
	$Id: navBar.tpl,v 1.32 2008/04/22 07:01:54 franciscom Exp $ 
	Purpose: smarty template - title bar + menu 
	
	rev :
	     20080211 - changes action for user management
	     20070331 - BUGID 760 - added truncate to fix
*}

{*******************************************************************}
{lang_get var="labels"
          s="testproject,title_specification,title_execute,
             search_testcase,title_results,title_user_mgmt"}

{include file="inc_head.tpl"}
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{assign var="action_users_view" value="lib/usermanagement/usersView.php"}
{assign var="action_user_create" value="lib/usermanagement/usersEdit.php?doAction=create"}
{assign var="action_user_mgmt" value=$action_users_view}

<body>
<div style="float:left; height: 100%"><a href="index.php" target="_parent">{$logo}</a></div>

<div class="menu_title">

	{if $arrayProducts ne ""}
	<div style="float: right; padding: 2px;">
		<form name="productForm" action="lib/general/navBar.php" method="get"> 
		<span style="font-size: 80%">{$labels.testproject} </span>
		<select class="menu_combo" name="testproject" onchange="this.form.submit();">
      	{foreach key=tp_id item=tp_name from=$arrayProducts}
  		  <option value="{$tp_id}" title="{$tp_name|escape}"
  		    {if $tp_id == $currentProduct} selected="selected" {/if}>
  		    {$tp_name|truncate:#TESTPROJECT_TRUNCATE_SIZE#|escape}</option>
  		{/foreach}
		</select>
		</form>
	</div>
	{/if}

	<div class="bold" style="padding: 5px 10px 5px 25px;">
		TestLink {$tlVersion|escape} : {$user|escape}
	</div>

</div>

<div class="menu_bar">
   	<a href="index.php" target="_parent" accesskey="h" tabindex="1">{lang_get s='home'}</a> | 
   	{if $currentTProjectID && $rightViewSpec == "yes"}
   	<a href="lib/general/frmWorkArea.php?feature=editTc" target="mainframe" accesskey="s" 
      		tabindex="2">{$labels.title_specification}</a> | 
   	{/if}	
   	{if $rightExecute == "yes" and $countPlans > 0}
   	<a href="lib/general/frmWorkArea.php?feature=executeTest" target="mainframe" accesskey="e" 
     		tabindex="3">{$labels.title_execute}</a> | 
   	{/if}	
   	{if $rightMetrics == "yes" and $countPlans > 0}
   	<a href="lib/general/frmWorkArea.php?feature=showMetrics" target="mainframe" accesskey="r" 
      		tabindex="3">{$labels.title_results}</a> | 
   	{/if}	
   	{if $rightUserAdmin == "yes"}
   	<a href="{$action_user_mgmt}" target="mainframe" accesskey="u" 
      		tabindex="4">{$labels.title_user_mgmt}</a> | 
   	{/if}	
	{if $rights_mgt_view_events eq "yes"}
		<a href="lib/events/eventviewer.php" target="mainframe" accesskey="v" tabindex="5">{lang_get s='event_viewer'}</a> |
	{/if}
   	<a href='lib/usermanagement/userInfo.php' target="mainframe" accesskey="i" 
      		tabindex="6">{lang_get s='title_edit_personal_data'}</a> |
	{if $currentTProjectID && $rightViewSpec == "yes"}
		<form style="display:inline" target="mainframe" name="searchTC" id="searchTC"
		      action="lib/testcases/archiveData.php" method="get"> 
		<span style="font-size: 80%">{lang_get s='th_tcid'}: </span>
		<input style="font-size: 80%; width: 50px;" type="text" 
		       title="{$labels.search_testcase}" name="targetTestCase" value="" /> 
		<img src="{$smarty.const.TL_THEME_IMG_DIR}/magnifier.png" 
		     title="{lang_get s='search_testcase'}"
			 alt="{lang_get s='search_testcase'}"
		     onclick="document.getElementById('searchTC').submit()" /> | 
		<input type="hidden" name="edit" value="testcase"/>
		<input type="hidden" name="allow_edit" value="0"/>
		</form>
	{/if}
	<a href="logout.php" target="_parent" accesskey="q">{lang_get s='link_logout'}</a>
</div>

{if $updateMainPage == 1}
{literal}
<script type="text/javascript">
	parent.mainframe.location = "{/literal}{$basehref}{literal}lib/general/mainPage.php";
</script>
{/literal}
{/if}

</body>
</html>
