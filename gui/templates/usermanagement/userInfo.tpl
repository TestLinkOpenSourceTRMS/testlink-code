{* 
Testlink: smarty template - Edit own account 
@filesource userInfo.tpl

@internal revisions
@since 1.9.10
*}
{$cfg_section="login"}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var='labels'
          s='title_account_settings,warning_empty_pwd,warning_different_pwd,never_logged,
             warning_enter_less1,warning_enter_at_least1,warning_enter_at_least2,
             warning_enter_less2,th_login,th_first_name,th_last_name,
             th_email,th_locale,btn_save,th_old_passwd,audit_login_history,none,
             th_new_passwd,th_new_passwd_again,btn_change_passwd,audit_last_failed_logins,
             your_password_is_external,user_api_key,btn_apikey_generate,empty_email_address,
             audit_last_succesful_logins,warning,warning_empty_first_name,no_good_email_address,
             warning_empty_last_name,passwd_dont_match,empty_old_passwd,show_event_history,
             demo_update_user_disabled,last_update,title_personal_data'}

{$action_mgmt="lib/usermanagement/userInfo.php"}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_del_onclick.tpl"}


<script type="text/javascript">
var warning_empty_pwd = "{$labels.warning_empty_pwd|escape:'javascript'}";
var warning_different_pwd = "{$labels.warning_different_pwd|escape:'javascript'}";
var warning_enter_less1 = "{$labels.warning_enter_less1|escape:'javascript'}";
var warning_enter_at_least1 = "{$labels.warning_enter_at_least1|escape:'javascript'}";
var warning_enter_at_least2 = "{$labels.warning_enter_at_least2|escape:'javascript'}";
var warning_enter_less2 = "{$labels.warning_enter_less2|escape:'javascript'}";
var names_max_len={#NAMES_MAXLEN#};
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_name = "{$labels.warning_empty_first_name|escape:'javascript'}";
var warning_empty_last = "{$labels.warning_empty_last_name|escape:'javascript'}";
var warning_passwd_dont_match = "{$labels.passwd_dont_match|escape:'javascript'}";
var warning_empty_old_password = "{$labels.empty_old_passwd|escape:'javascript'}";
var warning_empty_email_address = "{$labels.empty_email_address|escape:'javascript'}";
var warning_no_good_email_address = "{$labels.no_good_email_address|escape:'javascript'}"; 

{literal}
function validatePersonalData(f)
{
  var email_warning;
  var show_email_warning=false;
  
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
      show_email_warning=true;
      email_warning=warning_empty_email_address;
  }
  else 
  { 
    if (!/\w{1,}[@][\w\-]{1,}([.]([\w\-]{1,})){1,3}$/.test(f.emailAddress.value))
    {
      show_email_warning=true;
      email_warning=warning_no_good_email_address;
    }
  }

  if( show_email_warning )
  {
      alert_message(alert_box_title,email_warning);
      selectField(f, 'emailAddress');
      return false;
  }

  return true;
}
{/literal}

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

function refreshLastUpdate (last_update) 
{
  document.getElementById("last_update").firstChild.nodeValue = last_update;
}
</script>
</head>

<body>

<h1 class="title">{$labels.title_account_settings}</h1>

{include file="inc_update.tpl" user_feedback=$user_feedback}

<div class="workBack">


<h2>{$labels.title_personal_data}</h2>
<form method="post" action="{$action_mgmt}" onsubmit="return validatePersonalData(this)">
  <input type="hidden" name="doAction" value="editUser" />
  <table class="common" width="50%">
    <tr>
      <th width="20%">{$labels.th_login}</th>
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
                 size="{#EMAIL_SIZE#}" maxlength="{#EMAIL_MAXLEN#}" required />
                 {include file="error_icon.tpl" field="emailAddress"}
      </td>
    </tr>
    <tr>
      <th>{$labels.th_locale}</th>
      <td>
        <script type="text/javascript">
        js_locale = new Array();
        {foreach key=locale item=value from=$gui->optLocale}
          js_locale['{$locale}'] = "{lang_get s='last_update' locale=$locale}";
        {/foreach}
        </script>
        
        <select name="locale" onchange="javascript:refreshLastUpdate(js_locale[this.options[this.selectedIndex].value]);">
        {html_options options=$gui->optLocale selected=$user->locale}
        </select>
        <span id="last_update">{$labels.last_update}</span>
      </td>
    </tr>
  </table>
  <div class="groupBtn">
    {if $tlCfg->demoMode}
      {$labels.demo_update_user_disabled}
    {else}
      <input type="submit" value="{$labels.btn_save}" />
    {/if} 
  </div>
</form>

<hr />
<h2>{lang_get s="title_personal_passwd"}</h2>
{if $external_password_mgmt eq 0}
  <form name="changePass" method="post" action="{$action_mgmt}"
    onsubmit="return checkPasswords('oldpassword','newpassword','newpassword_check');">
    <input type="hidden" name="doAction" value="changePassword" />
    <table class="common">
      <tr><th>{$labels.th_old_passwd}</th>
        <td><input type="password" name="oldpassword"  id="oldpassword"
                   size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" required /></td></tr>
      <tr><th>{$labels.th_new_passwd}</th>
        <td><input type="password" name="newpassword" id="newpassword"
                   size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" required /></td></tr>
      <tr><th>{$labels.th_new_passwd_again}</th>
        <td><input type="password" name="newpassword_check" id="newpassword_check"
                   size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" required /></td></tr>
    </table>
    <div class="groupBtn">
    {if $tlCfg->demoMode}
      {$labels.demo_update_user_disabled}
    {else}
      <input type="submit" value="{$labels.btn_change_passwd}" />
    {/if} 
    </div>
  </form>
{else}
   <p>{$labels.your_password_is_external}<p>
{/if}

{if $tlCfg->api->enabled}
<hr />
<h2>{lang_get s="title_api_interface"}</h2>
<div>
  <form name="genApi" method="post" action="{$action_mgmt}">
    <input type="hidden" name="doAction" value="genAPIKey" />
    <p>{$labels.user_api_key} = {$user->userApiKey|escape}</p>
    <div class="groupBtn">
      <input type="submit" value="{$labels.btn_apikey_generate}" />
    </div>
  </form>
</div>
{/if}


<hr />
<h2>{$labels.audit_login_history}
  {if $mgt_view_events == "yes"}
  <img style="margin-left:5px;" class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/question.gif" onclick="showEventHistoryFor('{$user->dbID}','users')" alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
</h2>
{/if}
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
  {if $loginHistory->failed != ''}
  <div>
    <h3>{$labels.audit_last_failed_logins}</h3>
    {foreach from=$loginHistory->failed item=event}
    <span>{localize_timestamp ts=$event->timestamp}</span>
    <span>{$event->description|escape}</span>
    <br/>
    {/foreach}
  </div>
  {/if}

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
