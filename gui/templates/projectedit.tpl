{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: projectedit.tpl,v 1.9 2007/01/26 21:01:23 schlundus Exp $
Purpose: smarty template - Edit existing product 
*}
{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_jsPicker.tpl"}

{literal}
<script type="text/javascript">
{/literal}
var warning_empty_tproject_name = "{lang_get s='warning_empty_tproject_name'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.tproject_name.value)) 
  {
      alert(warning_empty_tproject_name);
      selectField(f, 'tproject_name');
      return false;
  }
  return true;
}
</script>
{/literal}
</head>



<body>
{config_load file="input_dimensions.conf" section="projectedit"} {* Constant definitions *}

<h1>{lang_get s='title_product_mgmt'}
{if $action != "delete"} - {$name|escape}{/if}
</h1>

{* tabs *}
<div class="tabMenu">
	{if $id neq '-1'}
	<span class="unselected"><a href="lib/project/projectedit.php?show_create_screen">{lang_get s='btn_create'}</a></span> 
	<span class="selected">{lang_get s='btn_edit_del'}</span>
	{else}
	<span class="selected">{lang_get s='btn_create'}</span> 
	<span class="unselected"><a href="lib/project/projectedit.php">{lang_get s='btn_edit_del'}</a></span>
	{/if}

</div>

	{if $action == "activate" || $action == "inactivate"}
		<div class="info">{$sqlResult}</div>
	{else}
		{include file="inc_update.tpl" result=$sqlResult item="product" name=$name}
	{/if}

<div class="workBack">

{if $action == "delete"}
	{$sqlResult}
{/if}
	
{if $show_prod_attributes == "yes"}

	{* edit product form *}
	{if $found == "yes"}
		<div>
		<form name="edit_testproject" id="edit_testproject"
		      method="post" action="lib/project/projectedit.php"
		      onSubmit="javascript:return validateForm(this);">
		      
		<input type="hidden" name="id" value="{$id}" />
		<table class="common" width="80%">
		  {* 20051208 - fm #{$id} -> {$name} *} 
			<caption>
			{if $id neq '-1'}
				{lang_get s='caption_edit_product'} 
			{else}
				{lang_get s='caption_new_product'} 
			{/if}
				{$name|escape}</caption>
			<tr>
				<td>{lang_get s='name'}</td>
				<td><input type="text" name="tproject_name" 
  			           size="{#TESTPROJECT_NAME_SIZE#}" 
	  		           maxlength="{#TESTPROJECT_NAME_MAXLEN#}" 
				           value="{$name|escape}"/>
				  				{include file="error_icon.tpl" field="tproject_name"}
				</td>
			</tr>
	   <tr>
		  <td>{lang_get s='notes'}</td>
		  <td width="80%">{$notes}</td>
	   </tr>
			<tr>
				<td>{lang_get s='color'}</td>
				<td>
					<input type="text" name="color" value="{$color|escape}" maxlength="12" />
					{* this function below calls the color picker javascript function. 
					It can be found in the color directory *}
					<a href="javascript: TCP.popup(document.forms['edit_testproject'].elements['color'], '{$basehref}third_party/color_picker/picker.html');">
						<img width="15" height="13" border="0" alt="Click Here to Pick up the color" 
						src="third_party/color_picker/img/sel.gif" />
					</a>
				</td>
			</tr>
			<tr>
				<td>{lang_get s='enable_requirements'}</td>
				<td>
					<select name="optReq">
					{html_options options=$gsmarty_option_yes_no selected=$reqs_default}
					</select>
				</td>
			</tr>
	
		</table>
		<div class="groupBtn">
		{if $id neq '-1'}
			<input type="submit" name="do_edit" value="{lang_get s='btn_upd'}" />
		{else}
			<input type="submit" name="do_create" value="{lang_get s='btn_create'}" />
		{/if}
		
			{if $id neq '-1'}
				{if $active == '1'}
				<input type="submit" name="inactivateProduct" value="{lang_get s='btn_inactivate'}" />
				{else}
				<input type="submit" name="activateProduct" value="{lang_get s='btn_activate'}" />
				{/if}
				<input type="button" name="do_delete" value="{lang_get s='btn_del'}" 
					onclick="javascript:; if (confirm('{lang_get s="popup_product_delete"}{$name|escape}?'))
					{ldelim}location.href=fRoot+'lib/project/projectedit.php?do_delete=&amp;id={$id}&amp;name={$name|escape:"url"}';
					{rdelim};" />
			{/if}
		</div>

		</form>
	</div>
	{else}
		<p class="info">
		{if $name neq ''}
			{lang_get s='info_failed_loc_prod'} - {$name|escape}!<br />
		{/if}
		{lang_get s='invalid_query'}: {$sqlResult|escape}<p>
	{/if}

{/if}
</div>

{if $action != "no"}
	{* this renews menu bar after change *}
	{if $action == 'delete'}
	<script type="text/javascript">
	top.location = top.location;
	</script>
	{else}
	<script type="text/javascript">
	parent.titlebar.location.reload();
	</script>
	{/if}
{/if}

</body>
</html>
