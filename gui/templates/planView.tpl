{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planView.tpl,v 1.1 2007/01/29 09:17:54 franciscom Exp $ 
Purpose: smarty template - edit / delete Test Plan 
Revisions:
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
{if $editResult ne ""}
	<div>
		<p class="info">{$editResult}</p>
	</div>
{/if}

<div class="workBack">
<div id="testplan_management_list">
{if $tplans eq ''}
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
		{foreach item=testplan from=$tplans}
		<tr>
			<td><a href="lib/plan/planEdit.php?tplan_id={$testplan.id}&do_action=edit"> 
				     {$testplan.name|escape} 
				     {if $gsmarty_gui->show_icon_edit}
 				         <img title="{lang_get s='testplan_alt_edit_tp'}" 
 				              alt="{lang_get s='testplan_alt_edit_tp'}" 
 				              src="{$smarty.const.TL_THEME_IMG_DIR}/icon_edit.png"/>
 				     {/if}  
 				  </a>
			</td>
			<td>
				{$testplan.notes|strip_tags|strip|truncate:100}
			</td>
			<td>
			{if $testplan.active == 1}
				{lang_get s='Yes'}
			{else}
				{lang_get s='No'}
			{/if}
			</td>
			<td>
				<a href="javascript:delete_confirmation(fRoot+'lib/plan/planEdit.php?do_action=do_delete&amp;tplan_id={$testplan.id}');">
				  <img style="border:none" title="{lang_get s='testplan_alt_delete_tp'}" 
				       alt="{lang_get s='testplan_alt_delete_tp'}" 
				       src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"/></a>
			</td>
		</tr>
		{/foreach}

	</table>

{/if}
</div>

 {if $testplan_create}
 <div class="groupBtn">
    <form method="post" action="lib/plan/planEdit.php?do_action=create">
      <input type="submit" name="create_testplan" value="{lang_get s='btn_testplan_create'}" />
    </form>
  </div>
 {/if}
</div>



</body>
</html>
