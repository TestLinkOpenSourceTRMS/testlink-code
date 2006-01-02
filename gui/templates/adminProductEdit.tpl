{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: adminProductEdit.tpl,v 1.5 2006/01/02 14:05:59 franciscom Exp $
Purpose: smarty template - Edit existing product 

@author Francisco Mancardi - 20051211 - poor workaround for BUGID 180 Unable to delete Product
*}
{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsPicker.tpl"}
</head>
<body>

{* 20051211 - fm - deleted $name as additional workaround for BUG 180}
{* productName -> name 
<h1>{lang_get s='title_product_mgmt'} - {$name|escape}</h1>
*}
<h1>{lang_get s='title_product_mgmt'}</h1>


{* tabs *}
<div class="tabMenu">
	<span class="unselected"><a href="lib/admin/adminProductNew.php">{lang_get s='btn_create'}</a></span> 
	<span class="selected">{lang_get s='btn_edit_del'}</span>
</div>

	{if $action == "activate" || $action == "inactivate"}
		<div class="info">{$sqlResult} - {$action}</div>
	{elseif $action == "updated"}
		{include file="inc_update.tpl" result=$sqlResult item="Product" name=$name}
	{/if}

<div class="workBack">

{if $action == "delete"}
	{$sqlResult}
{/if}
	
{if $show_prod_attributes == "yes"}

	{* edit product form *}
	{if $found == "yes"}
		<div>
		<form name="editProduct" method="post" action="lib/admin/adminProductEdit.php">

		<input type="hidden" name="id" value="{$id}" />
  	{* 20051208 - fm - same width taht adminProductNew.tpl *}
		<table class="common" width="80%">
		  {* 20051208 - fm #{$id} -> {$name} *} 
			<caption>{lang_get s='caption_edit_product'} {$name}</caption>
			<tr>
				<td>{lang_get s='name'}</td>
				<td><input type="text" name="name" value="{$name|escape}" maxlength="100" /></td>
			</tr>
     {* ---------------------------------------------------------------- *}
     {* 20060101 - fm *}
	   <tr>
		  <td>{lang_get s='notes'}</td>
		  <td width="80%">{$notes}</td>
	   </tr>
    {* ---------------------------------------------------------------- *}
			<tr>
				<td>{lang_get s='color'}</td>
				<td>
					<input type="text" name="color" value="{$color|escape}" maxlength="12" />
					{* this function below calls the color picker javascript function. 
					It can be found in the color directory *}
					<a href="javascript: TCP.popup(document.forms['editProduct'].elements['color'], '{$basehref}third_party/color_picker/picker.html');">
						<img width="15" height="13" border="0" alt="Click Here to Pick up the color" 
						src="third_party/color_picker/img/sel.gif" />
					</a>
				</td>
			</tr>
			<tr>
				<td>{lang_get s='enable_requirements'}</td>
				<td><select name="optReq">
				{html_options options=$option_yes_no selected=$reqs_default}
				</select></td>
			</tr>
	
		</table>
		<div class="groupBtn">
			<input type="submit" name="editProduct" value="{lang_get s='btn_upd'}" />
			{if $active == '1'}
			<input type="submit" name="inactivateProduct" value="{lang_get s='btn_inactivate'}" />
			{else}
			<input type="submit" name="activateProduct" value="{lang_get s='btn_activate'}" />
			{/if}
			<input type="button" name="deleteProduct" value="{lang_get s='btn_del'}" 
				onclick="javascript:; if (confirm('{lang_get s="popup_product_delete"}'))
				{ldelim}location.href=fRoot+'lib/admin/adminProductEdit.php?deleteProduct=&id={$id}&name={$name|escape:"url"}';
				{rdelim};" />
		</div>

		</form>
	</div>
	{else}
		<p class="info">
		{if $name neq ''}
			{lang_get s='info_failed_loc_prod----'} {$name|escape}!<br>
		{/if}
		{lang_get s='invalid_query'}: {$sqlResult|escape}<p>
	{/if}

{/if}
</div>

{if $action != "no"}
	{* this renews menu bar after change *}
	<script type="text/javascript">parent.titlebar.location.reload();</script>
{/if}

</body>
</html>