{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqEdit.tpl,v 1.20 2009/12/19 13:08:39 franciscom Exp $
Purpose: smarty template - create / edit a req  
internal revision
20091125 - franciscom - 
*}
{* ------------------------------------------------------------------------- *}

{lang_get var='labels' 
          s='show_event_history,btn_save,cancel,status,scope,warning,req_doc_id,
             title,warning_expected_coverage,type,warning_expected_coverage_range,
             warning_empty_reqdoc_id,expected_coverage,warning_empty_req_title'}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$gui->editorType}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
	var alert_box_title = "{$labels.warning}";
	var warning_empty_req_docid = "{$labels.warning_empty_reqdoc_id}";
	var warning_empty_req_title = "{$labels.warning_empty_req_title}";
	var warning_expected_coverage = "{$labels.warning_expected_coverage}";
	var warning_expected_coverage_range = "{$labels.warning_expected_coverage_range}";
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
    {/literal}
		
    {if $gui->req_cfg->expected_coverage_management  }
		  {literal}
		  value = parseInt(f.expected_coverage.value);
		  if (isNaN(value))
		  {
		  	alert_message(alert_box_title,warning_expected_coverage);
		  	selectField(f,'expected_coverage');
		  	return false;
		  }
		  else if( value <= 0)
		  {
		  	alert_message(alert_box_title,warning_expected_coverage_range);
		  	selectField(f,'expected_coverage');
		  	return false;
		  }
		  {/literal}
		{/if}
		
		{literal}
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

  	<div class="labelHolder"><label for="reqDocId">{$labels.req_doc_id}</label>
  	   		{if $gui->grants->mgt_view_events eq "yes" and $gui->req_id}
			<img style="margin-left:5px;" class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/question.gif" 
			     onclick="showEventHistoryFor('{$gui->req_id}','requirements')" 
			     alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
		{/if}
  	</div>
	<div><input type="text" name="reqDocId" id="reqDocId"
  		        size="{#REQ_DOCID_SIZE#}" maxlength="{#REQ_DOCID_MAXLEN#}"
  		        value="{$gui->req.req_doc_id|escape}" />
  				{include file="error_icon.tpl" field="reqDocId"}
  	</div>
 	<br />
 	<div class="labelHolder"> <label for="req_title">{$labels.title}</label></div>
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
 	<br />

  	<div class="labelHolder"> <label for="reqType">{$labels.type}</label>
     	<select name="reqType">
  			{html_options options=$gui->reqTypeDomain selected=$gui->req.type}
  		</select>
  	</div>
  	<br />
 	<br />
 	
    {if $gui->req_cfg->expected_coverage_management  }
  	<div class="labelHolder"> <label for="expected_coverage">{$labels.expected_coverage}</label>
  	<input type="text" name="expected_coverage" id="expected_coverage"
  		        size="{#REQ_EXPECTED_COVERAGE_SIZE#}" maxlength="{#REQ_EXPECTED_COVERAGE_MAXLEN#}"
  		        value="{$gui->req.expected_coverage}" />
  		    {include file="error_icon.tpl" field="req_title"}
 	  </div>
  	<br />
    {/if}  	
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