{* 
Testlink: smarty template - 
$Id: usersedit.tpl,v 1.9 2007/02/24 08:20:17 franciscom Exp $ 
*}
{* 

20070223 - franciscom - BUGID
 
20070114 - franciscom - 
	1. using smarty config file
	2. improved management of default role id
*}
{include file="inc_head.tpl" jsValidate="yes" openhead="yes"}

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

{literal}
function validateForm(f)
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
  return true;
}
</script>
{/literal}



</head>

<body>
{config_load file="input_dimensions.conf" section='login'}

<h1>{lang_get s='title_user_mgmt'} - {lang_get s='title_account_settings'} </h1>

{***** TABS *****}
<div class="tabMenu">
{if $mgt_users == "yes"}
  {if $userData neq null}
	  <span class="selected">{lang_get s='menu_mod_user'}</span> 
	{else}
	  <span class="selected">{lang_get s='menu_new_user'}</span> 
	{/if}
	<span class="unselected"><a href="lib/usermanagement/usersview.php">{lang_get s='menu_view_users'}</a></span>
{/if}
{if $role_management == "yes"}
	<span class="unselected"><a href="lib/usermanagement/rolesedit.php">{lang_get s='menu_define_roles'}</a></span> 
{/if}	
	<span class="unselected"><a href="lib/usermanagement/rolesview.php">{lang_get s='menu_view_roles'}</a></span> 
{if $tp_user_role_assignment == "yes"}
	<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testproject">{lang_get s='menu_assign_product_roles'}</a></span> 
{/if}	
{if $tproject_user_role_assignment == "yes"}
	<span class="unselected"><a href="lib/usermanagement/usersassign.php?feature=testplan">{lang_get s='menu_assign_testplan_roles'}</a></span>
{/if}
</div>

{include file="inc_update.tpl" result=$result item="user" action="$action" user_feedback=$user_feedback}

<div class="workBack">

<h2>{lang_get s='caption_user_details'}</h2>
<form method="post" action="lib/usermanagement/usersedit.php" 
      name="useredit" onSubmit="javascript:return validateForm(this);">
      
	<input type="hidden" name="user_id" value="{$userData.id}" />
	<input type="hidden" name="user_login" value="{$userData.login}" />
	<table class="common">
		<tr>
			<th>{lang_get s='th_login'}</th>
			<td><input type="text" name="login" size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}" 
			{if $userData neq null}
				disabled="disabled"
			{/if}
			 value="{$userData.login|escape}" />
      {include file="error_icon.tpl" field="login"}
			 </td>
		</tr>
		<tr>
			<th>{lang_get s='th_first_name'}</th>
			<td><input type="text" name="first" value="{$userData.first|escape}" 
			     size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" />
			     {include file="error_icon.tpl" field="first"}
			</td></tr>
		<tr>
			<th>{lang_get s='th_last_name'}</th>
			<td><input type="text" name="last" value="{$userData.last|escape}" 
			     size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" />
 			     {include file="error_icon.tpl" field="last"}
			     </td>
		</tr>

		{if $userData eq null}
		     <tr>
			     <th>{lang_get s='th_password'}:</th>
			    {if $external_password_mgmt eq 0 }
		        <td><input type="password" name="password" 
		                   size="{#PASSWD_SIZE#}" 
		                   maxlength="{#PASSWD_SIZE#}" /></td>
		      {else}      
            <td>{lang_get s='password_mgmt_is_external'}</td>
		      {/if}      
		     </tr>
   {/if}
   
   
		<tr>
			<th>{lang_get s='th_email'}</th>
			<td><input type="text" name="email" value="{$userData.email|escape}" 
			           size="{#EMAIL_SIZE#}" maxlength="{#EMAIL_MAXLEN#}" /></td>
		</tr>
		<tr>
			<th>{lang_get s='th_role'}:</th>
			<td>
			  {* 20070114 - franciscom *}
  	    {assign var=selected_role value=$userData.role_id}
			  {if $userData.role_id eq 0}
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
        {assign var=selected_locale value=$userData.locale}
        {if $userData.locale|count_characters eq 0}
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
			  <input type="checkbox"  name="user_is_active" {if $userData.active eq 1} checked {/if} />
			</td>
		</tr>
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
<hr />

</div>

</body>
</html>
