{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planEdit.tpl,v 1.5 2006/08/17 19:29:59 schlundus Exp $ *}
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

	<table class="common" width="95%">
		<caption>{lang_get s='caption_edit_tp'}</caption>
		<tr>
			<th>{lang_get s='th_name'}</th>
			<th>{lang_get s='th_tp_notes'}</th>
			<th>{lang_get s='th_active'}</th>
			<th>{lang_get s='th_delete_tp'}</th>
		</tr>
		{section name=number loop=$arrPlan}
		<tr>
			<td><a href="lib/plan/planNew.php?tpID={$arrPlan[number].id}"> 
					{$arrPlan[number].name|escape}</a>
			</td>
			<td>
				{$arrPlan[number].notes|strip_tags|strip|truncate:100}
			</td>
			<td>
			{if $arrPlan[number].active == 1}
				{lang_get s='Yes'}
			{else}
				{lang_get s='No'}
			{/if}
			</td>
			<td>
				<a href="lib/plan/planEdit.php?deleteTP=1&id={$arrPlan[number][0]}">
				<img style="border:none" alt="{lang_get s='alt_delete_testplan'}" src="icons/thrash.png"/>
				</a>
			</td>
		</tr>
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