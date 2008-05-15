{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: printDocOptions.tpl,v 1.3 2008/05/15 14:45:17 havlat Exp $ *}
{* Purpose: smarty template - show test specification tree *}
{include file="inc_head.tpl" jsTree="yes"}
<body>
<h1 class="title">{$title|escape}</h1>

<div style="margin: 10px;">
<form method="post" action="lib/results/printDocument.php?type={$type}">

	<table class="smallGrey" >
		<caption>{lang_get s='caption_print_opt'}
				{include file="inc_help.tpl" helptopic="hlp_generateDocOptions"}
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
	</table>
</form>
</div>

<div class="tree" name="treeMenu" id="treeMenu">
	{$tree}
</div>
<br />

</body>
</html>
