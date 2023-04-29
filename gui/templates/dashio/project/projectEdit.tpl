{**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Smarty template - Edit existing Test project
 *
 * @filesource  projectEdit.tpl
 *
 *
 *}
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}
{$displayListURL=$gui->actions->displayListURL}
{$managerURL=$gui->actions->managerURL}
{$editAction=$gui->actions->editAction}

{lang_get var="labels" 
  s='show_event_history,th_active,cancel,info_failed_loc_prod,invalid_query,
  create_from_existent_tproject,opt_no,caption_edit_tproject,caption_new_tproject,name,
  title_testproject_management,testproject_enable_priority, testproject_enable_automation,
  public,testproject_color,testproject_alt_color,testproject_enable_requirements,
  testproject_enable_inventory,testproject_features,testproject_description,
  testproject_prefix,availability,mandatory,warning,warning_empty_tcase_prefix,api_key,
  warning_empty_tproject_name,testproject_issue_tracker_integration,issue_tracker,
  testproject_code_tracker_integration,code_tracker,testproject_reqmgr_integration,reqmgrsystem,
  no_rms_defined,no_issuetracker_defined,no_codetracker_defined,testproject_prefix_hint'}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$editorType}
{include file="inc_del_onclick.tpl"}

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

  to = document.getElementById(targetOID);
  if ( typeof(to) == 'undefined' || to == null) {
    return;
  }

  so = document.getElementById(selectOID);
  to.disabled = false;
  if (so.selectedIndex == 0){
    to.checked = false;
    to.disabled = true;
  }  
}

</script>

{* --------------------------------------------------------------------- *}
{* Both are needed *}
{include file="bootstrap.inc.tpl"}
<script src="{$basehref}third_party/bootbox/bootbox.all.min.js"></script>
{* --------------------------------------------------------------------- *}
</head>

<body onload="manageTracker('issue_tracker_id','issue_tracker_enabled');
      manageTracker('code_tracker_id','code_tracker_enabled');">

{include file="aside.tpl"}
<div id="main-content">
<h1 class="{#TITLE_CLASS#}">
  {$gui->pageTitle|escape} 
  {if $gui->mgt_view_events eq "yes" and $gui->itemID}
    <img style="margin-left:5px;" class="clickable" src="{$tlImages.help}" 
           onclick="showEventHistoryFor('{$gui->itemID}','testprojects')" 
           alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
  {/if}
</h1>

{if $gui->user_feedback != ''}  
  <script>
  var userMsg = "{$gui->user_feedback}"
  bootbox.alert(userMsg);
  </script>
{/if}

