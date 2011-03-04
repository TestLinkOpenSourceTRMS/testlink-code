{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqSpecEdit.tpl,v 1.24.2.3 2011/01/14 14:39:04 asimon83 Exp $
Purpose: smarty template - create a new req document

rev:
  20110304 - asimon - added help icon with a description of some of the "new" features
  20110114 - asimon - simplified checking for editor type by usage of $gui->editorType
  20110111 - Julian - Added Save, Cancel Button on top of the page
  20110110 - Julian - BUGID 4154: Warning message when navigating away from changed requirement
                                  specification without saving
  20101124 - Julian - BUGID 4051: Ajax login on timeout for requirement specifications to avoid data loss
  20101006 - asimon - BUGID 3854
  20100810 - asimon - BUGID 3317: disabled total count of requirements by default
  20100808 - asimon - added logic to refresh filtered tree on changes
  20091230 - franciscom - req spec type
*}
{* ------------------------------------------------------------------------- *}

{lang_get var="labels"
          s='warning,warning_empty_req_spec_title,title,scope,req_total,type,
             doc_id,cancel,show_event_history,warning_empty_doc_id,warning_countreq_numeric,
             warning_unsaved'}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$gui->editorType}
{include file="inc_del_onclick.tpl"}
<script language="javascript" src="gui/javascript/ext_extensions.js" type="text/javascript"></script>

<script type="text/javascript">
//BUGID 3943: Escape all messages (string)
	var alert_box_title = "{$labels.warning|escape:'javascript'}";
	var warning_empty_req_spec_title = "{$labels.warning_empty_req_spec_title|escape:'javascript'}";
	var warning_empty_doc_id = "{$labels.warning_empty_doc_id|escape:'javascript'}";
	var warning_countreq_numeric = "{$labels.warning_countreq_numeric|escape:'javascript'}";
	{literal}
	function validateForm(f)
	{
   
		if (isWhitespace(f.doc_id.value)) 
  	{
    	alert_message(alert_box_title,warning_empty_doc_id);
			selectField(f, 'doc_id');
			return false;
		}

		if (isWhitespace(f.title.value))
		{
			alert_message(alert_box_title,warning_empty_req_spec_title);
			selectField(f,'title');
			return false;
		}

		{/literal}
		{if $gui->external_req_management}
		{literal}
		if (isNaN(parseInt(f.countReq.value)))
		{
			alert_message(alert_box_title,warning_countreq_numeric);
			selectField(f,'countReq');
			return false;
		}
		{/literal}
		{/if}
		{literal}
		
		return Ext.ux.requireSessionAndSubmit(f);
	}
	{/literal}
	</script>

{* BUGID 4154 *}
{if $tlCfg->gui->checkNotSaved}
  <script type="text/javascript">
  var unload_msg = "{$labels.warning_unsaved|escape:'javascript'}";
  var tc_editor = "{$gui->editorType}";
  </script>
  <script src="gui/javascript/checkmodified.js" type="text/javascript"></script>
{/if}

</head>

<body>
<h1 class="title">
	{if $gui->action_descr != ''}{$gui->action_descr|escape}{/if} {$gui->main_descr|escape}
	{include file="inc_help.tpl" helptopic="hlp_req_spec_edit" show_help_icon=true}
</h1>

{include file="inc_update.tpl" user_feedback=$gui->user_feedback}

<div class="workBack">
	<form name="reqSpecEdit" id="reqSpecEdit" method="post" onSubmit="javascript:return validateForm(this);">
	    <input type="hidden" name="req_spec_id" value="{$gui->req_spec_id}" />

	{* BUGID 3854 *}
	{* BUGID 4154 - when save or cancel is pressed do not show modification warning *}
	<div class="groupBtn">
		<input type="hidden" name="doAction" value="" />
		<input type="submit" name="createSRS" value="{$gui->submit_button_label}"
	       onclick="show_modified_warning = false; doAction.value='{$gui->operation}';" />
		<input type="button" name="go_back" value="{$labels.cancel}" 
			onclick="javascript: show_modified_warning = false; history.back();"/>
	</div>
	<br />
  	<div class="labelHolder"><label for="doc_id">{$labels.doc_id}</label>
  	</div>
	  <div><input type="text" name="doc_id" id="doc_id"
  		        size="{#REQSPEC_DOCID_SIZE#}" maxlength="{#REQSPEC_DOCID_MAXLEN#}"
  		        value="{$gui->req_spec_doc_id|escape}" />
  				{include file="error_icon.tpl" field="doc_id"}
  	</div>
	
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
	   	
	   	{if $gui->external_req_management}
	   	<br />
	   	<div class="labelHolder"><label for="countReq">{$labels.req_total}</label>
			<input type="text" id="countReq" name="countReq" size="{#REQ_COUNTER_SIZE#}" 
			      maxlength="{#REQ_COUNTER_MAXLEN#}" value="{$gui->total_req_counter}" />
		</div>
		{/if}
		
	  <br />
		
  	<div class="labelHolder"> <label for="reqSpecType">{$labels.type}</label>
     	<select name="reqSpecType">
  			{html_options options=$gui->reqSpecTypeDomain selected=$gui->req_spec.type}
  		</select>
  	</div>

		
	    <br />
		{if $gui->cfields neq ""}
			<div class="custom_field_container">
		    	{$gui->cfields}
		    </div>
		<br />
		{/if}

		{* BUGID 3854 *}
		{* BUGID 4154 - when save or cancel is pressed do not show modification warning *}
		<div class="groupBtn">
			<input type="submit" name="createSRS" value="{$gui->submit_button_label}"
		       onclick="show_modified_warning = false; doAction.value='{$gui->operation}';" />
			<input type="button" name="go_back" value="{$labels.cancel}" 
				onclick="javascript: show_modified_warning = false; history.back();"/>
		</div>
	</form>
</div>

<script type="text/javascript" defer="1">
   	document.forms[0].doc_id.focus()
</script>

{if isset($gui->refreshTree) && $gui->refreshTree}
	{include file="inc_refreshTreeWithFilters.tpl"}
{/if}

</body>
</html>