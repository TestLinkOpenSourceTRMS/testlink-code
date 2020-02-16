{*
Testlink Open Source Project - http://testlink.sourceforge.net/
@filesource menu.inc.tpl
include to generate menu when managing users and roles
*}

{* Action managed via menu *}
{$tproject_id = $gui->tproject_id}
{$lib = 'lib/usermanagement'}
{$act['view_users']['url'] = $lib|cat:'/usersView.php'}
{$act['view_roles']['url'] = $lib|cat:'/rolesView.php?tproject_id='}
{$act['assign_users_tproject']['url'] = $lib|cat:'/usersAssign.php?featureType=testproject&tproject_id='}
{$act['assign_users_tplan']['url'] = $lib|cat:'/usersAssign.php?featureType=testplan&tproject_id='}


{lang_get var="menuLbl"
          s="menu_new_user,menu_view_users,menu_edit_user,menu_define_roles,menu_edit_role,menu_view_roles,menu_assign_testproject_roles,menu_assign_testplan_roles"}

{foreach from=$act key=ak item=mx }
  {$act[$ak]['class'] = ''}
  {if $gui->highlight->$ak == 1}
    {$act[$ak]['class'] = ' class="active" '}
  {/if}
{/foreach}

{if $gui->grants->user_mgmt == "no"}
  {$act[$ak]['class'] = ''}
{/if}

<div class="container">
  <ul class="nav nav-pills">

    {if $gui->grants->user_mgmt == "yes"}
	    <li {$act['view_users']['class']} ><a href="{$act['view_users']['url']}{$tproject_id}">{$menuLbl.menu_view_users}</a></li>
	  {/if}

    {if $gui->grants->role_mgmt == "yes"}    
      <li {$act['view_roles']['class']} ><a href="{$act['view_roles']['url']}{$tproject_id}">{$menuLbl.menu_view_roles}</a></li>
    {/if}

    {if $gui->grants->tproject_user_role_assignment == "yes"}
      <li {$act['assign_users_tproject']['class']} ><a href="{$act['assign_users_tproject']['url']}{$tproject_id}">{$menuLbl.menu_assign_testproject_roles}</a></li>
    {/if}

    {if $gui->grants->tplan_user_role_assignment == "yes"}
      <li {$act['assign_users_tplan']['class']} ><a href="{$act['assign_users_tplan']['url']}{$tproject_id}">{$menuLbl.menu_assign_testplan_roles}</a></li>
    {/if}

  </ul>  
</div>