{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqSpecEdit.tpl,v 1.3 2007/11/25 18:57:47 franciscom Exp $
Purpose: smarty template - create a new req document 

rev: 20071120 - franciscom - added ext js alert message box

*}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

{literal}
<script type="text/javascript">
{/literal}
var alert_box_title = "{lang_get s='warning'}";
var warning_empty_req_spec_title = "{lang_get s='warning_empty_req_spec_title'}";
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
          alt="$text_hint" title="$text_hint"  style="float: right;"}
 {$main_descr|escape} 
</h1>

{if $modify_req_rights == "yes"}
  <div class="workBack">
    {if $action_descr != ''}
    <h1>{$action_descr|escape}</h1>
    <br />
    {/if}
    
    {include file="inc_update.tpl" user_feedback=$user_feedback}
    	
    <form name="reqSpecEdit" id="reqSpecEdit" method="post" onSubmit="javascript:return validateForm(this);">
    <input type="hidden" name="req_spec_id" value="{$req_spec_id}">
   
   <p>
   <div class="labelHolder"><label for="req_spec_title">{lang_get s='title'}</label></div> 
   <div>
    <input type="text" id="req_spec_title" name="req_spec_title" 
           size="{#REQ_SPEC_TITLE_SIZE#}"  
    		   maxlength="{#REQ_SPEC_TITLE_MAXLEN#}" 
           value="{$req_spec_title}"/>
  				{include file="error_icon.tpl" field="req_spec_title"}
   </div>
   </p>
   <br />
   <p>
	 <div class="labelHolder">
		<label for="scope">{lang_get s='scope'}</label>
		</div>
		<div>
		{$scope}
   </div>
   </p>
   <br />
   <p>
   <div class="labelHolder"><label for="countReq">{lang_get s='req_total'}</label>
	 <input type="text" name="countReq" 
		      size="{#REQ_COUNTER_SIZE#}" maxlength="{#REQ_COUNTER_MAXLEN#}" 
			    value="{$total_req_counter}" />
	 </div>
   </p>
     <br />
   {* Custom fields *}
   {if $cf neq ""}
     <div class="custom_field_container">
     {$cf}
     </div>
     <br />
   {/if}
   
<div class="groupBtn">
	<input type="hidden" name="do_action" value="">
	<input type="submit" name="createSRS" value="{$submit_button_label}" 
	       onclick="do_action.value='{$submit_button_action}'"/>
</div>

 </form>
 </div>
{/if}


<script type="text/javascript" defer="1">
   	document.forms[0].req_spec_title.focus()
</script>

</body>
</html>
