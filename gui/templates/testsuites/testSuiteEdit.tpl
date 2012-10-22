{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource	testSuiteEdit.tpl
Purpose: smarty template - edit test specification: test suites 

@internal revisions
@since 2.0

*}
{lang_get var="labels"
          s='warning_empty_testsuite_name,title_edit_level,btn_save,tc_keywords,cancel,warning,warning_required_cf,
             warning_unsaved,comp_name,details'}
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
  if (isWhitespace(f.testsuiteName.value)) 
  {
      alert_message(alert_box_title,warning_empty_container_name);
      selectField(f, 'testsuiteName');
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
<h1 class="title">{lang_get s=$gui->containerType}{$smarty.const.TITLE_SEP}{$gui->name|escape}</h1> 
<div class="workBack">

  {if $gui->midAirCollision}
	  {include file = "midAirCollisionMessage.inc.tpl" mdcArgsMain = "{$gui->midAirCollisionMsg.main}" 
	                                                   mdcArgsDetails = "{$gui->midAirCollisionMsg.details}" }
  {/if}
  <h1 class="title">{$labels.title_edit_level} {lang_get s=$gui->containerType}</h1> 
	<form method="post" action="lib/testsuites/testSuiteEdit.php>
	      name="container_edit" id="container_edit"
        onSubmit="javascript:return validateForm(this);">
	
	<div>
		<input type="hidden" name="testsuiteID" id="testsuiteID" value="{$gui->id}">
	  <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}"> 
		<input type="submit" name="update_testsuite" value="{$labels.btn_save}" 
		       onclick="show_modified_warning = false;" />
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="javascript: show_modified_warning = false; history.back();"/>
		       
		<input type="hidden" name="midAirCollisionTimeStamp" id="midAirCollisionTimeStamp" 
		                     value = "{$gui->tsuite.modification_ts}"       
	</div>
  {include file="testsuites/testSuiteViewerRW.inc.tpl"}
  {if $gui->cf != ""} {* Custom fields *}
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
{if $gui->refreshTree}{$tlRefreshTreeJS}{/if}
</body>
</html>