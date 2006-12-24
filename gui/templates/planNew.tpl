{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planNew.tpl,v 1.12 2006/12/24 11:48:18 franciscom Exp $

Purpose: smarty template - create Test Plan
Revisions:

	20060224 - franciscom - removed the rights check
	20061109 - mht - update for TL1.7; GUI update
	20061223 - franciscom - utilizzo input_dimensions.conf
*}

{include file="inc_head.tpl"}

<body>
{config_load file="input_dimensions.conf" section="planNew"} {* Constant definitions *}

<h1>{lang_get s='testplan_title_tp_management'}</h1>

<div class="tabMenu">
	{if $tpID eq 0}
		<span class="selected">{lang_get s='testplan_menu_create'}</span> 
	{else}
		<span class="unselected"><a href="lib/plan/planEdit.php?action=empty">{lang_get s='testplan_menu_create'}</a></span> 
		<span class="selected">{lang_get s='testplan_menu_edit'}</span> 
	{/if}
	<span class="unselected"><a href="lib/plan/planEdit.php">{lang_get s='testplan_menu_list'}</a></span> 
</div>

{include file="inc_update.tpl" result=$sqlResult item="TestPlan" action="add"}

<div class="workBack">

	<h2>
	{if $tpID eq 0}
		{lang_get s='testplan_title_create'}
		{assign var='form_action' value='create'} 
	{else}
		{lang_get s='testplan_title_edit'} 
		{assign var='form_action' value='update'} 
	{/if}
	{lang_get s='testplan_title_for_project'} '{$prod_name|escape}'</h2>

	<form method="post" action="lib/plan/planEdit.php?action={$form_action}">
	<input type="hidden" name="tpID" value="{$tpID}">
	<table class="common" width="80%">
	  {* 20051120 - fm *}
		<tr><th>{lang_get s='testplan_th_name'}</th></tr>
		<tr>
			<td><input type="text" name="name" 
			           size="{#TESTPLAN_NAME_SIZE#}" 
			           maxlength="{#TESTPLAN_NAME_MAXLEN#}" 
			           value="{$tpName|escape}"/></td>
		</tr>
		<tr><th>{lang_get s='testplan_th_notes'}</th></tr>
		<tr>
			<td >{$notes}</td>
		</tr>
		{if $tpID eq 0}
			<tr><th>{lang_get s='testplan_question_create_tp_from'}</th></tr>
			<tr><td>
				<select name="copy">
				<option value="noCopy">{lang_get s='opt_no'}</option>
				{section name=number loop=$arrPlan}
					<option value="{$arrPlan[number][0]}">{$arrPlan[number][1]|escape}</option>
				{/section}
				</select>
			</td></tr>
		{else}
			<tr><td>
				{lang_get s='testplan_th_active'}
				<input type="checkbox" name="active" 
				{if $tpActive eq 1}
					checked="checked"
				{/if}
				/>
      		</td></tr>
		{/if}
	</table>

	<div class="groupBtn">	
		{if $tpID eq 0}
			<input type="submit" name="newTestPlan" value="{lang_get s='testplan_btn_new'}" />
		{else}
			<input type="submit" name="editTestPlan" value="{lang_get s='testplan_btn_edit'}" />
		{/if}
	</div>

	</form>

<p>{lang_get s='testplan_txt_notes'}</p>
	
</div>


</body>
</html>