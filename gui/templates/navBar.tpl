{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: navBar.tpl,v 1.5 2006/02/19 13:03:32 schlundus Exp $ *}
{* Purpose: smarty template - title bar + menu *}
{* Andreas Morsing: changed the product selection *}
{* 20050826 - scs - added input for entering tcid *}

{*******************************************************************}
{include file="inc_head.tpl"}
<body>

<div class="tltitle">
	<div style="float: right; margin-right: 5px;">
		<a href="logout.php" target="_parent" accesskey="q">{lang_get s='link_logout'}</a>
	</div>
	<img alt="TestLink icon" src="icons/twist.gif" width="15px" 
			height="15px" style="margin-left: 5px; vertical-align: middle;" />
	<span class="bold">TestLink {$tlVersion|escape} : {$user|escape} 
	{if $productRole  neq null}
	- {lang_get s='product_role'}{$productRole|escape}
	{/if}
	</span>
</div>
<div class="menu">

	{if $arrayProducts ne ""}
	<div style="float: right;">
		<form name="productForm" action="lib/general/navBar.php" method="get"> 
		<span style="font-size: 80%">{lang_get s='product'} </span>
		<select class="tlcombo1" name="product" onchange="this.form.submit();">
			{html_options options=$arrayProducts selected=$currentProduct}
		</select>
		</form>
	</div>
	<div style="float: right;margin-right:5px">
		<form style="display:inline" target="mainframe" name="searchTC" action="lib/testcases/archiveData.php" method="get"> 
		<span style="font-size: 80%">{lang_get s='th_tcid'}: </span>
		<input style="font-size: 75%" type="text" name="data" value="" size="5" maxlength="10"/>
		<input type="hidden" name="edit" value="testcase"/>
		<input type="hidden" name="allow_edit" value="0"/>
		</form>
	</div>
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
      	<a href='documentation/user-manual.html' target="mainframe" 
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