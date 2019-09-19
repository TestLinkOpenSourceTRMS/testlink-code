{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_jsCfieldsValidation.tpl,v 1.6 2009/09/21 09:27:53 franciscom Exp $ 
Purpose: smarty template - include custom fields validation code 

rev: 
    20090823 - franciscom - added textarea_length, to improve BUGID 2414- check for text area character qty.
*}
{lang_get var="cf_warning_msg"
          s="warning_numeric_cf,warning_float_cf,warning_email_cf,warning_text_area_cf"}

<script type="text/javascript" src='gui/javascript/cfield_validation.js'></script>

{literal}
<script type="text/javascript">
{/literal}
var cfMessages= new Object;
cfMessages.warning_numeric_cf="{$cf_warning_msg.warning_numeric_cf}";
cfMessages.warning_float_cf="{$cf_warning_msg.warning_float_cf}";
cfMessages.warning_email_cf="{$cf_warning_msg.warning_email_cf}";
cfMessages.warning_text_area_cf="{$cf_warning_msg.warning_text_area_cf}";


var cfChecks = new Object;
cfChecks.email = {$tlCfg->validation_cfg->user_email_valid_regex_js};
cfChecks.textarea_length = {$tlCfg->custom_fields->max_length};
{literal}
</script>
{/literal}