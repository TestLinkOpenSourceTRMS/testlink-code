{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tcEdit.tpl,v 1.26 2010/03/06 16:43:14 erikeloff Exp $ 
Purpose: smarty template - edit test specification: test case

@internal Revisions:
	20100306 - eloff - BUGID 3062 - Check for duplicate name
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
var warning_empty_testcase_name = "{$labels.warning_empty_tc_title}";
var alert_box_title = "{$labels.warning}";

{literal}
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
	show_modified_warning=false;
	return Ext.ux.requireSessionAndSubmit(the_form);
}

function checkDuplicateName() {
	Ext.Ajax.request({
		url: 'lib/ajax/checkDuplicateName.php',
		method: 'GET',
		params: {
			testcase_id: $('testcase_id').value,
			name: $('testcase_name').value
		},
		success: function(result, request) {
			var obj = Ext.util.JSON.decode(result.responseText);
			$("testcase_name_warning").innerHTML = obj['message'];
		},
		failure: function (result, request) {
		}
	});
}

{/literal}
</script>
{if $tlCfg->gui->checkNotSaved}
<script type="text/javascript">
var unload_msg = "{$labels.warning_unsaved}";
var tc_editor = "{$tlCfg->gui->text_editor.all.type}";
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

	<input type="hidden" name="testcase_id" id="testcase_id" value="{$gui->tc.testcase_id}" />
	<input type="hidden" name="tcversion_id" value="{$gui->tc.id}" />
	<input type="hidden" name="version" value="{$gui->tc.version}" />
	<input type="hidden" name="doAction" value="" />
  	<input type="hidden" name="show_mode" value="{$gui->show_mode}" />

	<div class="groupBtn">
		<input id="do_update" type="submit" name="do_update" 
		       onclick="doAction.value='doUpdate'" value="{$labels.btn_save}" />
		
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="history.back();"/>
	</div>	

	{assign var=this_template_dir value=$smarty.template|dirname}
	{include file="$this_template_dir/tcEdit_New_viewer.tpl"}
	
	<div class="groupBtn">
		<input id="do_update" type="submit" name="do_update" 
		       onclick="doAction.value='doUpdate'" value="{$labels.btn_save}" />
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="history.back();"/>
	</div>	
</form>

<script type="text/javascript" defer="1">
   	document.forms[0].testcase_name.focus();
</script>

</div>
</body>
</html>
