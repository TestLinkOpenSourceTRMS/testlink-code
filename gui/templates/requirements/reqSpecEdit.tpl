{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqSpecEdit.tpl,v 1.8 2008/05/02 07:09:23 franciscom Exp $
Purpose: smarty template - create a new req document

rev: 20080415 - franciscom - refactoring
     20071120 - franciscom - added ext js alert message box

*}

{lang_get var="labels"
          s='warning,warning_empty_req_spec_title,title,scope,req_total'}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

{literal}
<script type="text/javascript">
{/literal}
var alert_box_title = "{$labels.warning}";
var warning_empty_req_spec_title = "{$labels.warning_empty_req_spec_title}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.req_spec_title.value))
  {

      alert_message(alert_box_title,warning_empty_req_spec_title);
      selectField(f, 'req_spec_title');
      return false;
  }
  return true;
}
</script>
{/literal}
</head>

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<body>
<h1>
 {lang_get s='help' var='common_prefix'}
 {lang_get s='req_spec' var="xx_alt"}
 {assign var="text_hint" value="$common_prefix: $xx_alt"}
 {include file="inc_help.tpl" help="requirementsCoverage" locale=$locale
          inc_help_alt="$text_hint" inc_help_title="$text_hint"  inc_help_style="float: right;"}
 {$gui->main_descr|escape}
</h1>

{if $gui->grants->req_mgmt == "yes"}
  <div class="workBack">
    {if $gui->action_descr != ''}
    <h1>{$gui->action_descr|escape}</h1>
    <br />
    {/if}

    {include file="inc_update.tpl" user_feedback=$gui->user_feedback}

    <form name="reqSpecEdit" id="reqSpecEdit" method="post" onSubmit="javascript:return validateForm(this);">
    <input type="hidden" name="req_spec_id" value="{$gui->req_spec_id}" />

   <div class="labelHolder"><label for="req_spec_title">{$labels.title}</label></div>
   <div>
    <input type="text" id="req_spec_title" name="req_spec_title"
           size="{#REQ_SPEC_TITLE_SIZE#}"
    		   maxlength="{#REQ_SPEC_TITLE_MAXLEN#}"
           value="{$gui->req_spec_title}" />
  				{include file="error_icon.tpl" field="req_spec_title"}
   </div>
   <br />
	 <div class="labelHolder">
		<label for="scope">{$labels.scope}</label>
		</div>
		<div>
		{$gui->scope}
   </div>
   <br />
   <div class="labelHolder"><label for="countReq">{$labels.req_total}</label>
	 <input type="text" name="countReq"
		      size="{#REQ_COUNTER_SIZE#}" maxlength="{#REQ_COUNTER_MAXLEN#}"
			    value="{$gui->total_req_counter}" />
	 </div>
     <br />
   {* Custom fields *}
   {if $gui->cfields neq ""}
     <div class="custom_field_container">
     {$gui->cfields}
     </div>
     <br />
   {/if}

<div class="groupBtn">
	<input type="hidden" name="doAction" value="" />
	<input type="submit" name="createSRS" value="{$gui->submit_button_label}"
	       onclick="doAction.value='{$gui->operation}'" />
</div>

 </form>
 </div>
{/if}


<script type="text/javascript" defer="1">
   	document.forms[0].req_spec_title.focus()
</script>

</body>
</html>