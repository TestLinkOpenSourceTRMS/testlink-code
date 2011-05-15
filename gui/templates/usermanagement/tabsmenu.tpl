{*
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: tabsmenu.tpl,v 1.6 2010/05/01 20:16:32 franciscom Exp $

include to generate menu when managing users and roles

@internal revisions
  20100501 - franciscom - BUGID 3410: Smarty 3.0 compatibility

*}

{$tprojectID = $gui->tproject_id}

{$action_create_role ="lib/usermanagement/rolesEdit.php?tproject_id=$tprojectID&doAction=create"}
{$action_view_roles ="lib/usermanagement/rolesView.php?tproject_id=$tprojectID"}

{$action_create_user ="lib/usermanagement/usersEdit.php?tproject_id=$tprojectID&doAction=create"}
{$action_edit_user ="lib/usermanagement/usersEdit.php?tproject_id=$tprojectID&doAction=edit&user_id="}
{$action_view_users ="lib/usermanagement/usersView.php?tproject_id=$tprojectID"}
{$action_assign_users_tproject ="lib/usermanagement/usersAssign.php?tproject_id=$tprojectID&featureType=testproject"}
{$action_assign_users_tplan ="lib/usermanagement/usersAssign.php?tproject_id=$tprojectID&featureType=testplan"}


{lang_get var="tabsMenuLabels"
          s="menu_new_user,menu_view_users,menu_edit_user,menu_define_roles,
             menu_edit_role,menu_view_roles,menu_assign_testproject_roles,menu_assign_testplan_roles"}

<div class="tabMenu">
{if $gui->grants->user_mgmt == "yes"}
 	{if $gui->highlight->edit_user}
	   <span class="selected">{$tabsMenuLabels.menu_edit_user}</span>
	{else}
	   {if $gui->highlight->create_user}
	       <span class="selected">{$tabsMenuLabels.menu_new_user}</span>
	   {/if}
	{/if}

	{$closure =""}
	{if $gui->highlight->view_users}
	   <span class="selected">
	{else}
	   <span class="unselected"><a href="{$action_view_users}">
	   {$closure ="</a>"}
	{/if}
	{$tabsMenuLabels.menu_view_users}{$closure}</span>
{/if}

{if $gui->grants->role_mgmt == "yes"}
	{$closure =""}
	{if $gui->highlight->view_roles}
	   <span class="selected">{$tabsMenuLabels.menu_view_roles}</span>
	{else}
		{if $gui->highlight->edit_role}
	   		<span class="selected">{$tabsMenuLabels.menu_edit_role}</span>
		{else}
	 		<span class="unselected"><a href="{$action_view_roles}">{$tabsMenuLabels.menu_view_roles}</a></span>
		{/if}
	{/if}
{/if}

{if $gui->grants->tproject_user_role_assignment == "yes"}
  {$closure =""}
  {if $gui->highlight->assign_users_tproject}
	   <span class="selected">
	{else}
	   <span class="unselected"><a href="{$action_assign_users_tproject}">
	   {$closure ="</a>"}
	{/if}
	{$tabsMenuLabels.menu_assign_testproject_roles}{$closure}</span>
{/if}


{if $gui->grants->tplan_user_role_assignment == "yes"}
  {$closure =""}
  {if $gui->highlight->assign_users_tplan}
	   <span class="selected">
	{else}
	   <span class="unselected"><a href="{$action_assign_users_tplan}">
	   {$closure ="</a>"}
	{/if}
	{$tabsMenuLabels.menu_assign_testplan_roles}{$closure}</span>
{/if}
</div>