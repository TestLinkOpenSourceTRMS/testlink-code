{* 
TestLink Open Source Project - http://testlink.sourceforge.net/

@filesource	containerNew.tpl
Purpose: smarty template - create containers

20110114 - asimon - simplified checking for editor type by usage of $gui->editorType
20110110 - Julian - BUGID 4155: Warning message when navigating away from changed test
                                suite without saving
                  - added cancel button on top / create,cancel button on bottom
20101226 - franciscom - BUGID 4088: Required parameter for custom fields
20101202 - asimon - BUGID 4067: refresh tree problems 
20101012 - franciscom - BUGID 3887: CF Types validation
20100501 - franciscom - BUGID 3410: Smarty 3.0 compatibility
                        removed use of smarty.template to get current directory to include other
                        templates. On 3.0 RC smarty.template do not contains current dir

*}
{lang_get var="labels"
          s="warning_empty_testsuite_name,title_create,tc_keywords,warning_required_cf,
             warning,btn_create_testsuite,cancel,warning_unsaved"}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes"}

<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
var {$opt_cfg->js_ot_name} = new OptionTransfer("{$opt_cfg->from->name}","{$opt_cfg->to->name}");
{$opt_cfg->js_ot_name}.saveRemovedLeftOptions("{$opt_cfg->js_ot_name}_removedLeft");
{$opt_cfg->js_ot_name}.saveRemovedRightOptions("{$opt_cfg->js_ot_name}_removedRight");
{$opt_cfg->js_ot_name}.saveAddedLeftOptions("{$opt_cfg->js_ot_name}_addedLeft");
{$opt_cfg->js_ot_name}.saveAddedRightOptions("{$opt_cfg->js_ot_name}_addedRight");
{$opt_cfg->js_ot_name}.saveNewLeftOptions("{$opt_cfg->js_ot_name}_newLeft");
{$opt_cfg->js_ot_name}.saveNewRightOptions("{$opt_cfg->js_ot_name}_newRight");
</script>


{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
//BUGID 3943: Escape all messages (string)
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_container_name = "{$labels.warning_empty_testsuite_name|escape:'javascript'}";
var warning_required_cf = "{$labels.warning_required_cf|escape:'javascript'}";

function validateForm(f)
{
  if (isWhitespace(f.container_name.value)) 
  {
      alert_message(alert_box_title,warning_empty_container_name);
      selectField(f, 'container_name');
      return false;
  }
  
  /* Validation of a limited type of custom fields */
  var cf_designTime = document.getElementById('cfields_design_time');
	if (cf_designTime)
 	{
 		var cfields_container = cf_designTime.getElementsByTagName('input');
 		var checkRequiredCF = checkRequiredCustomFields(cfields_container);
		if(!checkRequiredCF.status_ok)
	  {
	      alert_message(alert_box_title,warning_required_cf.replace(/%s/, checkRequiredCF.cfield_label));
	      return false;
		}

 		var cfieldsChecks = validateCustomFields(cfields_container);
		if(!cfieldsChecks.status_ok)
	  	{
	    	var warning_msg = cfMessages[cfieldsChecks.msg_id];
	      	alert_message(alert_box_title,warning_msg.replace(/%s/, cfieldsChecks.cfield_label));
	      	return false;
		}

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
</script>

{* BUGID 4155 *}
{if $tlCfg->gui->checkNotSaved}
  <script type="text/javascript">
  var unload_msg = "{$labels.warning_unsaved|escape:'javascript'}";
  var tc_editor = "{$editorType}";
  </script>
  <script src="gui/javascript/checkmodified.js" type="text/javascript"></script>
{/if}

</head>

<body onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0]);focusInputField('name')">
{config_load file="input_dimensions.conf" section="containerEdit"} {* Constant definitions *}

<h1 class="title">{$parent_info.description}{$smarty.const.TITLE_SEP}{$parent_info.name|escape}</h1>

<div class="workBack">
<h1 class="title">{$labels.title_create} {lang_get s=$level}</h1>
	
{* BUGID 4067 *}
{include file="inc_update.tpl" result=$sqlResult 
                               user_feedback=$user_feedback
                               item=$level action="add" name=$name
                               refresh=$gui->refreshTree}


<form method="post" action="lib/testcases/containerEdit.php?containerID={$containerID}&tproject_id={$gui->tproject_id}" 
	      name="container_new" id="container_new"
        onSubmit="javascript:return validateForm(this);">


	<div style="font-weight: bold;">
		<div>
      		<input type="hidden" name="add_testsuite" id="add_testsuite" />
			<input type="submit" name="add_testsuite_button" value="{$labels.btn_create_testsuite}"
			       onclick="show_modified_warning = false;" />
			<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="javascript: show_modified_warning = false; history.back();"/>
		</div>	
	  {include file="testcases/inc_testsuite_viewer_rw.tpl"}

   {* Custom fields *}
   {if $cf != ""}
     <br />
     <div id="cfields_design_time" class="custom_field_container">
     {$cf}
     </div>
   {/if}
   
  	 <br />
   <div>
   <a href={$gui->keywordsViewHREF}>{$labels.tc_keywords}</a>
	 {include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
	 </div>
	 <br />
	<div>
		<input type="submit" name="add_testsuite_button" value="{$labels.btn_create_testsuite}" 
		       onclick="show_modified_warning = false;" />
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="javascript: show_modified_warning = false; history.back();"/>
	</div>	

</div>
</form>
</body>
</html>