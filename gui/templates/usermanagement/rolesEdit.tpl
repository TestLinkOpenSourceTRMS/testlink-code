{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: rolesEdit.tpl,v 1.22 2010/11/06 11:42:47 amkhullar Exp $
Purpose: smarty template - create/edit user role

rev :
     20081030 - franciscom - new area system rights
     20080412 - franciscom - refactoring - reducing coupling with  php script
     20080409 - franciscom - refactoring
     20071227 - franciscom - look and feel.

     20070725 - franciscom
     - added js check on role name
     - use of input_dimensions.conf

     20070829 - jbarchibald
      -  bug 1000  - Testplan User Role Assignments
*}


{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$gui->editorType}
{include file="inc_del_onclick.tpl"}
{include file="inc_jsCheckboxes.tpl"}

{literal}
<script type="text/javascript">
{/literal}
{lang_get var="labels"
          s='btn_save,warning,warning_modify_role,warning_empty_role_name,th_rights,
             error_role_no_rights,caption_possible_affected_users,enter_role_notes,
             title_user_mgmt,caption_define_role,th_mgttc_rights,th_req_rights,
             th_product_rights,th_user_rights,th_kw_rights,th_cf_rights,th_system_rights,
             th_platform_rights,
             th_rolename,th_tp_rights,btn_cancel'}
             
//BUGID 3943: Escape all messages (string)
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_modify_role = "{$labels.warning_modify_role|escape:'javascript'}";
var warning_empty_role_name = "{$labels.warning_empty_role_name|escape:'javascript'}";
var warning_error_role_no_rights = "{$labels.error_role_no_rights|escape:'javascript'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.rolename.value))
  {
      alert_message(alert_box_title,warning_empty_role_name);
      selectField(f, 'rolename');
      return false;
  }

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
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}


<h1 class="title">{$labels.title_user_mgmt} - {$labels.caption_define_role}</h1>

{***** TABS *****}
{include file="usermanagement/tabsmenu.tpl" grants=$gui->grants highlight=$gui->highlight}

{include file="inc_update.tpl" user_feedback=$gui->userFeedback}

