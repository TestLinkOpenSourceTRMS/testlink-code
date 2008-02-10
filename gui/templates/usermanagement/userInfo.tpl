{* Testlink: smarty template - Edit own account *}
{* $Id: userInfo.tpl,v 1.8 2008/02/10 18:45:01 franciscom Exp $ *}
{* 
*}
{assign var="cfg_section" value="login" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var='labels' 
          s='title_account_settings,warning_empty_pwd,warning_different_pwd,never_logged,
             warning_enter_less1,warning_enter_at_least1,warning_enter_at_least2,
             warning_enter_less2,th_login,th_first_name,th_last_name,
             th_email,th_locale,btn_save,th_old_passwd,audit_login_history,none,
             th_new_passwd,th_new_passwd_again,btn_change_passwd,audit_last_failed_logins,
             your_password_is_external,user_api_key,btn_apikey_generate,empty_email_address,
             audit_last_succesful_logins,warning,warning_empty_first_name,
             warning_empty_last_name,passwd_dont_match,empty_old_passwd,'}

{assign var="action_mgmt" value="lib/usermanagement/userInfo.php"}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_del_onclick.tpl"}


{literal}
<script type="text/javascript">
{/literal}
var warning_empty_pwd = "{$labels.warning_empty_pwd}";
var warning_different_pwd = "{$labels.warning_different_pwd}";
var warning_enter_less1 = "{$labels.warning_enter_less1}";
var warning_enter_at_least1 = "{$labels.warning_enter_at_least1}";
var warning_enter_at_least2 = "{$labels.warning_enter_at_least2}";
var warning_enter_less2 = "{$labels.warning_enter_less2}";
var names_max_len={#NAMES_MAXLEN#};
var alert_box_title = "{$labels.warning}";
var warning_empty_name = "{$labels.warning_empty_first_name}";
var warning_empty_last = "{$labels.warning_empty_last_name}";
var warning_passwd_dont_match = "{$labels.passwd_dont_match}";
var warning_empty_old_password = "{$labels.empty_old_passwd}";
var warning_empty_email_address = "{$labels.empty_email_address}";

{literal}
function validatePersonalData(f)
{
  if (isWhitespace(f.firstName.value)) 
  {
      alert_message(alert_box_title,warning_empty_name);
      selectField(f, 'firstName');
      return false;
  }

  if (isWhitespace(f.lastName.value)) 
  {
      alert_message(alert_box_title,warning_empty_last);
      selectField(f, 'lastName');
      return false;
  }
  
  if (isWhitespace(f.emailAddress.value)) 
  {
      alert_message(alert_box_title,warning_empty_email_address);
      selectField(f, 'emailAddress');
      return false;
  }
  
  return true;
}

function checkPasswords(oldp,newp,newp_check)
{

    var oldvalue=document.getElementById(oldp).value;
    
    if (isWhitespace(oldvalue)) 
    {
        alert_message(alert_box_title,warning_empty_old_password);
        return false;
    }
   
    if( !validatePassword(newp,newp_check) )
    {
      alert_message(alert_box_title,warning_passwd_dont_match);
      return false;
    }
    return true;
}
</script>
{/literal}
</head>

<body>

<h1>{$labels.title_account_settings}</h1>

{include file="inc_update.tpl" user_feeback=$user_feeback}

<div class="workBack">


<h2>{lang_get s="title_personal_data"}</h2>
<form method="post" action="{$action_mgmt}" onsubmit="return validatePersonalData(this)">
	<input type="hidden" name="id" value="{$user->dbID}" />
	<table class="common">
		<tr>
			<th>{$labels.th_login}</th>
			<td>{$user->login}</td>
		</tr>
		<tr>
			<th>{$labels.th_first_name}</th>
			<td><input type="text" name="firstName" value="{$user->firstName|escape}" 
			           size="{#NAMES_SIZE#}" maxlength="{#NAMES_MAXLEN#}" />
			  				{include file="error_icon.tpl" field="firstName"}
			</td>
		</tr>
		<tr>
			<th>{$labels.th_last_name}</th>
			<td><input type="text" name="lastName" value="{$user->lastName|escape}" 
			           size="{#NAMES_SIZE#}" maxlength="{#NAMES_MAXLEN#}" />
						  	 {include file="error_icon.tpl" field="lastName"}
			</td>
		</tr>
		<tr>
			<th>{$labels.th_email}</th>
			<td><input type="text" name="emailAddress" value="{$user->emailAddress|escape}" 
			           size="{#EMAIL_SIZE#}" maxlength="{#EMAIL_MAXLEN#}" />
						  	 {include file="error_icon.tpl" field="emailAddress"}
			</td>
		</tr>
		<tr>
			<th>{$labels.th_locale}</th>
			<td>		   
				<select name="locale">
				{html_options options=$optLocale selected=$user->locale}
				</select>	
			</td>
		</tr>
	</table>
	<div class="groupBtn">	
		<input type="submit" name="editUser" value="{$labels.btn_save}" />
	</div>
</form>

<hr />
<h2>{lang_get s="title_personal_passwd"}</h2>
{if $external_password_mgmt eq 0 }
	<form name="changePass" method="post" action="{$action_mgmt}" 
		onsubmit="return checkPasswords('oldpassword','newpassword','newpassword_check');">
		<input type="hidden" name="id" value="{$user->dbID}" />
		<table class="common">
			<tr><th>{$labels.th_old_passwd}</th>
				<td><input type="password" name="oldpassword"  id="oldpassword"
				           size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></td></tr>
			<tr><th>{$labels.th_new_passwd}</th>
				<td><input type="password" name="newpassword" id="newpassword"
				           size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></td></tr>
			<tr><th>{$labels.th_new_passwd_again}</th>
				<td><input type="password" name="newpassword_check" id="newpassword_check" 
				           size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></td></tr>
		</table>
		<div class="groupBtn">	
			<input type="submit" name="changePassword" value="{$labels.btn_change_passwd}" />
		</div>
	</form>
{else}
   <p>{$labels.your_password_is_external}<p>
{/if}

{if $api_ui_show eq 1}
<hr />
<h2>{lang_get s="title_api_interface"}</h2>
<div>									
	<form name="genApi" method="post" action="{$action_mgmt}">
	<input type="hidden" name="id" value="{$user->dbID}" />
	<p>{$labels.user_api_key} = {$user->userApiKey|escape}</p>
	<div class="groupBtn">	
		<input type="submit" name="genApiKey" value="{$labels.btn_apikey_generate}" />
	</div>
	</form>
</div>
{/if}


<hr />
<h2>{$labels.audit_login_history}</h2>
<div>
	<h3>{$labels.audit_last_succesful_logins}</h3>
	{if $loginHistory->ok != ''} 
	{foreach from=$loginHistory->ok item=event}
	<span>{localize_timestamp ts=$event->timestamp}</span>
	<span>{$event->description|escape}</span>
	<br/>
	{/foreach}
	{else}
	  {$labels.never_logged}
	{/if}
	
	
</div>
<div>
  {if $loginHistory->failed != ''} 
	  <h3>{$labels.audit_last_failed_logins}</h3>
	  {foreach from=$loginHistory->failed item=event}
	  <span>{localize_timestamp ts=$event->timestamp}</span>
	  <span>{$event->description|escape}</span>
	  <br/>
	  {/foreach}
  {/if}
</div>

</div>

{if $update_title_bar == 1}
{literal}
<script type="text/javascript">
	parent.titlebar.location.reload();
</script>
{/literal}
{/if}
</body>
</html>
