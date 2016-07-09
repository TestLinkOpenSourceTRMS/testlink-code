{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	reqEdit.tpl
Purpose: smarty template - create / edit a req  
@internal revisions

*}
{* ------------------------------------------------------------------------- *}

{lang_get var='labels' 
          s='show_event_history,btn_save,cancel,status,scope,warning,req_doc_id,
             title,warning_expected_coverage,type,warning_expected_coverage_range,
             warning_empty_reqdoc_id,expected_coverage,warning_empty_req_title,
             insert_last_req_doc_id,suggest_create_revision,revision_log_title,
             please_add_revision_log,suggest_create_revision_html,warning_suggest_create_revision,
             warning_unsaved,stay_here_req'}
             
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$gui->editorType}
{include file="inc_del_onclick.tpl"}
<script language="javascript" src="gui/javascript/ext_extensions.js" type="text/javascript"></script>

<script type="text/javascript">
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_req_docid = "{$labels.warning_empty_reqdoc_id|escape:'javascript'}";
var warning_empty_req_title = "{$labels.warning_empty_req_title|escape:'javascript'}";
var warning_expected_coverage = "{$labels.warning_expected_coverage|escape:'javascript'}";
var warning_expected_coverage_range = "{$labels.warning_expected_coverage_range|escape:'javascript'}";
var log_box_title = "{$labels.revision_log_title|escape:'javascript'}";
var log_box_text = "{$labels.please_add_revision_log|escape:'javascript'}";
var confirm_title = "{$labels.warning_suggest_create_revision|escape:'javascript'}";
var confirm_text = "{$labels.suggest_create_revision_html}";

// To manage hide/show expected coverage logic, depending of req type
var js_expected_coverage_cfg = new Array();
  
// DOM Object ID (oid)
// associative array with attributes
js_attr_cfg = new Array();
  
// Configuration for expected coverage attribute
js_attr_cfg['expected_coverage'] = new Array();
js_attr_cfg['expected_coverage']['oid'] = new Array();
js_attr_cfg['expected_coverage']['oid']['input'] = 'expected_coverage';
js_attr_cfg['expected_coverage']['oid']['container'] = 'expected_coverage_container';

{foreach from=$gui->attrCfg.expected_coverage key=req_type item=cfg_def}
  js_attr_cfg['expected_coverage'][{$req_type}]={$cfg_def};
{/foreach}

{literal}
function validateForm(f,cfg,check_expected_coverage)
{
	
	var cf_designTime = document.getElementById('custom_field_container');

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
	
  if (check_expected_coverage)
  {
	  if( cfg['expected_coverage'][f.reqType.value] == 1 )
	  {
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
	  }
	  else
	  {
	    f.expected_coverage.value = 0;
	  }
  }
	  
  /* Validation of a limited type of custom fields */
  if (cf_designTime)
  {
  	var cfields_container = cf_designTime.getElementsByTagName('input');
  	var cfieldsChecks = validateCustomFields(cfields_container);
  	if(!cfieldsChecks.status_ok)
    {
     	var warning_msg = cfMessages[cfieldsChecks.msg_id];
      alert_message(alert_box_title,warning_msg.replace(/%s/, cfieldsChecks.cfield_label));
      return false;
  	}
    
    /* Text area needs a special access */
  	cfields_container = cf_designTime.getElementsByTagName('textarea');
  	cfieldsChecks = validateCustomFields(cfields_container);
  	if(!cfieldsChecks.status_ok)
    {
     	var warning_msg = cfMessages[cfieldsChecks.msg_id];
      alert_message(alert_box_title,warning_msg.replace(/%s/, cfieldsChecks.cfield_label));
      return false;
  	}
  }
  if(f.prompt4log.value == 1)
  {
    Ext.Msg.prompt(log_box_title, log_box_text, function(btn, text){
        if (btn == 'ok'){
            f.goaway.value=1;
            f.prompt4log.value=0;
            f.do_save.value=1;
            f.save_rev.value=1;
            f.log_message.value=text;
            f.submit();
        }
      },this,true);    
      return false;    
    }
    else if(f.prompt4revision.value == 1)
    {
      Ext.Msg.prompt(confirm_title, confirm_text, function(btn, text){
        if (btn == 'ok'){
            f.goaway.value=1;
            f.prompt4log.value=0;
            f.do_save.value=1;
            f.save_rev.value=1;
            f.log_message.value=text;
            f.submit();
        }
        else
        {
            f.goaway.value=1;
            f.prompt4log.value=0;
            f.do_save.value=1;
            f.save_rev.value=0;
            f.log_message.value='';
            f.submit();
        }
      },this,true);    
      return false;    
    }
    // Warning!!!!
    // no idea if all these how the require session and submit 
    // will work with the other propmts.    
	  return Ext.ux.requireSessionAndSubmit(f);
	}
	{/literal}
	
	
	/**
   * 
   *
   */
  {literal} 
	window.onload = function()
  {
     // BUGID 4152: do not set focus on req doc id if log message window is shown
     if( document.getElementById('prompt4revision').value == 0 &&  document.getElementById('prompt4log').value == 0) {
	  focusInputField('reqDocId');
	 }
     {/literal}
     {* BUGID 3307 - disable this check if coverage management is disabled, to avoid javascript errors *}
     {if $gui->req_cfg->expected_coverage_management}
      configure_attr('reqType',js_attr_cfg);
     {/if}
     {literal}
  }
 
  
  /*
  function: configure_attr
            depending of req type, attributes will be set to disable, 
            if its value is nonsense for req type choosen by user.

  args :
         oid_type: id of html input used to choose req type
         cfg: see js_attr_cfg
         

  returns: -

*/
function configure_attr(oid_type,cfg)
{
  var o_reqtype=document.getElementById(oid_type);
  var oid;
  var keys2loop=new Array();
  var idx;
  var key;
  var attr_container;
  var attr2loop=new Array();
  attr2loop[0] = 'expected_coverage';
  
  for(idx=0;idx < attr2loop.length; idx++)
  {
    key=attr2loop[idx];
    oid=cfg[key]['oid']['container'];
    attr_container=document.getElementById(oid);
    if( cfg[key][o_reqtype.value] == 0 )
    {
      attr_container.style.display='none';
    }
    else
    {
      attr_container.style.display='';
    }
  }
} // configure_attr