<div class="workBack">

	<form name="rolesedit" id="rolesedit"
		method="post" action="lib/usermanagement/rolesEdit.php"
	{if $tlCfg->demoMode}
		onsubmit="alert('{lang_get s="warn_demo"}'); return false;">
	{else}
		{if $gui->grants->role_mgmt == "yes"}
		onSubmit="javascript:return validateForm(this);"
		{else}
		onsubmit="return false"
		{/if}
	{/if}
	>
	<input type="hidden" name="roleid" value="{$gui->role->dbID}" />
	<table class="common">
		<tr><th>{$labels.th_rolename}
			{if $gui->mgt_view_events eq "yes" && $gui->role->dbID}
				<img style="margin-left:5px;" class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/question.gif" onclick="showEventHistoryFor('{$gui->role->dbID}','roles')" alt="{lang_get s='show_event_history'}" title="{lang_get s='show_event_history'}"/>
			{/if}
		</th></tr>
		<tr><td>
			   <input type="text" name="rolename"
			          size="{#ROLENAME_SIZE#}" maxlength="{#ROLENAME_MAXLEN#}" value="{$gui->role->name|escape}"/>
 				 {include file="error_icon.tpl" field="rolename"}
		    </td></tr>
		<tr><th>{$labels.th_rights}</th></tr>
		<tr>
			<td>
				<table>
				<tr>
					<td><fieldset class="x-fieldset x-form-label-left"><legend >{$labels.th_tp_rights}</legend>
							{foreach from=$gui->rightsCfg->tplan_mgmt item=id key=k}
							<input class="tl-input" type="checkbox" name="grant[{$k}]" {$gui->checkboxStatus[$k]}/>{$id}<br />
							{/foreach}
						</fieldset>
					</td>
					<td>
						<fieldset class="x-fieldset x-form-label-left"><legend >{$labels.th_mgttc_rights}</legend>
						{foreach from=$gui->rightsCfg->tcase_mgmt item=id key=k}
						<input class="tl-input" type="checkbox" name="grant[{$k}]" {$gui->checkboxStatus[$k]} />{$id}<br />
						{/foreach}
						</fieldset>
					</td>
					<td>
						<fieldset class="x-fieldset x-form-label-left"><legend >{$labels.th_req_rights}</legend>
						{foreach from=$gui->rightsCfg->req_mgmt item=id key=k}
						<input class="tl-input" type="checkbox" name="grant[{$k}]" {$gui->checkboxStatus[$k]} />{$id}<br />
						{/foreach}
						</fieldset>
					</td>
					<td>
						<fieldset class="x-fieldset x-form-label-left"><legend >{$labels.th_product_rights}</legend>
						{foreach from=$gui->rightsCfg->tproject_mgmt item=id key=k}
						<input class="tl-input" type="checkbox" name="grant[{$k}]" {$gui->checkboxStatus[$k]} />{$id}<br />
						{/foreach}
						</fieldset>
					</td>
				</tr>
				<tr>
					<td><fieldset class="x-fieldset x-form-label-left"><legend >{$labels.th_user_rights}</legend>
							{foreach from=$gui->rightsCfg->user_mgmt item=id key=k}
							<input class="tl-input" type="checkbox" name="grant[{$k}]" {$gui->checkboxStatus[$k]} />{$id}<br />
							{/foreach}
						</fieldset>
					</td>
					<td><fieldset class="x-fieldset x-form-label-left"><legend >{$labels.th_kw_rights}</legend>
							{foreach from=$gui->rightsCfg->kword_mgmt item=id key=k}
							<input class="tl-input" type="checkbox" name="grant[{$k}]" {$gui->checkboxStatus[$k]} />{$id}<br />
							{/foreach}
						</fieldset>
					</td>
					<td><fieldset class="x-fieldset x-form-label-left"><legend >{$labels.th_cf_rights}</legend>
							{foreach from=$gui->rightsCfg->cfield_mgmt item=id key=k}
							<input class="tl-input" type="checkbox" name="grant[{$k}]" {$gui->checkboxStatus[$k]} />{$id}<br />
							{/foreach}
						</fieldset>
					</td>
					<td><fieldset class="x-fieldset x-form-label-left"><legend >{$labels.th_system_rights}</legend>
							{foreach from=$gui->rightsCfg->system_mgmt item=id key=k}
							<input class="tl-input" type="checkbox" name="grant[{$k}]" {$gui->checkboxStatus[$k]} />{$id}<br />
							{/foreach}
						</fieldset>
					</td>
				</tr>
				<tr>
					<td><fieldset class="x-fieldset x-form-label-left"><legend >{$labels.th_platform_rights}</legend>
							{foreach from=$gui->rightsCfg->platform_mgmt item=id key=k}
							<input class="tl-input" type="checkbox" name="grant[{$k}]" {$gui->checkboxStatus[$k]} />{$id}<br />
							{/foreach}
						</fieldset>
					</td>
				</tr>

			</table>
			</td>
		</tr>
		<tr><th>{$labels.enter_role_notes}</th></tr>
		<tr>
			<td width="80%">{$gui->notes}</td>
		</tr>

	</table>
	<div class="groupBtn">
	{if $gui->grants->role_mgmt == "yes" && $gui->role->dbID != $smarty.const.TL_ROLES_NO_RIGHTS}

		<input type="hidden" name="doAction" value="{$gui->operation}" />
		<input type="submit" name="role_mgmt" value="{$labels.btn_save}"
		         {if $gui->role != null && $gui->affectedUsers neq null} onClick="return modifyRoles_warning()"{/if}
		/>
	{/if}
		<input type="button" name="cancel" value="{$labels.btn_cancel}"
			onclick="javascript: location.href=fRoot+'lib/usermanagement/rolesView.php';" />
	</div>
	<br />
	{if $gui->affectedUsers neq null}
		<table class="common" style="width:50%">
		<caption>{$labels.caption_possible_affected_users}</caption>
		{foreach from=$gui->affectedUsers item=user}
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