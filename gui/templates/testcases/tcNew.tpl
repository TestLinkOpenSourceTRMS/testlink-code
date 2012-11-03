{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	tcNew.tpl
create new testcase
*}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var='labels' s='btn_create,cancel,warning,title_new_tc,warning_required_cf,
                          warning_empty_tc_title,stay_here_tc,warning_unsaved'}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes"}
{include file="inc_ext_js.tpl"}

<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
<script language="javascript" src="gui/javascript/tcase_utils.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
var {$gui->optionTransfer->jsName} = setUpOptionTransferEngine('{$gui->optionTransferJSObject}');
</script>

<script type="text/javascript">
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_testcase_name = "{$labels.warning_empty_tc_title|escape:'javascript'}";
var warning_required_cf = "{$labels.warning_required_cf|escape:'javascript'}";


function validateForm(f)
{
  if (isWhitespace(f.testcase_name.value)) 
  {
      alert_message(alert_box_title,warning_empty_testcase_name);
      selectField(f, 'testcase_name');
      return false;
  }
  
  if(!checkCustomFields('cfields_design_time',alert_box_title,warning_required_cf))
  {
  	return false;
  }

  return true;
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

<h1 class="title">{$gui->main_descr|escape}</h1>
<div class="workBack">

{include file="inc_update.tpl" name=$gui->name user_feedback=$gui->user_feedback 
         refresh=$smarty.session.setting_refresh_tree_on_action}

<form method="post" action="lib/testcases/tcEdit.php"
      name="tc_new" id="tc_new"
      onSubmit="javascript:return validateForm(this);">
      <input type="hidden" name="testcase_id" id="testcase_id" value=0>
      <input type="hidden" name="tsuiteID" id="tsuiteID" value="{$gui->tsuiteID}">
      <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">

  {if $gui->steps != ''}
  <table class="simple">
  	<tr>
  		<th width="{$gui->tableColspan}">{$labels.step_number}</th>
  		<th>{$labels.step_details}</th>
  		<th>{$labels.expected_results}</th>
  		<th width="25">{$labels.execution_type_short_descr}</th>
  	</tr>
  
   	{foreach from=$gui->steps item=step_info}
  	<tr>
  		<td style="text-align:righ;">{$step_info.step_number}</td>
  		<td >{$step_info.actions}</td>
  		<td >{$step_info.expected_results}</td>
  		<td>{$gui->execution_types[$step_info.execution_type]}</td>
  	</tr>
    {/foreach}	
  </table>	
  <p>
  <hr>
  {/if}

	<div class="groupBtn">
    <input type="hidden" id="do_create"  name="do_create" value="do_create" />
		<input type="submit" id="do_create_button"  name="do_create_button" value="{$labels.btn_create}" 
		       onclick="show_modified_warning=false;" />
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="javascript: show_modified_warning=false; history.back();"/>
	</div>	
	<div class="groupBtn">
			<input type="checkbox" id="stay_here"  name="stay_here" 
						{if $gui->stay_here} checked="checked" {/if}/>{$labels.stay_here_tc}
	</div>	
	{include file="testcases/tcEdit_New_viewer.tpl"}

	<div class="groupBtn">
		<input type="hidden" id="do_create_2"  name="do_create" value="do_create" />
		<input type="submit" id="do_create_button_2"  name="do_create_button" value="{$labels.btn_create}" 
			     onclick="show_modified_warning=false;" />
	  <input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="javascript: show_modified_warning=false; history.back();"/>
	</div>	
  
</form>
</div>

{if isset($gui->refreshTree) && $gui->refreshTree} {$tlRefreshTreeJS} {/if}
</body>
</html>