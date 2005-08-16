{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planEdit.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - edit / delete Test Plan *}
{* 20050810 - fm - changes in active field definition *}

{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_test_plan'}</h1>

<div class="tabMenu">
	<span class="unselected"><a href="lib/plan/planNew.php">{lang_get s='menu_create'}</a></span> 
	<span class="selected">{lang_get s='menu_edit_del'}</span> 
</div>

{if $editResult ne ""}
	<div>
		<p>{$editResult}</p>
	</div>
{/if}

<div class="workBack">
{if $arrPlan eq ''}
	{lang_get s='no_test_plans'}
{else}
	<form method="post">

	<table class="common" width="75%">
		<caption>{lang_get s='caption_edit_tp'}</caption>
		<tr>
			<th>{lang_get s='th_name'}</th>
			<th>{lang_get s='th_tp_notes'}</th>
			<th>{lang_get s='th_active'}</th>
			<th>{lang_get s='th_delete_tp'}</th>
		</tr>
		{section name=number loop=$arrPlan}
		<tr>
			<td>
				<input type="hidden" name="{$arrPlan[number][0]}" value="{$arrPlan[number][0]}" />
				<input type="text" name="name{$arrPlan[number][0]}" value="{$arrPlan[number][1]|escape}" />
			</td>
			<td>
				<textarea rows="2" cols="50" name="notes{$arrPlan[number][0]}">{$arrPlan[number][2]|escape}</textarea>
			</td>
			<td>
			
			{* 20050810 - fm - changes in active field definition *}
			{* {if $arrPlan[number][3] == 'y'} *}
			{if $arrPlan[number][3] == 1}
				<input type="radio" name="archive{$arrPlan[number][0]}" value="y" checked="checked" />{lang_get s='Yes'}<br />
				<input type="radio" name="archive{$arrPlan[number][0]}" value="n" />{lang_get s='No'}
			{else}
				<input type="radio" name="archive{$arrPlan[number][0]}" value="y" />{lang_get s='Yes'}<br />
				<input type="radio" name="archive{$arrPlan[number][0]}" value="n" checked="checked" />{lang_get s='No'}
			{/if}
			</td>
			<td>
				<input type="checkbox" name="delete{$arrPlan[number][0]}" />
			</td>
		<tr>
		{/section}

	</table>

	<div style="padding: 20px;">
		<input type="submit" name="editTestPlan" value="{lang_get s='btn_upd_tp'}" />
	</div>

	</form>
{/if}	
</div>

</body>
</html>