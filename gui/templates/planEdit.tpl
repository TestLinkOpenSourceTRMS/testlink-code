{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planEdit.tpl,v 1.10 2007/01/13 23:45:36 schlundus Exp $ 
Purpose: smarty template - edit / delete Test Plan 
Revisions:
	20050810 - fm - changes in active field definition 
	20061119 - mht - refactorization; update for TL1.7
	20061223 - franciscom - use of gsmarty_gui
*}
{include file="inc_head.tpl"}

<body>
<script type="text/javascript">
function delete_confirmation(delUrl) {ldelim}
	if (confirm("{lang_get s='testplan_msg_delete_confirm'}")){ldelim}
		window.location = delUrl;
	{rdelim}
{rdelim}
</script>

<h1>{lang_get s='testplan_title_tp_management'}</h1>

<div class="tabMenu">
	<span class="unselected"><a href="lib/plan/planEdit.php?action=empty">{lang_get s='testplan_menu_create'}</a></span> 
	<span class="selected">{lang_get s='testplan_menu_list'}</span> 
</div>

{if $editResult ne ""}
	<div>
		<p class="info">{$editResult}</p>
	</div>
{/if}

<div class="workBack">
<div id="testplan_management_list">
{if $arrPlan eq ''}
	{lang_get s='testplan_txt_empty_list'}

{else}
	<h2>{lang_get s='testplan_title_list'}</h2>
	<table class="simple" width="95%">
		<tr>
			<th>{lang_get s='testplan_th_name'}</th>
			<th>{lang_get s='testplan_th_notes'}</th>
			<th style="width: 60px;">{lang_get s='testplan_th_active'}</th>
			<th style="width: 60px;">{lang_get s='testplan_th_delete'}</th>
		</tr>
		{section name=number loop=$arrPlan}
		<tr>
			<td><a href="lib/plan/planNew.php?tpID={$arrPlan[number].id}"> 
				     {$arrPlan[number].name|escape} 
				     {if $gsmarty_gui->show_icon_edit}
 				         <img title="{lang_get s='testplan_alt_edit_tp'}" 
 				              alt="{lang_get s='testplan_alt_edit_tp'}" 
 				              src="gui/images/icon_edit.png"/>
 				     {/if}  
 				  </a>
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
				<a href="javascript:delete_confirmation('lib/plan/planEdit.php?action=delete&amp;id={$arrPlan[number].id}');">
				  <img style="border:none" title="{lang_get s='testplan_alt_delete_tp'}" 
				       alt="{lang_get s='testplan_alt_delete_tp'}" 
				       src="gui/images/icon_thrash.png"/></a>
			</td>
		</tr>
		{/section}

	</table>

{/if}
</div>
</div>

</body>
</html>
