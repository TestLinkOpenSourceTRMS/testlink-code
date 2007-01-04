{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: containerEdit.tpl,v 1.16 2007/01/04 15:27:58 franciscom Exp $
Purpose: smarty template - edit test specification: containers 

20061230 - franciscom - added custom field management
                        removed TL 1.6 useless code
*}
{include file="inc_head.tpl" openHead='yes' jsValidate="yes"}

<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
var {$opt_cfg->js_ot_name} = new OptionTransfer("{$opt_cfg->from->name}","{$opt_cfg->to->name}");
{$opt_cfg->js_ot_name}.saveRemovedLeftOptions("{$opt_cfg->js_ot_name}_removedLeft");
{$opt_cfg->js_ot_name}.saveRemovedRightOptions("{$opt_cfg->js_ot_name}_removedRight");
{$opt_cfg->js_ot_name}.saveAddedLeftOptions("{$opt_cfg->js_ot_name}_addedLeft");
{$opt_cfg->js_ot_name}.saveAddedRightOptions("{$opt_cfg->js_ot_name}_addedRight");
{$opt_cfg->js_ot_name}.saveNewLeftOptions("{$opt_cfg->js_ot_name}_newLeft");
{$opt_cfg->js_ot_name}.saveNewRightOptions("{$opt_cfg->js_ot_name}_newRight");
</script>

{literal}
<script type="text/javascript">
{/literal}
var warning_empty_container_name = "{lang_get s='warning_empty_testsuite_name'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.container_name.value)) 
  {
      alert(warning_empty_container_name);
      selectField(f, 'container_name');
      return false;
  }
  return true;
}
</script>
{/literal}


</head>

<body onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0])">
{config_load file="input_dimensions.conf" section="containerEdit"} {* Constant definitions *}
<h1>{lang_get s=$level}{$smarty.const.TITLE_SEP}{$name|escape}</h1> 

<div class="workBack">
  <h1>{lang_get s='title_edit_level'} {lang_get s=$level}</h1> 
	<form method="post" action="lib/testcases/containerEdit.php?testsuiteID={$containerID}" 
	      name="container_edit" id="container_edit"
        onSubmit="javascript:return validateForm(this);">

		<div style="float: right;">
			<input type="submit" name="update_testsuite" value="{lang_get s='btn_update_testsuite'}" />
		</div>
   {include file="inc_testsuite_viewer_rw.tpl"}

   {* Custom fields *}
   {if $cf neq ""}
     <p>
     <div class="custom_field_container">
     {$cf}
     </div>
     <p>
   {/if}
   
  <div>
   <a href={$gsmarty_href_keywordsView}>{lang_get s='tc_keywords'}</a>
	 {include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
	 </div>

	</form>
</div>

</body>
</html>