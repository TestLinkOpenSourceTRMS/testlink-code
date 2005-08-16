{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planPriority.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - define priority rules*}
{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_priority'}</h1>

<div class="tabMenu">
	<span class="unselected"><a href="lib/general/frmWorkArea.php?feature=priority">{lang_get s='menu_assign_ownership'}</a></span> 
	<span class="selected">{lang_get s='menu_define_prio'}</span> 
</div>

{include file="inc_update.tpl" result=$sqlResult item="Priority" }

<div class="workBack">

	<form method="post">
	<table class="common" width="40%">
		<tr><th>{lang_get s='tr_th_risk'}</th><th>{lang_get s='tr_th_prio_cba'}</th></tr>
		{section name=Row loop=$arrRules}
		<tr>
			<td>{$arrRules[Row].name|escape}
			<input type="hidden" name="id{$arrRules[Row].id}" value="{$arrRules[Row].id}" />
			</td>
			<td>
			<select name="priority{$arrRules[Row].name|escape}">
				{html_options options=$optionPriority selected=$arrRules[Row].priority}
			</select>
			</td>
		</tr>
		{/section}
	</table>

	<div style="padding: 20px;">
		<input type="submit" name="updatePriorityRules" value="{lang_get s='btn_upd_prio'}" />
	</div>
	</form>

	
</div>

</body>
</html>