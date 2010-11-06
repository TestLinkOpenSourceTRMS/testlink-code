{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqEdit.tpl,v 1.33 2010/11/06 11:42:47 amkhullar Exp $
Purpose: smarty template - create / edit a req  
internal revision
20101011 - franciscom - BUGID 3886: CF Types validation
20101006 - asimon - BUGID 3854
20100915 - Julian - BUGID 3777 - Allow to insert last req doc id when creating requirement
20100808 - asimon - added logic to refresh filtered tree on changes
20100502 - franciscom - BUGID 3413: removed debug info
20100319 - asimon - BUGID 1748 - added logic to add and remove requirement relations
20091231 - franciscom - added logic to display and check expected coverage
                        attribute based on req type, with configuration
                        managed using $tlCfg->req_cfg->type_expected_coverage

*}
{* ------------------------------------------------------------------------- *}

{lang_get var='labels' 
          s='show_event_history,btn_save,cancel,status,scope,warning,req_doc_id,
             title,warning_expected_coverage,type,warning_expected_coverage_range,
             warning_empty_reqdoc_id,expected_coverage,warning_empty_req_title,
             insert_last_req_doc_id'}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$gui->editorType}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
//BUGID 3943: Escape all messages (string)
	var alert_box_title = "{$labels.warning|escape:'javascript'}";
	var warning_empty_req_docid = "{$labels.warning_empty_reqdoc_id|escape:'javascript'}";
	var warning_empty_req_title = "{$labels.warning_empty_req_title|escape:'javascript'}";
	var warning_expected_coverage = "{$labels.warning_expected_coverage|escape:'javascript'}";
	var warning_expected_coverage_range = "{$labels.warning_expected_coverage_range|escape:'javascript'}";

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
	  
		return true;
	}
	{/literal}
	
	
	/**
   * 
   *
   */
  {literal} 
	window.onload = function()
  {
	   focusInputField('reqDocId');
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
</head>

<body>
<h1 class="title">{$gui->main_descr|escape}
	{if $gui->action_descr != ''}
		{$tlCfg->gui_title_separator_2}{$gui->action_descr|escape}
	{/if}
</h1>

{include file="inc_update.tpl" user_feedback=$gui->user_feedback}

<div class="workBack">
<form name="reqEdit" id="reqEdit" method="post" 
      onSubmit="javascript:return validateForm(this,js_attr_cfg,{$gui->req_cfg->expected_coverage_management});">

	<input type="hidden" name="req_spec_id" value="{$gui->req_spec_id}" />
	<input type="hidden" name="requirement_id" value="{$gui->req_id}" />
	<input type="hidden" name="req_version_id" value="{$gui->req_version_id}" />
	<input type="hidden" name="last_doc_id" id="last_doc_id" value="{$gui->last_doc_id|escape}" />
	
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
  		        value="{$gui->req.title|escape}" />
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
  		        value="{$coverage_to_display}" />
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
	<div class="groupBtn">
		<input type="hidden" name="doAction" value="" />
		<input type="submit" name="create_req" value="{$labels.btn_save}"
	         onclick="doAction.value='{$gui->operation}';parent.frames['treeframe'].document.forms['filter_panel_form'].submit();"/>
		<input type="button" name="go_back" value="{$labels.cancel}" 
			onclick="javascript: history.back();"/>
	</div>
</form>
</div>

{if isset($gui->refreshTree) && $gui->refreshTree}
	{include file="inc_refreshTreeWithFilters.tpl"}
{/if}

</body>
</html>