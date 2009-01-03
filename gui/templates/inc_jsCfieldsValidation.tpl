{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_jsCfieldsValidation.tpl,v 1.2 2009/01/03 17:26:39 franciscom Exp $ 
Purpose: smarty template - include custom fields validation code 
*}
{lang_get var="cf_warning_msg"
          s="warning_numeric_cf,warning_float_cf,warning_email_cf"}

<script type="text/javascript" src='gui/javascript/cfield_validation.js'></script>

{literal}
<script type="text/javascript">
{/literal}
var cfMessages= new Object;
cfMessages.warning_numeric_cf="{$cf_warning_msg.warning_numeric_cf}";
cfMessages.warning_float_cf="{$cf_warning_msg.warning_float_cf}";
cfMessages.warning_email_cf="{$cf_warning_msg.warning_email_cf}";

var cfChecks = new Object;
cfChecks.email = {$tlCfg->validation_cfg->user_email_valid_regex};
{literal}
</script>
{/literal}