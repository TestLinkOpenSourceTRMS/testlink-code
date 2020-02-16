{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource planEditJS.inc.tpl
*}

<script type="text/javascript">
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_tp_name = "{$labels.warning_empty_tp_name|escape:'javascript'}";

function validateForm(f)
{
  var cf_designTime = document.getElementById('custom_field_container');
  if (isWhitespace(f.testplan_name.value)) {
      alert_message(alert_box_title,warning_empty_tp_name);
      selectField(f, 'testplan_name');
      return false;
  }
  
  /* Validation of a limited type of custom fields */
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
  
    /* Text area needs a special access */
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

/**
 * manage_copy_ctrls
 *
 */
function manage_copy_ctrls(container_id,display_control_value,hide_value)
{
 o_container=document.getElementById(container_id);

 if( display_control_value == hide_value )
 {
   o_container.style.display='none';
 }
 else
 {
    o_container.style.display='';
 }
}

/**
 * Be Carefull this TRUST on existence of $gui->delAttachmentURL
 */
function jsCallDeleteFile(btn, text, o_id)
{ 
  var my_action='';
  if( btn == 'yes' ) {
    my_action='{$gui->delAttachmentURL}'+o_id;
    window.location=my_action;
  }
}        
</script>