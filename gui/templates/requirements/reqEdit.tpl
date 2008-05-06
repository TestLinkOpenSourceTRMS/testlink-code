{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqEdit.tpl,v 1.10 2008/05/06 06:26:09 franciscom Exp $
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


{literal}
<script type="text/javascript">
window.onload=function()
{
 focusInputField('reqDocId');
}
</script>
{/literal}
</head>


<body>
<h1 class="title">{$gui->main_descr|escape}</h1>

<div class="workBack">
{if $gui->action_descr != ''}
    <h1 class="title">{$gui->action_descr|escape}</h1>
    <br />
 {/if}

{include file="inc_update.tpl" user_feedback=$gui->user_feedback}


<form name="reqEdit" id="reqEdit" method="post" onSubmit="javascript:return validateForm(this);">
	<input type="hidden" name="req_spec_id" value="{$gui->req_spec_id}" />
	<input type="hidden" name="requirement_id" value="{$gui->req_id}" />

  	<div class="labelHolder"> <label for="reqDocId">{lang_get s='req_doc_id'}</label></div>
	<div><input type="text" name="reqDocId" id="reqDocId"
  		        size="{#REQ_DOCID_SIZE#}" maxlength="{#REQ_DOCID_MAXLEN#}"
  		        value="{$gui->req.req_doc_id}" />
  				{include file="error_icon.tpl" field="reqDocId"}
  	</div>
 	<br />
 	<div class="labelHolder"> <label for="req_title">{lang_get s='title'}</label></div>
  	<div><input type="text" name="req_title"
  		        size="{#REQ_TITLE_SIZE#}" maxlength="{#REQ_TITLE_MAXLEN#}"
  		        value="{$gui->req.title}" />
  		    {include file="error_icon.tpl" field="req_title"}
 	 </div>
  	<br />
  	<div class="labelHolder"> <label for="scope">{lang_get s='scope'}</label></div>
	<div>{$gui->scope}</div>
 	<br />
  	<div class="labelHolder"> <label for="reqStatus">{lang_get s='status'}</label>
     	<select name="reqStatus">
  			{html_options options=$gui->reqStatusDomain selected=$gui->req.status}
  		</select>
  	</div>
  	<br />
   	{* Custom fields *}
   	{if $gui->cfields != ""}
    	<div class="custom_field_container">
    	{$gui->cfields}
     	</div>
     <br />
  	{/if}

	<div class="groupBtn">
		<input type="hidden" name="doAction" value="" />
		<input type="submit" name="create_req" value="{$gui->submit_button_label}"
	         onclick="doAction.value='{$gui->operation}'"/>
	</div>
</form>

</div>

</body>
</html>