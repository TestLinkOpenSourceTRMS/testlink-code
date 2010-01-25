{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tcStepEdit.tpl,v 1.6 2010/01/25 20:37:17 franciscom Exp $ 
Purpose: create/edit test case step

rev: 
  20100125 - franciscom - fixed bug on checks on existence of step number
  20100123 - franciscom - checks on existence of step number
  20100111 - eloff - BUGID 2036 - Check modified content before exit
     
*}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels"
          s="warning_step_number_already_exists,warning,warning_step_number,
             btn_save,cancel,warning_unsaved"}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes" editorType=$gui->editorType}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
var warning_step_number = "{$labels.warning_step_number}";
var alert_box_title = "{$labels.warning}";
var warning_step_number_already_exists = "{$labels.warning_step_number_already_exists}";

{literal}
function validateForm(f,step_set,step_number_on_edit)
{
  var value = '';
  var status_ok = true;
  var feedback = '';
  var value_found_on_set=false;
  var value_step_mistmatch=false;
	value = parseInt(f.step_number.value);

	if( isNaN(value) || value <= 0)
	{
		alert_message(alert_box_title,warning_step_number);
		selectField(f,'step_number');
		return false;
	}

  // check is step number is free/available
  // alert('#1# - step_set:' + step_set + ' - step_set.length:' + step_set.length);
  // alert('#2# - step_numver.value:' + value + ' - step_number_on_edit:' + step_number_on_edit);
  if( step_set.length > 0 )
  {
    value_found_on_set = (step_set.indexOf(value) >= 0);
    value_step_mistmatch = (value != step_number_on_edit);
    // alert('#3# - value_found_on_set:' + value_found_on_set + ' - value_step_mistmatch:' + value_step_mistmatch);

    if(value_found_on_set && value_step_mistmatch)
    {
      feedback = warning_step_number_already_exists.replace('%s',value);
 	    alert_message(alert_box_title,feedback);
		  selectField(f,'step_number');
		  return false;
		}
  }
	return true;
}
{/literal}
</script>
{if $tlCfg->gui->checkNotSaved}
<script type="text/javascript">
var UNLOAD_MSG = "{$labels.warning_unsaved}";
var TC_EDITOR = "{$tlCfg->gui->text_editor.all.type}";
</script>
<script src="gui/javascript/checkmodified.js" type="text/javascript"></script>
{/if}
</head>

<body onLoad="focusInputField('step')">
<h1 class="title">{$gui->main_descr}</h1> 

<div class="workBack" style="width:97%;">

{if $gui->user_feedback != ''}
	<div>
		<p class="info">{$gui->user_feedback}</p>
	</div>
{/if}

{if $gui->has_been_executed}
    {lang_get s='warning_editing_executed_step' var="warning_edit_msg"}
    <div class="messages" align="center">{$warning_edit_msg}</div>
{/if}

<form method="post" action="lib/testcases/tcEdit.php" name="tcStepEdit"
      onSubmit="return validateForm(this,'{$gui->step_set}',{$gui->step_number});">
	<input type="hidden" name="testcase_id" value="{$gui->tcase_id}" />
	<input type="hidden" name="tcversion_id" value="{$gui->tcversion_id}" />
	<input type="hidden" name="doAction" value="" />
 	<input type="hidden" name="show_mode" value="{$gui->show_mode}" />
	<input type="hidden" name="step_id" value="{$gui->step_id}" />

	<div class="groupBtn">
		<input id="do_update" type="submit" name="do_update" 
		       onclick="doAction.value='{$gui->operation}'" value="{$labels.btn_save}" />
		
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="history.back();"/>
	</div>	

	{assign var=this_template_dir value=$smarty.template|dirname}
	{include file="$this_template_dir/tcStepEditViewer.tpl"}
	
	<div class="groupBtn">
		<input id="do_update" type="submit" name="do_update" 
		       onclick="doAction.value='{$gui->operation}'" value="{$labels.btn_save}" />
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="history.back();"/>
	</div>	
</form>

<script type="text/javascript" defer="1">
   	document.forms[0].step_number.focus();
</script>

</div>
</body>
</html>
