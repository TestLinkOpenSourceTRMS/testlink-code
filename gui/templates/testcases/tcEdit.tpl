{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tcEdit.tpl,v 1.16 2009/07/18 14:42:22 franciscom Exp $ 
Purpose: smarty template - edit test specification: test case

rev: 20090422 - franciscom - BUGID 2414
     20090419 - franciscom - BUGID  - edit while executing
*}

{lang_get var="labels"
          s="warning,warning_empty_tc_title,btn_save,cancel,warning_unsaved"}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes" editorType=$gui->editorType}

{include file="inc_del_onclick.tpl"}
<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>


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
var UNLOAD_MSG = "{$labels.warning_unsaved}";
var TC_EDITOR = "{$tlCfg->gui->text_editor.all.type}";
{literal}
function validateForm(f)
{
    var status_ok = true;
	
  	if (isWhitespace(f.testcase_name.value)) 
  	{
    	  alert_message(alert_box_title,warning_empty_testcase_name);
		    selectField(f,'testcase_name');
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
	  return true;
}
</script>

<script type="text/javascript">
// @TODO - Always ADD on internal revisions NEW FEATURES, thing that has not been do
// 
// Notify on exit with unsaved data 
// @TODO use EXTJS dialog

var IGNORE_UNLOAD = true;

function doBeforeUnload() 
{
   checkFCKEditorChanged(); //check FCKeditors 
   if(IGNORE_UNLOAD) return; // Let the page unload

   if(window.event)
   {
      window.event.returnValue = UNLOAD_MSG; // IE
   }
   else
   {
      return UNLOAD_MSG; // FX
   }   
}

if(window.body)
{
   window.body.onbeforeunload = doBeforeUnload; // IE
}
else
{
   window.onbeforeunload = doBeforeUnload; // FX
}

// verify if content of any editor changed
function checkFCKEditorChanged()
{
	if (TC_EDITOR == "fckeditor")
	{
		var edSummary = FCKeditorAPI.GetInstance('summary') ;
		var edSteps = FCKeditorAPI.GetInstance('steps') ;
		var edExpResults = FCKeditorAPI.GetInstance('expected_results') ;

		if(edSummary.IsDirty() || edSteps.IsDirty() || edExpResults.IsDirty()) 
		{
		  // ABSOLUTELY BAD naming convention, why has to be UPPER CASE ????
			IGNORE_UNLOAD = false;
		}	
	}
}
{/literal}
</script>

</head>

<body onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0]);focusInputField('testcase_name')">
{config_load file="input_dimensions.conf" section="tcNew"}
<h1 class="title">{lang_get s='title_edit_tc'}{$smarty.const.TITLE_SEP}{$tc.name|escape}
	{$smarty.const.TITLE_SEP_TYPE3}{lang_get s='version'} {$tc.version}</h1> 

<div class="workBack" style="width:97%;">

{if $has_been_executed}
    {lang_get s='warning_editing_executed_tc' var="warning_edit_msg"}
    <div class="messages" align="center">{$warning_edit_msg}</div>
{/if}

<form method="post" action="lib/testcases/tcEdit.php" name="tc_edit"
      onSubmit="javascript:return validateForm(this);">

	<input type="hidden" name="testcase_id" value="{$tc.testcase_id}" />
	<input type="hidden" name="tcversion_id" value="{$tc.id}" />
	<input type="hidden" name="version" value="{$tc.version}" />
	<input type="hidden" name="doAction" value="" />
  <input type="hidden" name="show_mode" value="{$gui->show_mode}" />

	<div class="groupBtn">
		<input id="do_update" type="submit" name="do_update" 
		       onclick="doAction.value='doUpdate'"  value="{$labels.btn_save}" />
		
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="javascript: history.back();"/>
	</div>	

	{assign var=this_template_dir value=$smarty.template|dirname}
	{include file="$this_template_dir/tcEdit_New_viewer.tpl"}
	<div class="groupBtn">
		<input id="do_update" type="submit" name="do_update" 
		       onclick="IGNORE_UNLOAD = true; doAction.value='doUpdate'"   value="{$labels.btn_save}" />
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="javascript: history.back();"/>
	</div>	
</form>

<script type="text/javascript" defer="1">
   	document.forms[0].testcase_name.focus();
</script>

</div>
</body>
</html>
