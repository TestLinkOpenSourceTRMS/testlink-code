{*
TestLink Open Source Project - http://testlink.sourceforge.net/

@filesource	reqSpecEdit.tpl
Purpose: smarty template - create a new req document

@internal revisions
20110903 - franciscom - TICKET 4661: Implement Requirement Specification Revisioning for better traceabilility
*}

{lang_get var="labels"
          s='warning,warning_empty_req_spec_title,title,scope,req_total,type,warning_required_cf,
             doc_id,cancel,show_event_history,warning_empty_doc_id,warning_countreq_numeric,
             warning_unsaved,revision_log_title,please_add_revision_log,warning_suggest_create_revision,
             suggest_create_revision_html'}
             
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$gui->editorType}
{include file="inc_action_onclick.tpl"} {* includes ext-js *}
<script language="javascript" src="gui/javascript/ext_extensions.js" type="text/javascript"></script>

<script type="text/javascript">
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_req_spec_title = "{$labels.warning_empty_req_spec_title|escape:'javascript'}";
var warning_empty_doc_id = "{$labels.warning_empty_doc_id|escape:'javascript'}";
var warning_countreq_numeric = "{$labels.warning_countreq_numeric|escape:'javascript'}";
var warning_required_cf = "{$labels.warning_required_cf|escape:'javascript'}";

// TICKET 4661
var log_box_title = "{$labels.revision_log_title|escape:'javascript'}";
var log_box_text = "{$labels.please_add_revision_log|escape:'javascript'}";
var confirm_title = "{$labels.warning_suggest_create_revision|escape:'javascript'}";
var confirm_text = "{$labels.suggest_create_revision_html}";

	
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
	
	{if $gui->external_req_management}
	  if (isNaN(parseInt(f.countReq.value)))
	  {
	  	alert_message(alert_box_title,warning_countreq_numeric);
	  	selectField(f,'countReq');
	  	return false;
	  }
	{/if}

	// alert('INSIDE validateForm(56)');
	if(!checkCustomFields('custom_field_container',alert_box_title,warning_required_cf))
	{
		// alert('INSIDE validateForm(58)');
		return false;
	}
	// alert('INSIDE validateForm(60)');

	// ---------------------------------------------------------
	// Revision Log Logic
	// alert(f.prompt4log.value);
	// alert(f.prompt4revision.value);
	if(f.prompt4log.value == 1)
	{
		Ext.Msg.prompt(	log_box_title, log_box_text, 
						function(btn, text)
						{
							if (btn == 'ok')
							{
								f.goaway.value=1;
								f.prompt4log.value=0;
								f.do_save.value=1;
								f.save_rev.value=1;
								f.log_message.value=text;
								f.submit();
							}
						},
						this,true);    
		
		return false;    
	} 
	else if(f.prompt4revision.value == 1)
	{
		Ext.Msg.prompt(	confirm_title, confirm_text, 
						function(btn, text)
						{
							if (btn == 'ok')
							{
								f.save_rev.value=1;
								f.log_message.value=text;
							}
							else
							{
								f.save_rev.value=0;
								f.log_message.value='';
							}
							f.goaway.value=1;
							f.prompt4log.value=0;
							f.do_save.value=1;
							f.submit();
						},this,true);    
		return false;    
	}
	// -----------------------------------------------------------------

	return Ext.ux.requireSessionAndSubmit(f);
}
</script>

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

	<div class="groupBtn">
		<input type="hidden" name="parentID" value="{$gui->parentID}" />
	    <input type="hidden" name="req_spec_id" id="req_spec_id" value="{$gui->req_spec_id}" />
	    <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />

		<input type="hidden" name="doAction" value="" />

		{* BEGIN - Revision Log Logic *}
		<input type="hidden" name="save_rev" id="save_rev" value="0" />
		<input type="hidden" name="log_message" id="log_message" value="" />
		<input type="hidden" name="goaway" id="goaway" value="0" />
		<input type="hidden" name="prompt4log" id="prompt4log" value="{$gui->askForLog}" />
		<input type="hidden" name="do_save" id="do_save" value="{$gui->askForRevision}" />
		<input type="hidden" name="prompt4revision" id="prompt4revision" value="{$gui->askForRevision}" />
		{* END - Revision Log Logic *}

		{* Actions that are displayed ON TOP and BOTTOM of page for usability *}
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
		{if $gui->cfields != ""}
			<div class="custom_field_container" id="custom_field_container">
		    	{$gui->cfields}
		    </div>
		<br />
		{/if}

		{* Actions that are displayed ON TOP and BOTTOM of page for usability *}
		<div class="groupBtn">
			<input type="submit" name="createSRS" value="{$gui->submit_button_label}"
		       onclick="show_modified_warning = false; doAction.value='{$gui->operation}';" />
			<input type="button" name="go_back" value="{$labels.cancel}" 
				onclick="javascript: show_modified_warning = false; history.back();"/>
		</div>


	{* Revision Log Logic *}
	{if isset($gui->askForLog) && $gui->askForLog}
		<script>
		if( document.getElementById('prompt4log').value == 1 )
		{
		  	validateForm(document.forms['reqSpecEdit'],'askforlog');
		}
		</script>
	{/if}
	
	{if isset($gui->askForRevision) && $gui->askForRevision}
		<script>
		if( document.getElementById('prompt4revision').value == 1 )
		{
		  validateForm(document.forms['reqSpecEdit'],'askforrevision');
		}
		</script>
	{/if}

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