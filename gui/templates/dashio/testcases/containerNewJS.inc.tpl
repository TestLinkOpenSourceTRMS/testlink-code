{* 
TestLink Open Source Project - http://testlink.sourceforge.net/

Purpose: smarty template - with JS functions / actions

@filesource containerNewJS.inc.tpl
*}
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