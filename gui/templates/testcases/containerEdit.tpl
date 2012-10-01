{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource	containerEdit.tpl
Purpose: smarty template - edit test specification: containers 

@internal revisions
@since 2.0

*}
{lang_get var="labels"
          s='warning_empty_testsuite_name,title_edit_level,btn_save,tc_keywords,cancel,warning,warning_required_cf,
          warning_unsaved'}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes" editorType=$editorType}
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
  var tc_editor = "{$editorType}";
  </script>
  <script src="gui/javascript/checkmodified.js" type="text/javascript"></script>
{/if}

</head>
<body onLoad="{$gui->optionTransfer->jsName}.init(document.forms[0]);focusInputField('name')">
<h1 class="title">{lang_get s=$gui->containerType}{$smarty.const.TITLE_SEP}{$gui->name|escape}</h1> 
<div class="workBack">
  <h1 class="title">{$labels.title_edit_level} {lang_get s=$gui->containerType}</h1> 
	<form method="post" action="lib/testcases/containerEdit.php?testsuiteID={$gui->containerID}&tproject_id={$gui->tproject_id}" 
	      name="container_edit" id="container_edit"
        onSubmit="javascript:return validateForm(this);">
	
	<div>
		<input type="submit" name="update_testsuite" value="{$labels.btn_save}" 
		       onclick="show_modified_warning = false;" />
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="javascript: show_modified_warning = false; history.back();"/>
	</div>
	 
	 {include file="testcases/inc_testsuite_viewer_rw.tpl"}

   {* Custom fields *}
   {if $gui->cf != ""}
     <p>
     <div id="cfields_design_time" class="custom_field_container">
     {$gui->cf}
     </div>
     <p>
   {/if}
   
  <div>
   <a href={$gui->keywordsViewHREF}>{$labels.tc_keywords}</a>
	 {include file="opt_transfer.inc.tpl" option_transfer=$gui->optionTransfer}
	 </div>
	<br></br>
	<div>
		<input type="submit" name="update_testsuite" value="{$labels.btn_save}"
		       onclick="show_modified_warning = false;" />
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="javascript: show_modified_warning = false; history.back();"/>
	</div>
	</form>
</div>

</body>
</html>