{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planPriority.tpl,v 1.4 2006/09/09 07:11:27 franciscom Exp $ 
Purpose: smarty template - define priority rules

20060908 - franciscom - added test plan name to title
                        removed action tabs
                        used array flavour for inputs
*}
{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='title_priority'} {$testplan_name}</h1>
{include file="inc_update.tpl" result=$sqlResult item="Priority" }

<div class="workBack">
	<form method="post">
	<table class="common" width="40%">
		<tr><th>{lang_get s='tr_th_risk'}</th><th>{lang_get s='tr_th_prio_cba'}</th></tr>
		{section name=Row loop=$arrRules}
		<tr>
			<td>{$arrRules[Row].risk_importance|escape}</td>
			<td>
			<select name="priority[{$arrRules[Row].id}]">
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