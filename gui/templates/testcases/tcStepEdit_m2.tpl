{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tcStepEdit_m2.tpl,v 1.1 2010/03/28 17:40:32 franciscom Exp $ 
Purpose: create/edit test case step

rev:
	20100327 - franciscom - improvements on goback logic
	20100125 - franciscom - fixed bug on checks on existence of step number
	20100124 - eloff - BUGID 3088 - Check valid session before submit
	20100123 - franciscom - checks on existence of step number
	20100111 - eloff - BUGID 2036 - Check modified content before exit

*}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}


{assign var="module" value='lib/testcases/'}
{assign var="tcase_id" value=$gui->tcase_id}
{assign var="tcversion_id" value=$gui->tcversion_id}

{* Used on several operations to implement goback *}
{assign var="tcViewAction" value="lib/testcases/archiveData.php?tcase_id=$tcase_id"}
{assign var="goBackAction" value="$basehref$tcViewAction"}

{assign var="url_args" value="tcEdit.php?doAction=editStep&testcase_id=$tcase_id&tcversion_id=$tcversion_id"}
{assign var="url_args" value="$url_args&goback_url=$basehref$tcViewAction&step_id="}
{assign var="hrefEditStep"  value="$basehref$module$url_args"}

{lang_get var="labels"
          s="warning_step_number_already_exists,warning,warning_step_number,
             expected_results,step_details,step_number_verbose,btn_cancel,
             btn_save,cancel,warning_unsaved,step_number,execution_type_short_descr"}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes" editorType=$gui->editorType}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript" src="gui/javascript/ext_extensions.js" language="javascript"></script>
<script type="text/javascript">
var warning_step_number = "{$labels.warning_step_number}";
var alert_box_title = "{$labels.warning}";
var warning_step_number_already_exists = "{$labels.warning_step_number_already_exists}";

{literal}
function validateForm(the_form,step_set,step_number_on_edit)
{
	var value = '';
	var status_ok = true;
	var feedback = '';
	var value_found_on_set=false;
	var value_step_mistmatch=false;
	value = parseInt(the_form.step_number.value);

	if( isNaN(value) || value <= 0)
	{
		alert_message(alert_box_title,warning_step_number);
		selectField(the_form,'step_number');
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
		  selectField(the_form,'step_number');
		  return false;
		}
  }
	show_modified_warning=false;
	return Ext.ux.requireSessionAndSubmit(the_form);
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
	<input type="hidden" name="step_number" value="{$gui->step_number}" />
	<input type="hidden" name="goback_url" value="{$goBackAction}" />


  <table class="simple">
  	<tr>
  		<th width="{$gui->tableColspan}">{$labels.step_number}</th>
  		<th>{$labels.step_details}</th>
  		<th>{$labels.expected_results}</th>
      {if $session['testprojectOptions']->automationEnabled}
  		  <th width="25">{$labels.execution_type_short_descr}</th>
  		{/if}  
  	</tr>
  
  {if $gui->steps != ''}
   	{foreach from=$gui->steps item=step_info }
  	  <tr>
      {if $step_info.step_number == $gui->step_number}
		    <td style="text-align:righ;">{$gui->step_number}</td>
  		  <td>{$steps}</td>
  		  <td>{$expected_results}</td>
		    {if $session['testprojectOptions']->automationEnabled}
		    <td>
		    	<select name="exec_type" onchange="content_modified = true">
        	  	{html_options options=$gui->execution_types selected=$gui->step_exec_type}
	        </select>
      	</td>
      	{/if}
      {else}
        <td style="text-align:righ;"><a href="{$hrefEditStep}{$step_info.id}">{$step_info.step_number}</a></td>
  	  	<td ><a href="{$hrefEditStep}{$step_info.id}">{$step_info.actions}</a></td>
  	  	<td >{$step_info.expected_results}</td>
        {if $session['testprojectOptions']->automationEnabled}
  	  	  <td>{$gui->execution_types[$step_info.execution_type]}</td>
  	  	{/if}  
      {/if}
  	  </tr>
    {/foreach}
  {/if}
  {if $gui->action == 'createStep' || $gui->action == 'doCreateStep'}
  	<tr>
		  <td style="text-align:righ;">{$gui->step_number}</td>
  		<td>{$steps}</td>
  		<td>{$expected_results}</td>
		    {if $session['testprojectOptions']->automationEnabled}
		    <td>
		    	<select name="exec_type" onchange="content_modified = true">
        	  	{html_options options=$gui->execution_types selected=$gui->step_exec_type}
	        </select>
      	</td>
      	{/if}
  	</tr>
  {/if}
  </table>	
  <p>
  <hr>


	<div class="groupBtn">
		<input id="do_update" type="submit" name="do_update" 
		       onclick="doAction.value='{$gui->operation}'" value="{$labels.btn_save}" />
  	<input type="button" name="cancel" value="{$labels.btn_cancel}"
    	     {if $gui->goback_url != ''}  onclick="location='{$gui->goback_url}'"
    	     {else}  onclick="javascript:history.back();" {/if} />
	</div>	
</form>

<script type="text/javascript" defer="1">
   	document.forms[0].step_number.focus();
</script>

</div>
</body>
</html>
