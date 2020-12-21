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

{$cellContent = $tlCfg->layout->cellContent}
{$cellLabel = $tlCfg->layout->cellLabel}

{$buttonGroupLayout = "form-group"} {* Domain: form-group, groupBtn *}
{$inputClass = ""}
{$textAreaCfg.rows = #TESTPLAN_CFG_ROWS#}
{$textAreaCfg.cols = #TESTPLAN_CFG_COLS#}



{$form_action='create'}
{if $gui->itemID != 0}
  {$form_action='update'}
{/if} 
{$url_args="lib/plan/planEdit.php?action="}
{$edit_url="$basehref$url_args$form_action"}

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

    <div style="margin: 8px;" id="8container">
      {include file="inc_update.tpl" user_feedback=$gui->user_feedback}

      <div class="row mt">
        <div class="col-lg-12">
          <div class="form-panel">
            <form class="form-horizontal style-form" name="testplan_mgmt" id="testplan_mgmt" 
              method="post" action="{$edit_url}"">
              <input type="hidden" id="itemID" name="itemID" 
                     value="{$gui->itemID}" />
              <input type="hidden" id="tproject_id" name="tproject_id"
                     value="{$gui->tproject_id}" />
              <input type="hidden" id="tplan_id" name="tplan_id"
                     value="{$gui->itemID}" />


              <div class="form-group">
                <label for="name" class="{$cellLabel}">{$labels.testplan_th_name}</label>
                <div class="{$cellContent}">
                  <input type="text" name="testplan_name"
                     size="{#TESTPLAN_NAME_SIZE#}"
                     maxlength="{#TESTPLAN_NAME_MAXLEN#}"
                     value="{$gui->testplan_name|escape}" required />
                </div> <!-- cellContent -->  
              </div> <!-- class="form-group" -->


              <div class="form-group">
                <label for="notes" class="{$cellLabel}">{$labels.testplan_th_notes}</label>
                <div class="{$cellContent}">
                  {$gui->notes}
                </div> <!-- cellContent -->  
              </div> <!-- class="form-group" -->


              <div class="form-group">
                <label for="active" class="{$cellLabel}">{$labels.testplan_th_active}</label>
                <div class="{$cellContent}">
                  <input type="checkbox" name="active" {if $gui->is_active eq 1}  checked="checked" {/if} />
                </div> <!-- cellContent -->  
              </div> <!-- class="form-group" -->

              <div class="form-group">
                <label for="is_public" class="{$cellLabel}">{$labels.public}</label>
                <div class="{$cellContent}">
                  <input type="checkbox" name="is_public" {if $gui->is_public eq 1}  checked="checked" {/if} />
                </div> <!-- cellContent -->  
              </div> <!-- class="form-group" -->

              {if $gui->cfields neq ''}
                <div id="custom_field_container" class="form-group"> {* class="custom_field_container"> *}
                  {$gui->cfields}
                </div>
              {/if}



              {if isset($gui->api_key) && $gui->api_key != ''}
                <div class="form-group">
                  <label for="name" class="{$cellLabel}">{$labels.api_key}</label>
                  <div class="{$cellContent}">
                    {$gui->api_key}
                  </div> <!-- cellContent -->  
                </div> <!-- class="form-group" -->
              {/if}


              {if $gui->itemID eq 0 && $gui->tplans != null}
                <div class="form-group">
                  <label for="name" class="{$cellLabel}">{$labels.testplan_question_create_tp_from}</label>
                  <div class="{$cellContent}">
                      <select name="copy_from_tplan_id"
                              onchange="manage_copy_ctrls('copy_controls',this.value,'0')">
                      <option value="0">{$labels.opt_no}</option>
                      {foreach item=testplan from=$gui->tplans}
                        <option value="{$testplan.id}">{$testplan.name|escape}</option>
                      {/foreach}
                      </select>
                  </div> <!-- cellContent -->  
                </div> <!-- class="form-group" -->

                <div class="form-group">
                  <label class="{$cellLabel}">&nbsp;</label>
                  <div class="{$cellContent}" id="copy_controls" style="display:none;">
                      {assign var=this_template_dir value=$smarty.template|dirname}
                      {include file="$this_template_dir/inc_controls_planEdit.tpl"}
                  </div> <!-- cellContent -->  
                </div> <!-- class="form-group" -->

              {/if}

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
          </div> <!-- class="form-panel" -->
        </div> <!-- class="col-lg-12" -->
      </div> <!-- class="row mt" -->

      {if $planID != 0}
        <div class="row mt">
          <div class="col-lg-12">
            <div class="form-panel">
              {$downloadOnly=true}
              {if $gui->userGrants->testplan_create eq 'yes'}
                {$downloadOnly=false}
              {/if}

              {include file="attachments.inc.tpl" 
                                attach_id=$planID
                                attach_tableName=$gui->attachmentTableName
                                attach_attachmentInfos=$gui->attachments  
                                attach_downloadOnly=$downloadOnly
                                attach_loadOnCancelURL=$loadOnCancelURL}
            </div> <!-- class="form-panel" -->
          </div> <!-- class="col-lg-12" -->
        </div> <!-- class="row mt" -->
      {/if}


    </div> <!-- id="8container" -->
  </div> <!-- id="main-content" -->
  {include file="supportJS.inc.tpl"}
</body>
</html>