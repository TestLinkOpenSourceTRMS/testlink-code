{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcPrintNavigator.tpl,v 1.6 2007/01/26 08:11:06 franciscom Exp $ *}
{* Purpose: smarty template - show test specification tree *}
{include file="inc_head.tpl" jsTree="yes"}

<body>

<h1>{$title|escape}</h1>

<div style="margin: 10px;">
<form method="post" action="lib/print/selectData.php?type={$type}">

	<table class="smallGrey" >
		<caption>{lang_get s='caption_print_opt'}
				{include file="inc_help.tpl" filename="printFilter.html" help="printFilter" locale="$locale"}
		</caption>
		{section name=number loop=$arrCheckboxes}
		<tr>
			<td>{$arrCheckboxes[number].description}</td>
			<td><input type="checkbox" name="{$arrCheckboxes[number].value}" id="cb{$arrCheckboxes[number].value}"
			{if $arrCheckboxes[number].checked == 'y'}checked="checked"{/if} 
			/></td>
		</tr>
		{/section}
		<tr>
			<td>{lang_get s='tr_td_show_as'}</td>
			<td><select id="format" name="format">
			{html_options options=$arrFormat selected=$selFormat}
			</select></td>
		</tr>

		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="setPrefs" 
                 value="{lang_get s='btn_set_pref'}" style="font-size: 90%;" /></td>
		</tr>

	</table>
</form>
</div>

<div class="tree" name="treeMenu" id="treeMenu">
	{$tree}
</div>

</body>
</html>
