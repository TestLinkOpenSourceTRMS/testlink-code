{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource tcEdit.tpl
Purpose: smarty template - edit test specification: test case

@internal revisions
20111016 - franciscom - logic to check estimated_execution_duration
*}

{lang_get var="labels"
          s="warning,warning_empty_tc_title,btn_save,warning_required_cf,
             version,title_edit_tc,cancel,warning_unsaved,warning_estimated_execution_duration_format"}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes" editorType=$gui->editorType}
{include file="inc_ext_js.tpl"}

<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
<script language="javascript" src="gui/javascript/ext_extensions.js" type="text/javascript"></script>
<script language="javascript" src="gui/javascript/tcase_utils.js" type="text/javascript"></script>

<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
var {$gui->optionTransfer->jsName} = setUpOptionTransferEngine('{$gui->optionTransferJSObject}');
</script>


<script type="text/javascript">
var warning_empty_testcase_name = "{$labels.warning_empty_tc_title|escape:'javascript'}";
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_required_cf = "{$labels.warning_required_cf|escape:'javascript'}";
var warning_estimated_execution_duration_format = "{$labels.warning_estimated_execution_duration_format|escape:'javascript'}";

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
	
	val2check = the_form.estimated_execution_duration.value;
	if( isNaN(val2check) || /^\s+$/.test(val2check.trim()))
	{
		alert_message(alert_box_title,warning_estimated_execution_duration_format);
		return false;
	}
	
	if(!checkCustomFields('cfields_design_time',alert_box_title,warning_required_cf))
	{
		return false;
	}

	return Ext.ux.requireSessionAndSubmit(the_form);
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

<body onLoad="{$gui->optionTransfer->jsName}.init(document.forms[0]);focusInputField('testcase_name')">
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
	<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
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

{if isset($gui->refreshTree) && $gui->refreshTree} {$tlRefreshTreeJS} {/if}
</div>
</body>
</html>