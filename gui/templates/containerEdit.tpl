{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerEdit.tpl,v 1.8 2005/10/10 19:18:23 schlundus Exp $ *}
{* Purpose: smarty template - edit test specification: containers *}
{* Note: htmlarea cannot be within tag <p> *}
{*
	20050823 - scs - localized title
			lang_get('component');
			lang_get('category');
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">

<h1>{lang_get s='title_edit_level'} {$level}</h1> 

{if $level == 'category'}
	<form method="post" action="lib/testcases/containerEdit.php?categoryID={$containerID}" /> 
		<div style="float: right;">
			<input type="submit" name="updateCat" value="{lang_get s='btn_update_cat'}" />
		</div>
   {include file="inc_cat_viewer_rw.tpl"}

	</form>

{elseif $level == "component"}
	<form method="post" action="lib/testcases/containerEdit.php?componentID={$containerID}" /> 
		<div style="float: right;">
			<input type="submit" name="updateCOM" value="Update" />
		</div>

   {include file="inc_comp_viewer_rw.tpl"}
	</form>

{/if}

</div>

</body>
</html>