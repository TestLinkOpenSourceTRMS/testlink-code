{* 
TestLink Open Source Project - http://testlink.sourceforge.net/

Purpose: smarty template - create containers

@filesource containerNew.tpl
*}

{config_load file="input_dimensions.conf" section="containerEdit"}
{lang_get var="labels"
          s="warning_empty_testsuite_name,title_create,tc_keywords,
             warning,btn_create_testsuite,btn_save,cancel,warning_unsaved"}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes"}

<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
var {$opt_cfg->js_ot_name} = new OptionTransfer("{$opt_cfg->from->name}","{$opt_cfg->to->name}");
{$opt_cfg->js_ot_name}.saveRemovedLeftOptions("{$opt_cfg->js_ot_name}_removedLeft");
{$opt_cfg->js_ot_name}.saveRemovedRightOptions("{$opt_cfg->js_ot_name}_removedRight");
{$opt_cfg->js_ot_name}.saveAddedLeftOptions("{$opt_cfg->js_ot_name}_addedLeft");
{$opt_cfg->js_ot_name}.saveAddedRightOptions("{$opt_cfg->js_ot_name}_addedRight");
{$opt_cfg->js_ot_name}.saveNewLeftOptions("{$opt_cfg->js_ot_name}_newLeft");
{$opt_cfg->js_ot_name}.saveNewRightOptions("{$opt_cfg->js_ot_name}_newRight");
</script>


{include file="inc_del_onclick.tpl"}
<script type="text/javascript">
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_container_name = "{$labels.warning_empty_testsuite_name|escape:'javascript'}";
function validateForm(f)
{
  if (isWhitespace(f.container_name.value)) 
  {
    alert_message(alert_box_title,warning_empty_container_name);
    selectField(f, 'container_name');
    return false;
  }
  
  /* Validation of a limited type of custom fields */
  var cf_designTime = document.getElementById('cfields_design_time');
  if (cf_designTime)
  {
    var cfields_container = cf_designTime.getElementsByTagName('input');
    var cfieldsChecks = validateCustomFields(cfields_container);
    if(!cfieldsChecks.status_ok)
    {
      var warning_msg = cfMessages[cfieldsChecks.msg_id];
      alert_message(alert_box_title,warning_msg.replace(/%s/, cfieldsChecks.cfield_label));
      return false;
    }

    cfields_container = cf_designTime.getElementsByTagName('textarea');
    cfieldsChecks = validateCustomFields(cfields_container);
    if(!cfieldsChecks.status_ok)
    {
      var warning_msg = cfMessages[cfieldsChecks.msg_id];
      alert_message(alert_box_title,warning_msg.replace(/%s/, cfieldsChecks.cfield_label));
      return false;
    }
  }
  
  return true;
}
</script>

{if $tlCfg->gui->checkNotSaved}
  <script type="text/javascript">
  var unload_msg = "{$labels.warning_unsaved|escape:'javascript'}";
  var tc_editor = "{$editorType}";
  </script>
  <script src="gui/javascript/checkmodified.js" type="text/javascript"></script>
{/if}

</head>

<body onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0]);focusInputField('name')">
<h1 class="title">{$parent_info.description}{$smarty.const.TITLE_SEP}{$parent_info.name|escape}</h1>
<div class="workBack">
<h1 class="title">{$labels.title_create} {lang_get s=$level}</h1>
{include file="inc_update.tpl" result=$sqlResult 
                               user_feedback=$user_feedback
                               item=$level action="add" name=$name
                               refresh=$gui->refreshTree}


<form method="post" action="{$basehref}lib/testcases/containerEdit.php?containerID={$containerID}"
        name="container_new" id="container_new"
        onSubmit="javascript:return validateForm(this);">

  <div style="font-weight: bold;">
    <div>
      <input type="hidden" name="containerType" id="containerType" value="{$gui->containerType}"/>
      <input type="hidden" name="add_testsuite" id="add_testsuite" />
      <input type="submit" name="add_testsuite_button" value="{$labels.btn_save}"
             onclick="show_modified_warning = false;" />
  
      <input type="button" name="go_back" value="{$labels.cancel}" 
             onclick="show_modified_warning=false; 
                     javascript: {if isset($gui->cancelActionJS)}{$gui->cancelActionJS} {else} history.back() {/if};"/>



    </div>  
    {include file="testcases/inc_testsuite_viewer_rw.tpl"}

   {* Custom fields *}
   {if $cf neq ""}
     <br />
     <div id="cfields_design_time" class="custom_field_container">
     {$cf}
     </div>
   {/if}
   
     <br />
   <div>
   {$kwView = $gsmarty_href_keywordsView|replace:'%s%':$gui->tproject_id}
   <a href={$kwView}>{$labels.tc_keywords}</a>
   {include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
   </div>
   <br />
  <div>
    <input type="submit" name="add_testsuite_button" value="{$labels.btn_save}" 
           onclick="show_modified_warning = false;" />

    <input type="button" name="go_back" value="{$labels.cancel}" 
           onclick="show_modified_warning=false; 
                    javascript: {if isset($gui->cancelActionJS)}{$gui->cancelActionJS} {else} history.back() {/if};"/>
  </div>  

</div>
</form>
</body>
</html>