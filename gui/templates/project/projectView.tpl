{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: projectView.tpl,v 1.11 2008/09/20 21:02:53 schlundus Exp $
Purpose: smarty template - edit / delete Test Plan

Development hint:
     some variables smarty and javascript are created on the inc_*.tpl files.

Rev :
    20080805 - franciscom - api config refactoring
    20080116 - franciscom - added option to show/hide id useful for API

*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}
{assign var="managerURL" value="lib/project/projectEdit.php"}
{assign var="deleteAction" value="$managerURL?doAction=doDelete&tprojectID="}
{assign var="editAction" value="$managerURL?doAction=edit&amp;tprojectID="}
{assign var="createAction" value="$managerURL?doAction=create"}

{lang_get s='popup_product_delete' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

{lang_get var="labels" s='title_testproject_management,testproject_txt_empty_list,tcase_id_prefix,
                          th_name,th_notes,testproject_alt_edit,testproject_alt_active,
                          th_requirement_feature,testproject_alt_delete,btn_create,
                          testproject_alt_requirement_feature,th_active,th_delete,th_id'}


{include file="inc_head.tpl" openHead="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'{$deleteAction}';
</script>
</head>

<body {$body_onload}>

<h1 class="title">{$labels.title_testproject_management}</h1>
<div class="workBack">
<div id="testproject_management_list">
{if $tprojects eq ''}
	{$labels.testproject_txt_empty_list}

{else}
	<table id="item_view" class="simple sortable" width="95%">
		<tr>
			<th>{$toogle_api_info_img}{$sortHintIcon}{$labels.th_name}</th>
			<th class="{$noSortableColumnClass}">{$labels.th_notes}</th>
			<th>{$sortHintIcon}{$labels.tcase_id_prefix}</th>
			<th class="{$noSortableColumnClass}">{$labels.th_requirement_feature}</th>
			<th class="icon_cell">{$labels.th_active}</th>
			{if $canManage == "yes"}
			<th class="icon_cell">{$labels.th_delete}</th>
			{/if}
		</tr>
		{foreach item=testproject from=$tprojects}
		<tr>
			<td><span class="api_info" style='display:none'>{$tlCfg->api->id_format|replace:"%s":$testproject.id}</span>
			    <a href="{$editAction}{$testproject.id}">
				     {$testproject.name|escape}
				     {if $gsmarty_gui->show_icon_edit}
 				         <img title="{$labels.testproject_alt_edit}"
 				              alt="{$labels.testproject_alt_edit}"
 				              src="{$smarty.const.TL_THEME_IMG_DIR}/icon_edit.png"/>
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
				{if $testproject.option_reqs eq 1}
  					<img style="border:none"
  				            title="{$labels.testproject_alt_requirement_feature}"
  				            alt="{$labels.testproject_alt_requirement_feature}"
  				            src="{$smarty.const.TL_THEME_IMG_DIR}/apply_f2_16.png"/>
  				{else}
  					&nbsp;
  				{/if}
			</td>
			<td class="clickable_icon">
				{if $testproject.active eq 1}
  					<img style="border:none"
  				            title="{$labels.testproject_alt_active}"
  				            alt="{$labels.testproject_alt_active}"
  				            src="{$smarty.const.TL_THEME_IMG_DIR}/apply_f2_16.png"/>
  				{else}
  					&nbsp;
  				{/if}
			</td>
			{if $canManage == "yes"}
			<td class="clickable_icon">
				  <img style="border:none;cursor: pointer;"
				       alt="{$labels.testproject_alt_delete}"
					   title="{$labels.testproject_alt_delete}"
					   onclick="delete_confirmation({$testproject.id},'{$testproject.name|escape}',
					                                '{$del_msgbox_title}','{$warning_msg}');"
				     src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"/>
			</td>
			{/if}
		</tr>
		{/foreach}

	</table>

{/if}
</div>

 {if $canManage}
 <div class="groupBtn">
    <form method="post" action="{$createAction}">
      <input type="submit" name="create" value="{$labels.btn_create}" />
    </form>
  </div>
 {/if}
</div>

{* *}
{if $doAction == "reloadAll"}
	<script type="text/javascript">
	top.location = top.location;
	</script>
{else}
  {if $doAction == "reloadNavBar"}
	<script type="text/javascript">
  // remove query string to avoid reload of home page,
  // instead of reload only navbar
  var href_pieces=parent.titlebar.location.href.split('?');
	parent.titlebar.location=href_pieces[0];
	</script>
  {/if}
{/if}

</body>
</html>