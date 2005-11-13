{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planOwner.tpl,v 1.3 2005/11/13 19:19:31 schlundus Exp $ *}
{* Purpose: smarty template - assign ownership and priority *}
{* 20050514 - fm: I18N*}
{* 20051112 - scs - changed item to 'TestSuite', TestSuite name wasn't not escaped *}

{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_plan_ownership'}</h1>

<div class="tabMenu">
	<span class="selected">{lang_get s='assign_ownership'}</span> 
	<span class="unselected"><a href="lib/plan/planPriority.php" 
		target="_parent">{lang_get s='def_prio_rules'}</a></span> 
</div>

{include file="inc_update.tpl" result=$sqlResult item="TestSuite" }

<div class="workBack">

	{section name=Row loop=$arrSuites}
	<hr />
	<form method="post">
	<table class="common" width="75%">
		<tr>
      <th>{lang_get s='th_test_suite'}</th>
      <th>{lang_get s='th_imp'}</th>
      <th>{lang_get s='th_risk'}</th>
			<th>{lang_get s='th_owner'}</th>
		</tr>
		<tr>
			<td>{$arrSuites[Row].name|escape}</td>
			<td><select name="importance">
					{html_options options=$optionImportance selected=$arrSuites[Row].importance}
				</select></td>
			<td><select name="risk">
				{html_options options=$optionRisk selected=$arrSuites[Row].risk}
				</select>
			</td>
			<td><select name="owner">
					<option value="none">{lang_get s='opt_label_none'}</option>
					{html_options values=$arrUsers output=$arrUsers selected=$arrSuites[Row].owner}
				</select>
			</td>
		</tr>
	</table>

	<div style="padding: 20px;">
		<input type="hidden" name="id" value="{$arrSuites[Row].id}" />
		<input type="submit" name="updateSuiteAttribute" value="{lang_get s='btn_upd'}">
	</div>
	</form>
	{/section}

	
</div>

</body>
</html>