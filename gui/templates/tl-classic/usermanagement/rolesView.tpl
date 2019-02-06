{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource rolesView.tpl
Purpose: smarty template - View defined roles

*}
{$roleActionMgr="lib/usermanagement/rolesEdit.php"}
{$createRoleAction="$roleActionMgr?doAction=create"}
{$editRoleAction="$roleActionMgr?doAction=edit&roleid="}
{$duplicateRoleAction="$roleActionMgr?doAction=duplicate&roleid="}

{lang_get var="labels"
          s="btn_create,title_user_mgmt,title_roles,delete_role,caption_possible_affected_users,
             warning_users_will_be_reset,btn_confirm_delete,btn_cancel,no_roles,th_duplicate_role,
             th_roles,th_role_description,th_delete,alt_edit_role,alt_delete_role,N_A,duplicate_role"}

{$cfg_section=$smarty.template|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get s='warning_delete_role' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}
{include file="bootstrap.inc.tpl"}

<style>
.table-hover tr:hover td, .table-hover tr:hover th {
   background-color: #ffffff;
}
</style> 
</head>

<body {$body_onload}>
<h1 class="title">{$gui->main_title}</h1>

{include file="usermanagement/menu.inc.tpl"}

{include file="inc_update.tpl" result=$sqlResult}

{$draw_create_btn="1"}
<div class="workBack">
{if $gui->affectedUsers neq null}
  {$draw_create_btn="0"}

  {* show user list of users having role he/she want to delete *}
  <h1 class="title">{$labels.delete_role} {$gui->roles[$gui->roleid]->name|escape}</h1>

    <table class="table" style="width:50%">
    <caption>{$labels.caption_possible_affected_users}</caption>
    {foreach from=$gui->affectedUsers item=user}
    <tr>
        <td>{$user->getDisplayName()|escape}</td>
    </tr>
    {/foreach}
    </table>
    <div class="legend_container">{$labels.warning_users_will_be_reset} => {$gui->roles[$gui->role_id_replacement]->name|escape}</div><br />
    <div class="groupBtn">
        <input type="submit" name="confirmed" value="{$labels.btn_confirm_delete}"
               onclick="location='lib/usermanagement/rolesView.php?doAction=confirmDelete&roleid={$gui->roleid}'"/>
        <input type="submit" value="{$labels.btn_cancel}"
               onclick="location='lib/usermanagement/rolesView.php'" />
    </div>
{else}
    {if $gui->roles eq ''}
        {$labels.no_roles}
    {else}
        {* data table *}
        <table class="table table-hover sortable common">
            <tr>
                <th width="30%">{$tlImages.sort_hint}{$labels.th_roles}</th>
                <th class="{$noSortableColumnClass}">{$labels.th_role_description}</th>
                <th class="icon_cell">{$labels.th_delete}</th>
                <th class="icon_cell">{$labels.th_duplicate_role}</th>
            </tr>
            {foreach from=$gui->roles item=role}
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
				<td>{if $gui->editorType == 'none'}{$role->description|nl2br}{else}{$role->description|strip_tags|strip}{/if}</td>
                <td>
                    {if $role->dbID > $smarty.const.TL_LAST_SYSTEM_ROLE}
                       <img style="border:none;cursor: pointer;" title="{$labels.alt_delete_role}"
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

{* Poor's man solution instead of on ready *}
<script type="text/javascript">
/* All this stuff is need for logic contained in inc_del_onclick.tpl */
// alert(document.getElementById('CSRFName').value);
var del_action=fRoot+'lib/usermanagement/rolesView.php?doAction=delete'+
               '&csrfid=' + document.getElementById('CSRFName').value + 
               '&csrftoken=' + document.getElementById('CSRFToken').value +
               '&roleid=';
</script>
</body>