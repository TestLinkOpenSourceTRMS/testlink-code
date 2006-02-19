{* smarty template - view all keywords of product; ver. 1.0 *}
{* $Id: rolesview.tpl,v 1.1 2006/02/19 13:08:05 schlundus Exp $ *}
{* Purpose: smarty template - View all roless *}
{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_roles'}</h1>

{* tabs *}
<div class="tabMenu">
	<span class="unselected"><a href="lib/usermanagement/usersedit.php">{lang_get s='menu_new_user'}</a></span> 
	<span class="unselected"><a href="lib/usermanagement/usersview.php">{lang_get s='menu_mod_user'}</a></span>
	<br /><hr />
	<span class="unselected"><a href="lib/usermanagement/rolesedit.php">{lang_get s='menu_define_roles'}</a></span> 
	<span class="selected">{lang_get s='menu_view_roles'}</span>
	<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=product">{lang_get s='menu_assign_product_roles'}</a></span> 
	<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testplan">{lang_get s='menu_assign_testplan_roles'}</a></span>
</div>

{* show SQL result *}
{include file="inc_update.tpl" result=$sqlResult item="role" name=$role.role action="deleted"}


<div class="workBack">
{if $affectedUsers neq null}
	<table class="common" style="width:50%">
	<caption>{lang_get s='caption_possible_affected_users'}</caption>
	{foreach from=$affectedUsers item=i}
	<tr>
		<td>{$allUsers[$i].first|escape} {$allUsers[$i].last|escape} [{$allUsers[$i].login|escape}]</td>
	</tr>
	{/foreach}
	</table>
	<p>{lang_get s='warning_users_will_be_reset'}</p>
	<div class="groupBtn">	
		<input type="submit" name="confirmed" value="{lang_get s='btn_confirm_delete'}" onclick="location='lib/usermanagement/rolesview.php?confirmed=1&deleterole=1&id={$id}'"/>
		<input type="submit" value="{lang_get s='btn_cancel'}" onclick="location='lib/usermanagement/rolesview.php'" />
	</div>
{else}
	{if $roles eq ''}
		{lang_get s='no_roles'}
	{else}
		{* data table *}
		<table class="common" width="50%">
			<tr>
				<th width="30%">{lang_get s='th_roles'}</th>
				<th>{lang_get s='th_delete'}</th>
			</tr>
			{foreach from=$roles item=role}
			<tr>
				<td>
					<a href="lib/usermanagement/rolesedit.php?id={$role.id}">
						{$role.role|escape}
					</a>
				</td>
				<td>
				{if $role.id > 9}
					<a href="lib/usermanagement/rolesview.php?deleterole=1&id={$role.id}">
					<img style="border:none" alt="{lang_get s='alt_delete_keyword'}" src="icons/thrash.png"/>
					</a>
				{else}
					{lang_get s='N/A'}
				{/if}
				</td>
			</tr>
			{/foreach}
		</table>
	{/if}
{/if}
</div>

</bod