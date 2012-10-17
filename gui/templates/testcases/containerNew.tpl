{* 
TestLink Open Source Project - http://testlink.sourceforge.net/

@filesource	containerNew.tpl
@internal revisions
@since 2.0

*}
{lang_get var="labels"
          s="warning_empty_testsuite_name,title_create,tc_keywords,warning_required_cf,
             warning,btn_create_testsuite,cancel,warning_unsaved,details,comp_name"}

{config_load file="input_dimensions.conf" section="containerEdit"}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes"}
{include file="inc_ext_js.tpl"}

<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
var {$gui->optionTransfer->jsName} = setUpOptionTransferEngine('{$gui->optionTransferJSObject}');
</script>


<script type="text/javascript">
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

<body onLoad="{$gui->optionTransfer->jsName}.init(document.forms[0]);focusInputField('name')">
<h1 class="title">{$gui->parent_info.description}{$smarty.const.TITLE_SEP}{$gui->parent_info.name|escape}</h1>

<div class="workBack">
<h1 class="title">{$labels.title_create} {lang_get s=$gui->containerType}</h1>
	
{include file="inc_update.tpl" result=$sqlResult 
                               user_feedback=$gui->user_feedback
                               item=$gui->containerType action="add" name=$gui->name
                               refresh=$gui->refreshTree}


<form method="post" 
      action="lib/testcases/containerEdit.php?containerID={$gui->containerID}&tproject_id={$gui->tproject_id}" 
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
   {if $gui->cf != ""}
     <br />
     <div id="cfields_design_time" class="custom_field_container">
     {$gui->cf}
     </div>
   {/if}
   	 <br />
   <div>
   <a href={$gui->keywordsViewHREF}>{$labels.tc_keywords}</a>
	 {include file="opt_transfer.inc.tpl" option_transfer=$gui->optionTransfer}
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