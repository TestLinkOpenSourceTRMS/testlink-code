{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planPriority.tpl,v 1.1 2007/12/02 17:03:00 franciscom Exp $ 
Purpose: smarty template - define priority rules

20060908 - franciscom - added test plan name to title
                        removed action tabs
                        used array flavour for inputs
*}
{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='test_plan'}{$smarty.const.TITLE_SEP}{$testplan_name|escape}</h1>

<div class="workBack">
{include file="inc_update.tpl" result=$sqlResult item="Priority" }
<h1>{lang_get s='title_priority'}</h1>


	<form method="post">
	<table class="common" width="40%">
		<tr>
		    <th>{lang_get s='tr_th_risk'}</th>
		    <th>{lang_get s='tr_th_importance'}</th>
		    <th>{lang_get s='tr_th_prio_cba'}</th>
		</tr>
	  {foreach item=risk_imp from=$rip_rules}
		<tr>
			<td>{$risk_imp.risk_verbose|escape}</td>
			<td>{$risk_imp.importance_verbose|escape}</td>
			
			<td>
			<select name="priority[{$risk_imp.id}]">
				{html_options options=$optionPriority selected=$risk_imp.priority}
			</select>
			</td>
		</tr>
		{/foreach}
	</table>

	<div style="padding: 20px;">
		<input type="submit" name="updatePriorityRules" value="{lang_get s='btn_upd_prio'}" />
	</div>
	</form>
</div>
</body>
</html>