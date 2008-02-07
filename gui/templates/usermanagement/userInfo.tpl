{* Testlink: smarty template - Edit own account *}
{* $Id: userInfo.tpl,v 1.7 2008/02/07 13:54:43 franciscom Exp $ *}
{* 
*}
{assign var="cfg_section" value="login" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var='labels' 
          s='title_account_settings,warning_empty_pwd,warning_different_pwd,
             warning_enter_less1,warning_enter_at_least1,warning_enter_at_least2,
             warning_enter_less2,th_login,th_first_name,th_last_name,
             th_email,th_locale,btn_save,th_old_passwd,
             th_new_passwd,th_new_passwd_again,btn_change_passwd,
             your_password_is_external,user_api_key,btn_apikey_generate'}

{include file="inc_head.tpl" jsValidate="yes"}

<body>

<h1>{$labels.title_account_settings}</h1>

{include file="inc_update.tpl" result=$msg action="updated" item="user" name=$user->login}

<div class="workBack">

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

{literal}
function valAllText(form)
{
	if (valTextLength(form.first,names_max_len,1) && valTextLength(form.last,names_max_len,1))
	{
		return true;
	}
	return false;
}
</script>
{/literal}

<h2>{lang_get s="title_personal_data"}</h2>
<form method="post" action="lib/usermanagement/userInfo.php" onsubmit="return valAllText(this)">
	<input type="hidden" name="id" value="{$user->dbID}" />
	<table class="common">
		<tr>
			<th>{$labels.th_login}</th>
			<td>{$user->login}</td>
		</tr>
		<tr>
			<th>{$labels.th_first_name}</th>
			<td><input type="text" name="first" value="{$user->firstName|escape}" 
			           size="{#NAMES_SIZE#}" maxlength="{#NAMES_MAXLEN#}" /></td>
		</tr>
		<tr>
			<th>{$labels.th_last_name}</th>
			<td><input type="text" name="last" value="{$user->lastName|escape}" 
			           size="{#NAMES_SIZE#}" maxlength="{#NAMES_MAXLEN#}" /></td>
		</tr>
		<tr>
			<th>{$labels.th_email}</th>
			<td><input type="text" name="email" value="{$user->emailAddress|escape}" 
			           size="{#EMAIL_SIZE#}" maxlength="{#EMAIL_MAXLEN#}" /></td>
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
	<form name="changePass" method="post" action="lib/usermanagement/userInfo.php" 
		onsubmit="return validatePassword(document.changePass);">
		<input type="hidden" name="id" value="{$user->dbID}" />
		<table class="common">
			<tr><th>{$labels.th_old_passwd}</th>
				<td><input type="password" name="old" size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></td></tr>
			<tr><th>{$labels.th_new_passwd}</th>
				<td><input type="password" name="new1" size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></td></tr>
			<tr><th>{$labels.th_new_passwd_again}</th>
				<td><input type="password" name="new2" size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" /></td></tr>
		</table>
		<div class="groupBtn">	
			<input type="submit" name="changePasswd" value="{$labels.btn_change_passwd}" />
		</div>
	</form>
{else}
   <p>{$labels.your_password_is_external}<p>
{/if}

{if $api_ui_show eq 1}
<hr />
<h2>{lang_get s="title_api_interface"}</h2>
<div>									
	<form name="genApi" method="post" action="lib/usermanagement/userInfo.php">
	<input type="hidden" name="id" value="{$user->dbID}" />
	<p>{$labels.user_api_key} = {$user->userApiKey|escape}</p>
	<div class="groupBtn">	
		<input type="submit" name="genApi" value="{$labels.btn_apikey_generate}" />
	</div>
	</form>
</div>
{/if}


<hr />
<h2>{lang_get s="audit_login_history"}</h2>
<div>
	<h3>{lang_get s="audit_last_succesful_logins"}</h3>
	{foreach from=$successfulLogins item=event}
	<span>{localize_timestamp ts=$event->timestamp}</span>
	<span>{$event->description|escape}</span>
	<br/>
	{/foreach}
</div>
<div>
	<h3>{lang_get s="audit_last_failed_logins"}</h3>
	{foreach from=$failedLogins item=event}
	<span>{localize_timestamp ts=$event->timestamp}</span>
	<span>{$event->description|escape}</span>
	<br/>
	{foreachelse}
	<span>{lang_get s="none"}</span>
	{/foreach}
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
