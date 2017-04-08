{*
Testlink: smarty template -
@filesource usersEdit.tpl

@internal revisions
*}

{config_load file="input_dimensions.conf" section='login'}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_del_onclick.tpl"}

{lang_get var="labels"
          s='warning_empty_login,warning_empty_first_name,warning,btn_save,
             warning_empty_pwd,warning_different_pwd,empty_email_address,
             title_user_mgmt,title_account_settings,menu_edit_user,menu_new_user,
             menu_view_users,menu_define_roles,menu_view_roles,no_good_email_address,expiration_date,created_on,
             menu_assign_testproject_roles,warning_empty_last_name,btn_apikey_generate,show_calender,clear_date,
             menu_assign_testplan_roles,caption_user_details,show_event_history,
             th_login,th_first_name,th_last_name,th_password,th_email,authentication_method,
             th_role,th_locale,th_active,password_mgmt_is_external,demo_update_user_disabled,
             btn_upd_user_data,btn_add,btn_cancel,button_reset_password,demo_reset_password_disabled,title_api_interface'}

<script type="text/javascript">
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_login      = "{$labels.warning_empty_login|escape:'javascript'}";
var warning_empty_first_name = "{$labels.warning_empty_first_name|escape:'javascript'}";
var warning_empty_last_name  = "{$labels.warning_empty_last_name|escape:'javascript'}";
var warning_empty_pwd = "{$labels.warning_empty_pwd|escape:'javascript'}";
var warning_different_pwd = "{$labels.warning_different_pwd|escape:'javascript'}";
var warning_empty_email_address = "{$labels.empty_email_address|escape:'javascript'}";
var warning_no_good_email_address = "{$labels.no_good_email_address|escape:'javascript'}"; 


function validateForm(f,check_password)
{
  var email_check = {$tlCfg->validation_cfg->user_email_valid_regex_js};
  var email_warning;
  var show_email_warning=false;

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
    show_email_warning=true;
    email_warning=warning_empty_email_address;
  }
  else 
  { 
    if(!email_check.test(f.emailAddress.value))
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

/**
 *
 */
function managePasswordInputs(oid,targetSetOID)
{
  var source = document.getElementById(oid);
  var targetOID = targetSetOID.split(",");
  var len = 0;
  var idx = 0;
  var elem = '';
  var passwordMgmt = [];  // critic because ww want an array

  {foreach key=methodCode item=methodCfg from=$gui->authCfg.domain}
  passwordMgmt['{$methodCode}']={if $methodCfg.allowPasswordManagement} 1 {else} 0 {/if};
  {/foreach}

  // Both work ok
  // alert(source.options[source.selectedIndex].value);
  // alert(source.value);
  len=targetOID.length;
  for(idx=0; idx < len; idx++)
  {
    elem = document.getElementById(targetOID[idx]);
    if(passwordMgmt[source.value])
    {
      elem.style.display = '';
    }  
    else
    {
      elem.style.display = 'none';
    }  
  }
}
</script>

{$ext_location=$smarty.const.TL_EXTJS_RELATIVE_PATH}
<link rel="stylesheet" type="text/css" href="{$basehref}{$ext_location}/css/ext-all.css" />
</head>

<body>
<h1 class="title">{$gui->main_title} </h1>
{include file="inc_update.tpl" result=$result item="user" action="$action" user_feedback=$user_feedback}

{if $gui->op->status > 0}
  {$user_id=''}
  {$user_login=''}
  {$user_login_readonly=''}
  {$reset_password_enabled=0}
  {$reset_password_form_style="display:none"}
  
  {$show_password_field=1}

  {if $operation == 'doCreate'}
    {$check_password=1}
      {if $gui->user neq null}
        {$user_login=$gui->user->login}
      {/if}
  {else}
     {$check_password=0}
     {$user_id=$gui->user->dbID}
     {$user_login=$gui->user->login}
     {$user_login_readonly='readonly="readonly" disabled="disabled"'}
     {$reset_password_enabled=1}
     {$reset_password_form_style="display:"}
     {$show_password_field=0}
  {/if}


  {$auth = $gui->user->authentication}
  {if $auth == ''}
    {$auth = $gui->authCfg.method}
  {/if}  
  {if $gui->authCfg.domain[$auth].allowPasswordManagement == false}
    {$check_password=0}
    {$reset_password_enabled=0}
    {$reset_password_form_style="display:none"}
    {$show_password_field=0}
  {/if}  




<div class="workBack">
<form method="post" action="lib/usermanagement/usersEdit.php" class="x-form" name="useredit" 
    onSubmit="javascript:return validateForm(this,{$check_password});">
  <input type="hidden" name="user_id" id="user_id_form1" value="{$user_id}" />
  <input type="hidden" id="user_login" name="user_login" value="{$user_login}" />

  <fieldset class="x-fieldset x-form-label-left" style="width:50%;">
  <legend class="x-fieldset-header x-unselectable" style="-moz-user-select: none;">
  {$labels.caption_user_details}
  {if $gui->grants->mgt_view_events eq "yes" && $user_id}
  <img style="margin-left:5px;" class="clickable" src="{$tlImages.help}" 
       onclick="showEventHistoryFor('{$user_id}','users')"
       alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
  {/if}
  </legend>
  <table class="common">
    <tr>
      <th style="background:none;">{$labels.th_login}</th>
      <td><input type="text" name="login" size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}"
      {$user_login_readonly} value="{$gui->user->login|escape}" required />
      {include file="error_icon.tpl" field="login"}
       </td>
    </tr>
    <tr>
      <th style="background:none;">{$labels.th_first_name}</th>
      <td><input type="text" name="firstName" value="{$gui->user->firstName|escape}"
           size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" required />
           {include file="error_icon.tpl" field="firstName"}
      </td></tr>
    <tr>
      <th style="background:none;">{$labels.th_last_name}</th>
      <td><input type="text" name="lastName" value="{$gui->user->lastName|escape}"
           size="{#NAMES_SIZE#}" maxlength="{#NAMES_SIZE#}" required />
           {include file="error_icon.tpl" field="lastName"}
           </td>
    </tr>

    {if $show_password_field}
      <tr id="passwordContainer">
      {if $external_password_mgmt eq 0}
        <th style="background:none;">{$labels.th_password}</th>
        <td><input type="password" id="password" name="password"
                   size="{#PASSWD_SIZE#}" maxlength="{#PASSWD_SIZE#}" required />
            {include file="error_icon.tpl" field="password"}
        </td>
      {/if}
      </tr>
   {/if}


    <tr>
      <th style="background:none;">{$labels.th_email}</th>
      <td><input type="text" id="email" name="emailAddress" value="{$gui->user->emailAddress|escape}"
                 size="{#EMAIL_SIZE#}" maxlength="{#EMAIL_MAXLEN#}" required />
          {include file="error_icon.tpl" field="emailAddress"}
      </td>
    </tr>
    <tr>
      <th style="background:none;">{$labels.th_role}</th>
      <td>
        {$selected_role=$gui->user->globalRoleID}
        {if $gui->user->globalRoleID eq 0}
          {$selected_role=$tlCfg->default_roleid}
        {/if}
        <select name="rights_id">
        {foreach key=role_id item=role from=$optRights}
            <option value="{$role_id}" {if $role_id == $selected_role} selected="selected" {/if}>
          {$role->getDisplayName()|escape}
        </option>
        {/foreach}
        </select>
      </td>
    </tr>

    <tr>
      <th style="background:none;">{$labels.th_locale}</th>
      <td>
        {$selected_locale=$gui->user->locale}
        {if $gui->user->locale|count_characters eq 0}
           {$selected_locale=$locale}
        {/if}

        <select name="locale">
        {html_options options=$gui->optLocale selected=$selected_locale}
        </select>
      </td>
    </tr>

    <tr>
      <th style="background:none;">{$labels.authentication_method}</th>
      <td>
        <select id="authentication" name="authentication"> 
        {html_options options=$gui->auth_method_opt selected=$gui->user->authentication}
        </select>
      </td>
    </tr>


    <tr>
      <th style="background:none;">{$labels.th_active}</th>
      <td>
        <input type="checkbox"  name="user_is_active" {if $gui->user->isActive eq 1} checked {/if} />
      </td>
    </tr>

    {if $gui->expDateEnabled}
    <tr>
      <th style="background:none;">{$labels.expiration_date}</th>
      <td>

        <input type="text" name="expiration_date" id="expiration_date" 
               value="{$gui->user->expiration_date|escape}" size="{#DATE_PICKER#}"
               onclick="showCal('expiration_date-cal','expiration_date','{$gsmarty_datepicker_format}');" readonly />

        <img title="{$labels.show_calender}" src="{$tlImages.calendar}"
             onclick="showCal('expiration_date-cal','expiration_date','{$gsmarty_datepicker_format}');" >


        <img title="{$labels.clear_date}" src="{$tlImages.clear}"
               onclick="javascript:var x = document.getElementById('expiration_date'); x.value = '';" >
        <div id="expiration_date-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>

      </td>
    </tr>
    {/if}

    {if $external_password_mgmt eq 1}
      <td>{$labels.password_mgmt_is_external}</td>
    {/if}

  </table>

  {$submitEnabled="1"}
  {if $tlCfg->demoMode}
    {if $operation == 'doUpdate'}
      {$submitEnabled="0"}
    {/if} 
  {/if}


  <div class="groupBtn">
  {if $submitEnabled}
    <input type="hidden" name="doAction" id="doActionUserEdit" value="{$operation}" />
    <input type="submit" name="do_update"   value="{$labels.btn_save}" />
  {else}
    {$labels.demo_update_user_disabled}<br>
  {/if}
  <input type="button" name="cancel" value="{$labels.btn_cancel}"
      onclick="javascript: location.href=fRoot+'lib/usermanagement/usersView.php';" />

  </div>
</fieldset>
</form>

<br />
<form method="post" action="lib/usermanagement/usersEdit.php" 
      style=" {$reset_password_form_style}" 
      id="user_reset_password" name="user_reset_password">
  {if $tlCfg->demoMode}
    {$labels.demo_reset_password_disabled}
  {else}
    <input type="hidden" name="doAction" id="doReset" value="" />
    <input type="hidden" name="user_id" id="user_id_form2" value="{$user_id}" />
    <input type="submit" id="do_reset_password" name="do_reset_password" 
           value="{$labels.button_reset_password}" 
           onclick="doReset.value='resetPassword'"/>

    {if $tlCfg->api->enabled && $submitEnabled}       
    <input type="submit" id="genAPIKey" value="{$labels.btn_apikey_generate}" 
           onclick="doReset.value='genAPIKey'"/>
    {/if}
  {/if} 
</form>
</div>

{/if}
</body>
</html>
