{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqSpecEdit.tpl,v 1.16 2009/08/29 19:21:42 schlundus Exp $
Purpose: smarty template - create a new req document

*}
{* ------------------------------------------------------------------------- *}

{lang_get var="labels"
          s='warning,warning_empty_req_spec_title,title,scope,req_total,cancel,show_event_history,warning_countreq_numeric'}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$gui->editorType}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
	var alert_box_title = "{$labels.warning}";
	var warning_empty_req_spec_title = "{$labels.warning_empty_req_spec_title}";
	var warning_countreq_numeric = "{$labels.warning_countreq_numeric}";
	{literal}
	function validateForm(f)
	{
		if (isWhitespace(f.title.value))
		{
			alert_message(alert_box_title,warning_empty_req_spec_title);
			selectField(f,'title');
			return false;
		}
		if (isNaN(parseInt(f.countReq.value)))
		{
			alert_message(alert_box_title,warning_countreq_numeric);
			selectField(f,'countReq');
			return false;
		}
		return true;
	}
	{/literal}
	</script>
</head>

<body>
<h1 class="title">
	{if $gui->action_descr != ''}{$gui->action_descr|escape}{/if} {$gui->main_descr|escape}
	{include file="inc_help.tpl" helptopic="hlp_requirementsCoverage"}
</h1>

{include file="inc_update.tpl" user_feedback=$gui->user_feedback}

<div class="workBack">
	<form name="reqSpecEdit" id="reqSpecEdit" method="post" onSubmit="javascript:return validateForm(this);">
	    <input type="hidden" name="req_spec_id" value="{$gui->req_spec_id}" />
	
		<div class="labelHolder"><label for="req_spec_title">{$labels.title}</label>
	   		{if $mgt_view_events eq "yes" and $gui->req_spec_id}
				<img style="margin-left:5px;" class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/question.gif" 
				     onclick="showEventHistoryFor('{$gui->req_spec_id}','req_specs')" 
				     alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
			{/if}
	   	</div>
	   	<div>
		    <input type="text" id="title" name="title"
		           size="{#REQ_SPEC_TITLE_SIZE#}"
				   maxlength="{#REQ_SPEC_TITLE_MAXLEN#}"
		           value="{$gui->req_spec_title|escape}" />
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
			<input type="text" id="countReq" name="countReq" size="{#REQ_COUNTER_SIZE#}" 
			      maxlength="{#REQ_COUNTER_MAXLEN#}" value="{$gui->total_req_counter}" />
		</div>
	    <br />
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
			<input type="button" name="go_back" value="{$labels.cancel}" 
				onclick="javascript: history.back();"/>
		</div>
	</form>
</div>

<script type="text/javascript" defer="1">
   	document.forms[0].req_spec_title.focus()
</script>

</body>
</html>