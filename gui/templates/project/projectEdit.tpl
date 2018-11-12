{**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Smarty template - Edit existing Test project
 *
 * @filesource  projectEdit.tpl
 *
 * @internal revisions
 * @since 1.9.14
 *
 *}
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}
{$managerURL="lib/project/projectEdit.php"}
{$editAction="$managerURL?doAction=edit&tprojectID="}

{lang_get var="labels" 
  s='show_event_history,th_active,cancel,info_failed_loc_prod,invalid_query,
  create_from_existent_tproject,opt_no,caption_edit_tproject,caption_new_tproject,name,
  title_testproject_management,testproject_enable_priority, testproject_enable_automation,
  public,testproject_color,testproject_alt_color,testproject_enable_requirements,
  testproject_enable_inventory,testproject_features,testproject_description,
  testproject_prefix,availability,mandatory,warning,warning_empty_tcase_prefix,api_key,
  warning_empty_tproject_name,testproject_issue_tracker_integration,issue_tracker,
  testproject_code_tracker_integration,code_tracker,testproject_reqmgr_integration,reqmgrsystem,
  no_rms_defined,no_issuetracker_defined,no_codetracker_defined'}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$editorType}
{include file="inc_del_onclick.tpl"}

{if $gui_cfg->testproject_coloring neq 'none'}
  {include file="inc_jsPicker.tpl"}
{/if}

<script type="text/javascript">
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_tcase_prefix = "{$labels.warning_empty_tcase_prefix|escape:'javascript'}";
var warning_empty_tproject_name = "{$labels.warning_empty_tproject_name|escape:'javascript'}";

function validateForm(f)
{
  if (isWhitespace(f.tprojectName.value))
  {
     alert_message(alert_box_title,warning_empty_tproject_name);
     selectField(f, 'tprojectName');
     return false;
  }
  if (isWhitespace(f.tcasePrefix.value))
  {
     alert_message(alert_box_title,warning_empty_tcase_prefix);
     selectField(f, 'tcasePrefix');
     return false;
  }

  return true;
}

/**
 *
 *
 */
function manageTracker(selectOID,targetOID)
{
  var so;
  var to;

  so = document.getElementById(selectOID);
  to = document.getElementById(targetOID);

  to.disabled = false;
  if(so.selectedIndex == 0)
  {
    to.checked = false;
    to.disabled = true;
  }  

}

</script>
</head>

<body id="tl-container" class="container-fluid" onload="manageTracker('issue_tracker_id','issue_tracker_enabled');manageTracker('code_tracker_id','code_tracker_enabled');">
<section class="row workBack">
  <section class="col-lg-12 col-md-12 col-sm-12 col-xs-12 tl-box-main-footer">
    <h1>
      {$main_descr|escape} {$tlCfg->gui_title_separator_1} {$caption|escape}
      {if $mgt_view_events eq "yes" and $gui->tprojectID}
      <img class="clickable" src="{$tlImages.help}" onclick="showEventHistoryFor('{$gui->tprojectID}','testprojects')" alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
      {/if}
    </h1>
  </section>
  <section class="col-lg-12 col-md-12 col-sm-12 col-xs-12 tl-box-main-2">
    <div class="row">
    {if $user_feedback != ''}
      {include file="inc_update.tpl" user_feedback=$user_feedback feedback_type=$feedback_type}
    {/if}
    {if $gui->found == "yes"}
      <form name="edit_testproject" id="edit_testproject" method="post" action="{$managerURL}" onSubmit="javascript:return validateForm(this);">
        <section class="col-lg-10 col-md-10 col-sm-10 col-xs-12 col-lg-offset-1 col-md-offset-1 col-sm-offset-1 tl-box-main-3">
        {if $gui->tprojectID eq 0}
          {if $gui->testprojects != ''}
          <div>
            {$labels.create_from_existent_tproject}
          </div>
          <select name="copy_from_tproject_id">
            <option value="0">{$labels.opt_no}</option>
            {foreach item=testproject from=$gui->testprojects}
              <option value="{$testproject.id}">{$testproject.name|escape}</option>
            {/foreach}
          </select>
         {/if}
        {/if}
        {if $gui->tprojectID eq 0}
          {if $gui->testprojects != ''}
          <div>
            {$labels.create_from_existent_tproject}
          </div>
          <select name="copy_from_tproject_id">
            <option value="0">{$labels.opt_no}</option>
            {foreach item=testproject from=$gui->testprojects}
              <option value="{$testproject.id}">{$testproject.name|escape}</option>
            {/foreach}
          </select>
          {/if}
        {/if}
        <div class="row">
          <br />
          <div class="form-group col-lg-8">
            <label for="txtName">{$labels.name} *</label>
            <input id="txtName" type="text" name="tprojectName" class="form-control" size="{#TESTPROJECT_NAME_SIZE#}" value="{$gui->tprojectName|escape}" maxlength="{#TESTPROJECT_NAME_MAXLEN#}" required />
            {include file="error_icon.tpl" field="tprojectName"}
          </div>
          <div class="form-group col-lg-4">
            <label for="txtTcasePrefix">{$labels.testproject_prefix} *</label>
            <input id="txtTcasePrefix" type="text" name="tcasePrefix" class="form-control" size="{#TESTCASE_PREFIX_SIZE#}" value="{$gui->tcasePrefix|escape}" maxlength="{#TESTCASE_PREFIX_MAXLEN#}" required />
            {include file="error_icon.tpl" field="tcasePrefix"}
          </div>
          <div class="form-group col-lg-12">
            <label>{$labels.testproject_description}</label>
            {$notes}
            {if $gui_cfg->testproject_coloring neq 'none'}
              {$labels.testproject_color}
              <input type="text" name="color" value="{$color|escape}" maxlength="12" />
              {* this function below calls the color picker javascript function.
              It can be found in the color directory *}
              <a href="javascript: TCP.popup(document.forms['edit_testproject'].elements['color'], '{$basehref}third_party/color_picker/picker.html');">
                <img width="15" height="13" border="0" alt="{$labels.testproject_alt_color}" src="third_party/color_picker/img/sel.gif" />
              </a>
            {/if}
          </div>
          <h2 class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">{$labels.testproject_features}</h2>
          <br />
          <div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12">
            <input id="chkEnabledRequirements" type="checkbox" name="optReq" {if $gui->projectOptions->requirementsEnabled} checked="checked" {/if} />
            <label for="chkEnabledRequirements" class="checkbox-inline">{$labels.testproject_enable_requirements}</label>
          </div>
          <div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12">
            <input id="chkEnabledPriority" type="checkbox" name="optPriority" {if $gui->projectOptions->testPriorityEnabled} checked="checked"  {/if} />
            <label for="chkEnabledPriority" class="checkbox-inline">{$labels.testproject_enable_priority}</label>
          </div>
          <div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12">
            <input id="chkEnabledAutomation" type="checkbox" name="optAutomation" {if $gui->projectOptions->automationEnabled} checked="checked" {/if} />
            <label for="chkEnabledAutomation" class="checkbox-inline">{$labels.testproject_enable_automation}</label>
          </div>
          <div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12">
            <input id="chkEnabledInventory" type="checkbox" name="optInventory" {if $gui->projectOptions->inventoryEnabled} checked="checked" {/if} />
            <label for="chkEnabledInventory" class="checkbox-inline">{$labels.testproject_enable_inventory}</label>
          </div>
        </div>
        <h2 class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">{$labels.testproject_issue_tracker_integration}</h2>
        
        {if $gui->issueTrackers == ''}
        <div class="form-group col-lg-12">
          <p class="alert alert-warning text-center">{$labels.no_issuetracker_defined}</p>
        </div>
        {else}
        <div class="form-group col-lg-2">
          <input type="checkbox" id="issue_tracker_enabled" name="issue_tracker_enabled" {if $gui->issue_tracker_enabled == 1} checked="checked" {/if} />
          <label for="issue_tracker_enabled" class="checkbox-inline">{$labels.th_active}</label>
        </div>
        <div class="form-group col-lg-2">
          <label class="col-2" for="issue_tracker_id">{$labels.issue_tracker}</label>
        </div>
        <div class="form-group col-lg-8">
          <select name="issue_tracker_id" id="issue_tracker_id" class="form-control" onchange="manageTracker('issue_tracker_id','issue_tracker_enabled');">
            <option value="0">&nbsp;</option>
            {foreach item=issue_tracker from=$gui->issueTrackers}
              <option value="{$issue_tracker.id}" {if $issue_tracker.id == $gui->issue_tracker_id} selected {/if} >{$issue_tracker.verbose|escape}</option>
            {/foreach}
          </select>
        </div>
        {/if}

        <h2 class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">{$labels.testproject_code_tracker_integration}</h2>
        {if $gui->codeTrackers == ''}
        <div class="form-group col-lg-12">
          <p class="alert alert-warning text-center">{$labels.no_codetracker_defined}</p>
        </div>
        {else}
        <div class="form-group col-lg-2">
          <input type="checkbox" id="code_tracker_enabled" name="code_tracker_enabled" {if $gui->code_tracker_enabled == 1} checked="checked" {/if} />
          <label for="code_tracker_enabled" class="checkbox-inline">{$labels.th_active}</label>
        </div>
        <div class="form-group col-lg-2">
          <label class="col-2" for="code_tracker_id">{$labels.code_tracker}</label>
        </div>
        <div class="form-group col-lg-8">
          <select name="code_tracker_id" id="code_tracker_id" class="form-control" onchange="manageTracker('code_tracker_id','code_tracker_enabled');">
            <option value="0">&nbsp;</option>
            {foreach item=code_tracker from=$gui->codeTrackers}
            <option value="{$code_tracker.id}" {if $code_tracker.id == $gui->code_tracker_id} selected {/if} >{$code_tracker.verbose|escape}</option>
            {/foreach}
          </select>
        </div>
        {/if}

        {*
        <h2 class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">{$labels.testproject_reqmgr_integration}</h2>
        {if $gui->reqMgrSystems == ''}
        <div class="form-group col-lg-12">
          <p class="alert alert-warning text-center">{$labels.no_rms_defined}</p>
        </div>
        {else}
        <div class="form-group col-lg-2">
          <input type="checkbox" id="reqmgr_integration_enabled" name="reqmgr_integration_enabled" {if $gui->reqmgr_integration_enabled == 1} checked="checked" {/if} />
          <label for="reqmgr_integration_enabled" class="checkbox-inline">{$labels.th_active}</label>
        </div>
        <div class="form-group col-lg-2">
          {$labels.reqmgrsystem}
        </div>
        <div class="form-group col-lg-8">
          <select name="reqmgrsystem_id" id="reqmgrsystem_id">
            <option value="0">&nbsp;</option>
            {foreach item=rms from=$gui->reqMgrSystems}
            <option value="{$rms.id}" {if $rms.id == $gui->reqmgrsystem_id} selected {/if} >{$rms.verbose|escape}</option>
            {/foreach}
          </select>
        </div>
        {/if}
        *}
        <h2 class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">{$labels.availability}</h2>
        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
          <input id="avaliabilityActive" type="checkbox" name="active" {if $gui->active eq 1} checked="checked" {/if} />
          <label for="avaliabilityActive" class="checkbox-inline">{$labels.th_active}</label>
        </div>
        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
          <input id="avaliabilityPublic" type="checkbox" name="is_public" {if $gui->is_public eq 1} checked="checked"  {/if} />
          <label for="avaliabilityPublic" class="checkbox-inline">{$labels.public}</label>
        </div>

        {if $gui->api_key != ''}
        <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
          <label for="txtApiKey">{$labels.api_key}</label>
          <input id="txtApiKey" type="text" class="form-control" disabled value="{$gui->api_key}" />
        </div>
        {/if}
        {if $gui->canManage == "yes"}
        <input type="hidden" name="doAction" value="{$doActionValue}" />
        <input type="hidden" name="tprojectID" value="{$gui->tprojectID}" />

        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-6">
          <input type="button" name="go_back" class="form-control btn btn-default" value="{$labels.cancel}" onclick="javascript: location.href=fRoot+'lib/project/projectView.php';" />
        </div>
        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-6">
          <input type="submit" name="doActionButton" class="form-control btn btn-success" value="{$buttonValue}" />
        </div>
        {/if}

      </section><!-- IF still not terminated to refactor,not think about this xD -->
    </form>
    {else}
      <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <p class="alert alert-info">
        {if $gui->tprojectName != ''}
          {$labels.info_failed_loc_prod} - {$gui->tprojectName|escape}!<br />
        {/if}
        {$labels.invalid_query}: {$sqlResult|escape}</p>
      </div>
    {/if}
    <h3 class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">* {$labels.mandatory}</h3>
  </div> <!--END .row-->
  </section>
</body>
</html>
