{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: adminProductNew.tpl,v 1.3 2006/01/02 14:05:59 franciscom Exp $ *}
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
<table class="common" width="80%">
	<caption>{lang_get s='caption_new_product'}</caption>
	<tr>
		<td>{lang_get s='name'}</td>
		<td><input type="text" name="name" size="100" maxlength="100" /></td>
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