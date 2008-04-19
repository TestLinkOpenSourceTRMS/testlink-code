{*
Testlink: smarty template -
$Id: usersEdit.tpl,v 1.14 2008/04/19 16:28:37 franciscom Exp $

20080419 - franciscom - BUGID 1496
         -  bug 1000  - Testplan User Role Assignments
*}

{config_load file="input_dimensions.conf" section='login'}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_del_onclick.tpl"}

{lang_get var="labels"
          s='warning_empty_login,warning_empty_first_name,warning,btn_save,
             warning_empty_pwd,warning_different_pwd,empty_email_address,
             title_user_mgmt,title_account_settings,menu_edit_user,menu_new_user,
             menu_view_users,menu_define_roles,menu_view_roles,
             menu_assign_testproject_roles,warning_empty_last_name,
             menu_assign_testplan_roles,caption_user_details,
             th_login,th_first_name,th_last_name,th_password,th_email,
             th_role,th_locale,th_active,password_mgmt_is_external,
             btn_upd_user_data,btn_add,btn_cancel,button_reset_password'}

{literal}
<script type="text/javascript">
{/literal}
var alert_box_title = "{$labels.warning}";
var warning_empty_login      = "{$labels.warning_empty_login}";
var warning_empty_first_name = "{$labels.warning_empty_first_name}";
var warning_empty_last_name  = "{$labels.warning_empty_last_name}";
var warning_empty_pwd = "{$labels.warning_empty_pwd}";
var warning_different_pwd = "{$labels.warning_different_pwd}";
var warning_empty_email = "{$labels.empty_email_address}";


{literal}
function validateForm(f,check_password)
{
  if (isWhitespace(f.login.value))
  {
      alert_message(alert_box_title,warning_empty_login);
      selectField(f, 'login');
      return false;
  }

  if (isWhitespace(f.firstName.value))
  {
      alert_message(alert_box_title,warning_empty_first_name);
      selectField(f, 'firstName');
      return false;
  }

  if (isWhitespace(f.lastName.value))
  {
      alert_message(alert_box_title,warning_empty_last_name);
      selectField(f, 'lastName');
      return false;
  }

  if( check_password )
  {
    if (isWhitespace(f.password.value))
    {
        alert_message(alert_box_title,warning_empty_pwd);
        selectField(f, 'password');
        return false;
    }
  }

  if (isWhitespace(f.emailAddress.value))
  {
      alert_message(alert_box_title,warning_empty_email);
      selectField(f, 'emailAddress');
      return false;
  }

  return true;
}
</script>
{/literal}


{assign var="ext_version" value="-2.0"}
<link rel="stylesheet" type="text/css" href="{$basehref}third_party/ext{$ext_version}/css/ext-all.css" />
</head>

<body>

<h1>{$labels.title_user_mgmt} - {$labels.title_account_settings} </h1>

{assign var="user_id" value=''}
{assign var="user_login" value=''}
{assign var="user_login_readonly" value=''}
{assign var="reset_password_enabled" value=0}
{assign var="show_password_field" value=1}


{if $operation == 'doCreate' }
   {assign var="check_password" value=1}
   {if $userData neq null}
       {assign var="user_login" value=$userData->login}
   {/if}
{else}
   {assign var="check_password" value=0}
   {assign var="user_id" value=$userData->dbID}
   {assign var="user_login" value=$userData->login}
   {assign var="user_login_readonly" value='readonly="readonly"'}
   {assign var="reset_password_enabled" value=1}
   {assign var="show_password_field" value=0}
{/if}

{if $external_password_mgmt eq 1 }
  {assign var="check_password" value=0}
  {assign var="reset_password_enabled" value=0}
  {assign var="show_password_field" value=0}
{/if}



{***** TABS *****}
{include file="usermanagement/tabsmenu.tpl"}

{include file="inc_update.tpl" result=$result item="user" action="$action" user_feedback=$user_feedback}

<div class="workBack">
<form method="post" action="lib/usermanagement/usersEdit.php" class="x-form"
      name="useredit" onSubmit="javascript:return validateForm(this,{$check_password});">

	<input type="hidden" name="user_id" value="{$user_id}" />
	<input type="hidden" id="user_login" name="user_login" value="{$user_login}" />

  <fieldset class="x-fieldset x-form-label-left" style="width:50%;">
  <legend class="x-fieldset-header x-unselectable" style="-moz-user-select: none;">
  {$labels.caption_user_details}
  </legend>
	<table class="common">
		<tr>
			<th style="background:none;">{$labels.th_login}</th>
			<td><input type="text" name="login" size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}"
			{$user_login_readonly} value="{$userData->login|escape}" />
      {include file="error_icon.tpl" field="login"}
			 </td>
		</tr>
		<tr>
			<th style="background:none;">{$labels.th_first_name}</th>
			<td><input type="text" name="firstName" value="{$userData->firstName|escape}"
			     size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" />
			     {include file="error_icon.tpl" field="firstName"}
			</td></tr>
		<tr>
			<th style="background:none;">{$labels.th_last_name}</th>
			<td><input type="text" name="lastName" value="{$userData->lastName|escape}"
			     size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" />
 			     {include file="error_icon.tpl" field="lastName"}
			     </td>
		</tr>

		{if $show_password_field}
		     <tr>
			    {if $external_password_mgmt eq 0 }
 			      <th style="background:none;">{$labels.th_password}</th>
		        <td><input type="password" id="password" name="password"
		                   size="{#PASSWD_SIZE#}"
		                   maxlength="{#PASSWD_SIZE#}" />
		            {include file="error_icon.tpl" field="password"}
		        </td>
		      {/if}
		     </tr>
   {/if}


		<tr>
			<th style="background:none;">{$labels.th_email}</th>
			<td><input type="text" id="email" name="emailAddress" value="{$userData->emailAddress|escape}"
			           size="{#EMAIL_SIZE#}" maxlength="{#EMAIL_MAXLEN#}" />
          {include file="error_icon.tpl" field="emailAddress"}
			</td>
		</tr>
		<tr>
			<th style="background:none;">{$labels.th_role}</th>
			<td>
		  	{assign var=selected_role value=$userData->globalRoleID}
			  {if $userData->globalRoleID eq 0}
 			      {assign var=selected_role value=$tlCfg->default_roleid}
			  {/if}
				<select name="rights_id">
				{foreach key=role_id item=role from=$optRights}
		        <option value="{$role_id}" {if $role_id == $selected_role} selected="selected" {/if}>
					{$role->name|escape}
				</option>
				{/foreach}
				</select>
			</td>
		</tr>

		<tr>
			<th style="background:none;">{$labels.th_locale}</th>
			<td>
        {assign var=selected_locale value=$userData->locale}
        {if $userData->locale|count_characters eq 0}
           {assign var=selected_locale value=$locale}
        {/if}

				<select name="locale">
				{html_options options=$optLocale selected=$selected_locale}
				</select>
			</td>
		</tr>

		<tr>
			<th style="background:none;">{$labels.th_active}</th>
			<td>
			  <input type="checkbox"  name="user_is_active" {if $userData->bActive eq 1} checked {/if} />
			</td>
		</tr>

    {if $external_password_mgmt eq 1 }
      <td>{$labels.password_mgmt_is_external}</td>
    {/if}

	</table>

	<div class="groupBtn">
	<input type="hidden" name="doAction" id="doActionUserEdit" value="{$operation}">
	<input type="submit" name="do_update"   value="{$labels.btn_save}" />
	<input type="button" name="cancel" value="{$labels.btn_cancel}"
			onclick="javascript: location.href=fRoot+'lib/usermanagement/usersView.php';" />

	</div>
</fieldset>
</form>

{if $reset_password_enabled}
<br />
<form method="post" action="lib/usermanagement/usersEdit.php" name="user_reset_password">
	<input type="hidden" name="doAction" id="doActionResetPassword" value="resetPassword" />
  <input type="hidden" name="user_id" value="{$user_id}" />
	<input type="submit" id="do_reset_password" name="do_reset_password" value="{$labels.button_reset_password}" />
</form>
{/if}

</div>

</body>
</html>
