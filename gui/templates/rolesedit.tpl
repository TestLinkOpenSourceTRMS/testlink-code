{* smarty template - view all keywords of product; ver. 1.0 *}
{* $Id: rolesedit.tpl,v 1.4 2006/08/29 19:41:36 schlundus Exp $ *}
{* Purpose: smarty template - View all roless *}
{include file="inc_head.tpl"}

<body>

{literal}
<script type="text/javascript">
{/literal}
var warning_modify_role = "{lang_get s='warning_modify_role'}";
{literal}
</script>
{/literal}

<h1>{lang_get s='title_roles'}</h1>

<div class="tabMenu">
{if $mgt_users == "yes"}
	<span class="unselected"><a href="lib/usermanagement/usersedit.php">{lang_get s='menu_new_user'}</a></span> 
	<span class="unselected"><a href="lib/usermanagement/usersview.php">{lang_get s='menu_mod_user'}</a></span>
	<br /><hr />
{/if}
{if $role_management == "yes"}
	<span class="selected">{lang_get s='menu_define_roles'}</span> 
{/if}
	<span class="unselected"><a href="lib/usermanagement/rolesview.php">{lang_get s='menu_view_roles'}</a></span>
{if $tp_user_role_assignment == "yes"}
	<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testproject">{lang_get s='menu_assign_product_roles'}</a></span> 
{/if}
{if $tproject_user_role_assignment == "yes"}
	<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testplan">{lang_get s='menu_assign_testplan_roles'}</a></span>
{/if}
</div>

{* show SQL result *}
{include file="inc_update.tpl" result=$sqlResult item="role" name=$role.role action="$action"}

{* Create Form *}
<div class="workBack">

	<form name="addKey" 
		method="post" action="lib/usermanagement/rolesedit.php"
	{if $role_management != "yes"}
		onsubmit="return false" 
	{/if}
	>
	<input type="hidden" name="id" value="{$role.id}" />
	<table class="common">
		<caption>{lang_get s='caption_define_role'}</caption>
		<tr>
			<th>{lang_get s='th_rolename'}</th>
			<td>
				<input type="text" name="rolename" size="20" maxlength="20" value="{$role.role|escape}"/>
			</td>
			<td>&nbsp;
			</td>
		</tr>
		<tr>
			<th>{lang_get s='th_rights'}</th>
			<td>
				<table>
				<tr>
					<td><fieldset><legend >{lang_get s='th_tp_rights'}</legend>
							{foreach from=$tpRights item=id key=k}
							<input type="checkbox" name="{$k}" {$roleRights[$k]}/>{$id}<br />
							{/foreach}
						</fieldset>
					</td>
					<td>
						<fieldset><legend >{lang_get s='th_mgttc_rights'}</legend>
						{foreach from=$tcRights item=id key=k}
						<input type="checkbox" name="{$k}" {$roleRights[$k]} />{$id}<br />
						{/foreach}
						</fieldset>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>
						<fieldset><legend >{lang_get s='th_product_rights'}</legend>
						{foreach from=$pRights item=id key=k}
						<input type="checkbox" name="{$k}" {$roleRights[$k]} />{$id}<br />
						{/foreach}
						</fieldset>
					</td>
					<td><fieldset><legend >{lang_get s='th_kw_rights'}</legend>
							{foreach from=$kwRights item=id key=k}
							<input type="checkbox" name="{$k}" {$roleRights[$k]} />{$id}<br />
							{/foreach}
						</fieldset>
					</td>
					<td>
						<fieldset><legend >{lang_get s='th_req_rights'}</legend>
						{foreach from=$reqRights item=id key=k}
						<input type="checkbox" name="{$k}" {$roleRights[$k]} />{$id}<br />
						{/foreach}
						</fieldset>
					</td>
				</tr>
				<tr>
					<td><fieldset><legend >{lang_get s='th_user_rights'}</legend>
							{foreach from=$uRights item=id key=k}
							<input type="checkbox" name="{$k}" {$roleRights[$k]} />{$id}<br />
							{/foreach}
						</fieldset>
					</td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<th>{lang_get s='enter_role_notes'}</th>
			<td width="80%">{$notes}</td>
		</tr>

	</table>
	{if $role_management == "yes"}
		<div class="groupBtn">	
		{if $role == 0}
			<input type="submit" name="newRole" value="{lang_get s='btn_create_role'}" />
		{else}
			<input type="submit" name="editRole" value="{lang_get s='btn_edit_role'}" 
			{if $affectedUsers neq null}
				onClick="return modifyRoles_warning()"
			{/if}
			/>
		{/if}
	{/if}
	</div>
	<br />
	{if $affectedUsers neq null}
		<table class="common" style="width:50%">
		<caption>{lang_get s='caption_possible_affected_users'}</caption>
		{foreach from=$affectedUsers item=i}
		<tr>
			<td>{$allUsers[$i].first|escape} {$allUsers[$i].last|escape} [{$allUsers[$i].login|escape}]</td>
		</tr>
		{/foreach}
		</table>
	{/if}
	</form>
	
</div>

</body>
</html>