<div class="workBack">
  {if $gui->found == "yes"}
    <div style="width:90%; margin: auto;">
    <form name="edit_testproject" id="edit_testproject"
          method="post" action="{$managerURL}"
          onSubmit="javascript:return validateForm(this);">

    {$tdStyle = 'style="padding:10px;"'}      
    <table id="item_view" style="width:100%;outline-style: solid; outline-width: 2px;">

      {if $gui->itemID eq 0}
        {* Can we use other test project as SOURCE for copy? *}
        {if $gui->testprojects != ''}
          <tr>
            <td {$tdStyle}>{$labels.create_from_existent_tproject}</td>
            <td>
              <select id="copy_from_tproject_id" name="copy_from_tproject_id">
              <option value="0">{$labels.opt_no}</option>
               {foreach item=testproject from=$gui->testprojects}
                 <option value="{$testproject.id}">{$testproject.name|escape}</option>
               {/foreach}
              </select>
            </td>
          </tr>
        {/if}
      {/if}
      <tr>
        <td {$tdStyle}>{$labels.name} *</td>
        <td><input type="text" id="tprojectName" name="tprojectName" size="{#TESTPROJECT_NAME_SIZE#}"
            value="{$gui->tprojectName|escape}" maxlength="{#TESTPROJECT_NAME_MAXLEN#}" required />
        </td>
      </tr>
      <tr>
        <td {$tdStyle}>{$labels.testproject_prefix} *</td>
        <td><input type="text" id="tcasePrefix" name="tcasePrefix" 
                   size="{#TESTCASE_PREFIX_SIZE#}"
                   value="{$gui->tcasePrefix|escape}" 
                   maxlength="{#TESTCASE_PREFIX_MAXLEN#}" required />
            <i class="fa fa-info-circle" aria-hidden="true" title="{$labels.testproject_prefix_hint}"></i>       
        </td>
      </tr>
      <tr>
        <td {$tdStyle}>{$labels.testproject_description}</td>
        <td style="width:80%">{$gui->notes}</td>
      </tr>
      <tr>
        <td {$tdStyle}>{$labels.testproject_features}</td><td></td>
      </tr>
      <tr>
        <td></td><td>
            <input type="checkbox" id="optReq" name="optReq" 
                {if $gui->projectOptions->requirementsEnabled} checked="checked"  {/if} />
          {$labels.testproject_enable_requirements}
        </td>
      </tr>
      <tr>
        <td></td><td>
          <input type="checkbox" id="optPriority" name="optPriority" 
              {if $gui->projectOptions->testPriorityEnabled} checked="checked"  {/if} />
          {$labels.testproject_enable_priority}
        </td>
      </tr>
      <tr>
        <td></td><td>
          <input type="checkbox" id="optAutomation" name="optAutomation" 
                {if $gui->projectOptions->automationEnabled} checked="checked" {/if} />
          {$labels.testproject_enable_automation}
        </td>
      </tr>
      <tr>
        <td {$tdStyle}>{$labels.testproject_issue_tracker_integration}</td><td></td>
      </tr>
      {if $gui->issueTrackers == ''}
        <tr>
          <td></td>
          <td>{$labels.no_issuetracker_defined}</td>
        </tr>
      {else}
        <tr>
          <td></td>
          <td>
            <input type="checkbox" id="issue_tracker_enabled"
                   name="issue_tracker_enabled" {if $gui->issue_tracker_enabled == 1} checked="checked" {/if} />
            {$labels.th_active}
          </td>
        </tr>
        <tr>
          <td></td>
          <td>
            {$labels.issue_tracker}
             <select name="issue_tracker_id" id="issue_tracker_id"
             onchange="manageTracker('issue_tracker_id','issue_tracker_enabled');">
             <option value="0">&nbsp;</option>
             {foreach item=issue_tracker from=$gui->issueTrackers}
               <option value="{$issue_tracker.id}" 
                 {if $issue_tracker.id == $gui->issue_tracker_id} selected {/if} 
               >
               {$issue_tracker.verbose|escape}</option>
             {/foreach}
             </select>
          </td>
        </tr>
      {/if}

      <tr>
        <td {$tdStyle}>{$labels.testproject_code_tracker_integration}</td><td></td>
      </tr>
      {if $gui->codeTrackers == ''}
        <tr>
          <td></td>
          <td>{$labels.no_codetracker_defined}</td>
        </tr>
      {else}
        <tr>
          <td></td>
          <td>
            <input type="checkbox" id="code_tracker_enabled"
                   name="code_tracker_enabled" {if $gui->code_tracker_enabled == 1} checked="checked" {/if} />
            {$labels.th_active}
          </td>
        </tr>
        <tr>
          <td></td>
          <td>
            {$labels.code_tracker}
             <select name="code_tracker_id" id="code_tracker_id"
             onchange="manageTracker('code_tracker_id','code_tracker_enabled');">
             <option value="0">&nbsp;</option>
             {foreach item=code_tracker from=$gui->codeTrackers}
               <option value="{$code_tracker.id}" 
                 {if $code_tracker.id == $gui->code_tracker_id} selected {/if} 
               >
               {$code_tracker.verbose|escape}</option>
             {/foreach}
             </select>
          </td>
        </tr>
      {/if}
         
      <tr>
        <td {$tdStyle}>{$labels.availability}</td><td></td>
      </tr>
      <tr>
        <td></td><td>
            <input type="checkbox" id="active" name="active" {if $gui->active eq 1} checked="checked" {/if} />
            {$labels.th_active}
          </td>
          </tr>

      <tr>
        <td></td><td>
            <input type="checkbox" id="is_public" name="is_public" {if $gui->is_public eq 1} checked="checked"  {/if} />
            {$labels.public}
          </td>
      </tr>
      
      {if $gui->api_key != ''}
      <tr>
        <td {$tdStyle}>{$labels.api_key}</td>
        <td>{$gui->api_key}</td>
      </tr>
      {/if}


      <tr><td cols="2">&nbsp;</td></tr>

      <tr><td cols="2" {$tdStyle}>
        {if $gui->canManage == "yes"}
        <div class="groupBtn">
          <input type="hidden" name="doAction" value="{$gui->doActionValue}" />
          <input class="{#BUTTON_CLASS#}" type="submit"
                 name="doActionButton" id="doActionButton"
                 value="{$gui->buttonValue}" />
                 
          <input class="{#BUTTON_CLASS#}" type="button" 
                 name="go_back" id="go_back"
                 value="{$labels.cancel}" 
                 onclick="javascript: location.href=fRoot+'{$displayListURL}';" />

          <input type="hidden" name="itemID" id="itemID" value="{$gui->itemID}">
          <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
          <input type="hidden" name="tplan_id" id="tplan_id" value="{$gui->tplan_id}">

        </div>
        {/if}
      </td></tr>
    </table>
    </form>
  </div>
  {else}
    <p class="info">
    <<<projectEdit.tpl>>>
    {if $gui->tprojectName != ''}
      {$labels.info_failed_loc_prod} - {$gui->tprojectName|escape}!<br />
    {/if}
    {$labels.invalid_query}: {$sqlResult|escape}</p>
  {/if}
</div>
</div>
{include file="supportJS.inc.tpl"}
</body>
</html>
