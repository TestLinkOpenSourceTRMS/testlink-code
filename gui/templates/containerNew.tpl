{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerNew.tpl,v 1.6 2005/08/29 11:13:46 schlundus Exp $ *}
{* Purpose: smarty template - create containers *}
{* I18N: 20050528 - fm *}
{*
  20050824 - fm
  htmlarea replaced with fckedit
  data -> componentID and ProductID
  
	20050821 - scs - changed p-tags to div-tags because there arent alloed for htmlarea
	lang_get('component');
	lang_get('category');
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">

<h1>{lang_get s='title_create'}  {$level|escape}</h1>
	
{include file="inc_update.tpl" result=$sqlResult 
                               item=$level action="add" name=$name
                               refresh="yes"}

{if $level eq "category"}
	<form method="post" action="lib/testcases/containerEdit.php?componentID={$containerID}">
		<div class="bold">
			<div style="float: right;">
				<input type="submit" name="addCAT" value="{lang_get s='btn_create_cat'}" />
			</div>	
			{include file="inc_cat_viewer_rw.tpl"}
	</form>

{elseif $level == 'component'}
	
	<form method="post" action="lib/testcases/containerEdit.php?productID={$containerID}">
		<div style="font-weight: bold;">
			<div style="float: right;">
				<input type="submit" name="addCOM" value="{lang_get s='btn_create_comp'}" />
			</div>	
			{include file="inc_comp_viewer_rw.tpl"}
    </div>
	</form>
	
{/if}	

</div>

</body>
</html>