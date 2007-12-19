{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: rolesview.tpl,v 1.3 2007/12/19 20:27:19 schlundus Exp $ 
Purpose: smarty template - View defined roles 

rev :
     20071013 - franciscom -
     20070921 - franciscom - BUGID - added strip_tags|strip to notes 
     20070829 - jbarchibald
      -  bug 1000  - Testplan User Role Assignments

*}
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get s='warning_delete_role' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is need for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'lib/usermanagement/rolesview.php?deleterole=1&id=';
</script>

</head>

<body {$body_onload}>
<h1>{lang_get s='title_user_mgmt'} - {lang_get s='title_roles'}</h1>

{* tabs *}
<div class="tabMenu">
{if $mgt_users == "yes"}
	<span class="unselected"><a href="lib/usermanagement/usersedit.php">{lang_get s='menu_new_user'}</a></span> 
	<span class="unselected"><a href="lib/usermanagement/usersview.php">{lang_get s='menu_view_users'}</a></span>
{/if}
{if $role_management == "yes"}
	<span class="unselected"><a href="lib/usermanagement/rolesedit.php">{lang_get s='menu_define_roles'}</a></span> 
{/if}
	<span class="selected">{lang_get s='menu_view_roles'}</span>
{if $tproject_user_role_assignment == "yes"}
	<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testproject">{lang_get s='menu_assign_testproject_roles'}</a></span> 
{/if}
{if $tp_user_role_assignment == "yes"}
	<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testplan">{lang_get s='menu_assign_testplan_roles'}</a></span>
{/if}
</div>

{* show SQL result *}
{include file="inc_update.tpl" result=$sqlResult item="Role" name=$role.role action="deleted"}


<div class="workBack">
{if $affectedUsers neq null}
  {* show user list of users having role he/she want to delete *}
  <h1>{lang_get s='delete_role'} {$roles[$id].role|escape}</h1>
  
	<table class="common" style="width:50%">
	<caption>{lang_get s='caption_possible_affected_users'}</caption>
	{foreach from=$affectedUsers item=i}
	<tr>
		<td>{$allUsers[$i].first|escape} {$allUsers[$i].last|escape} [{$allUsers[$i].login|escape}]</td>
	</tr>
	{/foreach}
	</table>
	<div class="legend_container">{lang_get s='warning_users_will_be_reset'} {$roles[$role_id_replacement].role|escape}</div><br />
	<div class="groupBtn">	
		<input type="submit" name="confirmed" value="{lang_get s='btn_confirm_delete'}" 
		       onclick="location='lib/usermanagement/rolesview.php?confirmed=1&amp;deleterole=1&amp;id={$id}'"/>
		<input type="submit" value="{lang_get s='btn_cancel'}" 
		       onclick="location='lib/usermanagement/rolesview.php'" />
	</div>
{else}
	{if $roles eq ''}
		{lang_get s='no_roles'}
	{else}
		{* data table *}
		<table class="common" width="50%">
			<tr>
				<th width="30%">{lang_get s='th_roles'}</th>
				<th>{lang_get s='th_role_description'}</th>
				<th>{lang_get s='th_delete'}</th>
			</tr>
			{foreach from=$roles item=role}
			<tr>
				<td>
					<a href="lib/usermanagement/rolesedit.php?id={$role.id}">
						{$role.role|escape}
   		      {if $gsmarty_gui->show_icon_edit}
 						  <img title="{lang_get s='alt_edit_role'}" 
 						       alt="{lang_get s='alt_edit_role'}" 
 						       title="{lang_get s='alt_edit_role'}" 
 						       src="{$smarty.const.TL_THEME_IMG_DIR}/icon_edit.png"/>
 						{/if}       
					</a>
				</td>
				<td>
					{$role.notes|strip_tags|strip}
				</td>
				<td>
				{if $role.id > $smarty.const.TL_LAST_SYSTEM_ROLE}
		       <img style="border:none;cursor: pointer;" 
  				            title="{lang_get s='alt_delete_role'}" 
  				            alt="{lang_get s='alt_delete_role'}" 
 					            onclick="delete_confirmation({$role.id},'{$role.role|escape:'javascript'}',
 					                                         '{$del_msgbox_title}','{$warning_msg}');"
  				            src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"/>
				{else}
					{lang_get s='N_A'}
				{/if}
				</td>
			</tr>
			{/foreach}
		</table>
	{/if}
{/if}
</div>

</body>
