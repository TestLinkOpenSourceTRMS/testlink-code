{* 
Testlink: smarty template - 
$Id: usersEdit.tpl,v 1.2 2007/12/22 09:58:58 schlundus Exp $ 

20070829 - jbarchibald
      -  bug 1000  - Testplan User Role Assignments
*}

{config_load file="input_dimensions.conf" section='login'}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{literal}
<script type="text/javascript">
{/literal}
var warning_empty_login      = "{lang_get s='warning_empty_login'}";
var warning_empty_first_name = "{lang_get s='warning_empty_first_name'}";
var warning_empty_last_name  = "{lang_get s='warning_empty_last_name'}";

var warning_empty_pwd = "{lang_get s='warning_empty_pwd'}";
var warning_different_pwd = "{lang_get s='warning_different_pwd'}";
var warning_enter_less1 = "{lang_get s='warning_enter_less1'}";
var warning_enter_at_least1 = "{lang_get s='warning_enter_at_least1'}";
var warning_enter_at_least2 = "{lang_get s='warning_enter_at_least2'}";
var warning_enter_less2 = "{lang_get s='warning_enter_less2'}";
var warning_empty_email = "{lang_get s='empty_email_address'}";


{literal}
function validateForm(f,check_password)
{
  if (isWhitespace(f.login.value)) 
  {
      alert(warning_empty_login);
      selectField(f, 'login');
      return false;
  }

  if (isWhitespace(f.first.value)) 
  {
      alert(warning_empty_first_name);
      selectField(f, 'first');
      return false;
  }
  
  if (isWhitespace(f.last.value)) 
  {
      alert(warning_empty_last_name);
      selectField(f, 'last');
      return false;
  }
  
  if( check_password )
  {
    if (isWhitespace(f.password.value)) 
    {
        alert(warning_empty_pwd);
        selectField(f, 'password');
        return false;
    }
  }

  if (isWhitespace(f.email.value)) 
  {
      alert(warning_empty_email);
      selectField(f, 'email');
      return false;
  }

  return true;
}
</script>
{/literal}



</head>

<body>

<h1>{lang_get s='title_user_mgmt'} - {lang_get s='title_account_settings'} </h1>

{* This check allows us to understand if we are creating a new user *}
{assign var="user_id" value=''}
{assign var="user_login" value=''}
{assign var="check_password" value=1}
{if $external_password_mgmt eq 1 }
  {assign var="check_password" value=0}
{/if}

{if $userData neq null}
  {assign var="check_password" value=0}
  {assign var="user_id" value=$userData->dbID}
  {assign var="user_login" value=$userData->login}
{/if}


{***** TABS *****}
<div class="tabMenu">
{if $mgt_users == "yes"}
  {if $userData neq null}
	  <span class="selected">{lang_get s='menu_edit_user'}</span> 
	{else}
	  <span class="selected">{lang_get s='menu_new_user'}</span> 
	{/if}
	<span class="unselected"><a href="lib/usermanagement/usersview.php">{lang_get s='menu_view_users'}</a></span>
{/if}
{if $role_management == "yes"}
	<span class="unselected"><a href="lib/usermanagement/rolesedit.php">{lang_get s='menu_define_roles'}</a></span> 
{/if}	
	<span class="unselected"><a href="lib/usermanagement/rolesview.php">{lang_get s='menu_view_roles'}</a></span> 
{if $tproject_user_role_assignment == "yes"}
	<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testproject">{lang_get s='menu_assign_testproject_roles'}</a></span> 
{/if}	
{if $tp_user_role_assignment == "yes"}
	<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testplan">{lang_get s='menu_assign_testplan_roles'}</a></span>
{/if}
</div>

{include file="inc_update.tpl" result=$result item="user" action="$action" user_feedback=$user_feedback}

<div class="workBack">

<h2>{lang_get s='caption_user_details'}</h2>
<form method="post" action="lib/usermanagement/usersedit.php" 
      name="useredit" onSubmit="javascript:return validateForm(this,{$check_password});">
      
	<input type="hidden" name="user_id" value="{$user_id}" />
	<input type="hidden" id="user_login" name="user_login" value="{$user_login}" />
	<table class="common">
		<tr>
			<th>{lang_get s='th_login'}</th>
			<td><input type="text" name="login" size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}" 
			{if $userData neq null}
				disabled="disabled"
			{/if}
			 value="{$userData->login|escape}" />
      {include file="error_icon.tpl" field="login"}
			 </td>
		</tr>
		<tr>
			<th>{lang_get s='th_first_name'}</th>
			<td><input type="text" name="first" value="{$userData->firstName|escape}" 
			     size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" />
			     {include file="error_icon.tpl" field="first"}
			</td></tr>
		<tr>
			<th>{lang_get s='th_last_name'}</th>
			<td><input type="text" name="last" value="{$userData->lastName|escape}" 
			     size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" />
 			     {include file="error_icon.tpl" field="last"}
			     </td>
		</tr>

		{if $userData eq null}
		     <tr>
			    {if $external_password_mgmt eq 0 }
 			      <th>{lang_get s='th_password'}</th>
		        <td><input type="password" id="password" name="password" 
		                   size="{#PASSWD_SIZE#}" 
		                   maxlength="{#PASSWD_SIZE#}" />
		            {include file="error_icon.tpl" field="password"}       
		        </td>
		      {/if}      
		     </tr>
   {/if}
   
   
		<tr>
			<th>{lang_get s='th_email'}</th>
			<td><input type="text" id="email" name="email" value="{$userData->emailAddress|escape}" 
			           size="{#EMAIL_SIZE#}" maxlength="{#EMAIL_MAXLEN#}" />
          {include file="error_icon.tpl" field="email"}       
			</td>
		</tr>
		<tr>
			<th>{lang_get s='th_role'}</th>
			<td>
		  	   {assign var=selected_role value=$userData->globalRoleID}
			  {if $userData->globalRoleID eq 0}
        	  {assign var=selected_role value=$smarty.const.TL_DEFAULT_ROLEID}	  
			  {/if}
				<select name="rights_id"> 
				{html_options options=$optRights 
				              selected=$selected_role}
				</select>
			</td>
		</tr>

		<tr>
			<th>{lang_get s='th_locale'}</th>
			<td>		   
        {* 20060425 - franciscom - better management of default locale 
           Very important: the locale member that holds the value of TL_DEFAULT_LOCALE
                           is declared in tlsmarty.inc.php
        *}
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
			<th>{lang_get s='th_active'}</th>
			<td> 
			  <input type="checkbox"  name="user_is_active" {if $userData->bActive eq 1} checked {/if} />
			</td>
		</tr>

    {if $external_password_mgmt eq 1 }
      <td>{lang_get s='password_mgmt_is_external'}</td>    
    {/if}

	</table>
	
	<div class="groupBtn">	
	{if $userData neq null}
		<input type="submit" name="do_update" value="{lang_get s='btn_upd_user_data'}" />
	{else}
		<input type="submit" name="do_update" value="{lang_get s='btn_add'}" />
	{/if}
	
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: location.href=fRoot+'lib/usermanagement/usersview.php';" />

	</div>
</form>
    
{if $userData neq null and $external_password_mgmt eq 0}
<br /><form method="post" action="lib/usermanagement/usersedit.php" name="user_reset_password">
  	<input type="hidden" name="user_id" value="{$user_id}" />
	<input type="submit" id="do_reset_password" name="do_reset_password" value="{lang_get s='button_reset_password'}" />
</form>
{/if}

<hr />

</div>

</body>
</html>
