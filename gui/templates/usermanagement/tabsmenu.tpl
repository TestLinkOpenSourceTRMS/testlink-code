{assign var="action_create_role" value="lib/usermanagement/rolesEdit.php?doAction=create"}
{assign var="action_view_roles" value="lib/usermanagement/rolesView.php"}


{assign var="action_create_user" value="lib/usermanagement/usersEdit.php?doAction=create"}
{assign var="action_edit_user" value="lib/usermanagement/usersEdit.php?doAction=edit&amp;user_id="}
{assign var="action_view_users" value="lib/usermanagement/usersView.php"}
{assign var="action_assign_users_tproject" value="lib/usermanagement/usersAssign.php?feature=testproject"}
{assign var="action_assign_users_tplan" value="lib/usermanagement/usersAssign.php?feature=testplan"}


{lang_get var="labels"
          s="menu_new_user,menu_view_users,menu_edit_user,menu_define_roles,
             menu_edit_role,menu_view_roles,menu_assign_testproject_roles,menu_assign_testplan_roles"}

<div class="tabMenu">
{if $grants->user_mgmt == "yes"}
 	{if $highlight->edit_user}
	   <span class="selected">{$labels.menu_edit_user}</span>
	{else}
	   {if $highlight->create_user}
	       <span class="selected">{$labels.menu_new_user}</span>
	   {/if}
	{/if}

  {assign var="closure" value=""}
	{if $highlight->view_users}
	   <span class="selected">
	{else}
	   <span class="unselected"><a href="{$action_view_users}">
	   {assign var="closure" value="</a>"}
	{/if}
	{$labels.menu_view_users}{$closure}</span>
{/if}

{if $grants->role_mgmt == "yes"}
	{assign var="closure" value=""}
	{if $highlight->view_roles}
	   <span class="selected">{$labels.menu_view_roles}</span>
	{else}
		{if $highlight->edit_role}
	   		<span class="selected">{$labels.menu_edit_role}</span>
		{else}
	 		<span class="unselected"><a href="{$action_view_roles}">{$labels.menu_view_roles}</a></span>
		{/if}
	{/if}
{/if}

{if $grants->tproject_user_role_assignment == "yes"}
  {assign var="closure" value=""}
  {if $highlight->assign_users_tproject}
	   <span class="selected">
	{else}
	   <span class="unselected"><a href="{$action_assign_users_tproject}">
	   {assign var="closure" value="</a>"}
	{/if}
	{$labels.menu_assign_testproject_roles}{$closure}</span>
{/if}


{if $grants->tplan_user_role_assignment == "yes"}
  {assign var="closure" value=""}
  {if $highlight->assign_users_tplan}
	   <span class="selected">
	{else}
	   <span class="unselected"><a href="{$action_assign_users_tplan}">
	   {assign var="closure" value="</a>"}
	{/if}
	{$labels.menu_assign_testplan_roles}{$closure}</span>
{/if}
</div>