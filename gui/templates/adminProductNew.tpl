{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: adminProductNew.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - Add a new product *}
{* @author Andreas Morsing - changed reload *}
{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsPicker.tpl"}
</head>
<body>

<h1>{lang_get s='title_product_mgmt'}</h1>

{* tabs *}
<div class="tabMenu">
	<span class="selected">{lang_get s='btn_create'}</span> 
	<span class="unselected"><a href="lib/admin/adminProductEdit.php">{lang_get s='btn_edit_del'}</a></span>
</div>

{* product was added *}
{if $sqlResult ne ""}

	{include file="inc_update.tpl" result=$sqlResult item="Product" action="add" name=$name}

	{* reload menubar *}
	<script type="text/javascript">
		parent.titlebar.location = parent.titlebar.location;
	</script>
{/if}

<div class="workBack">

{* new user form *}
<div>
<form method="post" name="createProduct">
<table class="common" width="50%">
	<caption>{lang_get s='caption_new_product'}</caption>
	<tr>
		<td>{lang_get s='name'}</td>
		<td><input type="text" name="name" maxlength="100" /></td>
	</tr>
	<tr>
		<td>{lang_get s='color'}</td>
		<td>
			<input type="text" name="color" value="{$defaultColor}" maxlength="12" />
			{* this function below calls the color picker javascript function. 
			It can be found in the color directory *}
			<a href="javascript: TCP.popup(document.forms['createProduct'].elements['color'], '{$basehref}third_party/color_picker/picker.html');">
			<img width="15" height="13" border="0" alt="{lang_get s='alt_pick_up_color'}" 
				src="third_party/color_picker/img/sel.gif" />
			</a>
		</td>
	</tr>
	<tr>
		<td>{lang_get s='enable_requirements'}</td>
		<td><select name="optReq">
			{html_options options=$option_yes_no selected="0"}
		</select></td>
	</tr>
</table>
<div class="groupBtn">
	<input type="submit" name="newProduct" value="{lang_get s='btn_create'}" />
</div>
</form>
</div>

</div>

</body>
</html>