/**
 * insert_last_doc_id
 *
 */
function insert_last_doc_id() 
{
	var last_id = document.getElementById('last_doc_id').value;
	var field = document.getElementById('reqDocId');
	field.value = last_id;
}
{/literal}
</script>

{* BUGID 4153 *}
{if $tlCfg->gui->checkNotSaved}
  <script type="text/javascript">
  var unload_msg = "{$labels.warning_unsaved|escape:'javascript'}";
  var tc_editor = "{$gui->editorType}";
  </script>
  <script src="gui/javascript/checkmodified.js" type="text/javascript"></script>
{/if}

</head>

<body>
<h1 class="title">{$gui->main_descr|escape}
	{if $gui->action_descr != ''}
		{$tlCfg->gui_title_separator_2}{$gui->action_descr|escape}
	{/if}
	{include file="inc_help.tpl" helptopic="hlp_req_edit" show_help_icon=true}
</h1>

{include file="inc_update.tpl" user_feedback=$gui->user_feedback}

<div class="workBack">
<form name="reqEdit" id="reqEdit" method="post" 
      action="{$basehref}lib/requirements/reqEdit.php" 
      onSubmit="javascript:return validateForm(this,js_attr_cfg,{$gui->req_cfg->expected_coverage_management});">

	<input type="hidden" name="req_spec_id" value="{$gui->req_spec_id}" />
	<input type="hidden" name="requirement_id" value="{$gui->req_id}" />
	<input type="hidden" name="req_version_id" value="{$gui->req_version_id}" />
	<input type="hidden" name="last_doc_id" id="last_doc_id" value="{$gui->last_doc_id|escape}" />
	<input type="hidden" name="save_rev" id="save_rev" value="0" />
	<input type="hidden" name="log_message" id="log_message" value="" />
	<input type="hidden" name="goaway" id="goaway" value="0" />
	<input type="hidden" name="prompt4log" id="prompt4log" value="{$gui->askForLog}" />
	<input type="hidden" name="do_save" id="do_save" value="{$gui->askForRevision}" />
	<input type="hidden" name="prompt4revision" id="prompt4revision" value="{$gui->askForRevision}" />
	
	{* BUGID 4063 *}
	{* BUGID 4153 - when save or cancel is pressed do not show modification warning *}
	<div class="groupBtn">
		<input type="submit" name="create_req" value="{$labels.btn_save}"
	         onclick="show_modified_warning = false; doAction.value='{$gui->operation}';"/>
		<input type="button" name="go_back" value="{$labels.cancel}" 
			onclick="javascript: show_modified_warning = false; history.back();"/>
	</div>
	{* BUGID 3953 - Only show checkbox to create another requirement on req creation *}
	{if $gui->doAction == 'create' || $gui->doAction == 'doCreate'}
	<div class="groupBtn">
	<input type="checkbox" id="stay_here"  name="stay_here" 
	       {if $gui->stay_here} checked="checked" {/if}/>{$labels.stay_here_req}
	</div>
	{/if}
	<br />
	
  	<div class="labelHolder"><label for="reqDocId">{$labels.req_doc_id}</label>
  	   		{if $gui->grants->mgt_view_events eq "yes" and $gui->req_id}
			<img style="margin-left:5px;" class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/question.gif" 
			     onclick="showEventHistoryFor('{$gui->req_id}','requirements')" 
			     alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
		{/if}
  	</div>
  	
	<div><input type="text" name="reqDocId" id="reqDocId"
  		        size="{#REQ_DOCID_SIZE#}" maxlength="{#REQ_DOCID_MAXLEN#}"
  		        value="{$gui->req.req_doc_id|escape}" required />
  				{include file="error_icon.tpl" field="reqDocId"}
  				
  				{* BUGID 3777 *}
  				{if $gui->req_cfg->allow_insertion_of_last_doc_id && $gui->last_doc_id != null  && 
  				    ($gui->doAction == 'create' || $gui->doAction == 'doCreate')}
	  				<span onclick="javascript:insert_last_doc_id();" >
	  				<img src="{$smarty.const.TL_THEME_IMG_DIR}/insert_step.png"
	  				     title='{$labels.insert_last_req_doc_id}: "{$gui->last_doc_id|escape}"'/>
	  				</span>
  				{/if}
  				
  	</div>
 	<br />
 	<div class="labelHolder"> <label for="req_title">{$labels.title}</label></div>
  	<div><input type="text" name="req_title" id="req_title"
  		        size="{#REQ_TITLE_SIZE#}" maxlength="{#REQ_TITLE_MAXLEN#}"
  		        value="{$gui->req.title|escape}" required />
  		    {include file="error_icon.tpl" field="req_title"}
 	 </div>
  	<br />
  	<div class="labelHolder"> <label for="scope">{$labels.scope}</label></div>
	<div>{$gui->scope}</div>
 	<br />
  	<div class="labelHolder"> <label for="reqStatus">{$labels.status}</label>
     	<select name="reqStatus" id="reqStatus">
  			{html_options options=$gui->reqStatusDomain selected=$gui->req.status}
  		</select>
  	</div>
  	<br />
 	<br />

	{if $gui->req.type}
		{assign var="preSelectedType" value=$gui->req.type}
	{else}
		{assign var="preSelectedType" value=$gui->preSelectedType}
	{/if}

  	<div class="labelHolder" id="reqType_container"> <label for="reqType">{$labels.type}</label>
     	<select name="reqType" id="reqType"
     	{* BUGID 3307 - disable this check if coverage management is disabled, to avoid javascript errors *}
     	{if $gui->req_cfg->expected_coverage_management}
     	     	  onchange="configure_attr('reqType',js_attr_cfg);"
     	{/if}
     	>
  			{html_options options=$gui->reqTypeDomain selected=$preSelectedType}
  		</select>
  	</div>
  	<br />
 	<br />
 	
 	{if $gui->req_cfg->expected_coverage_management}
  		<div class="labelHolder" id="expected_coverage_container"> <label for="expected_coverage">{$labels.expected_coverage}</label>
  	
  	{if $gui->req.expected_coverage}
			{assign var="coverage_to_display" value=$gui->req.expected_coverage}
		{else}
			{assign var="coverage_to_display" value=$gui->expected_coverage}
		{/if}
  	
  		<input type="text" name="expected_coverage" id="expected_coverage"
  		        size="{#REQ_EXPECTED_COVERAGE_SIZE#}" maxlength="{#REQ_EXPECTED_COVERAGE_MAXLEN#}"
  		        value="{$coverage_to_display}" required />
  		{include file="error_icon.tpl" field="expected_coverage"}
  	
 		</div>
 	{/if}
 	
  	<br />
    
   	{* Custom fields *}
   	{if $gui->cfields != ""}
   	  {* ID is used on logic to validate CF contain according to CF type *}
    	<div id="custom_field_container" class="custom_field_container">
    	{$gui->cfields}
     	</div>
     <br />
  	{/if}

	{* BUGID 3854 *}
	{* BUGID 4153 - when save or cancel is pressed do not show modification warning *}
	<div class="groupBtn">
		<input type="hidden" name="doAction" id="doAction" value="{$gui->operation}" />
		<input type="submit" name="create_req" value="{$labels.btn_save}"
	         onclick="show_modified_warning = false; doAction.value='{$gui->operation}';"/>
		<input type="button" name="go_back" value="{$labels.cancel}" 
			onclick="javascript: show_modified_warning = false; history.back();"/>
	</div>

  {if isset($gui->askForLog) && $gui->askForLog}
    <script>
    var ddd = '{$gui->req_cfg->expected_coverage_management}';
    {literal}
    if( document.getElementById('prompt4log').value == 1 )
    {
      validateForm(document.forms['reqEdit'],js_attr_cfg,ddd);
    }
    </script>
    {/literal}
  {/if}
  
  {if isset($gui->askForRevision) && $gui->askForRevision}
    <script>
    var ddd = '{$gui->req_cfg->expected_coverage_management}';
    {literal}
    if( document.getElementById('prompt4revision').value == 1 )
    {
      validateForm(document.forms['reqEdit'],js_attr_cfg,ddd);
    }
    {/literal}
    </script>
  {/if}
</form>
</div>

{if isset($gui->refreshTree) && $gui->refreshTree}
	{include file="inc_refreshTreeWithFilters.tpl"}
{/if}

</body>
</html>