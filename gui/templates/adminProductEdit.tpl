{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: adminProductEdit.tpl,v 1.3 2005/12/09 10:04:33 franciscom Exp $ *}
{* Purpose: smarty template - Edit existing product *}
{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsPicker.tpl"}
</head>
<body>

{* productName -> name *}
<h1>{lang_get s='title_product_mgmt'} - {$name|escape}</h1>

{* tabs *}
<div class="tabMenu">
	<span class="unselected"><a href="lib/admin/adminProductNew.php">{lang_get s='btn_create'}</a></span> 
	<span class="selected">{lang_get s='btn_edit_del'}</span>
</div>

	{if $action == "activate" || $action == "inactivate"}
		<div class="info">{$sqlResult}</div>
	{elseif $action == "updated"}
		{include file="inc_update.tpl" result=$sqlResult item="Product" name=$name}
	{/if}

<div class="workBack">

{if $action == "delete"}
	{$sqlResult}
{else}

	{* edit product form *}
	{if $founded == "yes"}
		<div>
		<form name="editProduct" method="post" action="lib/admin/adminProductEdit.php">

		<input type="hidden" name="id" value="{$id}" />
  	{* 20051208 - fm - same width taht adminProductNew.tpl *}
		<table class="common" width="50%">
		  {* 20051208 - fm #{$id} -> {$name} *} 
			<caption>{lang_get s='caption_edit_product'} {$name}</caption>
			<tr>
				<td>{lang_get s='name'}</td>
				<td><input type="text" name="name" value="{$name|escape}" maxlength="100" /></td>
			</tr>
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
			{lang_get s='info_failed_loc_prod'} {$name|escape}!<br>
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