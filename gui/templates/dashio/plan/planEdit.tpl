{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource planEdit.tpl

Purpose: smarty template - create/edit Test Plan
*}
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels"
          s="warning,warning_empty_tp_name,testplan_title_edit,public,api_key,
             testplan_th_name,testplan_th_notes,testplan_question_create_tp_from,
             opt_no,testplan_th_active,btn_testplan_create,btn_upd,cancel,
             show_event_history,testplan_txt_notes,file_upload_ko"}


{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$gui->editorType}
{include file="inc_del_onclick.tpl"}
{include file="plan/planEditJS.inc.tpl"}

{include file="plan/planEditJS.inc.tpl"}
{include file="bootstrap.inc.tpl"}
<script src="{$basehref}third_party/bootbox/bootbox.all.min.js"></script>
</head>

{$planID = $gui->itemID}
{if !isset($loadOnCancelURL)}
  {$loadOnCancelURL=""}
{/if}


<body>
{if $gui->uploadOp != null }
  <script>
  var uplMsg = "{$labels.file_upload_ko}<br>";
  var doAlert = false;
  {if $gui->uploadOp->statusOK == false}
    uplMsg += "{$gui->uploadOp->msg}<br>";
    doAlert = true;
  {/if}
  if (doAlert) {
    bootbox.alert(uplMsg);
  }
  </script>
{/if}

{include file="aside.tpl"}  
<div id="main-content">
<h1 class="{#TITLE_CLASS#}">{$gui->main_descr|escape}</h1>

<div class="workBack">
{include file="inc_update.tpl" user_feedback=$gui->user_feedback}
  {$form_action='create'}
  {if $gui->itemID != 0}
    <h2>
    {$labels.testplan_title_edit} {$gui->testplan_name|escape}
    {$form_action='update'}
    {if $gui->userGrants->mgt_view_events eq "yes"}
      <img style="margin-left:5px;" class="clickable" src="{$tlImages.help}" 
           onclick="showEventHistoryFor('{$gui->itemID}','testplans')" alt="{$labels.show_event_history}" 
           title="{$labels.show_event_history}"/>
    {/if}
    </h2>
  {/if}

  <form method="post" name="testplan_mgmt" id="testplan_mgmt"
        action="lib/plan/planEdit.php?action={$form_action}"
        onSubmit="javascript:return validateForm(this);">
    <input type="hidden" id="itemID" name="itemID" 
           value="{$gui->itemID}" />
    <input type="hidden" id="tproject_id" name="tproject_id"
           value="{$gui->tproject_id}" />
    <input type="hidden" id="tplan_id" name="tplan_id"
           value="{$gui->itemID}" />

  <table class="common" width="80%">

    <tr><th style="background:none;">{$labels.testplan_th_name}</th>
      <td><input type="text" name="testplan_name"
                 size="{#TESTPLAN_NAME_SIZE#}"
                 maxlength="{#TESTPLAN_NAME_MAXLEN#}"
                 value="{$gui->testplan_name|escape}" required />
          {include file="error_icon.tpl" field="testplan_name"}
      </td>
    </tr>
    <tr><th style="background:none;">{$labels.testplan_th_notes}</th>
      <td >{$gui->notes}</td>
    </tr>
    {if $gui->itemID eq 0}
      {if $gui->tplans}
        <tr><th style="background:none;">{$labels.testplan_question_create_tp_from}</th>
        <td>
        <select name="copy_from_tplan_id"
                onchange="manage_copy_ctrls('copy_controls',this.value,'0')">
        <option value="0">{$labels.opt_no}</option>
        {foreach item=testplan from=$gui->tplans}
          <option value="{$testplan.id}">{$testplan.name|escape}</option>
        {/foreach}
        </select>

            <div id="copy_controls" style="display:none;">
            {assign var=this_template_dir value=$smarty.template|dirname}
            {include file="$this_template_dir/inc_controls_planEdit.tpl"}
            </div>
        </td>
        </tr>
      {/if}
    {/if}
      <tr>
        <th style="background:none;">{$labels.testplan_th_active}</th>
          <td>
            <input type="checkbox" name="active" {if $gui->is_active eq 1}  checked="checked" {/if} />
          </td>
      </tr>
      <tr>
        <th style="background:none;">{$labels.public}</th>
          <td>
            <input type="checkbox" name="is_public" {if $gui->is_public eq 1} checked="checked" {/if} />
          </td>
      </tr>

      {if isset($gui->api_key) && $gui->api_key != ''}
      <tr>
        <th style="background:none;">{$labels.api_key}</th>
        <td>{$gui->api_key}</td>
      </tr>
      {/if}



    {if $gui->cfields neq ''}
    <tr>
      <td  colspan="2">
     <div id="custom_field_container" class="custom_field_container">
     {$gui->cfields}
     </div>
      </td>
    </tr>
    {/if}
  </table>

  <div class="groupBtn">
    {if $gui->itemID eq 0}
      <input type="hidden" name="do_action" value="do_create" />
      <input class="{#BUTTON_CLASS#}" type="submit" 
             name="do_create" id="do_create" 
             value="{$labels.btn_testplan_create}"
             onclick="do_action.value='do_create'"/>
    {else}

      <input type="hidden" name="do_action" value="do_update" />
      <input class="{#BUTTON_CLASS#}" type="submit" 
             name="do_update" id="do_update"
             value="{$labels.btn_upd}"
             onclick="do_action.value='do_update'"/>
    {/if}
    <input class="{#BUTTON_CLASS#}" type="button" 
           name="go_back" id="go_back"
           value="{$labels.cancel}"
           onclick="javascript: location.href=fRoot+'{$gui->actions->displayListURL};'"/>
  </div>

  </form>
{if $planID != 0}
  {$downloadOnly=true}
  {if $gui->userGrants->testplan_create eq 'yes'}
    {$downloadOnly=false}
  {/if}

  {include file="attachments.inc.tpl" 
               attach_id=$planID
               attach_tableName=$gui->attachmentTableName
               attach_attachmentInfos=$gui->attachments[$planID]  
               attach_downloadOnly=$downloadOnly
               attach_loadOnCancelURL=$loadOnCancelURL}
{/if}
             
<p>{$labels.testplan_txt_notes}</p>

</div>
</div>
{include file="supportJS.inc.tpl"}
</body>
</html>