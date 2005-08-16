{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planTesters.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - show users/plan for assignment *}
{* I18N: 20050528 - fm *}

{include file="inc_head.tpl"}
{include file="inc_jsCheckboxes.tpl"}

<body>

<h1>{$title|escape}</h1>

<div class="workBack">

{* menu for users or plan assignment *}
<form method="post">
	<div>
		<input type="submit" name="submit" value="{lang_get s='btn_save'}" style="margin: 5px;" />
		<input type="button" name="foo" onclick='javascript: box("checkingBlock", true);' 
				value="Check All" style="margin: 5px;" />
		<input type="button" name="foo" onclick='javascript: box("checkingBlock", false);' 
				value="{lang_get s='btn_uncheck_all'}" style="margin: 5px;" />
	</div>
	<div id="checkingBlock">
	<table>
	{section name=Row loop=$arrData}
		<tr>
			<td>
				{$arrData[Row].name|escape}
			</td>
			<td>
				<input type="checkbox" name="{$arrData[Row].id}" 
						value="{$arrData[Row].id}" {$arrData[Row].checked} />
			</td>
		</tr>
	{/section}
	</table>
	</div>
</form>


</div>

</body>
</html>