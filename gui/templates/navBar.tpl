{* Testlink Open Source Project - http://testlink.sourceforge.net/ 
$Id: navBar.tpl,v 1.11 2006/03/13 19:19:54 schlundus Exp $ 
Purpose: smarty template - title bar + menu 

20060226 - franciscom - logo
20060224 - franciscom - productRole -> testprojectRole
Andreas Morsing: changed the product selection 
20050826 - scs - added input for entering tcid 

*}

{*******************************************************************}
{include file="inc_head.tpl"}
<body>

<div class="tltitle">
	<div style="width:100%;">
		{$logo}
		<div class="bold" style="float:left;padding-left:5px;width:49%;display:inline">TestLink {$tlVersion|escape} : {$user|escape}
		{if $testprojectRole  neq null}	
			- {lang_get s='product_role'}{$testprojectRole|escape}
		{/if}
		</div>
		<div style="text-align:right;"><a style="width:100%;padding-right:5px;" href="logout.php" target="_parent" accesskey="q">{lang_get s='link_logout'}</a></div>
	</div>
</div>
<div class="menu">

	{if $arrayProducts ne ""}
	<div style="float: right;">
		<form name="productForm" action="lib/general/navBar.php" method="get"> 
		<span style="font-size: 80%">{lang_get s='product'} </span>
		{* 20060224 - franciscom - name="product" *}
		<select class="tlcombo1" name="testproject" onchange="this.form.submit();">
			{html_options options=$arrayProducts selected=$currentProduct}
		</select>
		</form>
	</div>
	{if $view_tc_rights ne ""}
	<div style="float: right;margin-right:5px">
		<form style="display:inline" target="mainframe" name="searchTC" action="lib/testcases/archiveData.php" method="get"> 
		<span style="font-size: 80%">{lang_get s='th_tcid'}: </span>
		<input style="font-size: 75%" type="text" name="data" value="" size="5" maxlength="10"/>
		<input type="hidden" name="edit" value="testcase"/>
		<input type="hidden" name="allow_edit" value="0"/>
		</form>
	</div>
	{/if}
	{/if}
	
	<div style="padding: 2px;">
      	<a href="index.php" target="_parent" accesskey="h" tabindex="1">{lang_get s='home'}</a> | 
      	{if $rightViewSpec == "yes"}
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
      	<a href='lib/user/userInfo.php' target="mainframe" accesskey="i" 
      		tabindex="5">{lang_get s='title_edit_personal_data'}</a> | 
      	<a href='documentation/TL1_6-user-manual.html' target="mainframe" 
      		tabindex="6">{lang_get s='title_documentation'}</a>
    </div>

</div>
{if $updateMainPage == 1}
{literal}
<script type="text/javascript">
	parent.mainframe.location = parent.mainframe.location;
</script>
{/literal}
{/if}

</body>
</html>