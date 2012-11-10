{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	projectView.tpl
Purpose: smarty template - edit / delete Test Plan

Development hint:
some variables smarty and javascript are created on the inc_*.tpl files.

@internal revisions
*}
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{$contextID=$gui->contextTprojectID}
{$managerURL="lib/project/projectEdit.php"}
{$deleteAction="$managerURL?doAction=doDelete&contextTprojectID=$contextID&tprojectID="}
{$editAction="$managerURL?doAction=edit&contextTprojectID=$contextID&tprojectID="}
{$createAction="$managerURL?doAction=create&contextTprojectID=$contextID"}
{$toggleActive="$managerURL?doAction=toggleActive&contextTprojectID="}
{$togglePublic="$managerURL?doAction=togglePublic&contextTprojectID="}

{lang_get s='popup_product_delete' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{lang_get var="labels" 
		s='title_testproject_management,testproject_txt_empty_list,tcase_id_prefix,
		th_name,th_notes,testproject_alt_edit,testproject_alt_active,inactive,
		th_requirement_feature,testproject_alt_delete,btn_create,public,private,
		testproject_alt_requirement_feature,th_active,th_delete,th_id'}


{include file="inc_head.tpl" openHead="yes" enableTableSorting="yes"}
{include file="inc_action_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_action_onclick.tpl */
var target_action=fRoot+'{$deleteAction}';
</script>
</head>

<body {$body_onload}>

<h1 class="title">{$labels.title_testproject_management}</h1>
<div class="workBack">

{if $gui->canManage}
<div class="groupBtn">
	<form method="post" action="{$createAction}">
		<input type="submit" name="create" value="{$labels.btn_create}" />
	</form>
</div>
{/if}

<div id="testproject_management_list">
{if $gui->tprojects == ''}
	{$labels.testproject_txt_empty_list}
{else}
  <form method="post" id="projectView" name="projectView" action="{$managerURL}">
  <input type="hidden" name="doAction" id="doAction" value="">
  <input type="hidden" name="contextTprojectID" id="contextTprojectID" value="">
	<table id="item_view" class="simple_tableruler sortable">
		<tr>
			<th>{$tlImages.toggle_api_info}{$tlImages.sort_hint}{$labels.th_name}</th>
			<th class="{$noSortableColumnClass}">{$labels.th_notes}</th>
			<th>{$tlImages.sort_hint}{$labels.tcase_id_prefix}</th>
			<th class="{$noSortableColumnClass}">{$labels.th_requirement_feature}</th>
			<th class="icon_cell">{$labels.th_active}</th>
			<th class="icon_cell">{$labels.public}</th>
			{if $gui->canManage == "yes"}
			<th class="icon_cell">{$labels.th_delete}</th>
			{/if}
		</tr>
		{foreach item=testproject from=$gui->tprojects}
		<tr>
			<td><span class="api_info" style='display:none'>{$tlCfg->api->id_format|replace:"%s":$testproject.id}</span>
			    <a href="{$editAction}{$testproject.id}">
				     {$testproject.name|escape}
				     {if $gsmarty_gui->show_icon_edit}
 				         <img title="{$labels.testproject_alt_edit}" alt="{$labels.testproject_alt_edit}"
 				              src="{$tlImages.edit}"/>
 				     {/if}
 				  </a>
			</td>
			<td>
				{$testproject.notes|strip_tags|strip|truncate:#TESTPROJECT_NOTES_TRUNCATE#}
			</td>
			<td width="10%">
				{$testproject.prefix|escape}
			</td>
			<td class="clickable_icon">
				{if $testproject.opt->requirementsEnabled}
  					<img style="border:none" title="{$labels.testproject_alt_requirement_feature}"
  				            alt="{$labels.testproject_alt_requirement_feature}" src="{$tlImages.checked}"/>
  				{else}
  					&nbsp;
  				{/if}
			</td>
			<td class="clickable_icon">
				{if $testproject.active}
  					<input type="image" style="border:none" title="{$labels.testproject_alt_active}"
  					       onClick = "doAction.value='toggleActive',contextTprojectID.value={$testproject.id}"
  				         alt="{$labels.testproject_alt_active}" src="{$tlImages.checked}"/>
  				{else}
			      <a href="{$toggleActive}{$testproject.id}" title="{$labels.inactive}">&nbsp;</a>
  				{/if}
			</td>
			<td class="clickable_icon">
				{if $testproject.is_public}
  					<input type="image" style="border:none"  title="{$labels.public}" alt="{$labels.public}" 
  					       onClick = "doAction.value='togglePublic',contextTprojectID.value={$testproject.id}"
  					       src="{$tlImages.checked}" />
  				{else}
			      <a href="{$togglePublic}{$testproject.id}" title="{$labels.private}">&nbsp;</a>
  				{/if}
			</td>
			{if $gui->canManage == "yes"}
			<td class="clickable_icon">
				  <img style="border:none;cursor: pointer;"  alt="{$labels.testproject_alt_delete}"
					     title="{$labels.testproject_alt_delete}"
					     onclick="action_confirmation({$testproject.id},'{$testproject.name|escape:'javascript'|escape}',
					                                '{$del_msgbox_title}','{$warning_msg}');"
				       src="{$tlImages.delete}"/>
			</td>
			{/if}
		</tr>
		{/foreach}

	</table>
  </form>
{/if}
</div>

</div>

{if $gui->reloadType == "reloadAll"}
	<script type="text/javascript">
	top.location = top.location;
	</script>
{else}
  {if $gui->reloadType == "reloadNavBar"}
	<script type="text/javascript">
  	// remove query string to avoid reload of home page,
  	// instead of reload only navbar
  	var href_pieces=parent.titlebar.location.href.split('?');
	parent.titlebar.location=href_pieces[0]+"?debugCaller=projectView.tpl&runUpdateLogic=0";
	</script>
  {/if}
{/if}

</body>
</html>