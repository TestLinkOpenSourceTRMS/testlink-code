{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planNew.tpl,v 1.8 2006/01/14 17:47:54 schlundus Exp $

Purpose: smarty template - create Test Plan

20050528 - fm - I18N  
20050824 - scs - changed item to TestPlan 
20051120 - fm - added product name info
20051121 - scs - added escaping of product name
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

	<form method="post" action="lib/plan/planNew.php">
	<input type="hidden" name="tpID" value="{$tpID}">
	<table class="common" width="80%">
	  {* 20051120 - fm *}
		<caption>
		{if $tpID eq 0}
			{lang_get s='caption_new_tp'} 
		{else}
			{lang_get s='caption_edit_tp'} 
		{/if}
		- {lang_get s='caption_for_product'} {$prod_name|escape}</caption>
		<tr>
			<td width="30%">{lang_get s='caption_tp_name'}</td>
			<td><input type="text" name="name" maxlength="100" value="{$tpName|escape}"/></td>
		</tr>
		<tr>
			<td>{lang_get s='caption_tp_notes'}</td>
			{* <td><input type="text" name="notes" size="50" /></td> *}
			<td >{$notes}</td>
		</tr>
		{if $tpID eq 0}
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
		{else}
		<tr>
			<td>{lang_get s='th_active'}:</td>
			<td>
				<input type="checkbox" name="active" 
				{if $tpActive eq 1}
					checked="checked"
				{/if}
				/>
      		</td>
		</tr>
		{/if}
	</table>

	<div class="groupBtn">	
		{if $tpID eq 0}
			<input type="submit" name="newTestPlan" value="{lang_get s='btn_new'}" />
		{else}
			<input type="submit" name="editTestPlan" value="{lang_get s='btn_edit'}" />
		{/if}
	</div>

	</form>
	
</div>

</body>
</html>