{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: rolesView.tpl,v 1.17 2010/10/17 09:46:37 franciscom Exp $
Purpose: smarty template - View defined roles

@internal revisions
@since 1.9.7

*}
{assign var="roleActionMgr" value="lib/usermanagement/rolesEdit.php"}
{assign var="createRoleAction" value="$roleActionMgr?doAction=create"}
{assign var="editRoleAction" value="$roleActionMgr?doAction=edit&amp;roleid="}
{assign var="duplicateRoleAction" value="$roleActionMgr?doAction=duplicate&amp;roleid="}

{lang_get var="labels"
          s="btn_create,title_user_mgmt,title_roles,delete_role,caption_possible_affected_users,
             warning_users_will_be_reset,btn_confirm_delete,btn_cancel,no_roles,th_duplicate_role,
             th_roles,th_role_description,th_delete,alt_edit_role,alt_delete_role,N_A,duplicate_role"}

{assign var="cfg_section" value=$smarty.template|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get s='warning_delete_role' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is need for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'lib/usermanagement/rolesView.php?doAction=delete&roleid=';
</script>

</head>

<body {$body_onload}>
<h1 class="title">{$labels.title_user_mgmt} - {$labels.title_roles}</h1>

{***** TABS *****}
{include file="usermanagement/tabsmenu.tpl"}

{include file="inc_update.tpl" result=$sqlResult}

{assign var="draw_create_btn" value="1"}
<div class="workBack">
{if $affectedUsers neq null}
  {assign var="draw_create_btn" value="0"}

  {* show user list of users having role he/she want to delete *}
  <h1 class="title">{$labels.delete_role} {$roles[$id]->name|escape}</h1>

	<table class="common" style="width:50%">
	<caption>{$labels.caption_possible_affected_users}</caption>
	{foreach from=$affectedUsers item=user}
	<tr>
		<td>{$user->getDisplayName()|escape}</td>
	</tr>
	{/foreach}
	</table>
	<div class="legend_container">{$labels.warning_users_will_be_reset} => {$roles[$role_id_replacement]->name|escape}</div><br />
	<div class="groupBtn">
		<input type="submit" name="confirmed" value="{$labels.btn_confirm_delete}"
		       onclick="location='lib/usermanagement/rolesView.php?doAction=confirmDelete&roleid={$id}'"/>
		<input type="submit" value="{$labels.btn_cancel}"
		       onclick="location='lib/usermanagement/rolesView.php'" />
	</div>
{else}
	{if $roles eq ''}
		{$labels.no_roles}
	{else}
		{* data table *}
		<table class="common sortable" width="70%">
			<tr>
				<th width="30%">{$tlImages.sort_hint}{$labels.th_roles}</th>
				<th class="{$noSortableColumnClass}">{$labels.th_role_description}</th>
				<th class="icon_cell">{$labels.th_delete}</th>
				<th class="icon_cell">{$labels.th_duplicate_role}</th>
			</tr>
			{foreach from=$roles item=role}
			{if $role->dbID neq $smarty.const.TL_ROLES_INHERITED}
			<tr>
				<td>
					<a href="{$editRoleAction}{$role->dbID}">
						{$role->getDisplayName()|escape}
						{if $gsmarty_gui->show_icon_edit}
 						  <img title="{$labels.alt_edit_role}"
 						       alt="{$labels.alt_edit_role}"
 						       title="{$labels.alt_edit_role}"
 						       src="{$smarty.const.TL_THEME_IMG_DIR}/icon_edit.png" />
 						{/if}
					</a>
				</td>
				<td>
					{$role->description|strip_tags|strip}
				</td>
				<td>
					{if $role->dbID > $smarty.const.TL_LAST_SYSTEM_ROLE}
				       <img style="border:none;cursor: pointer;"
		  				            title="{$labels.alt_delete_role}"
		  				            alt="{$labels.alt_delete_role}"
		 					            onclick="delete_confirmation({$role->dbID},'{$role->getDisplayName()|escape:'javascript'|escape}',
		 					                                         '{$del_msgbox_title}','{$warning_msg}');"
		  				            src="{$tlImages.delete}"/>
					{/if}
				</td>

				<td>
          <a href="{$duplicateRoleAction}{$role->dbID}">
          <img style="border:none;cursor: pointer;" title="{$labels.duplicate_role}" alt="{$labels.duplicate_role}"
               src="{$tlImages.duplicate}"/>
          </a>
				</td>

			</tr>
			{/if}
			{/foreach}
		</table>
	{/if}
{/if}
{if $draw_create_btn}
<div class="groupBtn">
<form method="post" action="{$createRoleAction}" name="launch_create">
<input type="submit" name="doCreate"  value="{$labels.btn_create}" />
</form>
</div>
{/if}
</div>
</body>