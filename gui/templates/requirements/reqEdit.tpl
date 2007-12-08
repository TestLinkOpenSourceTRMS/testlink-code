{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqEdit.tpl,v 1.5 2007/12/08 18:10:26 franciscom Exp $
Purpose: smarty template - create / edit a req  
*}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

{literal}
<script type="text/javascript">
{/literal}
var alert_box_title = "{lang_get s='warning'}";
var warning_empty_req_docid = "{lang_get s='warning_empty_reqdoc_id'}";
var warning_empty_req_title = "{lang_get s='warning_empty_req_title'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.reqDocId.value)) 
  {
      alert_message(alert_box_title,warning_empty_req_docid);
      selectField(f, 'reqDocId');
      return false;
  }
  
  if (isWhitespace(f.req_title.value)) 
  {
      alert_message(alert_box_title,warning_empty_req_title);
      selectField(f, 'req_title');
      return false;
  }
  return true;
}
</script>
{/literal}
</head>


<body onload="document.forms[0].elements[0].focus()">
<h1>{$main_descr|escape}</h1>


<div class="workBack">
{if $action_descr != ''}
    <h1>{$action_descr|escape}</h1>
    <br />
 {/if}

{include file="inc_update.tpl" result=$sqlResult item="Requirement" name=$name action=$action}


<form name="reqEdit" id="reqEdit" method="post" onSubmit="javascript:return validateForm(this);">
  <input type="hidden" name="req_spec_id" value="{$req_spec_id}">
  <input type="hidden" name="requirement_id" value="{$req_id}">

  <div class="labelHolder"> <label for="reqDocId">{lang_get s='req_doc_id'}</label></div> 
	<div><input type="text" name="reqDocId" 
  		        size="{#REQ_DOCID_SIZE#}" maxlength="{#REQ_DOCID_MAXLEN#}" 
  		        value="{$req.req_doc_id}" />
  				{include file="error_icon.tpl" field="reqDocId"}
  </div>
  <br />
  <div class="labelHolder"> <label for="req_title">{lang_get s='title'}</label></div>
  <div><input type="text" name="req_title" 
  		        size="{#REQ_TITLE_SIZE#}" maxlength="{#REQ_TITLE_MAXLEN#}" 
  		        value="{$req.title}" />
  		    {include file="error_icon.tpl" field="req_title"}
  </div>
  <br />
  <div class="labelHolder"> <label for="scope">{lang_get s='scope'}</label></div>
	<div>{$scope}</div>
  <br />
  <div class="labelHolder"> <label for="reqStatus">{lang_get s='status'}</label>
     <select name="reqStatus">
  			{html_options options=$selectReqStatus selected=$req.status}
  		</select>
  </div>
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

<input type="submit" name="create_req" value="{$submit_button_label}" 
	       onclick="do_action.value='{$submit_button_action}'"/>
</div>
</form>

</div>

</div>

</body>
</html>