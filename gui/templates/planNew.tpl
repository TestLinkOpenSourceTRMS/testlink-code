{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planNew.tpl,v 1.5 2005/11/21 07:02:24 franciscom Exp $

Purpose: smarty template - create Test Plan

20050528 - fm - I18N  
20050824 - scs - changed item to TestPlan 
20051120 - fm - added product name info

*}

{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_test_plan'}</h1>

<div class="tabMenu">
	<span class="selected">{lang_get s='menu_create'}</span> 
	<span class="unselected"><a href="lib/plan/planEdit.php">{lang_get s='menu_edit_del'}</a></span> 
</div>

{include file="inc_update.tpl" result=$sqlResult item="TestPlan" action="add"}

<div class="workBack">

	<form method="post">

	<table class="common" width="75%">
	  {* 20051120 - fm *}
		<caption>{lang_get s='caption_new_tp'} - {lang_get s='caption_for_product'} {$prod_name}</caption>
		
		
		<tr>
			<td width="40%">{lang_get s='caption_tp_name'}</td>
			<td><input type="text" name="name" maxlength="200" /></td>
		</tr>
		<tr>
			<td>{lang_get s='caption_tp_notes'}</td>
			<td><input type="text" name="notes" size="50" /></td>
		</tr>
		<tr>
			<td>{lang_get s='question_create_tp_from'}</td>
			<td>
				<select name="copy">
				<option value="noCopy">{lang_get s='opt_no'}</option>
				{section name=number loop=$arrPlan}
					<option value="{$arrPlan[number][0]}">{$arrPlan[number][1]|escape}</option>
				{/section}
				</select>
			</td>
		<tr>
			<td>{lang_get s='question_want_rights'}</td>
			<td><input type="checkbox" name="rights" checked="checked" />{lang_get s='say_yes'}</td>
		</tr>

	</table>

	<div style="padding: 20px;">
		<input type="submit" name="newTestPlan" value="{lang_get s='btn_new'}" />
	</div>

	</form>
	
</div>

</body>
</html>