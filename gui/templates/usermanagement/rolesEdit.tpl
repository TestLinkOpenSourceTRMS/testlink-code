{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: rolesEdit.tpl,v 1.7 2007/12/27 18:50:23 schlundus Exp $
Purpose: smarty template - create/edit user role 

rev :
     20071227 - franciscom - look and feel.
     
     20070725 - franciscom
     - added js check on role name
     - use of input_dimensions.conf

     20070829 - jbarchibald
      -  bug 1000  - Testplan User Role Assignments
*}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}
{include file="inc_jsCheckboxes.tpl"}

{literal}
<script type="text/javascript">
{/literal}
{lang_get s='warning,warning_modify_role,warning_empty_role_name,error_role_no_rights' var="labels"}

var alert_box_title = "{$labels.warning}";
var warning_modify_role = "{$labels.warning_modify_role}";
var warning_empty_role_name = "{$labels.warning_empty_role_name}";
var warning_error_role_no_rights = "{$labels.error_role_no_rights}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.rolename.value)) 
  {
      alert_message(alert_box_title,warning_empty_role_name);
      selectField(f, 'rolename');
      return false;
  }

  /* 20071227 - franciscom */
  if( checkbox_count_checked(f.id) == 0)
  {
      alert_message(alert_box_title,warning_error_role_no_rights);
      return false;
  } 
 
  return true;
}
</script>
{/literal}
</head>


<body>
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1>{lang_get s='title_user_mgmt'} - {lang_get s='caption_define_role'}</h1>

<div class="tabMenu">
{if $mgt_users == "yes"}
	<span class="unselected"><a href="lib/usermanagement/usersEdit.php">{lang_get s='menu_new_user'}</a></span> 
	<span class="unselected"><a href="lib/usermanagement/usersView.php">{lang_get s='menu_view_users'}</a></span>
{/if}
{if $role_management == "yes"}
	<span class="selected">{lang_get s='menu_define_roles'}</span> 
{/if}
	<span class="unselected"><a href="lib/usermanagement/rolesView.php">{lang_get s='menu_view_roles'}</a></span>
{if $tproject_user_role_assignment == "yes"}
	<span class="unselected"><a href="lib/usermanagement/usersAssign.php?feature=testproject">{lang_get s='menu_assign_testproject_roles'}</a></span> 
{/if}
{if $tp_user_role_assignment == "yes"}
	<span class="unselected"><a href="lib/usermanagement/usersAssign.php?feature=testplan">{lang_get s='menu_assign_testplan_roles'}</a></span>
{/if}
</div>

{* show SQL result *}
{include file="inc_update.tpl" result=$sqlResult item="Role" name=$role->name action="$action"}

{* Create Form *}
<div class="workBack">

	<form name="rolesedit" id="rolesedit" 
		method="post" action="lib/usermanagement/rolesEdit.php" 
	{if $role_management == "yes"}
	  onSubmit="javascript:return validateForm(this);"	
	{else}	
		onsubmit="return false" 
	{/if}
	>
	<input type="hidden" name="roleid" value="{$role->dbID}" />
	<table class="common">
		<tr><th>{lang_get s='th_rolename'}</th></tr>
		<tr><td>
			   <input type="text" name="rolename" 
			          size="{#ROLENAME_SIZE#}" maxlength="{#ROLENAME_MAXLEN#}" value="{$role->name|escape}"/>
 				 {include file="error_icon.tpl" field="rolename"}
		    </td></tr>
		<tr><th>{lang_get s='th_rights'}</th></tr>
		<tr>
			<td>
				<table>
				<tr>
					<td><fieldset class="x-fieldset x-form-label-left"><legend >{lang_get s='th_tp_rights'}</legend>
							{foreach from=$tpRights item=id key=k}
							<input class="tl-input" type="checkbox" name="grant[{$k}]" {$checkboxStatus[$k]}/>{$id}<br />
							{/foreach}
						</fieldset>
					</td>
					<td>
						<fieldset class="x-fieldset x-form-label-left"><legend >{lang_get s='th_mgttc_rights'}</legend>
						{foreach from=$tcRights item=id key=k}
						<input class="tl-input" type="checkbox" name="grant[{$k}]" {$checkboxStatus[$k]} />{$id}<br />
						{/foreach}
						</fieldset>
					</td>
					<td>
						<fieldset class="x-fieldset x-form-label-left"><legend >{lang_get s='th_req_rights'}</legend>
						{foreach from=$reqRights item=id key=k}
						<input class="tl-input" type="checkbox" name="grant[{$k}]" {$checkboxStatus[$k]} />{$id}<br />
						{/foreach}
						</fieldset>
					</td>
					<td>
						<fieldset class="x-fieldset x-form-label-left"><legend >{lang_get s='th_product_rights'}</legend>
						{foreach from=$pRights item=id key=k}
						<input class="tl-input" type="checkbox" name="grant[{$k}]" {$checkboxStatus[$k]} />{$id}<br />
						{/foreach}
						</fieldset>
					</td>
				</tr>
				<tr>
					<td><fieldset class="x-fieldset x-form-label-left"><legend >{lang_get s='th_user_rights'}</legend>
							{foreach from=$uRights item=id key=k}
							<input class="tl-input" type="checkbox" name="grant[{$k}]" {$checkboxStatus[$k]} />{$id}<br />
							{/foreach}
						</fieldset>
					</td>
					<td><fieldset class="x-fieldset x-form-label-left"><legend >{lang_get s='th_kw_rights'}</legend>
							{foreach from=$kwRights item=id key=k}
							<input class="tl-input" type="checkbox" name="grant[{$k}]" {$checkboxStatus[$k]} />{$id}<br />
							{/foreach}
						</fieldset>
					</td>
					<td><fieldset class="x-fieldset x-form-label-left"><legend >{lang_get s='th_cf_rights'}</legend>
							{foreach from=$cfRights item=id key=k}
							<input class="tl-input" type="checkbox" name="grant[{$k}]" {$checkboxStatus[$k]} />{$id}<br />
							{/foreach}
						</fieldset>
					</td>
				</tr>

			</table>
			</td>
		</tr>
		<tr><th>{lang_get s='enter_role_notes'}</th></tr>
		<tr>
			<td width="80%">{$notes}</td>
		</tr>

	</table>
	{if $role_management == "yes" && $role->dbID != $noRightsRole}
	
		<div class="groupBtn">	
		<input type="hidden" name="doAction" value="{$action_type}" />
		<input type="submit" name="role_mgmt" value="{lang_get s='btn_save'}" 
		         {if $role != 0 && $affectedUsers neq null} onClick="return modifyRoles_warning(){/if}"/>
	{/if}
	</div>
	<br />
	{if $affectedUsers neq null}
		<table class="common" style="width:50%">
		<caption>{lang_get s='caption_possible_affected_users'}</caption>
		{foreach from=$affectedUsers item=user}
		<tr>
			<td>{$user->getDisplayName()|escape}</td>
		</tr>
		{/foreach}
		</table>
	{/if}
	</form>
	
</div>

</body>
</html>