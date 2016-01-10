{*
Testlink Open Source Project - http://testlink.sourceforge.net/
@filesource menu.inc.tpl
include to generate menu when managing users and roles

@internal revisions
@since 1.9.15
*}

{$ulib='lib/usermanagement'}
{$action_view_roles="$ulib/rolesView.php"}
{$action_view_users="$ulib/usersView.php"}
{$action_assign_users_tproject="$ulib/usersAssign.php?featureType=testproject"}
{$action_assign_users_tplan="$ulib/usersAssign.php?featureType=testplan"}


{lang_get var="menuLbl"
          s="menu_new_user,menu_view_users,menu_edit_user,menu_define_roles,menu_edit_role,menu_view_roles,menu_assign_testproject_roles,menu_assign_testplan_roles"}

<div class="tabMenu">
{if $gui->grants->user_mgmt == "yes"}
  {if $gui->highlight->create_user}
	<span class="selected">{$menuLbl.menu_new_user}</span>
  {/if}

  {$closure=""}
	{if $gui->highlight->view_users}
	   <span class="selected">
	{else}
	   <span class="unselected"><a href="{$action_view_users}">
	   {assign var="closure" value="</a>"}
	{/if}
	{$menuLbl.menu_view_users}{$closure}</span>
{/if}

{if $gui->grants->role_mgmt == "yes"}
	{assign var="closure" value=""}
	{if $gui->highlight->view_roles}
	   <span class="selected">{$menuLbl.menu_view_roles}</span>
	{else}
		{if $gui->highlight->edit_role}
	   		<span class="selected">{$menuLbl.menu_edit_role}</span>
		{else}
	 		<span class="unselected"><a href="{$action_view_roles}">{$menuLbl.menu_view_roles}</a></span>
		{/if}
	{/if}
{/if}

{if $gui->grants->tproject_user_role_assignment == "yes"}
  {assign var="closure" value=""}
  {if $gui->highlight->assign_users_tproject}
	   <span class="selected">
	{else}
	   <span class="unselected"><a href="{$action_assign_users_tproject}">
	   {assign var="closure" value="</a>"}
	{/if}
	{$menuLbl.menu_assign_testproject_roles}{$closure}</span>
{/if}


{if $gui->grants->tplan_user_role_assignment == "yes"}
  {assign var="closure" value=""}
  {if $gui->highlight->assign_users_tplan}
	   <span class="selected">
	{else}
	   <span class="unselected"><a href="{$action_assign_users_tplan}">
	   {assign var="closure" value="</a>"}
	{/if}
	{$menuLbl.menu_assign_testplan_roles}{$closure}</span>
{/if}
</div>