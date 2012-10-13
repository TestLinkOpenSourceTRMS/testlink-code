{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	issueTrackerView.tpl

@internal revisions
*}
{include file="inc_head.tpl" jsValidate="yes" openHead="yes" enableTableSorting="yes"}
{include file="inc_action_onclick.tpl"}

{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var='labels'
          s='th_issuetracker,th_issuetracker_type,th_delete,th_description,menu_assign_kw_to_tc,
          	 btn_create,alt_delete,th_issuetracker_env,warning_delete,delete,href_issuetracker_management'}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_action_onclick.tpl */
var del_action=fRoot+'lib/issuetrackers/issueTrackerEdit.php?doAction=doDelete&id=';
</script>
</head>
<body {$gui->body_onload}>
<h1 class="title">{$labels.href_issuetracker_management}</h1>
{include file="inc_feedback.tpl" user_feedback=$gui->user_feedback}
<div class="workBack">
	{if $gui->items != ''}
	<table class="simple_tableruler sortable">
		<tr>
			<th width="30%">{$tlImages.sort_hint}{$labels.th_issuetracker}</th>
			<th>{$tlImages.sort_hint}{$labels.th_issuetracker_type}</th>
			<th>{$labels.th_issuetracker_env}</th>
			{if $gui->canManage != ""}
				<th style="min-width:70px">{$tlImages.sort_hint}{$labels.th_delete}</th>
			{/if}
		</tr>

   {foreach key=item_id item=item_def from=$gui->items}
		<tr>
			<td>
				{if $gui->canManage != ""}
					<a href="lib/issuetrackers/issueTrackerEdit.php?doAction=edit&amp;id={$item_def.id}">
				{/if}
				{$item_def.name|escape}
				{if $gui->canManage != ""}
					</a>
				{/if}
			</td>
			<td>{$item_def.type_descr|escape}</td>
			<td class="clickable_icon">{$item_def.check_env_msg|escape}</td>
			<td class="clickable_icon">
				{if $gui->canManage != ""  && $item_def.link_count == 0}
			  		<img style="border:none;cursor: pointer;"
			       		alt="{$labels.alt_delete}" title="{$labels.alt_delete}"   
             		src="{$tlImages.delete}"			     
				     	 onclick="delete_confirmation({$item_def.id},
				              '{$item_def.name|escape:'javascript'|escape}',
				              '{$labels.delete}','{$labels.warning_delete}');" />
				{/if}
				</td>
		</tr>
		{/foreach}
	</table>
	{/if}
	
	<div class="groupBtn">	
  	<form name="item_view" id="item_view" method="post" action="lib/issuetrackers/issueTrackerEdit.php"> 
   	  <input type="hidden" name="doAction" id="doAction" value="" />
      <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
  		{if $gui->canManage != ""}
  	  		<input type="submit" id="create" name="create" value="{$labels.btn_create}" 
  	             onclick="doAction.value='create'"/>
  		{/if}
    </form>
	</div>
</div>

</body>
</html>