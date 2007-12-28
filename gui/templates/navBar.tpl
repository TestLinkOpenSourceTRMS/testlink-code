{* 
	Testlink Open Source Project - http://testlink.sourceforge.net/ 
	$Id: navBar.tpl,v 1.23 2007/12/28 18:55:04 schlundus Exp $ 
	Purpose: smarty template - title bar + menu 
	
	rev :
	     20070331 - BUGID 760 - added truncate to fix
*}

{*******************************************************************}
{include file="inc_head.tpl"}
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<body>
<div style="float:left;">{$logo}</div>

<div class="menu_title">

	{if $arrayProducts ne ""}
	<div style="float: right; padding: 2px;">
		<form name="productForm" action="lib/general/navBar.php" method="get"> 
		<span style="font-size: 80%">{lang_get s='product'} </span>
		<select class="menu_combo" name="testproject" onchange="this.form.submit();">
      	{foreach key=tp_id item=tp_name from=$arrayProducts}
  		  <option value="{$tp_id}" title="{$tp_name|escape}"
  		    {if $tp_id == $currentProduct} selected="selected" {/if}>
  		    {$tp_name|truncate:#TESTPROJECT_TRUNCATE_SIZE#|escape}</option>
  		{/foreach}
		</select>
		{if $countProjects == 1}
			<input type="button" value="{lang_get s='btn_ok'}" onclick="this.form.submit();"/>
		{/if}
		</form>
	</div>
	{/if}

	<div class="bold" style="padding: 5px 10px 5px 25px;">TestLink {$tlVersion|escape} : {$user|escape}
	{if $testprojectRole  neq null}	
		- {lang_get s='product_role'} {$testprojectRole|escape}
	{/if}
	</div>

</div>

<div class="menu_bar">
   	<a href="index.php" target="_parent" accesskey="h" tabindex="1">{lang_get s='home'}</a> | 
   	{if $currentTProjectID && $rightViewSpec == "yes"}
   	<a href="lib/general/frmWorkArea.php?feature=editTc" target="mainframe" accesskey="s" 
      		tabindex="2">{lang_get s='title_specification'}</a> | 
   	{/if}	
   	{if $rightExecute == "yes" and $countPlans > 0}
   	<a href="lib/general/frmWorkArea.php?feature=executeTest" target="mainframe" accesskey="e" 
     		tabindex="3">{lang_get s='title_execute'}</a> | 
   	{/if}	
   	{if $rightMetrics == "yes" and $countPlans > 0}
   	<a href="lib/general/frmWorkArea.php?feature=showMetrics" target="mainframe" accesskey="r" 
      		tabindex="3">{lang_get s='title_results'}</a> | 
   	{/if}	
   	{if $rightUserAdmin == "yes"}
   	<a href="lib/usermanagement/usersedit.php" target="mainframe" accesskey="u" 
      		tabindex="4">{lang_get s='title_user_mgmt'}</a> | 
   	{/if}	
   	<a href='lib/usermanagement/userinfo.php' target="mainframe" accesskey="i" 
      		tabindex="5">{lang_get s='title_edit_personal_data'}</a> |
	{if $currentTProjectID && $rightViewSpec == "yes"}
		<form style="display:inline" target="mainframe" name="searchTC" id="searchTC"
		      action="lib/testcases/archiveData.php" method="get"> 
		<span style="font-size: 80%">{lang_get s='th_tcid'}: </span>
		<input style="font-size: 80%; width: 50px;" type="text" 
		       title="{lang_get s='search_testcase'}" name="id" value="" /> 
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
