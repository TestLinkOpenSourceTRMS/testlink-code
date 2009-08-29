{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqEdit.tpl,v 1.17 2009/08/29 19:21:42 schlundus Exp $
Purpose: smarty template - create / edit a req  
*}
{* ------------------------------------------------------------------------- *}

{lang_get var='labels' s='btn_save,cancel,status,scope'}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$gui->editorType}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
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
	
	window.onload = function()
		{
			focusInputField('reqDocId');
		}
	{/literal}
</script>
</head>

<body>
<h1 class="title">{$gui->main_descr|escape}xx
	{if $gui->action_descr != ''}
		{$tlCfg->gui_title_separator_2}{$gui->action_descr|escape}
	{/if}
</h1>

{include file="inc_update.tpl" user_feedback=$gui->user_feedback}

<div class="workBack">
<form name="reqEdit" id="reqEdit" method="post" onSubmit="javascript:return validateForm(this);">

	<input type="hidden" name="req_spec_id" value="{$gui->req_spec_id}" />
	<input type="hidden" name="requirement_id" value="{$gui->req_id}" />

  	<div class="labelHolder"><label for="reqDocId">{lang_get s='req_doc_id'}</label>
  	   		{if $gui->grants->mgt_view_events eq "yes" and $gui->req_id}
			<img style="margin-left:5px;" class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/question.gif" 
			     onclick="showEventHistoryFor('{$gui->req_id}','requirements')" 
			     alt="{lang_get s='show_event_history'}" title="{lang_get s='show_event_history'}"/>
		{/if}
  	</div>
	<div><input type="text" name="reqDocId" id="reqDocId"
  		        size="{#REQ_DOCID_SIZE#}" maxlength="{#REQ_DOCID_MAXLEN#}"
  		        value="{$gui->req.req_doc_id|escape}" />
  				{include file="error_icon.tpl" field="reqDocId"}
  	</div>
 	<br />
 	<div class="labelHolder"> <label for="req_title">{lang_get s='title'}</label></div>
  	<div><input type="text" name="req_title"
  		        size="{#REQ_TITLE_SIZE#}" maxlength="{#REQ_TITLE_MAXLEN#}"
  		        value="{$gui->req.title|escape}" />
  		    {include file="error_icon.tpl" field="req_title"}
 	 </div>
  	<br />
  	<div class="labelHolder"> <label for="scope">{$labels.scope}</label></div>
	<div>{$gui->scope}</div>
 	<br />
  	<div class="labelHolder"> <label for="reqStatus">{$labels.status}</label>
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
		<input type="submit" name="create_req" value="{$labels.btn_save}"
	         onclick="doAction.value='{$gui->operation}'"/>
		<input type="button" name="go_back" value="{$labels.cancel}" 
			onclick="javascript: history.back();"/>
	</div>
</form>
</div>

</body>
</html>