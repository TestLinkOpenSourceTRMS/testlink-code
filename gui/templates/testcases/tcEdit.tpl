{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tcEdit.tpl,v 1.33.2.2 2011/01/14 14:39:04 asimon83 Exp $ 
Purpose: smarty template - edit test specification: test case

@internal Revisions:
  20110114 - asimon - simplified checking for editor type by usage of $gui->editorType
  20110111 - Julian - Improved modified warning message when navigating away without saving
  20101010 - franciscom - refactoring of BUGID 3062 -> gui/javascript/tcase_utils.js
                          added testsuite_id for same logic
  20100810 - asimon - BUGID 3579: solved tree refreshing problems
  20100315 - franciscom - BUGID 3410: Smarty 3.0 compatibility - changes in smarty.template behaviour
	20100306 - eloff - BUGID 3062 - Check for duplicate name via AJAX call - checkTCaseDuplicateName()
	20100124 - eloff - BUGID 3088 - Check valid session before submit
	20100110 - eloff - BUGID 2036 - Check modified content before exit
	20090422 - franciscom - BUGID 2414
	20090419 - franciscom - BUGID  - edit while executing
*}

{lang_get var="labels"
          s="warning,warning_empty_tc_title,btn_save,
             version,title_edit_tc,cancel,warning_unsaved"}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes" editorType=$gui->editorType}

{include file="inc_del_onclick.tpl"}
<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
<script language="javascript" src="gui/javascript/ext_extensions.js" type="text/javascript"></script>
<script language="javascript" src="gui/javascript/tcase_utils.js" type="text/javascript"></script>

{assign var="opt_cfg" value=$gui->opt_cfg}
<script type="text/javascript" language="JavaScript">
var {$opt_cfg->js_ot_name} = new OptionTransfer("{$opt_cfg->from->name}","{$opt_cfg->to->name}");
{$opt_cfg->js_ot_name}.saveRemovedLeftOptions("{$opt_cfg->js_ot_name}_removedLeft");
{$opt_cfg->js_ot_name}.saveRemovedRightOptions("{$opt_cfg->js_ot_name}_removedRight");
{$opt_cfg->js_ot_name}.saveAddedLeftOptions("{$opt_cfg->js_ot_name}_addedLeft");
{$opt_cfg->js_ot_name}.saveAddedRightOptions("{$opt_cfg->js_ot_name}_addedRight");
{$opt_cfg->js_ot_name}.saveNewLeftOptions("{$opt_cfg->js_ot_name}_newLeft");
{$opt_cfg->js_ot_name}.saveNewRightOptions("{$opt_cfg->js_ot_name}_newRight");
</script>

<script type="text/javascript">
//BUGID 3943: Escape all messages (string)
var warning_empty_testcase_name = "{$labels.warning_empty_tc_title|escape:'javascript'}";
var alert_box_title = "{$labels.warning|escape:'javascript'}";

{literal}        
/**
 * validate certain form controls before submitting
 *
 */
function validateForm(the_form)
{
    var status_ok = true;
	
	if (isWhitespace(the_form.testcase_name.value))
	{
		alert_message(alert_box_title,warning_empty_testcase_name);
		selectField(the_form,'testcase_name');
		return false;
	}
	var cf_designTime = document.getElementById('cfields_design_time');
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

        // 20090421 - franciscom - BUGID 
 		cfields_container = cf_designTime.getElementsByTagName('textarea');
 		cfieldsChecks = validateCustomFields(cfields_container);
		if(!cfieldsChecks.status_ok)
	  	{
	    	var warning_msg = cfMessages[cfieldsChecks.msg_id];
	      	alert_message(alert_box_title,warning_msg.replace(/%s/, cfieldsChecks.cfield_label));
	      	return false;
		}
	}
	return Ext.ux.requireSessionAndSubmit(the_form);
}
{/literal}
</script>

{if $tlCfg->gui->checkNotSaved}
  <script type="text/javascript">
  var unload_msg = "{$labels.warning_unsaved|escape:'javascript'}";
  var tc_editor = "{$gui->editorType}";
  </script>
  <script src="gui/javascript/checkmodified.js" type="text/javascript"></script>
{/if}
</head>

<body onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0]);focusInputField('testcase_name')">
{config_load file="input_dimensions.conf" section="tcNew"}
<h1 class="title">{$labels.title_edit_tc}{$smarty.const.TITLE_SEP}{$gui->tc.name|escape}
	{$smarty.const.TITLE_SEP_TYPE3}{$labels.version} {$gui->tc.version}</h1> 

<div class="workBack" style="width:97%;">

{if $gui->has_been_executed}
    {lang_get s='warning_editing_executed_tc' var="warning_edit_msg"}
    <div class="messages" align="center">{$warning_edit_msg}</div>
{/if}

<form method="post" action="lib/testcases/tcEdit.php" name="tc_edit"
      onSubmit="return validateForm(this);">

	<input type="hidden" name="testsuite_id" id="testsuite_id" value="{$gui->tc.testsuite_id}" />
	<input type="hidden" name="testcase_id" id="testcase_id" value="{$gui->tc.testcase_id}" />
	<input type="hidden" name="tcversion_id" value="{$gui->tc.id}" />
	<input type="hidden" name="version" value="{$gui->tc.version}" />
	<input type="hidden" name="doAction" value="" />
  	<input type="hidden" name="show_mode" value="{$gui->show_mode}" />
	
	{* when save or cancel is pressed do not show modification warning *}
	<div class="groupBtn">
		<input id="do_update" type="submit" name="do_update" 
		       onclick="show_modified_warning=false; doAction.value='doUpdate'" value="{$labels.btn_save}" />
		
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="show_modified_warning=false; javascript: history.back();"/>
	</div>	
	{include file="testcases/tcEdit_New_viewer.tpl"}
	
	{* when save or cancel is pressed do not show modification warning *}
	<div class="groupBtn">
		<input id="do_update" type="submit" name="do_update" 
		       onclick="show_modified_warning=false; doAction.value='doUpdate'" value="{$labels.btn_save}" />
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="show_modified_warning=false; javascript: history.back();"/>
	</div>	
</form>

<script type="text/javascript" defer="1">
   	document.forms[0].testcase_name.focus();
</script>

{if isset($gui->refreshTree) && $gui->refreshTree}
	{include file="inc_refreshTreeWithFilters.tpl"}
{/if}

</div>
</body>
</html>
